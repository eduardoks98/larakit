// ==========================================
// USE TIMER HOOK
// Hook para countdown timer
// ==========================================

import { useState, useCallback, useEffect, useRef } from 'react';

interface UseTimerOptions {
  /** Segundos iniciais */
  initialSeconds?: number;

  /** Callback quando timer expira */
  onExpire?: () => void;

  /** Auto-start */
  autoStart?: boolean;
}

interface UseTimerReturn {
  /** Segundos restantes */
  seconds: number;

  /** Se está rodando */
  isRunning: boolean;

  /** Se expirou */
  isExpired: boolean;

  /** Iniciar timer */
  start: (seconds?: number) => void;

  /** Parar timer */
  stop: () => void;

  /** Resetar timer */
  reset: (seconds?: number) => void;

  /** Adicionar tempo */
  addTime: (seconds: number) => void;

  /** Formatar como mm:ss */
  formatted: string;
}

/**
 * Hook para countdown timer
 */
export function useTimer(options: UseTimerOptions = {}): UseTimerReturn {
  const { initialSeconds = 60, onExpire, autoStart = false } = options;

  const [seconds, setSeconds] = useState(initialSeconds);
  const [isRunning, setIsRunning] = useState(autoStart);
  const [isExpired, setIsExpired] = useState(false);

  const intervalRef = useRef<NodeJS.Timeout | null>(null);
  const onExpireRef = useRef(onExpire);

  // Atualizar ref do callback
  useEffect(() => {
    onExpireRef.current = onExpire;
  }, [onExpire]);

  // Efeito do timer
  useEffect(() => {
    if (!isRunning) return;

    intervalRef.current = setInterval(() => {
      setSeconds(prev => {
        if (prev <= 1) {
          setIsRunning(false);
          setIsExpired(true);
          onExpireRef.current?.();
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    return () => {
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
      }
    };
  }, [isRunning]);

  // Iniciar timer
  const start = useCallback((newSeconds?: number) => {
    if (newSeconds !== undefined) {
      setSeconds(newSeconds);
    }
    setIsExpired(false);
    setIsRunning(true);
  }, []);

  // Parar timer
  const stop = useCallback(() => {
    setIsRunning(false);
    if (intervalRef.current) {
      clearInterval(intervalRef.current);
    }
  }, []);

  // Resetar timer
  const reset = useCallback((newSeconds?: number) => {
    setIsRunning(false);
    setIsExpired(false);
    setSeconds(newSeconds ?? initialSeconds);
    if (intervalRef.current) {
      clearInterval(intervalRef.current);
    }
  }, [initialSeconds]);

  // Adicionar tempo
  const addTime = useCallback((additionalSeconds: number) => {
    setSeconds(prev => prev + additionalSeconds);
  }, []);

  // Formatar como mm:ss
  const formatted = `${Math.floor(seconds / 60)}:${(seconds % 60).toString().padStart(2, '0')}`;

  return {
    seconds,
    isRunning,
    isExpired,
    start,
    stop,
    reset,
    addTime,
    formatted,
  };
}
