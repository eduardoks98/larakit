// ==========================================
// SOCKET MANAGER
// Gerenciador de conexões Socket.IO
// ==========================================

import type { Server, Socket } from 'socket.io';
import type { AuthUser } from '@mysys/game-sdk-shared';
import type { AuthService } from '../auth/AuthService';
import type { RoomManager, BaseRoom } from './RoomManager';

/**
 * Socket autenticado
 */
export interface AuthenticatedSocket extends Socket {
  user?: AuthUser;
  playerId?: string;
}

/**
 * Opções do SocketManager
 */
export interface SocketManagerOptions {
  /** Requer autenticação para conectar */
  requireAuth?: boolean;

  /** Handler para conexão */
  onConnect?: (socket: AuthenticatedSocket, user?: AuthUser) => void;

  /** Handler para desconexão */
  onDisconnect?: (socket: AuthenticatedSocket, reason: string) => void;
}

/**
 * Gerenciador de conexões Socket.IO
 */
export class SocketManager<TRoom extends BaseRoom, TPlayer> {
  protected io: Server;
  protected roomManager: RoomManager<TRoom, TPlayer>;
  protected authService: AuthService;
  protected options: SocketManagerOptions;

  /** Map de socketId para userId */
  protected socketUserMap: Map<string, string> = new Map();

  /** Map de userId para socketId */
  protected userSocketMap: Map<string, string> = new Map();

  constructor(
    io: Server,
    roomManager: RoomManager<TRoom, TPlayer>,
    authService: AuthService,
    options: SocketManagerOptions = {}
  ) {
    this.io = io;
    this.roomManager = roomManager;
    this.authService = authService;
    this.options = {
      requireAuth: true,
      ...options,
    };

    this.setupConnectionHandler();
  }

  /**
   * Configura handler de conexão
   */
  protected setupConnectionHandler(): void {
    this.io.on('connection', async (socket: AuthenticatedSocket) => {
      // Autenticar
      if (this.options.requireAuth) {
        const result = await this.authService.validateSocketAuth(socket.handshake.auth);

        if (!result.valid || !result.user) {
          socket.emit('error', {
            code: 'AUTH_INVALID_TOKEN',
            message: result.error || 'Authentication failed',
          });
          socket.disconnect(true);
          return;
        }

        socket.user = result.user;
        socket.playerId = result.user.id;

        // Registrar mapeamentos
        this.socketUserMap.set(socket.id, result.user.id);
        this.userSocketMap.set(result.user.id, socket.id);
      }

      // Handler de desconexão
      socket.on('disconnect', (reason) => {
        if (socket.playerId) {
          this.socketUserMap.delete(socket.id);
          this.userSocketMap.delete(socket.playerId);
        }

        this.options.onDisconnect?.(socket, reason);
      });

      // Callback de conexão
      this.options.onConnect?.(socket, socket.user);
    });
  }

  /**
   * Obtém socket de um usuário
   */
  getSocketByUserId(userId: string): AuthenticatedSocket | null {
    const socketId = this.userSocketMap.get(userId);
    if (!socketId) return null;
    return this.io.sockets.sockets.get(socketId) as AuthenticatedSocket || null;
  }

  /**
   * Obtém usuário de um socket
   */
  getUserIdBySocketId(socketId: string): string | null {
    return this.socketUserMap.get(socketId) || null;
  }

  /**
   * Emite para uma sala
   */
  emitToRoom(roomCode: string, event: string, data: unknown): void {
    this.io.to(roomCode).emit(event, data);
  }

  /**
   * Emite para um usuário específico
   */
  emitToUser(userId: string, event: string, data: unknown): void {
    const socket = this.getSocketByUserId(userId);
    if (socket) {
      socket.emit(event, data);
    }
  }

  /**
   * Faz socket entrar em uma sala Socket.IO
   */
  joinSocketRoom(socket: AuthenticatedSocket, roomCode: string): void {
    socket.join(roomCode);
  }

  /**
   * Faz socket sair de uma sala Socket.IO
   */
  leaveSocketRoom(socket: AuthenticatedSocket, roomCode: string): void {
    socket.leave(roomCode);
  }

  /**
   * Conta conexões ativas
   */
  getConnectionCount(): number {
    return this.io.sockets.sockets.size;
  }

  /**
   * Obtém o servidor Socket.IO
   */
  getIO(): Server {
    return this.io;
  }

  /**
   * Obtém o RoomManager
   */
  getRoomManager(): RoomManager<TRoom, TPlayer> {
    return this.roomManager;
  }
}
