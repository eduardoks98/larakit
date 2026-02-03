// ==========================================
// ELO CALCULATOR
// Sistema de ranking baseado na fórmula ELO padrão
// Com modificadores de performance
// ==========================================

import type {
  PlayerPerformance,
  GameContext,
  EloCalculationInput,
  EloCalculationResult,
} from '../types/ranking.types';

const K_FACTOR = 32;
const BASE_WEIGHT = 0.4;
const PERFORMANCE_WEIGHT = 0.6;
const MAX_PERFORMANCE_BONUS = 15;
const MIN_PERFORMANCE_PENALTY = -10;

// ==========================================
// FUNÇÕES BASE
// ==========================================

function expectedScore(playerElo: number, opponentElo: number): number {
  return 1 / (1 + Math.pow(10, (opponentElo - playerElo) / 400));
}

/**
 * Calcula a mudança de ELO após uma partida 1v1
 */
export function calculateEloChange(
  playerElo: number,
  opponentElo: number,
  won: boolean
): number {
  const expected = expectedScore(playerElo, opponentElo);
  const actual = won ? 1 : 0;
  const change = Math.round(K_FACTOR * (actual - expected));

  if (won && change < 5) return 5;
  if (!won && change > -5) return -5;

  return change;
}

/**
 * Calcula ELO para partida com múltiplos jogadores (2-4)
 */
export function calculateMultiplayerEloChange(
  playerElo: number,
  allPlayersElo: number[],
  playerPosition: number,
  totalPlayers: number = allPlayersElo.length
): number {
  const opponentsElo = allPlayersElo.filter(e => e !== playerElo);
  const avgOpponentElo = opponentsElo.length > 0
    ? opponentsElo.reduce((a, b) => a + b, 0) / opponentsElo.length
    : allPlayersElo.reduce((a, b) => a + b, 0) / allPlayersElo.length;

  let actualScore: number;
  if (totalPlayers === 2) {
    actualScore = playerPosition === 1 ? 1.0 : 0.0;
  } else if (totalPlayers === 3) {
    switch (playerPosition) {
      case 1: actualScore = 1.0; break;
      case 2: actualScore = 0.5; break;
      default: actualScore = 0.0; break;
    }
  } else {
    switch (playerPosition) {
      case 1: actualScore = 1.0; break;
      case 2: actualScore = 0.66; break;
      case 3: actualScore = 0.33; break;
      default: actualScore = 0.0; break;
    }
  }

  const expected = expectedScore(playerElo, avgOpponentElo);
  let change = Math.round(K_FACTOR * (actualScore - expected));

  if (playerPosition === 1 && change < 5) change = 5;
  else if (playerPosition === totalPlayers && change > -5) change = -5;

  return change;
}

// ==========================================
// MÉTRICAS DE PERFORMANCE
// ==========================================

function calculateDamageEfficiency(perf: PlayerPerformance): number {
  if (perf.totalRounds === 0) return 0.5;
  const netDamage = perf.damageDealt - perf.damageTaken - (perf.selfDamage * 1.5);
  const expectedDamage = 7 * perf.totalRounds;
  const efficiency = (netDamage + expectedDamage) / (expectedDamage * 2);
  return Math.max(0, Math.min(1, efficiency));
}

function calculateKillContribution(
  perf: PlayerPerformance,
  context: GameContext
): number {
  if (context.totalKills === 0) return 0.5;
  const expectedKills = context.totalKills / context.totalPlayers;
  const ratio = perf.kills / Math.max(1, expectedKills);
  return Math.max(0, Math.min(1, ratio / 2));
}

function calculateRoundDominance(perf: PlayerPerformance): number {
  if (perf.totalRounds === 0) return 0.5;
  return perf.roundsWon / perf.totalRounds;
}

function calculateSurvivalScore(perf: PlayerPerformance): number {
  if (perf.totalRounds === 0) return 0.5;
  const deathPenalty = perf.deaths / Math.max(1, perf.totalRounds);
  const selfDamagePenalty = perf.selfDamage / Math.max(1, perf.totalRounds * 2);
  const score = 1 - (deathPenalty * 0.6) - (selfDamagePenalty * 0.4);
  return Math.max(0, Math.min(1, score));
}

function calculatePerformanceScore(
  perf: PlayerPerformance,
  context: GameContext
): { score: number; breakdown: EloCalculationResult['breakdown'] } {
  const damageEfficiency = calculateDamageEfficiency(perf);
  const killContribution = calculateKillContribution(perf, context);
  const roundDominance = calculateRoundDominance(perf);
  const survivalScore = calculateSurvivalScore(perf);

  const score = (
    (damageEfficiency * 0.30) +
    (killContribution * 0.25) +
    (roundDominance * 0.25) +
    (survivalScore * 0.20)
  );

  return {
    score,
    breakdown: {
      damageEfficiency,
      killContribution,
      roundDominance,
      survivalScore,
    },
  };
}

function performanceToEloModifier(performanceScore: number): number {
  const centered = performanceScore - 0.5;
  if (centered >= 0) {
    return centered * 2 * MAX_PERFORMANCE_BONUS;
  } else {
    return centered * 2 * Math.abs(MIN_PERFORMANCE_PENALTY);
  }
}

// ==========================================
// FUNÇÃO PRINCIPAL
// ==========================================

/**
 * Calcula mudança de ELO considerando posição E desempenho
 */
export function calculatePerformanceBasedElo(input: EloCalculationInput): EloCalculationResult {
  const {
    playerElo,
    allPlayersElo,
    playerPosition,
    totalPlayers,
    performance,
    gameContext,
  } = input;

  const baseChange = calculateMultiplayerEloChange(
    playerElo,
    allPlayersElo,
    playerPosition,
    totalPlayers
  );

  const { score: perfScore, breakdown } = calculatePerformanceScore(performance, gameContext);
  const perfModifier = performanceToEloModifier(perfScore);

  let totalChange: number;

  if (playerPosition === 1) {
    const bonusFromPerf = Math.max(0, perfModifier);
    totalChange = baseChange + Math.round(bonusFromPerf * PERFORMANCE_WEIGHT);
  } else if (playerPosition === totalPlayers) {
    totalChange = baseChange + Math.round(perfModifier * PERFORMANCE_WEIGHT);
  } else {
    totalChange = Math.round(
      (baseChange * BASE_WEIGHT) +
      (baseChange * PERFORMANCE_WEIGHT) +
      (perfModifier * PERFORMANCE_WEIGHT)
    );
  }

  if (playerPosition === 1 && totalChange < 5) totalChange = 5;
  else if (playerPosition === totalPlayers && totalChange > -5) totalChange = -5;

  return {
    totalChange: Math.round(totalChange),
    baseChange,
    performanceModifier: Math.round(perfModifier),
    performanceScore: perfScore,
    breakdown,
  };
}

// ==========================================
// FUNÇÕES DE RANK
// ==========================================

/**
 * Calcula o rank baseado no ELO
 */
export function getRankFromElo(elo: number): string {
  if (elo >= 2400) return 'Grandmaster';
  if (elo >= 2100) return 'Master';
  if (elo >= 1800) return 'Diamond';
  if (elo >= 1500) return 'Platinum';
  if (elo >= 1200) return 'Gold';
  if (elo >= 900) return 'Silver';
  return 'Bronze';
}

/**
 * Retorna o ELO mínimo para um rank específico
 */
export function getMinEloForRank(rank: string): number {
  const ranks: Record<string, number> = {
    'Grandmaster': 2400,
    'Master': 2100,
    'Diamond': 1800,
    'Platinum': 1500,
    'Gold': 1200,
    'Silver': 900,
    'Bronze': 0,
  };
  return ranks[rank] || 0;
}
