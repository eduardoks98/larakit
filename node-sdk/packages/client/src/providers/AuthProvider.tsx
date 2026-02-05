// ==========================================
// AUTH PROVIDER
// Context de autenticacao com games-admin
// Token via httpOnly cookie (NAO em URL)
// ==========================================

import React, { createContext, useContext, useState, useEffect, useCallback, useRef, ReactNode } from 'react';
import type { AuthUser, AuthProvider as AuthProviderType, AuthState, AuthActions } from '@mysys/game-sdk-shared';

// Chave do localStorage para backup do token (fallback)
const TOKEN_KEY = 'mysys_token';

// Nome do cookie SSO
const COOKIE_NAME = 'mysys_token';

export interface AuthConfig {
  /** URL do games-admin (portal) */
  authUrl: string;

  /** Codigo do jogo */
  gameCode: string;

  /** Chave do Reverb para sync (opcional) */
  reverbKey?: string;

  /** Host do Reverb (opcional) */
  reverbHost?: string;

  /** Dominio do cookie (ex: .mysys.shop) */
  cookieDomain?: string;
}

interface AuthContextValue extends AuthState, AuthActions {
  /** Se e admin */
  isAdmin: boolean;
}

const AuthContext = createContext<AuthContextValue | null>(null);

/**
 * Le um cookie pelo nome
 */
function getCookie(name: string): string | null {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) {
    return parts.pop()?.split(';').shift() || null;
  }
  return null;
}

/**
 * Remove um cookie
 */
function deleteCookie(name: string, domain?: string): void {
  const domainPart = domain ? `; domain=${domain}` : '';
  document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/${domainPart}`;
}

export function AuthProvider({
  children,
  config,
}: {
  children: ReactNode;
  config: AuthConfig;
}) {
  const [state, setState] = useState<AuthState>({
    isAuthenticated: false,
    isLoading: true,
    user: null,
    token: null,
    error: null,
  });
  const [isAdmin, setIsAdmin] = useState(false);

  // Ref para BroadcastChannel (sync entre abas)
  const broadcastChannel = useRef<BroadcastChannel | null>(null);

  // Inicializar BroadcastChannel para sync entre abas
  useEffect(() => {
    try {
      broadcastChannel.current = new BroadcastChannel(`mysys_auth_${config.gameCode}`);

      broadcastChannel.current.onmessage = (event) => {
        if (event.data.type === 'LOGOUT') {
          // Outra aba fez logout, limpar estado local
          setState({
            isAuthenticated: false,
            isLoading: false,
            user: null,
            token: null,
            error: null,
          });
          setIsAdmin(false);
        } else if (event.data.type === 'LOGIN') {
          // Outra aba fez login, revalidar
          const token = getCookie(COOKIE_NAME) || localStorage.getItem(TOKEN_KEY);
          if (token) {
            validateToken(token);
          }
        }
      };
    } catch {
      // BroadcastChannel nao suportado
    }

    return () => {
      broadcastChannel.current?.close();
    };
  }, [config.gameCode]);

  // Carregar token no inicio
  // Prioridade: 1. Cookie httpOnly, 2. localStorage (fallback)
  useEffect(() => {
    // Verificar se veio de OAuth callback (token via cookie, NAO URL)
    // O games-admin agora seta o cookie httpOnly no redirect
    const cookieToken = getCookie(COOKIE_NAME);
    const localToken = localStorage.getItem(TOKEN_KEY);

    const token = cookieToken || localToken;

    if (token) {
      // Sincronizar localStorage com cookie
      if (cookieToken && !localToken) {
        localStorage.setItem(TOKEN_KEY, cookieToken);
      }
      validateToken(token);
    } else {
      setState(prev => ({ ...prev, isLoading: false }));
    }
  }, []);

  // Validar token com games-admin
  const validateToken = useCallback(async (token: string): Promise<boolean> => {
    try {
      const response = await fetch(
        `${config.authUrl}/api/games/${config.gameCode}/auth/validate`,
        {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
          },
          credentials: 'include', // Incluir cookies
        }
      );

      if (!response.ok) {
        // Token invalido, limpar tudo
        localStorage.removeItem(TOKEN_KEY);
        deleteCookie(COOKIE_NAME, config.cookieDomain);

        setState({
          isAuthenticated: false,
          isLoading: false,
          user: null,
          token: null,
          error: null,
        });
        setIsAdmin(false);
        return false;
      }

      const data = await response.json();

      // Salvar token no localStorage como backup
      localStorage.setItem(TOKEN_KEY, token);

      setState({
        isAuthenticated: true,
        isLoading: false,
        user: data.user,
        token,
        error: null,
      });
      setIsAdmin(data.isAdmin || false);

      return true;
    } catch (error) {
      localStorage.removeItem(TOKEN_KEY);
      setState({
        isAuthenticated: false,
        isLoading: false,
        user: null,
        token: null,
        error: 'Failed to validate token',
      });
      setIsAdmin(false);
      return false;
    }
  }, [config.authUrl, config.gameCode, config.cookieDomain]);

  // Login com provider OAuth
  // IMPORTANTE: Token vira via httpOnly cookie, NAO em URL
  const loginWithProvider = useCallback((provider: AuthProviderType) => {
    // URL de retorno apos OAuth (SEM token na URL)
    const returnUrl = encodeURIComponent(window.location.origin + '/lobby');

    // Redirecionar para games-admin
    // O games-admin vai setar o cookie httpOnly e redirecionar de volta
    const authUrl = `${config.authUrl}/login?source=${config.gameCode}&return_url=${returnUrl}&provider=${provider}`;
    window.location.href = authUrl;
  }, [config.authUrl, config.gameCode]);

  // Login com email/senha
  const loginWithEmail = useCallback(async (email: string, password: string) => {
    setState(prev => ({ ...prev, isLoading: true, error: null }));

    try {
      const response = await fetch(
        `${config.authUrl}/api/games/${config.gameCode}/auth/login`,
        {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include', // Receber cookie
          body: JSON.stringify({ email, password }),
        }
      );

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Login failed');
      }

      const data = await response.json();

      // Salvar token no localStorage como backup
      if (data.token) {
        localStorage.setItem(TOKEN_KEY, data.token);
      }

      setState({
        isAuthenticated: true,
        isLoading: false,
        user: data.user,
        token: data.token || null,
        error: null,
      });
      setIsAdmin(data.isAdmin || false);

      // Notificar outras abas
      broadcastChannel.current?.postMessage({ type: 'LOGIN' });
    } catch (error) {
      setState(prev => ({
        ...prev,
        isLoading: false,
        error: error instanceof Error ? error.message : 'Login failed',
      }));
    }
  }, [config.authUrl, config.gameCode]);

  // Registro com email
  const register = useCallback(async (email: string, password: string, username: string) => {
    setState(prev => ({ ...prev, isLoading: true, error: null }));

    try {
      const response = await fetch(
        `${config.authUrl}/api/games/${config.gameCode}/auth/register`,
        {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
          body: JSON.stringify({ email, password, username }),
        }
      );

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Registration failed');
      }

      const data = await response.json();

      if (data.token) {
        localStorage.setItem(TOKEN_KEY, data.token);
      }

      setState({
        isAuthenticated: true,
        isLoading: false,
        user: data.user,
        token: data.token || null,
        error: null,
      });

      broadcastChannel.current?.postMessage({ type: 'LOGIN' });
    } catch (error) {
      setState(prev => ({
        ...prev,
        isLoading: false,
        error: error instanceof Error ? error.message : 'Registration failed',
      }));
    }
  }, [config.authUrl, config.gameCode]);

  // Logout
  const logout = useCallback(async () => {
    try {
      // Chamar API de logout (vai invalidar token no servidor)
      await fetch(
        `${config.authUrl}/api/games/${config.gameCode}/auth/logout`,
        {
          method: 'POST',
          headers: {
            'Authorization': state.token ? `Bearer ${state.token}` : '',
            'Content-Type': 'application/json',
          },
          credentials: 'include',
        }
      );
    } catch {
      // Ignorar erros de logout (continuar limpeza local)
    } finally {
      // Limpar localStorage
      localStorage.removeItem(TOKEN_KEY);

      // Limpar cookie (se acessivel)
      deleteCookie(COOKIE_NAME, config.cookieDomain);

      // Atualizar estado
      setState({
        isAuthenticated: false,
        isLoading: false,
        user: null,
        token: null,
        error: null,
      });
      setIsAdmin(false);

      // Notificar outras abas
      broadcastChannel.current?.postMessage({ type: 'LOGOUT' });
    }
  }, [config.authUrl, config.gameCode, config.cookieDomain, state.token]);

  // Atualizar nickname
  const updateNickname = useCallback(async (nickname: string) => {
    if (!state.token) return;

    const response = await fetch(
      `${config.authUrl}/api/games/${config.gameCode}/auth/nickname`,
      {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${state.token}`,
          'Content-Type': 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify({ nickname }),
      }
    );

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Failed to update nickname');
    }

    setState(prev => ({
      ...prev,
      user: prev.user ? { ...prev.user, nickname } : null,
    }));
  }, [config.authUrl, config.gameCode, state.token]);

  const value: AuthContextValue = {
    ...state,
    isAdmin,
    loginWithProvider,
    loginWithEmail,
    register,
    logout,
    updateNickname,
    validateToken: () => validateToken(state.token || getCookie(COOKIE_NAME) || ''),
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuthContext(): AuthContextValue {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuthContext must be used within AuthProvider');
  }
  return context;
}

// Alias para compatibilidade
export const useAuth = useAuthContext;
