// ==========================================
// @mysys/game-sdk-shared - Constants
// ==========================================

// Re-export das constantes de ranking
export { LP_CONFIG, MMR_CONFIG, TIER_MMR_RANGES } from '../utils/rankingCalculator';
export { XP_CONSTANTS, XP_AWARDS } from '../utils/xpCalculator';

// Re-export dos tipos de tier
export { TIERS, DIVISIONS, TIERS_WITHOUT_DIVISIONS } from '../types/ranking.types';

// ==========================================
// GAME CONSTANTS
// ==========================================

/**
 * Configurações padrão de sala
 */
export const DEFAULT_ROOM_CONFIG = {
  maxPlayers: 4,
  minPlayers: 2,
  isRanked: false,
  turnTimeout: 120, // 2 minutos
  reconnectGracePeriod: 60, // 1 minuto
} as const;

/**
 * Limites do sistema
 */
export const SYSTEM_LIMITS = {
  maxRoomCodeLength: 6,
  maxPlayerNameLength: 20,
  maxChatMessageLength: 200,
  maxPasswordLength: 20,
  minPasswordLength: 4,
} as const;

/**
 * Timeouts em milissegundos
 */
export const TIMEOUTS = {
  socketConnect: 5000,
  socketReconnect: 3000,
  apiRequest: 10000,
  turnDefault: 120000,
  reconnectGrace: 60000,
} as const;

/**
 * Códigos de erro
 */
export const ERROR_CODES = {
  // Auth errors
  AUTH_INVALID_TOKEN: 'AUTH_INVALID_TOKEN',
  AUTH_EXPIRED_TOKEN: 'AUTH_EXPIRED_TOKEN',
  AUTH_USER_BANNED: 'AUTH_USER_BANNED',

  // Room errors
  ROOM_NOT_FOUND: 'ROOM_NOT_FOUND',
  ROOM_FULL: 'ROOM_FULL',
  ROOM_WRONG_PASSWORD: 'ROOM_WRONG_PASSWORD',
  ROOM_ALREADY_STARTED: 'ROOM_ALREADY_STARTED',
  ROOM_NOT_HOST: 'ROOM_NOT_HOST',

  // Game errors
  GAME_NOT_YOUR_TURN: 'GAME_NOT_YOUR_TURN',
  GAME_INVALID_ACTION: 'GAME_INVALID_ACTION',
  GAME_ALREADY_ENDED: 'GAME_ALREADY_ENDED',

  // Connection errors
  CONNECTION_TIMEOUT: 'CONNECTION_TIMEOUT',
  CONNECTION_LOST: 'CONNECTION_LOST',

  // Generic errors
  UNKNOWN_ERROR: 'UNKNOWN_ERROR',
  VALIDATION_ERROR: 'VALIDATION_ERROR',
} as const;

export type ErrorCode = typeof ERROR_CODES[keyof typeof ERROR_CODES];
