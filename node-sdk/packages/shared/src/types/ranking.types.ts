// ==========================================
// RANKING TYPES - Sistema de LP + MMR
// ==========================================

/**
 * Tiers do sistema de ranking
 */
export const TIERS = ['Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Master', 'Grandmaster', 'Challenger'] as const;
export type Tier = typeof TIERS[number];

/**
 * Divisões dentro de um tier (4 = IV, 1 = I)
 */
export const DIVISIONS = [4, 3, 2, 1] as const;
export type Division = typeof DIVISIONS[number] | null;

/**
 * Tiers que não têm divisões
 */
export const TIERS_WITHOUT_DIVISIONS: Tier[] = ['Master', 'Grandmaster', 'Challenger'];

/**
 * Informações completas de ranking
 */
export interface RankInfo {
  /** Tier atual */
  tier: Tier;

  /** Divisão atual (null para Master+) */
  division: Division;

  /** LP atual (0-100) */
  lp: number;

  /** MMR oculto */
  mmr: number;
}

/**
 * Métricas de performance em uma partida
 */
export interface PerformanceMetrics {
  kills: number;
  deaths: number;
  roundsWon: number;
  totalRounds: number;
  damageDealt: number;
  damageTaken: number;
  itemsUsed: number;
}

/**
 * Input para cálculo de ELO
 */
export interface EloCalculationInput {
  playerElo: number;
  allPlayersElo: number[];
  playerPosition: number;
  totalPlayers: number;
  performance: PlayerPerformance;
  gameContext: GameContext;
}

/**
 * Performance de um jogador para cálculo de ELO
 */
export interface PlayerPerformance {
  damageDealt: number;
  damageTaken: number;
  selfDamage: number;
  kills: number;
  deaths: number;
  roundsWon: number;
  totalRounds: number;
  itemsUsed: number;
  shotsFired: number;
}

/**
 * Contexto do jogo para cálculo de ELO
 */
export interface GameContext {
  totalPlayers: number;
  totalKills: number;
  totalDamage: number;
  totalRounds: number;
}

/**
 * Resultado do cálculo de ELO
 */
export interface EloCalculationResult {
  totalChange: number;
  baseChange: number;
  performanceModifier: number;
  performanceScore: number;
  breakdown: {
    damageEfficiency: number;
    killContribution: number;
    roundDominance: number;
    survivalScore: number;
  };
}

/**
 * Input para cálculo de LP/MMR
 */
export interface RankingInput {
  currentTier: string;
  currentDivision: number | null;
  currentLp: number;
  currentMmr: number;
  gamesSincePromo: number;
  position: number;
  totalPlayers: number;
  allPlayersMmr: number[];
  performanceScore: number;
  wasQuitter: boolean;
}

/**
 * Resultado do cálculo de LP/MMR
 */
export interface RankingResult {
  newTier: Tier;
  newDivision: Division;
  newLp: number;
  newMmr: number;
  lpChange: number;
  mmrChange: number;
  promoted: boolean;
  demoted: boolean;
  displayRank: string;
}

/**
 * Input para cálculo de XP
 */
export interface XpCalculationInput {
  position: number;
  totalPlayers: number;
  kills: number;
  roundsWon: number;
  totalRounds: number;
  damageDealt: number;
  itemsUsed: number;
  selfDamage: number;
  deaths: number;
  prestigeLevel: number;
}

/**
 * Breakdown de XP ganho
 */
export interface XpBreakdown {
  participation: number;
  positionBonus: number;
  killXp: number;
  roundWinXp: number;
  damageXp: number;
  itemXp: number;
  survivalXp: number;
  cleanPlayBonus: number;
}

/**
 * Resultado do cálculo de XP
 */
export interface XpCalculationResult {
  baseXp: number;
  prestigeMultiplier: number;
  totalXp: number;
  breakdown: XpBreakdown;
}

/**
 * Informações de level
 */
export interface LevelInfo {
  absoluteLevel: number;
  displayLevel: number;
  prestigeLevel: number;
  xpInCurrentLevel: number;
  xpForNextLevel: number;
  xpProgress: number;
  totalXp: number;
}
