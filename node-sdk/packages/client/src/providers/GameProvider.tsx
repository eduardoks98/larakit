// ==========================================
// GAME PROVIDER
// Provider combinado (Auth + Socket)
// ==========================================

import React, { ReactNode } from 'react';
import { AuthProvider, AuthConfig } from './AuthProvider';
import { SocketProvider, SocketConfig } from './SocketProvider';
import { useAuthContext } from './AuthProvider';

export interface GameConfig extends AuthConfig {
  /** URL do servidor do jogo */
  serverUrl: string;

  /** Conectar socket automaticamente */
  autoConnect?: boolean;

  /** Tentativas de reconexão */
  reconnectAttempts?: number;
}

/**
 * Provider interno que conecta Auth com Socket
 */
function SocketWithAuth({
  children,
  config,
}: {
  children: ReactNode;
  config: GameConfig;
}) {
  const { token } = useAuthContext();

  const socketConfig: SocketConfig = {
    serverUrl: config.serverUrl,
    token,
    autoConnect: config.autoConnect,
    reconnectAttempts: config.reconnectAttempts,
  };

  return (
    <SocketProvider config={socketConfig}>
      {children}
    </SocketProvider>
  );
}

/**
 * Provider principal que combina Auth e Socket
 */
export function GameProvider({
  children,
  config,
}: {
  children: ReactNode;
  config: GameConfig;
}) {
  const authConfig: AuthConfig = {
    authUrl: config.authUrl,
    gameCode: config.gameCode,
    reverbKey: config.reverbKey,
    reverbHost: config.reverbHost,
  };

  return (
    <AuthProvider config={authConfig}>
      <SocketWithAuth config={config}>
        {children}
      </SocketWithAuth>
    </AuthProvider>
  );
}
