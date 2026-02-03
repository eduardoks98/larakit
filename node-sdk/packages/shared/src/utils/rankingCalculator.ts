// ==========================================
// RANKING CALCULATOR - Sistema LP + MMR
// Inspirado no League of Legends
// ==========================================

import {
  TIERS,
  TIERS_WITHOUT_DIVISIONS,
  type Tier,
  type Division,
  type RankInfo,
  type RankingInput,
  type RankingResult,
  type PerformanceMetrics,
} from '../types/ranking.types';

// ==========================================
// CONSTANTES
// ==========================================

export const LP_CONFIG = {
  MAX_LP: 100,
  BASE_LP_WIN: 22,
  BASE_LP_LOSS: 18,
  MIN_LP_CHANGE: 10,
  MAX_LP_CHANGE: 50,
  DEMOTION_SHIELD_GAMES: 3,
  QUITTER_PENALTY_MULTIPLIER: 2.0,
};

export const MMR_CONFIG = {
  DEFAULT_MMR: 0,
  K_FACTOR: 32,
  MIN_MMR: 0,
  MAX_MMR: 3000,
};

export const TIER_MMR_RANGES: Record<Tier, { min: number; max: number }> = {
  Bronze: { min: 0, max: 399 },
  Silver: { min: 400, max: 799 },
  Gold: { min: 800, max: 1199 },
  Platinum: { min: 1200, max: 1599 },
  Diamond: { min: 1600, max: 1999 },
  Master: { min: 2000, max: 2199 },
  Grandmaster: { min: 2200, max: 2399 },
  Challenger: { min: 2400, max: 3000 },
};

// ==========================================
// FUNÇÕES AUXILIARES
// ==========================================

/**
 * Converte divisão numérica para romano
 */
export function divisionToRoman(division: Division): string {
  if (division === null) return '';
  const map: Record<number, string> = { 4: 'IV', 3: 'III', 2: 'II', 1: 'I' };
  return map[division] || '';
}

/**
 * Retorna o display name do rank
 */
export function getDisplayRank(tier: Tier | string, division: Division): string {
  const tierStr = tier as Tier;
  if (TIERS_WITHOUT_DIVISIONS.includes(tierStr)) {
    return tierStr;
  }
  return `${tierStr} ${divisionToRoman(division)}`;
}

/**
 * Obtém o tier baseado no MMR
 */
export function getTierFromMmr(mmr: number): Tier {
  for (const tier of [...TIERS].reverse()) {
    if (mmr >= TIER_MMR_RANGES[tier].min) {
      return tier;
    }
  }
  return 'Bronze';
}

/**
 * Obtém tier e divisão baseado no MMR
 */
export function getRankFromMmr(mmr: number): { tier: Tier; division: Division } {
  const tier = getTierFromMmr(mmr);

  if (TIERS_WITHOUT_DIVISIONS.includes(tier)) {
    return { tier, division: null };
  }

  const range = TIER_MMR_RANGES[tier];
  const tierRange = range.max - range.min;
  const divisionRange = tierRange / 4;
  const mmrInTier = mmr - range.min;
  const divisionIndex = Math.min(3, Math.floor(mmrInTier / divisionRange));
  const division = (4 - divisionIndex) as Division;

  return { tier, division };
}

function getExpectedPosition(playerMmr: number, allMmr: number[], totalPlayers: number): number {
  const sortedMmr = [...allMmr].sort((a, b) => b - a);
  const position = sortedMmr.findIndex(m => m === playerMmr) + 1;
  return position || Math.ceil(totalPlayers / 2);
}

/**
 * Calcula score de performance (0.0 a 1.0)
 */
export function calculatePerformanceScore(metrics: PerformanceMetrics): number {
  let score = 0.5;

  const kda = metrics.deaths > 0
    ? (metrics.kills / metrics.deaths)
    : metrics.kills > 0 ? 2.0 : 1.0;

  if (kda >= 2.0) score += 0.15;
  else if (kda >= 1.5) score += 0.10;
  else if (kda >= 1.0) score += 0.05;
  else if (kda < 0.5) score -= 0.10;

  if (metrics.totalRounds > 0) {
    const roundWinRate = metrics.roundsWon / metrics.totalRounds;
    if (roundWinRate >= 0.7) score += 0.10;
    else if (roundWinRate >= 0.5) score += 0.05;
    else if (roundWinRate < 0.3) score -= 0.05;
  }

  const damageRatio = metrics.damageTaken > 0
    ? metrics.damageDealt / metrics.damageTaken
    : metrics.damageDealt > 0 ? 2.0 : 1.0;

  if (damageRatio >= 2.0) score += 0.10;
  else if (damageRatio >= 1.5) score += 0.05;
  else if (damageRatio < 0.5) score -= 0.05;

  if (metrics.itemsUsed >= 5) score += 0.05;
  else if (metrics.itemsUsed >= 3) score += 0.02;

  return Math.max(0, Math.min(1, score));
}

// ==========================================
// FUNÇÕES PRINCIPAIS
// ==========================================

/**
 * Calcula mudança de MMR
 */
export function calculateMmrChange(
  playerMmr: number,
  allPlayersMmr: number[],
  position: number,
  totalPlayers: number,
  performanceScore: number
): number {
  const expectedPosition = getExpectedPosition(playerMmr, allPlayersMmr, totalPlayers);

  const avgOpponentMmr = allPlayersMmr
    .filter(m => m !== playerMmr)
    .reduce((sum, m) => sum + m, 0) / (allPlayersMmr.length - 1 || 1);

  const skillDiff = avgOpponentMmr - playerMmr;
  const skillModifier = Math.max(0.5, Math.min(1.5, 1 + (skillDiff / 400)));

  const positionScore = 1 - ((position - 1) / (totalPlayers - 1)) * 2;

  const expectationModifier = position < expectedPosition ? 1.2 :
                              position > expectedPosition ? 0.8 : 1.0;

  const perfModifier = 0.8 + (performanceScore * 0.4);

  const mmrChange = Math.round(
    MMR_CONFIG.K_FACTOR * positionScore * skillModifier * expectationModifier * perfModifier
  );

  return mmrChange;
}

/**
 * Calcula LP baseado no MMR e posição
 */
export function calculateLpChange(input: RankingInput): RankingResult {
  const {
    currentTier,
    currentDivision,
    currentLp,
    currentMmr,
    gamesSincePromo,
    position,
    totalPlayers,
    allPlayersMmr,
    performanceScore,
    wasQuitter,
  } = input;

  const mmrChange = calculateMmrChange(
    currentMmr,
    allPlayersMmr,
    position,
    totalPlayers,
    performanceScore
  );

  const newMmr = Math.max(
    MMR_CONFIG.MIN_MMR,
    Math.min(MMR_CONFIG.MAX_MMR, currentMmr + mmrChange)
  );

  const isWin = position <= Math.ceil(totalPlayers / 2);
  let baseLp = isWin ? LP_CONFIG.BASE_LP_WIN : -LP_CONFIG.BASE_LP_LOSS;

  const expectedRank = getRankFromMmr(currentMmr);
  const currentTierIndex = TIERS.indexOf(currentTier as Tier);
  const expectedTierIndex = TIERS.indexOf(expectedRank.tier);

  let mmrModifier = 1.0;
  if (expectedTierIndex > currentTierIndex) {
    mmrModifier = 1.0 + (expectedTierIndex - currentTierIndex) * 0.15;
  } else if (expectedTierIndex < currentTierIndex) {
    mmrModifier = 1.0 - (currentTierIndex - expectedTierIndex) * 0.15;
  }

  const perfModifier = 0.8 + (performanceScore * 0.4);
  let lpChange = Math.round(baseLp * mmrModifier * perfModifier);

  if (wasQuitter && lpChange < 0) {
    lpChange = Math.round(lpChange * LP_CONFIG.QUITTER_PENALTY_MULTIPLIER);
  }

  if (lpChange > 0) {
    lpChange = Math.min(LP_CONFIG.MAX_LP_CHANGE, Math.max(LP_CONFIG.MIN_LP_CHANGE, lpChange));
  } else {
    lpChange = Math.max(-LP_CONFIG.MAX_LP_CHANGE, Math.min(-LP_CONFIG.MIN_LP_CHANGE, lpChange));
  }

  let newLp = currentLp + lpChange;
  let newTier = currentTier as Tier;
  let newDivision = currentDivision as Division;
  let promoted = false;
  let demoted = false;

  // Promoção
  if (newLp >= LP_CONFIG.MAX_LP) {
    const overflow = newLp - LP_CONFIG.MAX_LP;

    if (TIERS_WITHOUT_DIVISIONS.includes(newTier)) {
      const tierIndex = TIERS.indexOf(newTier);
      if (tierIndex < TIERS.length - 1) {
        newTier = TIERS[tierIndex + 1];
        newLp = overflow;
        promoted = true;
      } else {
        newLp = LP_CONFIG.MAX_LP;
      }
    } else if (newDivision !== null && newDivision > 1) {
      newDivision = (newDivision - 1) as Division;
      newLp = overflow;
      promoted = true;
    } else {
      const tierIndex = TIERS.indexOf(newTier);
      if (tierIndex < TIERS.length - 1) {
        newTier = TIERS[tierIndex + 1];
        if (TIERS_WITHOUT_DIVISIONS.includes(newTier)) {
          newDivision = null;
        } else {
          newDivision = 4;
        }
        newLp = overflow;
        promoted = true;
      } else {
        newLp = LP_CONFIG.MAX_LP;
      }
    }
  }

  // Rebaixamento
  if (newLp < 0) {
    if (gamesSincePromo < LP_CONFIG.DEMOTION_SHIELD_GAMES) {
      newLp = 0;
    } else {
      const deficit = Math.abs(newLp);

      if (TIERS_WITHOUT_DIVISIONS.includes(newTier)) {
        const tierIndex = TIERS.indexOf(newTier);
        if (tierIndex > 0) {
          newTier = TIERS[tierIndex - 1];
          if (!TIERS_WITHOUT_DIVISIONS.includes(newTier)) {
            newDivision = 1;
          }
          newLp = LP_CONFIG.MAX_LP - deficit;
          demoted = true;
        } else {
          newLp = 0;
        }
      } else if (newDivision !== null && newDivision < 4) {
        newDivision = (newDivision + 1) as Division;
        newLp = LP_CONFIG.MAX_LP - deficit;
        demoted = true;
      } else {
        const tierIndex = TIERS.indexOf(newTier);
        if (tierIndex > 0) {
          newTier = TIERS[tierIndex - 1];
          newDivision = 1;
          newLp = LP_CONFIG.MAX_LP - deficit;
          demoted = true;
        } else {
          newLp = 0;
        }
      }
    }
  }

  newLp = Math.max(0, Math.min(LP_CONFIG.MAX_LP, newLp));

  return {
    newTier,
    newDivision,
    newLp,
    newMmr,
    lpChange,
    mmrChange,
    promoted,
    demoted,
    displayRank: getDisplayRank(newTier, newDivision),
  };
}

/**
 * Retorna valores padrão para novo jogador
 */
export function getDefaultRankInfo(): RankInfo {
  return {
    tier: 'Bronze',
    division: 4,
    lp: 0,
    mmr: MMR_CONFIG.DEFAULT_MMR,
  };
}

/**
 * Compara dois ranks
 */
export function compareRanks(
  tier1: Tier | string, division1: Division, lp1: number,
  tier2: Tier | string, division2: Division, lp2: number
): number {
  const tierIndex1 = TIERS.indexOf(tier1 as Tier);
  const tierIndex2 = TIERS.indexOf(tier2 as Tier);

  if (tierIndex1 !== tierIndex2) {
    return tierIndex2 - tierIndex1;
  }

  const div1 = division1 ?? 0;
  const div2 = division2 ?? 0;

  if (div1 !== div2) {
    return div1 - div2;
  }

  return lp2 - lp1;
}
