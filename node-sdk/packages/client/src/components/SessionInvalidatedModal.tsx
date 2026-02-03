// ==========================================
// SESSION INVALIDATED MODAL
// Modal exibido quando sessão é invalidada
// ==========================================

import React, { useState, useEffect } from 'react';
import { useSocket } from '../hooks/useSocket';
import { useAuth } from '../hooks/useAuth';

interface SessionInvalidatedModalProps {
  /** Texto do título */
  title?: string;

  /** Texto da mensagem */
  message?: string;

  /** Texto do botão */
  buttonText?: string;

  /** Callback ao clicar no botão */
  onConfirm?: () => void;

  /** Estilos customizados */
  styles?: {
    overlay?: React.CSSProperties;
    modal?: React.CSSProperties;
    title?: React.CSSProperties;
    message?: React.CSSProperties;
    button?: React.CSSProperties;
  };
}

/**
 * Modal exibido quando a sessão é invalidada
 * (ex: login em outro dispositivo)
 */
export function SessionInvalidatedModal({
  title = 'Sessão Encerrada',
  message = 'Sua sessão foi encerrada porque você fez login em outro dispositivo.',
  buttonText = 'Entendi',
  onConfirm,
  styles = {},
}: SessionInvalidatedModalProps) {
  const [isVisible, setIsVisible] = useState(false);
  const [reason, setReason] = useState<string | null>(null);
  const { on, off } = useSocket();
  const { logout } = useAuth();

  useEffect(() => {
    const handleSessionInvalidated = (invalidationReason: string) => {
      setReason(invalidationReason);
      setIsVisible(true);
    };

    on('sessionInvalidated', handleSessionInvalidated as (...args: unknown[]) => void);

    return () => {
      off('sessionInvalidated', handleSessionInvalidated as (...args: unknown[]) => void);
    };
  }, [on, off]);

  const handleConfirm = () => {
    setIsVisible(false);
    if (onConfirm) {
      onConfirm();
    } else {
      logout();
    }
  };

  if (!isVisible) return null;

  const defaultOverlayStyle: React.CSSProperties = {
    position: 'fixed',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.7)',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 9999,
    ...styles.overlay,
  };

  const defaultModalStyle: React.CSSProperties = {
    backgroundColor: '#1a1a2e',
    borderRadius: '12px',
    padding: '32px',
    maxWidth: '400px',
    width: '90%',
    textAlign: 'center',
    boxShadow: '0 20px 40px rgba(0, 0, 0, 0.3)',
    ...styles.modal,
  };

  const defaultTitleStyle: React.CSSProperties = {
    color: '#ffffff',
    fontSize: '24px',
    fontWeight: 600,
    marginBottom: '16px',
    ...styles.title,
  };

  const defaultMessageStyle: React.CSSProperties = {
    color: '#a0a0a0',
    fontSize: '16px',
    lineHeight: 1.5,
    marginBottom: '24px',
    ...styles.message,
  };

  const defaultButtonStyle: React.CSSProperties = {
    backgroundColor: '#4f46e5',
    color: '#ffffff',
    border: 'none',
    borderRadius: '8px',
    padding: '12px 32px',
    fontSize: '16px',
    fontWeight: 500,
    cursor: 'pointer',
    transition: 'background-color 0.2s',
    ...styles.button,
  };

  return (
    <div style={defaultOverlayStyle}>
      <div style={defaultModalStyle}>
        <h2 style={defaultTitleStyle}>{title}</h2>
        <p style={defaultMessageStyle}>
          {reason || message}
        </p>
        <button
          onClick={handleConfirm}
          style={defaultButtonStyle}
          type="button"
        >
          {buttonText}
        </button>
      </div>
    </div>
  );
}
