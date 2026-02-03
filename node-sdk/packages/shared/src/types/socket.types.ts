// ==========================================
// SOCKET TYPES - Eventos Socket.IO genéricos
// ==========================================

import { RoomInfo, RoomConfig, GameResult } from './game.types';
import { LobbyPlayer, GamePlayer } from './player.types';

/**
 * Eventos que o cliente pode emitir para o servidor
 */
export interface ClientToServerEvents {
  // ==========================================
  // ROOM EVENTS
  // ==========================================

  /** Criar nova sala */
  createRoom: (data: CreateRoomData, callback: (response: RoomResponse) => void) => void;

  /** Entrar em uma sala */
  joinRoom: (data: JoinRoomData, callback: (response: RoomResponse) => void) => void;

  /** Sair da sala atual */
  leaveRoom: (callback: (response: BasicResponse) => void) => void;

  /** Marcar como pronto/não pronto */
  toggleReady: (callback: (response: BasicResponse) => void) => void;

  /** Iniciar partida (apenas host) */
  startGame: (callback: (response: BasicResponse) => void) => void;

  /** Listar salas disponíveis */
  listRooms: (callback: (rooms: RoomInfo[]) => void) => void;

  // ==========================================
  // GAME EVENTS
  // ==========================================

  /** Ação genérica de jogo (estenda para seu jogo) */
  gameAction: (data: GameActionData, callback: (response: GameActionResponse) => void) => void;

  /** Reconectar a uma partida em andamento */
  reconnectToGame: (data: ReconnectData, callback: (response: ReconnectResponse) => void) => void;

  /** Pedir rematch */
  requestRematch: (callback: (response: BasicResponse) => void) => void;

  // ==========================================
  // CHAT EVENTS
  // ==========================================

  /** Enviar mensagem no chat */
  chatMessage: (message: string) => void;
}

/**
 * Eventos que o servidor pode emitir para o cliente
 */
export interface ServerToClientEvents {
  // ==========================================
  // ROOM EVENTS
  // ==========================================

  /** Sala foi criada */
  roomCreated: (data: RoomCreatedData) => void;

  /** Jogador entrou na sala */
  playerJoined: (player: LobbyPlayer) => void;

  /** Jogador saiu da sala */
  playerLeft: (data: { playerId: string; reason: 'left' | 'disconnected' | 'kicked' }) => void;

  /** Status de pronto mudou */
  playerReadyChanged: (data: { playerId: string; isReady: boolean }) => void;

  /** Sala foi deletada */
  roomDeleted: (reason: string) => void;

  /** Lista de salas atualizada */
  roomsUpdated: (rooms: RoomInfo[]) => void;

  // ==========================================
  // GAME EVENTS
  // ==========================================

  /** Jogo começou */
  gameStarted: (data: GameStartedData) => void;

  /** Atualização de estado do jogo */
  gameStateUpdated: (state: unknown) => void;

  /** Turno mudou */
  turnChanged: (data: TurnChangedData) => void;

  /** Timer do turno iniciado */
  turnTimerStarted: (data: { duration: number; endsAt: number }) => void;

  /** Turno expirou (timeout) */
  turnTimedOut: (data: { playerId: string }) => void;

  /** Round terminou */
  roundEnded: (data: RoundEndedData) => void;

  /** Jogo terminou */
  gameOver: (data: GameOverData) => void;

  /** Jogador desconectou durante partida */
  playerDisconnected: (data: { playerId: string; gracePeriod: number }) => void;

  /** Jogador reconectou */
  playerReconnected: (data: { playerId: string }) => void;

  // ==========================================
  // NOTIFICATION EVENTS
  // ==========================================

  /** Erro genérico */
  error: (data: { code: string; message: string }) => void;

  /** Sessão invalidada (login em outro lugar) */
  sessionInvalidated: (reason: string) => void;

  /** Achievements desbloqueados */
  achievementsUnlocked: (achievements: string[]) => void;

  // ==========================================
  // CHAT EVENTS
  // ==========================================

  /** Mensagem de chat recebida */
  chatMessageReceived: (data: { playerId: string; playerName: string; message: string; timestamp: number }) => void;
}

// ==========================================
// DATA TYPES
// ==========================================

export interface CreateRoomData {
  playerName: string;
  config?: Partial<RoomConfig>;
}

export interface JoinRoomData {
  roomCode: string;
  playerName: string;
  password?: string;
}

export interface ReconnectData {
  roomCode: string;
  reconnectToken: string;
}

export interface GameActionData {
  action: string;
  payload: unknown;
}

// ==========================================
// RESPONSE TYPES
// ==========================================

export interface BasicResponse {
  success: boolean;
  error?: string;
}

export interface RoomResponse extends BasicResponse {
  roomCode?: string;
  players?: LobbyPlayer[];
}

export interface GameActionResponse extends BasicResponse {
  newState?: unknown;
}

export interface ReconnectResponse extends BasicResponse {
  gameState?: unknown;
  players?: GamePlayer[];
}

// ==========================================
// EVENT DATA TYPES
// ==========================================

export interface RoomCreatedData {
  roomCode: string;
  config: RoomConfig;
}

export interface GameStartedData {
  players: GamePlayer[];
  initialState: unknown;
}

export interface TurnChangedData {
  currentPlayerId: string;
  turnNumber: number;
  timeout?: number;
}

export interface RoundEndedData {
  roundNumber: number;
  winnerId?: string;
  results: Array<{ playerId: string; roundResult: unknown }>;
}

export interface GameOverData {
  results: GameResult[];
  rankings?: Array<{
    playerId: string;
    eloChange: number;
    lpChange: number;
    xpGained: number;
  }>;
}
