// ==========================================
// RECONNECTION HANDLER
// Gerencia reconexões de jogadores
// ==========================================

import { generateUUID } from '@mysys/game-sdk-shared';

/**
 * Dados de reconexão pendente
 */
export interface PendingReconnection {
  playerId: string;
  roomCode: string;
  token: string;
  expiresAt: number;
  gameState?: unknown;
}

/**
 * Opções do ReconnectionHandler
 */
export interface ReconnectionOptions {
  /** Tempo de graça em ms (default: 60000 = 1 minuto) */
  gracePeriod?: number;

  /** Callback quando jogador reconecta */
  onReconnect?: (playerId: string, roomCode: string) => void;

  /** Callback quando tempo de graça expira */
  onGracePeriodExpired?: (playerId: string, roomCode: string) => void;
}

/**
 * Gerencia reconexões de jogadores desconectados
 */
export class ReconnectionHandler {
  private pendingReconnections: Map<string, PendingReconnection> = new Map();
  private tokenToPlayerMap: Map<string, string> = new Map();
  private options: ReconnectionOptions;
  private cleanupInterval: NodeJS.Timeout | null = null;

  constructor(options: ReconnectionOptions = {}) {
    this.options = {
      gracePeriod: 60000, // 1 minuto
      ...options,
    };

    // Iniciar limpeza periódica
    this.startCleanup();
  }

  /**
   * Registra desconexão para possível reconexão
   */
  registerDisconnection(
    playerId: string,
    roomCode: string,
    gameState?: unknown
  ): string {
    const token = generateUUID();
    const expiresAt = Date.now() + (this.options.gracePeriod || 60000);

    const reconnection: PendingReconnection = {
      playerId,
      roomCode,
      token,
      expiresAt,
      gameState,
    };

    // Remover reconexão anterior se existir
    this.removeByPlayerId(playerId);

    // Registrar nova
    this.pendingReconnections.set(playerId, reconnection);
    this.tokenToPlayerMap.set(token, playerId);

    return token;
  }

  /**
   * Tenta reconectar usando token
   */
  attemptReconnection(token: string): PendingReconnection | null {
    const playerId = this.tokenToPlayerMap.get(token);
    if (!playerId) return null;

    const reconnection = this.pendingReconnections.get(playerId);
    if (!reconnection) return null;

    // Verificar se expirou
    if (Date.now() > reconnection.expiresAt) {
      this.removeByPlayerId(playerId);
      return null;
    }

    // Reconexão bem sucedida - limpar dados
    this.removeByPlayerId(playerId);

    // Callback
    this.options.onReconnect?.(playerId, reconnection.roomCode);

    return reconnection;
  }

  /**
   * Verifica se jogador tem reconexão pendente
   */
  hasPendingReconnection(playerId: string): boolean {
    const reconnection = this.pendingReconnections.get(playerId);
    if (!reconnection) return false;

    // Verificar se ainda válida
    if (Date.now() > reconnection.expiresAt) {
      this.removeByPlayerId(playerId);
      return false;
    }

    return true;
  }

  /**
   * Obtém tempo restante de graça em ms
   */
  getRemainingGracePeriod(playerId: string): number {
    const reconnection = this.pendingReconnections.get(playerId);
    if (!reconnection) return 0;

    const remaining = reconnection.expiresAt - Date.now();
    return Math.max(0, remaining);
  }

  /**
   * Cancela reconexão pendente
   */
  cancelReconnection(playerId: string): void {
    this.removeByPlayerId(playerId);
  }

  /**
   * Remove por playerId
   */
  private removeByPlayerId(playerId: string): void {
    const reconnection = this.pendingReconnections.get(playerId);
    if (reconnection) {
      this.tokenToPlayerMap.delete(reconnection.token);
      this.pendingReconnections.delete(playerId);
    }
  }

  /**
   * Inicia limpeza periódica de reconexões expiradas
   */
  private startCleanup(): void {
    this.cleanupInterval = setInterval(() => {
      const now = Date.now();

      for (const [playerId, reconnection] of this.pendingReconnections) {
        if (now > reconnection.expiresAt) {
          // Callback antes de remover
          this.options.onGracePeriodExpired?.(playerId, reconnection.roomCode);

          // Remover
          this.removeByPlayerId(playerId);
        }
      }
    }, 5000); // Verificar a cada 5 segundos
  }

  /**
   * Para a limpeza periódica
   */
  stopCleanup(): void {
    if (this.cleanupInterval) {
      clearInterval(this.cleanupInterval);
      this.cleanupInterval = null;
    }
  }

  /**
   * Limpa todos os dados
   */
  clear(): void {
    this.pendingReconnections.clear();
    this.tokenToPlayerMap.clear();
  }

  /**
   * Retorna contagem de reconexões pendentes
   */
  getPendingCount(): number {
    return this.pendingReconnections.size;
  }
}
