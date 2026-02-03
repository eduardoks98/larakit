// ==========================================
// AUTH PROVIDER
// Context de autenticação com games-admin
// ==========================================

import React, { createContext, useContext, useState, useEffect, useCallback, ReactNode } from 'react';
import type { AuthUser, AuthProvider as AuthProviderType, AuthState, AuthActions } from '@mysys/game-sdk-shared';

const TOKEN_KEY = 'mysys_token';

export interface AuthConfig {
  /** URL do games-admin */
  authUrl: string;

  /** Código do jogo */
  gameCode: string;

  /** Chave do Reverb para sync (opcional) */
  reverbKey?: string;

  /** Host do Reverb (opcional) */
  reverbHost?: string;
}

interface AuthContextValue extends AuthState, AuthActions {}

const AuthContext = createContext<AuthContextValue | null>(null);

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

  // Carregar token do localStorage no início
  useEffect(() => {
    const token = localStorage.getItem(TOKEN_KEY);
    if (token) {
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
        }
      );

      if (!response.ok) {
        localStorage.removeItem(TOKEN_KEY);
        setState({
          isAuthenticated: false,
          isLoading: false,
          user: null,
          token: null,
          error: null,
        });
        return false;
      }

      const data = await response.json();

      setState({
        isAuthenticated: true,
        isLoading: false,
        user: data.user,
        token,
        error: null,
      });

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
      return false;
    }
  }, [config.authUrl, config.gameCode]);

  // Login com provider OAuth
  const loginWithProvider = useCallback((provider: AuthProviderType) => {
    const redirectUrl = encodeURIComponent(window.location.origin + '/auth/callback');
    const authUrl = `${config.authUrl}/api/games/${config.gameCode}/auth/${provider}/redirect?redirect_url=${redirectUrl}`;
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
          body: JSON.stringify({ email, password }),
        }
      );

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Login failed');
      }

      const data = await response.json();

      localStorage.setItem(TOKEN_KEY, data.token);

      setState({
        isAuthenticated: true,
        isLoading: false,
        user: data.user,
        token: data.token,
        error: null,
      });
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
          body: JSON.stringify({ email, password, username }),
        }
      );

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Registration failed');
      }

      const data = await response.json();

      localStorage.setItem(TOKEN_KEY, data.token);

      setState({
        isAuthenticated: true,
        isLoading: false,
        user: data.user,
        token: data.token,
        error: null,
      });
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
      if (state.token) {
        await fetch(
          `${config.authUrl}/api/games/${config.gameCode}/auth/logout`,
          {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${state.token}`,
              'Content-Type': 'application/json',
            },
          }
        );
      }
    } catch {
      // Ignorar erros de logout
    } finally {
      localStorage.removeItem(TOKEN_KEY);
      setState({
        isAuthenticated: false,
        isLoading: false,
        user: null,
        token: null,
        error: null,
      });
    }
  }, [config.authUrl, config.gameCode, state.token]);

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
    loginWithProvider,
    loginWithEmail,
    register,
    logout,
    updateNickname,
    validateToken: () => validateToken(state.token || ''),
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
