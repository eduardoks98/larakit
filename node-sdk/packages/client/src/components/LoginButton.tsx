// ==========================================
// LOGIN BUTTON
// Botão de login com providers OAuth
// ==========================================

import React from 'react';
import type { AuthProvider } from '@mysys/game-sdk-shared';
import { useAuth } from '../hooks/useAuth';

interface LoginButtonProps {
  /** Provider de autenticação */
  provider: AuthProvider;

  /** Classe CSS adicional */
  className?: string;

  /** Texto customizado */
  children?: React.ReactNode;

  /** Estilo inline */
  style?: React.CSSProperties;

  /** Desabilitado */
  disabled?: boolean;
}

const PROVIDER_LABELS: Record<AuthProvider, string> = {
  google: 'Google',
  facebook: 'Facebook',
  discord: 'Discord',
  email: 'Email',
};

const PROVIDER_COLORS: Record<AuthProvider, string> = {
  google: '#4285F4',
  facebook: '#1877F2',
  discord: '#5865F2',
  email: '#666666',
};

/**
 * Botão de login com provider OAuth
 */
export function LoginButton({
  provider,
  className,
  children,
  style,
  disabled,
}: LoginButtonProps) {
  const { loginWithProvider, isLoading } = useAuth();

  const handleClick = () => {
    if (!disabled && !isLoading) {
      loginWithProvider(provider);
    }
  };

  const defaultStyle: React.CSSProperties = {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    padding: '12px 24px',
    fontSize: '14px',
    fontWeight: 500,
    border: 'none',
    borderRadius: '8px',
    cursor: disabled || isLoading ? 'not-allowed' : 'pointer',
    opacity: disabled || isLoading ? 0.6 : 1,
    backgroundColor: PROVIDER_COLORS[provider],
    color: '#ffffff',
    transition: 'opacity 0.2s, transform 0.1s',
    ...style,
  };

  return (
    <button
      onClick={handleClick}
      disabled={disabled || isLoading}
      className={className}
      style={defaultStyle}
      type="button"
    >
      {children || `Entrar com ${PROVIDER_LABELS[provider]}`}
    </button>
  );
}
