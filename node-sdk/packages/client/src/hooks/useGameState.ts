// ==========================================
// USE GAME STATE HOOK
// Hook para gerenciar estado do jogo
// ==========================================

import { useState, useCallback, useEffect } from 'react';
import { useSocket } from './useSocket';

interface UseGameStateOptions<T> {
  /** Estado inicial */
  initialState?: T;

  /** Evento de atualização de estado */
  updateEvent?: string;
}

/**
 * Hook para gerenciar estado do jogo
 */
export function useGameState<T>(options: UseGameStateOptions<T> = {}) {
  const { updateEvent = 'gameStateUpdated' } = options;

  const [gameState, setGameState] = useState<T | null>(options.initialState || null);
  const { on, off, connected } = useSocket();

  // Atualizar estado parcialmente
  const updateState = useCallback((partial: Partial<T>) => {
    setGameState(prev => {
      if (!prev) return partial as T;
      return { ...prev, ...partial };
    });
  }, []);

  // Substituir estado completamente
  const setState = useCallback((newState: T) => {
    setGameState(newState);
  }, []);

  // Resetar estado
  const resetState = useCallback(() => {
    setGameState(options.initialState || null);
  }, [options.initialState]);

  // Listener para atualizações do servidor
  useEffect(() => {
    if (!connected) return;

    const handleUpdate = (state: T) => {
      setGameState(state);
    };

    on(updateEvent, handleUpdate as (...args: unknown[]) => void);

    return () => {
      off(updateEvent, handleUpdate as (...args: unknown[]) => void);
    };
  }, [connected, on, off, updateEvent]);

  return {
    gameState,
    updateState,
    setState,
    resetState,
    hasState: gameState !== null,
  };
}
