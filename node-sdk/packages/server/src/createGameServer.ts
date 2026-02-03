// ==========================================
// CREATE GAME SERVER
// Factory para criar servidor de jogo completo
// ==========================================

import express, { Express } from 'express';
import { createServer, Server as HttpServer } from 'http';
import { Server as SocketServer } from 'socket.io';
import cors from 'cors';

export interface GameServerConfig {
  /** Código do jogo */
  gameCode: string;

  /** URL do games-admin */
  gamesAdminUrl: string;

  /** Secret para JWT */
  jwtSecret: string;

  /** Configuração de CORS */
  cors?: {
    origin: string | string[];
    credentials?: boolean;
  };

  /** Porta (opcional, usa PORT env ou 3001) */
  port?: number;
}

export interface GameServerInstance {
  /** Express app */
  app: Express;

  /** Socket.IO server */
  io: SocketServer;

  /** HTTP server */
  httpServer: HttpServer;

  /** Configuração */
  config: GameServerConfig;
}

/**
 * Cria um servidor de jogo completo com Express e Socket.IO
 */
export function createGameServer(config: GameServerConfig): GameServerInstance {
  const app = express();

  // Middlewares básicos
  app.use(express.json());
  app.use(express.urlencoded({ extended: true }));

  // CORS
  if (config.cors) {
    app.use(cors({
      origin: config.cors.origin,
      credentials: config.cors.credentials ?? true,
      methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
      allowedHeaders: ['Content-Type', 'Authorization'],
    }));
  }

  // Health check
  app.get('/api/health', (req, res) => {
    res.json({
      status: 'ok',
      game: config.gameCode,
      timestamp: new Date().toISOString(),
    });
  });

  // Criar HTTP server
  const httpServer = createServer(app);

  // Criar Socket.IO server
  const io = new SocketServer(httpServer, {
    cors: config.cors ? {
      origin: config.cors.origin,
      credentials: config.cors.credentials ?? true,
    } : undefined,
    pingTimeout: 60000,
    pingInterval: 25000,
  });

  return {
    app,
    io,
    httpServer,
    config,
  };
}
