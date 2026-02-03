// ==========================================
// AUTH SERVICE
// Serviço de autenticação com games-admin
// ==========================================

import jwt from 'jsonwebtoken';
import type {
  AuthConfig,
  AuthUser,
  DecodedToken,
  TokenValidationResult,
} from '@mysys/game-sdk-shared';

export class AuthService {
  private config: AuthConfig;

  constructor(config: AuthConfig) {
    this.config = {
      ...config,
      tokenExpiration: config.tokenExpiration ?? 7 * 24 * 60 * 60, // 7 dias
    };
  }

  /**
   * Valida um token JWT
   */
  async validateToken(token: string): Promise<TokenValidationResult> {
    try {
      // Decodificar token localmente
      const decoded = jwt.verify(token, this.config.jwtSecret) as DecodedToken;

      // Verificar se o token é para este jogo
      if (decoded.game !== this.config.gameCode) {
        return {
          valid: false,
          error: 'Token is for a different game',
        };
      }

      // Validar com games-admin (opcional, para verificar se usuário ainda está ativo)
      const user = await this.fetchUserFromAdmin(decoded.sub);

      if (!user) {
        return {
          valid: false,
          error: 'User not found or inactive',
        };
      }

      return {
        valid: true,
        user,
      };
    } catch (error) {
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
   * Busca usuário do games-admin
   */
  private async fetchUserFromAdmin(userId: string): Promise<AuthUser | null> {
    try {
      const response = await fetch(
        `${this.config.gamesAdminUrl}/api/games/${this.config.gameCode}/users/${userId}`,
        {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
          },
        }
      );

      if (!response.ok) {
        return null;
      }

      const data = await response.json();
      return data.user as AuthUser;
    } catch {
      // Se games-admin não responder, assumir usuário válido baseado no token
      // Isso permite que o jogo funcione offline
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
   * Extrai token do header Authorization
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
