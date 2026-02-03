// ==========================================
// GAMES ADMIN CLIENT
// Cliente REST para comunicação com games-admin
// ==========================================

export interface GamesAdminConfig {
  /** URL base do games-admin */
  baseUrl: string;

  /** API Key do jogo */
  apiKey: string;

  /** API Secret do jogo */
  apiSecret: string;

  /** Timeout em ms (default: 10000) */
  timeout?: number;
}

export interface UserStats {
  gamesPlayed: number;
  gamesWon: number;
  kills?: number;
  deaths?: number;
  totalXp?: number;
  [key: string]: unknown;
}

export interface BetaJoinResult {
  success: boolean;
  message?: string;
  betaPlayerId?: string;
}

/**
 * Cliente para comunicação com games-admin
 */
export class GamesAdminClient {
  private config: GamesAdminConfig;

  constructor(config: GamesAdminConfig) {
    this.config = {
      timeout: 10000,
      ...config,
    };
  }

  /**
   * Faz requisição autenticada para games-admin
   */
  private async request<T>(
    method: string,
    path: string,
    body?: unknown
  ): Promise<T> {
    const controller = new AbortController();
    const timeoutId = setTimeout(
      () => controller.abort(),
      this.config.timeout
    );

    try {
      const response = await fetch(`${this.config.baseUrl}${path}`, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'X-API-Key': this.config.apiKey,
          'X-API-Secret': this.config.apiSecret,
        },
        body: body ? JSON.stringify(body) : undefined,
        signal: controller.signal,
      });

      if (!response.ok) {
        const error = await response.json().catch(() => ({}));
        throw new Error(error.message || `HTTP ${response.status}`);
      }

      return response.json();
    } finally {
      clearTimeout(timeoutId);
    }
  }

  /**
   * Sincroniza estatísticas do jogador
   */
  async syncUserStats(
    gameCode: string,
    userId: string,
    stats: UserStats
  ): Promise<void> {
    await this.request(
      'POST',
      `/api/games/${gameCode}/users/${userId}/stats`,
      stats
    );
  }

  /**
   * Registra início/fim de sessão
   */
  async trackSession(
    gameCode: string,
    userId: string,
    action: 'start' | 'end'
  ): Promise<void> {
    await this.request(
      'POST',
      `/api/games/${gameCode}/sessions/${action}`,
      { userId }
    );
  }

  /**
   * Notifica reset de dados do jogo
   */
  async notifyReset(
    gameCode: string,
    options: {
      resetStats: boolean;
      resetMatches: boolean;
      resetLeaderboards: boolean;
    }
  ): Promise<void> {
    await this.request(
      'POST',
      `/api/games/${gameCode}/reset-notification`,
      {
        resetAt: new Date().toISOString(),
        options,
      }
    );
  }

  /**
   * Obtém status do beta
   */
  async getBetaStatus(gameCode: string): Promise<{
    isInBeta: boolean;
    playersCount: number;
    maxPlayers: number | null;
  }> {
    return this.request('GET', `/api/games/${gameCode}/beta/status`);
  }

  /**
   * Registra jogador no beta
   */
  async joinBeta(
    gameCode: string,
    userId: string
  ): Promise<BetaJoinResult> {
    return this.request(
      'POST',
      `/api/games/${gameCode}/beta/join`,
      { userId }
    );
  }

  /**
   * Atualiza contador de partidas do beta tester
   */
  async updateBetaGamesPlayed(
    gameCode: string,
    userId: string,
    gamesPlayed: number
  ): Promise<void> {
    await this.request(
      'POST',
      `/api/games/${gameCode}/beta/update-games`,
      { userId, gamesPlayed }
    );
  }

  /**
   * Obtém rewards de beta disponíveis
   */
  async getBetaRewards(gameCode: string): Promise<Array<{
    id: string;
    type: string;
    rewardId: string;
    minGamesRequired: number;
  }>> {
    return this.request('GET', `/api/games/${gameCode}/beta/rewards`);
  }

  /**
   * Verifica saúde do games-admin
   */
  async healthCheck(): Promise<boolean> {
    try {
      await this.request('GET', '/api/health');
      return true;
    } catch {
      return false;
    }
  }
}
