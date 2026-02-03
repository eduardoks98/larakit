// ==========================================
// ROOM MANAGER
// Classe abstrata para gestão de salas
// ==========================================

import type { RoomInfo, RoomConfig, GameStatus } from '@mysys/game-sdk-shared';
import { generateRoomCode } from '@mysys/game-sdk-shared';

/**
 * Interface base para uma sala
 */
export interface BaseRoom {
  code: string;
  hostId: string;
  status: GameStatus;
  config: RoomConfig;
  createdAt: Date;
}

/**
 * Classe abstrata para gestão de salas
 * Estenda esta classe para implementar a lógica específica do seu jogo
 */
export abstract class RoomManager<TRoom extends BaseRoom, TPlayer> {
  protected rooms: Map<string, TRoom> = new Map();
  protected playerRoomMap: Map<string, string> = new Map();

  /**
   * Cria uma nova sala
   * @param hostId ID do jogador que está criando
   * @param options Opções da sala
   */
  abstract createRoom(hostId: string, options: Partial<RoomConfig>): TRoom;

  /**
   * Adiciona um jogador a uma sala
   * @param room Sala
   * @param player Jogador
   */
  abstract addPlayer(room: TRoom, player: TPlayer): void;

  /**
   * Remove um jogador de uma sala
   * @param room Sala
   * @param playerId ID do jogador
   */
  abstract removePlayer(room: TRoom, playerId: string): void;

  /**
   * Retorna os jogadores de uma sala
   * @param room Sala
   */
  abstract getPlayers(room: TRoom): TPlayer[];

  /**
   * Gera código de sala único
   */
  generateRoomCode(): string {
    let code: string;
    do {
      code = generateRoomCode(6);
    } while (this.rooms.has(code));
    return code;
  }

  /**
   * Obtém sala por código
   */
  getRoomByCode(code: string): TRoom | null {
    return this.rooms.get(code.toUpperCase()) || null;
  }

  /**
   * Obtém sala de um jogador
   */
  getRoomByPlayer(playerId: string): TRoom | null {
    const roomCode = this.playerRoomMap.get(playerId);
    if (!roomCode) return null;
    return this.getRoomByCode(roomCode);
  }

  /**
   * Registra associação jogador-sala
   */
  protected setPlayerRoom(playerId: string, roomCode: string): void {
    this.playerRoomMap.set(playerId, roomCode);
  }

  /**
   * Remove associação jogador-sala
   */
  protected clearPlayerRoom(playerId: string): void {
    this.playerRoomMap.delete(playerId);
  }

  /**
   * Adiciona sala ao mapa
   */
  protected addRoom(room: TRoom): void {
    this.rooms.set(room.code, room);
  }

  /**
   * Remove sala do mapa
   */
  protected deleteRoom(code: string): void {
    const room = this.rooms.get(code);
    if (room) {
      // Limpar associações de jogadores
      const players = this.getPlayers(room);
      for (const player of players) {
        this.clearPlayerRoom((player as unknown as { id: string }).id);
      }
      this.rooms.delete(code);
    }
  }

  /**
   * Retorna lista de salas para lobby
   */
  getAllRooms(): RoomInfo[] {
    const roomInfos: RoomInfo[] = [];

    for (const room of this.rooms.values()) {
      if (room.status === 'WAITING') {
        const players = this.getPlayers(room);
        roomInfos.push({
          code: room.code,
          hostName: this.getHostName(room),
          playerCount: players.length,
          maxPlayers: room.config.maxPlayers,
          hasPassword: !!room.config.password,
          isRanked: room.config.isRanked,
          status: room.status,
        });
      }
    }

    return roomInfos;
  }

  /**
   * Obtém nome do host da sala
   */
  protected getHostName(room: TRoom): string {
    const players = this.getPlayers(room);
    const host = players.find(p => (p as unknown as { id: string }).id === room.hostId);
    return (host as unknown as { name?: string })?.name || 'Unknown';
  }

  /**
   * Verifica se sala está cheia
   */
  isRoomFull(room: TRoom): boolean {
    return this.getPlayers(room).length >= room.config.maxPlayers;
  }

  /**
   * Verifica se jogador é host
   */
  isHost(room: TRoom, playerId: string): boolean {
    return room.hostId === playerId;
  }

  /**
   * Conta salas ativas
   */
  getActiveRoomCount(): number {
    return this.rooms.size;
  }

  /**
   * Conta jogadores online
   */
  getOnlinePlayerCount(): number {
    return this.playerRoomMap.size;
  }
}
