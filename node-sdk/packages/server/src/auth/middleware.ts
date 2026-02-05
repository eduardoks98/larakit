// ==========================================
// AUTH MIDDLEWARE
// Middleware Express para autenticacao
// Suporta token via Bearer header OU cookie
// ==========================================

import type { Request, Response, NextFunction, RequestHandler } from 'express';
import type { AuthUser } from '@mysys/game-sdk-shared';
import { AuthService } from './AuthService';

// Estender tipo Request para incluir user e isAdmin
declare global {
  namespace Express {
    interface Request {
      user?: AuthUser;
      token?: string;
      isAdmin?: boolean;
    }
  }
}

/**
 * Extrai cookies do header Cookie
 */
function parseCookies(cookieHeader?: string): Record<string, string> {
  const cookies: Record<string, string> = {};
  if (!cookieHeader) return cookies;

  cookieHeader.split(';').forEach(cookie => {
    const [name, ...rest] = cookie.split('=');
    if (name && rest.length > 0) {
      cookies[name.trim()] = rest.join('=').trim();
    }
  });

  return cookies;
}

/**
 * Extrai token de multiplas fontes (header Bearer ou cookie)
 */
function extractTokenFromRequest(req: Request, authService: AuthService): string | null {
  // 1. Tentar Bearer token primeiro (APIs server-to-server)
  const authHeader = req.headers.authorization;
  if (authHeader) {
    const parts = authHeader.split(' ');
    if (parts.length === 2 && parts[0] === 'Bearer') {
      return parts[1];
    }
  }

  // 2. Tentar cookie (requests do browser)
  // Express com cookie-parser popula req.cookies
  // Sem cookie-parser, precisamos parsear manualmente
  const cookies = req.cookies || parseCookies(req.headers.cookie);
  const cookieName = authService.getConfig().cookieName || 'mysys_token';

  if (cookies[cookieName]) {
    return cookies[cookieName];
  }

  return null;
}

/**
 * Middleware que requer autenticacao
 * Extrai token de Bearer header OU cookie httpOnly
 */
export function authMiddleware(authService: AuthService): RequestHandler {
  return async (req: Request, res: Response, next: NextFunction) => {
    const token = extractTokenFromRequest(req, authService);

    if (!token) {
      res.status(401).json({
        error: 'Authentication required',
        code: 'AUTH_REQUIRED',
      });
      return;
    }

    const result = await authService.validateToken(token);

    if (!result.valid) {
      res.status(401).json({
        error: result.error || 'Invalid token',
        code: 'AUTH_INVALID_TOKEN',
      });
      return;
    }

    // Admin tokens podem nao ter user (apenas validacao de assinatura)
    if (!result.user && !result.isAdmin) {
      res.status(401).json({
        error: 'User not found',
        code: 'AUTH_USER_NOT_FOUND',
      });
      return;
    }

    req.user = result.user;
    req.token = token;
    req.isAdmin = result.isAdmin;
    next();
  };
}

/**
 * Middleware que opcionalmente autentica (nao falha se nao houver token)
 */
export function optionalAuthMiddleware(authService: AuthService): RequestHandler {
  return async (req: Request, res: Response, next: NextFunction) => {
    const token = extractTokenFromRequest(req, authService);

    if (token) {
      const result = await authService.validateToken(token);
      if (result.valid) {
        req.user = result.user;
        req.token = token;
        req.isAdmin = result.isAdmin;
      }
    }

    next();
  };
}

/**
 * Middleware para verificar se usuario e admin
 */
export function adminMiddleware(authService: AuthService): RequestHandler {
  return async (req: Request, res: Response, next: NextFunction) => {
    const token = extractTokenFromRequest(req, authService);

    if (!token) {
      res.status(401).json({
        error: 'Authentication required',
        code: 'AUTH_REQUIRED',
      });
      return;
    }

    // Verificar tipo do token primeiro (mais rapido)
    const decoded = authService.decodeToken(token);

    if (!decoded || decoded.type !== 'admin') {
      res.status(403).json({
        error: 'Admin access required',
        code: 'AUTH_ADMIN_REQUIRED',
      });
      return;
    }

    // Validar token completamente
    const result = await authService.validateToken(token);

    if (!result.valid) {
      res.status(401).json({
        error: result.error || 'Invalid token',
        code: 'AUTH_INVALID_TOKEN',
      });
      return;
    }

    req.user = result.user;
    req.token = token;
    req.isAdmin = true;
    next();
  };
}

/**
 * Middleware para verificar se usuario e sponsor
 */
export function sponsorMiddleware(authService: AuthService): RequestHandler {
  return async (req: Request, res: Response, next: NextFunction) => {
    const token = extractTokenFromRequest(req, authService);

    if (!token) {
      res.status(401).json({
        error: 'Authentication required',
        code: 'AUTH_REQUIRED',
      });
      return;
    }

    const decoded = authService.decodeToken(token);

    if (!decoded || (decoded.type !== 'sponsor' && decoded.type !== 'admin')) {
      res.status(403).json({
        error: 'Sponsor access required',
        code: 'AUTH_SPONSOR_REQUIRED',
      });
      return;
    }

    const result = await authService.validateToken(token);

    if (!result.valid) {
      res.status(401).json({
        error: result.error || 'Invalid token',
        code: 'AUTH_INVALID_TOKEN',
      });
      return;
    }

    req.user = result.user;
    req.token = token;
    req.isAdmin = decoded.type === 'admin';
    next();
  };
}
