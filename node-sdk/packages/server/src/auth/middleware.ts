// ==========================================
// AUTH MIDDLEWARE
// Middleware Express para autenticação
// ==========================================

import type { Request, Response, NextFunction, RequestHandler } from 'express';
import type { AuthUser } from '@mysys/game-sdk-shared';
import { AuthService } from './AuthService';

// Estender tipo Request para incluir user
declare global {
  namespace Express {
    interface Request {
      user?: AuthUser;
      token?: string;
    }
  }
}

/**
 * Middleware que requer autenticação
 */
export function authMiddleware(authService: AuthService): RequestHandler {
  return async (req: Request, res: Response, next: NextFunction) => {
    const token = authService.extractTokenFromHeader(req.headers.authorization);

    if (!token) {
      res.status(401).json({
        error: 'Authentication required',
        code: 'AUTH_REQUIRED',
      });
      return;
    }

    const result = await authService.validateToken(token);

    if (!result.valid || !result.user) {
      res.status(401).json({
        error: result.error || 'Invalid token',
        code: 'AUTH_INVALID_TOKEN',
      });
      return;
    }

    req.user = result.user;
    req.token = token;
    next();
  };
}

/**
 * Middleware que opcionalmente autentica (não falha se não houver token)
 */
export function optionalAuthMiddleware(authService: AuthService): RequestHandler {
  return async (req: Request, res: Response, next: NextFunction) => {
    const token = authService.extractTokenFromHeader(req.headers.authorization);

    if (token) {
      const result = await authService.validateToken(token);
      if (result.valid && result.user) {
        req.user = result.user;
        req.token = token;
      }
    }

    next();
  };
}

/**
 * Middleware para verificar se usuário é admin
 */
export function adminMiddleware(authService: AuthService): RequestHandler {
  return async (req: Request, res: Response, next: NextFunction) => {
    const token = authService.extractTokenFromHeader(req.headers.authorization);

    if (!token) {
      res.status(401).json({
        error: 'Authentication required',
        code: 'AUTH_REQUIRED',
      });
      return;
    }

    const decoded = authService.decodeToken(token);

    if (!decoded || decoded.type !== 'admin') {
      res.status(403).json({
        error: 'Admin access required',
        code: 'AUTH_ADMIN_REQUIRED',
      });
      return;
    }

    const result = await authService.validateToken(token);

    if (!result.valid || !result.user) {
      res.status(401).json({
        error: result.error || 'Invalid token',
        code: 'AUTH_INVALID_TOKEN',
      });
      return;
    }

    req.user = result.user;
    req.token = token;
    next();
  };
}
