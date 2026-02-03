// ==========================================
// GAME TYPES - Tipos base para qualquer jogo
// ==========================================

/**
 * Status possíveis de uma partida
 */
export type GameStatus = 'WAITING' | 'IN_PROGRESS' | 'COMPLETED' | 'ABANDONED';

/**
 * Estado base de qualquer jogo
 * Estenda esta interface para criar estados específicos do seu jogo
 */
export interface BaseGameState {
  /** Código único da sala */
  roomCode: string;

  /** Status atual do jogo */
  status: GameStatus;

  /** Round atual (1-indexed) */
  currentRound: number;

  /** Número máximo de rounds */
  maxRounds: number;

  /** Timestamp de criação */
  createdAt: Date;

  /** Timestamp de início (quando status mudou para IN_PROGRESS) */
  startedAt?: Date;

  /** Timestamp de fim */
  endedAt?: Date;
}

/**
 * Configuração de sala/partida
 */
export interface RoomConfig {
  /** Número máximo de jogadores */
  maxPlayers: number;

  /** Número mínimo de jogadores para começar */
  minPlayers: number;

  /** Se a partida é ranqueada */
  isRanked: boolean;

  /** Senha da sala (opcional) */
  password?: string;

  /** Tempo limite por turno em segundos (0 = sem limite) */
  turnTimeout: number;

  /** Tempo de graça para reconexão em segundos */
  reconnectGracePeriod: number;
}

/**
 * Informações de uma sala no lobby
 */
export interface RoomInfo {
  /** Código da sala */
  code: string;

  /** Nome do host */
  hostName: string;

  /** Número de jogadores atual */
  playerCount: number;

  /** Número máximo de jogadores */
  maxPlayers: number;

  /** Se tem senha */
  hasPassword: boolean;

  /** Se é ranqueada */
  isRanked: boolean;

  /** Status */
  status: GameStatus;
}

/**
 * Resultado de uma partida para um jogador
 */
export interface GameResult {
  /** ID do jogador */
  playerId: string;

  /** Posição final (1 = primeiro lugar) */
  position: number;

  /** Se venceu */
  isWinner: boolean;

  /** Estatísticas do jogo */
  stats: PlayerGameStats;
}

/**
 * Estatísticas de um jogador em uma partida
 */
export interface PlayerGameStats {
  /** Rounds ganhos */
  roundsWon: number;

  /** Total de rounds jogados */
  totalRounds: number;

  /** Número de kills (se aplicável) */
  kills: number;

  /** Número de deaths (se aplicável) */
  deaths: number;

  /** Dano causado (se aplicável) */
  damageDealt: number;

  /** Dano recebido (se aplicável) */
  damageTaken: number;

  /** Auto-dano (se aplicável) */
  selfDamage: number;

  /** Itens usados (se aplicável) */
  itemsUsed: number;
}
