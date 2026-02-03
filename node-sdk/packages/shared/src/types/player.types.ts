// ==========================================
// PLAYER TYPES - Tipos de jogador
// ==========================================

/**
 * Jogador base com informações de conexão
 * Estenda esta interface para adicionar dados específicos do seu jogo
 */
export interface BasePlayer {
  /** ID único do jogador (UUID) */
  id: string;

  /** Nome de exibição */
  name: string;

  /** URL do avatar (opcional) */
  avatarUrl?: string;

  /** Se está conectado */
  connected: boolean;

  /** Token para reconexão */
  reconnectToken?: string;

  /** Timestamp da última atividade */
  lastActivity: Date;

  /** Socket ID atual */
  socketId?: string;
}

/**
 * Jogador em uma sala de espera
 */
export interface LobbyPlayer extends BasePlayer {
  /** Se está pronto para começar */
  isReady: boolean;

  /** Se é o host da sala */
  isHost: boolean;

  /** Posição na sala */
  position: number;
}

/**
 * Jogador durante uma partida
 */
export interface GamePlayer extends BasePlayer {
  /** Posição na ordem de turnos */
  turnPosition: number;

  /** Se está vivo/ativo no jogo */
  isAlive: boolean;

  /** Se é o turno atual deste jogador */
  isCurrentTurn: boolean;

  /** Informações de ranking (se partida ranqueada) */
  rankInfo?: PlayerRankDisplay;
}

/**
 * Informações de ranking para exibição
 */
export interface PlayerRankDisplay {
  /** Tier atual */
  tier: string;

  /** Divisão (null para Master+) */
  division: number | null;

  /** LP atual */
  lp: number;

  /** Level de XP */
  level: number;

  /** Nível de prestige */
  prestigeLevel: number;
}

/**
 * Perfil completo do jogador (para tela de perfil)
 */
export interface PlayerProfile extends BasePlayer {
  /** Email (se disponível) */
  email?: string;

  /** Data de criação da conta */
  createdAt: Date;

  /** Estatísticas globais */
  stats: GlobalPlayerStats;

  /** Informações de ranking */
  ranking: PlayerRankDisplay;

  /** Títulos desbloqueados */
  titles: string[];

  /** Título ativo */
  activeTitle?: string;
}

/**
 * Estatísticas globais do jogador
 */
export interface GlobalPlayerStats {
  /** Total de partidas jogadas */
  gamesPlayed: number;

  /** Total de vitórias */
  gamesWon: number;

  /** Taxa de vitória (0-100) */
  winRate: number;

  /** Total de kills */
  totalKills: number;

  /** Total de deaths */
  totalDeaths: number;

  /** KDA ratio */
  kdRatio: number;

  /** Total de XP */
  totalXp: number;

  /** Maior sequência de vitórias */
  longestWinStreak: number;

  /** Sequência atual de vitórias */
  currentWinStreak: number;
}
