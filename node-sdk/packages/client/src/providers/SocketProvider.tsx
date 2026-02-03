// ==========================================
// SOCKET PROVIDER
// Context de Socket.IO
// ==========================================

import React, { createContext, useContext, useState, useEffect, useCallback, ReactNode, useRef } from 'react';
import { io, Socket } from 'socket.io-client';

export interface SocketConfig {
  /** URL do servidor do jogo */
  serverUrl: string;

  /** Token de autenticação */
  token?: string | null;

  /** Conectar automaticamente */
  autoConnect?: boolean;

  /** Tentativas de reconexão */
  reconnectAttempts?: number;
}

interface SocketContextValue {
  /** Instância do socket */
  socket: Socket | null;

  /** Se está conectado */
  connected: boolean;

  /** Se está tentando conectar */
  connecting: boolean;

  /** Erro de conexão */
  error: string | null;

  /** Emitir evento */
  emit: (event: string, ...args: unknown[]) => void;

  /** Registrar listener */
  on: (event: string, handler: (...args: unknown[]) => void) => void;

  /** Remover listener */
  off: (event: string, handler: (...args: unknown[]) => void) => void;

  /** Conectar manualmente */
  connect: () => void;

  /** Desconectar manualmente */
  disconnect: () => void;
}

const SocketContext = createContext<SocketContextValue | null>(null);

export function SocketProvider({
  children,
  config,
}: {
  children: ReactNode;
  config: SocketConfig;
}) {
  const [socket, setSocket] = useState<Socket | null>(null);
  const [connected, setConnected] = useState(false);
  const [connecting, setConnecting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const socketRef = useRef<Socket | null>(null);

  // Criar e conectar socket
  const createSocket = useCallback(() => {
    if (socketRef.current) {
      socketRef.current.disconnect();
    }

    const newSocket = io(config.serverUrl, {
      autoConnect: false,
      reconnectionAttempts: config.reconnectAttempts ?? 5,
      reconnectionDelay: 1000,
      reconnectionDelayMax: 5000,
      auth: config.token ? { token: config.token } : undefined,
    });

    // Event handlers
    newSocket.on('connect', () => {
      setConnected(true);
      setConnecting(false);
      setError(null);
    });

    newSocket.on('disconnect', (reason) => {
      setConnected(false);
      if (reason === 'io server disconnect') {
        // Server forçou desconexão
        setError('Disconnected by server');
      }
    });

    newSocket.on('connect_error', (err) => {
      setConnecting(false);
      setError(err.message);
    });

    newSocket.on('error', (data: { code: string; message: string }) => {
      setError(data.message);
    });

    socketRef.current = newSocket;
    setSocket(newSocket);

    return newSocket;
  }, [config.serverUrl, config.token, config.reconnectAttempts]);

  // Conectar
  const connect = useCallback(() => {
    if (!socketRef.current) {
      createSocket();
    }

    if (socketRef.current && !socketRef.current.connected) {
      setConnecting(true);
      setError(null);
      socketRef.current.connect();
    }
  }, [createSocket]);

  // Desconectar
  const disconnect = useCallback(() => {
    if (socketRef.current) {
      socketRef.current.disconnect();
      setConnected(false);
      setConnecting(false);
    }
  }, []);

  // Emitir evento
  const emit = useCallback((event: string, ...args: unknown[]) => {
    if (socketRef.current && socketRef.current.connected) {
      socketRef.current.emit(event, ...args);
    }
  }, []);

  // Registrar listener
  const on = useCallback((event: string, handler: (...args: unknown[]) => void) => {
    if (socketRef.current) {
      socketRef.current.on(event, handler);
    }
  }, []);

  // Remover listener
  const off = useCallback((event: string, handler: (...args: unknown[]) => void) => {
    if (socketRef.current) {
      socketRef.current.off(event, handler);
    }
  }, []);

  // Efeito para auto-connect
  useEffect(() => {
    if (config.autoConnect !== false && config.token) {
      const newSocket = createSocket();
      setConnecting(true);
      newSocket.connect();
    }

    return () => {
      if (socketRef.current) {
        socketRef.current.disconnect();
        socketRef.current = null;
      }
    };
  }, [config.token, config.autoConnect, createSocket]);

  // Reconectar quando token mudar
  useEffect(() => {
    if (socketRef.current && config.token) {
      socketRef.current.auth = { token: config.token };

      if (socketRef.current.connected) {
        socketRef.current.disconnect().connect();
      }
    }
  }, [config.token]);

  const value: SocketContextValue = {
    socket,
    connected,
    connecting,
    error,
    emit,
    on,
    off,
    connect,
    disconnect,
  };

  return (
    <SocketContext.Provider value={value}>
      {children}
    </SocketContext.Provider>
  );
}

export function useSocketContext(): SocketContextValue {
  const context = useContext(SocketContext);
  if (!context) {
    throw new Error('useSocketContext must be used within SocketProvider');
  }
  return context;
}
