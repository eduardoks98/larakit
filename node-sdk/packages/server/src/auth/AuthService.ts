// ==========================================
// AUTH SERVICE
// Servico de autenticacao com games-admin
// Inclui validacao dual (JWT + API) e cache
// ==========================================

import jwt from 'jsonwebtoken';
import type {
  AuthConfig,
  AuthUser,
  DecodedToken,
  TokenValidationResult,
  TokenCacheEntry,
} from '@mysys/game-sdk-shared';

// TTL do cache de validacao (1 minuto)
const CACHE_TTL = 60 * 1000;

export class AuthService {
  private config: AuthConfig;
  private tokenCache: Map<string, TokenCacheEntry> = new Map();

  constructor(config: AuthConfig) {
    this.config = {
      ...config,
      tokenExpiration: config.tokenExpiration ?? 7 * 24 * 60 * 60, // 7 dias
      cookieName: config.cookieName ?? 'mysys_token',
    };

    // Limpar cache periodicamente (a cada 5 minutos)
    setInterval(() => this.cleanupCache(), 5 * 60 * 1000);
  }

  /**
   * Valida um token JWT com validacao dual (local + API)
   */
  async validateToken(token: string): Promise<TokenValidationResult> {
    try {
      // 1. Verificar cache primeiro
      const cached = this.tokenCache.get(token);
      if (cached && Date.now() - cached.timestamp < CACHE_TTL) {
        return {
          valid: cached.valid,
          user: cached.user,
          isAdmin: cached.isAdmin,
        };
      }

      // 2. Validar JWT localmente
      const decoded = jwt.verify(token, this.config.jwtSecret) as DecodedToken;

      // 3. Verificar se o token e para este jogo
      if (decoded.game !== this.config.gameCode) {
        this.cacheResult(token, { valid: false, timestamp: Date.now() });
        return {
          valid: false,
          error: 'Token is for a different game',
        };
      }

      // 4. Verificar tipo de token (admin vs user)
      const isAdmin = decoded.type === 'admin';

      // 5. Validar com games-admin via InternalController (fail-closed)
      const apiValidation = await this.validateTokenWithAdmin(token, decoded);

      if (!apiValidation.valid) {
        this.cacheResult(token, { valid: false, timestamp: Date.now() });
        return {
          valid: false,
          error: apiValidation.error || 'Token invalidated by server',
        };
      }

      // 6. Buscar dados do usuario
      const user = apiValidation.user || await this.fetchUserFromAdmin(decoded.sub);

      if (!user && !isAdmin) {
        this.cacheResult(token, { valid: false, timestamp: Date.now() });
        return {
          valid: false,
          error: 'User not found or inactive',
        };
      }

      // 7. Cachear resultado valido
      this.cacheResult(token, {
        valid: true,
        timestamp: Date.now(),
        user: user || undefined,
        isAdmin,
      });

      return {
        valid: true,
        user: user || undefined,
        isAdmin,
      };
    } catch (error) {
      // Cache resultados invalidos tambem (fail-closed)
      this.cacheResult(token, { valid: false, timestamp: Date.now() });

      if (error instanceof jwt.TokenExpiredError) {
        return {
          valid: false,
          error: 'Token expired',
        };
      }
      if (error instanceof jwt.JsonWebTokenError) {
        return {
          valid: false,
          error: 'Invalid token',
        };
      }
      return {
        valid: false,
        error: 'Token validation failed',
      };
    }
  }

  /**
   * Valida token com games-admin via InternalController
   * FAIL-CLOSED: Se a API falhar, o token e rejeitado
   */
  private async validateTokenWithAdmin(
    token: string,
    decoded: DecodedToken
  ): Promise<{ valid: boolean; error?: string; user?: AuthUser }> {
    // Se nao tiver apiKey configurada, pular validacao com admin
    // (modo desenvolvimento/offline)
    if (!this.config.apiKey) {
      console.warn('[AuthService] No API key configured, skipping admin validation');
      return { valid: true };
    }

    try {
      const response = await fetch(
        `${this.config.gamesAdminUrl}/api/internal/validate-token`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-API-Key': this.config.apiKey,
          },
          body: JSON.stringify({
            token,
            game_code: this.config.gameCode,
          }),
        }
      );

      if (!response.ok) {
        console.error('[AuthService] Admin validation failed:', response.status);
        // FAIL-CLOSED: Rejeitar se API retornar erro
        return { valid: false, error: 'Admin validation failed' };
      }

      const data = await response.json();

      // Verificar se token foi invalidado por logout
      if (!data.valid) {
        return {
          valid: false,
          error: data.error || 'Token invalidated',
        };
      }

      // Verificar last_logout_at
      if (data.last_logout_at) {
        const logoutTimestamp = new Date(data.last_logout_at).getTime() / 1000;
        if (decoded.iat < logoutTimestamp) {
          return {
            valid: false,
            error: 'Token invalidated by logout',
          };
        }
      }

      return {
        valid: true,
        user: data.user,
      };
    } catch (error) {
      console.error('[AuthService] Admin validation error:', error);
      // FAIL-CLOSED: Rejeitar se houver erro de rede
      return { valid: false, error: 'Unable to validate with admin server' };
    }
  }

  /**
   * Cacheia resultado de validacao
   */
  private cacheResult(token: string, entry: TokenCacheEntry): void {
    this.tokenCache.set(token, entry);
  }

  /**
   * Limpa entradas expiradas do cache
   */
  private cleanupCache(): void {
    const now = Date.now();
    for (const [token, entry] of this.tokenCache.entries()) {
      if (now - entry.timestamp > CACHE_TTL * 2) {
        this.tokenCache.delete(token);
      }
    }
  }

  /**
   * Invalida token no cache (chamado apos logout)
   */
  invalidateToken(token: string): void {
    this.tokenCache.delete(token);
  }

  /**
   * Limpa todo o cache
   */
  clearCache(): void {
    this.tokenCache.clear();
  }

  /**
   * Decodifica token sem validar (para debug)
   */
  decodeToken(token: string): DecodedToken | null {
    try {
      return jwt.decode(token) as DecodedToken;
    } catch {
      return null;
    }
  }

  /**
   * Busca usuario do games-admin
   */
  private async fetchUserFromAdmin(userId: string): Promise<AuthUser | null> {
    try {
      const headers: Record<string, string> = {
        'Content-Type': 'application/json',
      };

      // Adicionar API key se configurada
      if (this.config.apiKey) {
        headers['X-API-Key'] = this.config.apiKey;
      }

      const response = await fetch(
        `${this.config.gamesAdminUrl}/api/games/${this.config.gameCode}/users/${userId}`,
        {
          method: 'GET',
          headers,
        }
      );

      if (!response.ok) {
        return null;
      }

      const data = await response.json();
      return data.user as AuthUser;
    } catch {
      // Se games-admin nao responder, retornar null
      // A validacao principal ja foi feita pelo InternalController
      return null;
    }
  }

  /**
   * Valida token do Socket.IO handshake
   */
  async validateSocketAuth(auth: { token?: string }): Promise<TokenValidationResult> {
    if (!auth.token) {
      return {
        valid: false,
        error: 'No token provided',
      };
    }

    return this.validateToken(auth.token);
  }

  /**
   * Extrai token do header Authorization OU cookie
   * Prioridade: 1. Bearer header, 2. Cookie
   */
  extractToken(authHeader?: string, cookies?: Record<string, string>): string | null {
    // 1. Tentar Bearer token primeiro (APIs server-to-server)
    if (authHeader) {
      const parts = authHeader.split(' ');
      if (parts.length === 2 && parts[0] === 'Bearer') {
        return parts[1];
      }
    }

    // 2. Fallback: cookie (requests do browser)
    const cookieName = this.config.cookieName || 'mysys_token';
    if (cookies && cookies[cookieName]) {
      return cookies[cookieName];
    }

    return null;
  }

  /**
   * Extrai token do header Authorization (compatibilidade)
   * @deprecated Use extractToken() instead
   */
  extractTokenFromHeader(authHeader?: string): string | null {
    if (!authHeader) return null;

    const parts = authHeader.split(' ');
    if (parts.length !== 2 || parts[0] !== 'Bearer') {
      return null;
    }

    return parts[1];
  }

  /**
   * Getter para config
   */
  getConfig(): Readonly<AuthConfig> {
    return this.config;
  }
}
