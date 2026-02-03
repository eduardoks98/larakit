// ==========================================
// CREATE GAME
// Função principal para criar novo jogo
// ==========================================

import fs from 'fs-extra';
import path from 'path';
import chalk from 'chalk';

export interface CreateGameOptions {
  projectName: string;
  gameCode: string;
  gameName: string;
  gamesAdminUrl: string;
  maxPlayers: number;
  includeBot: boolean;
}

export async function createGame(options: CreateGameOptions): Promise<void> {
  const { projectName } = options;

  const projectPath = path.resolve(process.cwd(), projectName);

  // Verificar se diretório já existe
  if (await fs.pathExists(projectPath)) {
    throw new Error(`Diretório "${projectName}" já existe`);
  }

  // Criar estrutura de diretórios
  await fs.ensureDir(projectPath);

  console.log(chalk.gray('  Criando estrutura de diretórios...'));

  // Criar arquivos raiz
  await createRootFiles(projectPath, options);

  // Criar client
  await createClient(projectPath, options);

  // Criar server
  await createServer(projectPath, options);

  // Criar shared
  await createShared(projectPath, options);
}

async function createRootFiles(projectPath: string, options: CreateGameOptions): Promise<void> {
  const { projectName, gameCode, gameName, gamesAdminUrl } = options;

  // package.json
  await fs.writeJson(path.join(projectPath, 'package.json'), {
    name: projectName,
    version: '1.0.0',
    private: true,
    workspaces: ['src/client', 'src/server', 'src/shared'],
    scripts: {
      dev: 'concurrently "npm run dev:server" "npm run dev:client"',
      'dev:client': 'npm run dev -w src/client',
      'dev:server': 'npm run dev -w src/server',
      build: 'npm run build --workspaces',
      'db:migrate': 'npm run db:migrate -w src/server',
      'db:push': 'npm run db:push -w src/server',
    },
    devDependencies: {
      concurrently: '^8.2.2',
    },
  }, { spaces: 2 });

  // .env.example
  await fs.writeFile(path.join(projectPath, '.env.example'), `# Jogo
GAME_CODE=${gameCode}
GAME_NAME="${gameName}"

# games-admin
GAMES_ADMIN_URL=${gamesAdminUrl}
GAMES_ADMIN_JWT_SECRET=your_jwt_secret_here
GAMES_ADMIN_API_KEY=your_api_key_here
GAMES_ADMIN_API_SECRET=your_api_secret_here

# Server
PORT=3001
NODE_ENV=development

# Database
DATABASE_URL=mysql://user:password@localhost:3306/${projectName.replace(/-/g, '_')}

# Client
VITE_API_URL=http://localhost:3001
VITE_GAME_CODE=${gameCode}
VITE_AUTH_URL=${gamesAdminUrl}
`);

  // .gitignore
  await fs.writeFile(path.join(projectPath, '.gitignore'), `node_modules/
dist/
.env
.env.local
*.log
.DS_Store
`);

  // README.md
  await fs.writeFile(path.join(projectPath, 'README.md'), `# ${gameName}

Jogo desenvolvido com MySys Game SDK.

## Desenvolvimento

\`\`\`bash
# Instalar dependências
npm install

# Configurar ambiente
cp .env.example .env
# Editar .env

# Rodar migrações
npm run db:migrate

# Iniciar desenvolvimento
npm run dev
\`\`\`

## Estrutura

- \`src/client/\` - Frontend React + Vite
- \`src/server/\` - Backend Node.js + Express + Socket.IO
- \`src/shared/\` - Tipos e constantes compartilhados
`);

  // docker-compose.yml
  await fs.writeFile(path.join(projectPath, 'docker-compose.yml'), `version: '3.8'

services:
  db:
    image: mysql:8.0
    container_name: ${projectName}-db
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: ${projectName.replace(/-/g, '_')}
      MYSQL_USER: user
      MYSQL_PASSWORD: password
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
`);
}

async function createClient(projectPath: string, options: CreateGameOptions): Promise<void> {
  const clientPath = path.join(projectPath, 'src', 'client');
  await fs.ensureDir(path.join(clientPath, 'src', 'pages'));
  await fs.ensureDir(path.join(clientPath, 'src', 'components', 'game'));
  await fs.ensureDir(path.join(clientPath, 'src', 'hooks'));

  // package.json
  await fs.writeJson(path.join(clientPath, 'package.json'), {
    name: `@${options.projectName}/client`,
    private: true,
    type: 'module',
    scripts: {
      dev: 'vite',
      build: 'tsc && vite build',
      preview: 'vite preview',
    },
    dependencies: {
      '@mysys/game-sdk-client': '^1.0.0',
      '@mysys/game-sdk-shared': '^1.0.0',
      react: '^18.2.0',
      'react-dom': '^18.2.0',
      'react-router-dom': '^6.20.0',
    },
    devDependencies: {
      '@types/react': '^18.2.45',
      '@types/react-dom': '^18.2.18',
      '@vitejs/plugin-react': '^4.2.1',
      typescript: '^5.3.0',
      vite: '^5.0.0',
    },
  }, { spaces: 2 });

  // vite.config.ts
  await fs.writeFile(path.join(clientPath, 'vite.config.ts'), `import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  server: {
    port: 5173,
  },
  resolve: {
    alias: {
      '@': '/src',
    },
  },
});
`);

  // tsconfig.json
  await fs.writeJson(path.join(clientPath, 'tsconfig.json'), {
    compilerOptions: {
      target: 'ES2020',
      useDefineForClassFields: true,
      lib: ['ES2020', 'DOM', 'DOM.Iterable'],
      module: 'ESNext',
      skipLibCheck: true,
      moduleResolution: 'bundler',
      allowImportingTsExtensions: true,
      resolveJsonModule: true,
      isolatedModules: true,
      noEmit: true,
      jsx: 'react-jsx',
      strict: true,
      noUnusedLocals: true,
      noUnusedParameters: true,
      noFallthroughCasesInSwitch: true,
      paths: {
        '@/*': ['./src/*'],
      },
    },
    include: ['src'],
  }, { spaces: 2 });

  // index.html
  await fs.writeFile(path.join(clientPath, 'index.html'), `<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>${options.gameName}</title>
  </head>
  <body>
    <div id="root"></div>
    <script type="module" src="/src/main.tsx"></script>
  </body>
</html>
`);

  // main.tsx
  await fs.writeFile(path.join(clientPath, 'src', 'main.tsx'), `import React from 'react';
import ReactDOM from 'react-dom/client';
import { GameProvider } from '@mysys/game-sdk-client';
import App from './App';
import './styles/global.css';

const config = {
  gameCode: import.meta.env.VITE_GAME_CODE,
  authUrl: import.meta.env.VITE_AUTH_URL,
  serverUrl: import.meta.env.VITE_API_URL,
};

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <GameProvider config={config}>
      <App />
    </GameProvider>
  </React.StrictMode>
);
`);

  // App.tsx
  await fs.writeFile(path.join(clientPath, 'src', 'App.tsx'), `import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { useAuth } from '@mysys/game-sdk-client';
import { SessionInvalidatedModal } from '@mysys/game-sdk-client/components';
import Home from './pages/Home';
import Game from './pages/Game';

function App() {
  const { isLoading } = useAuth();

  if (isLoading) {
    return <div className="loading">Carregando...</div>;
  }

  return (
    <BrowserRouter>
      <SessionInvalidatedModal />
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/game" element={<Game />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
`);

  // Home.tsx
  await fs.writeFile(path.join(clientPath, 'src', 'pages', 'Home.tsx'), `import { useAuth } from '@mysys/game-sdk-client';
import { LoginButton } from '@mysys/game-sdk-client/components';

export default function Home() {
  const { user, isAuthenticated } = useAuth();

  return (
    <div className="home">
      <h1>${options.gameName}</h1>

      {!isAuthenticated ? (
        <div className="login-buttons">
          <LoginButton provider="google" />
          <LoginButton provider="discord" />
        </div>
      ) : (
        <div className="user-info">
          <p>Olá, {user?.nickname}!</p>
          <a href="/game">Jogar</a>
        </div>
      )}
    </div>
  );
}
`);

  // Game.tsx
  await fs.writeFile(path.join(clientPath, 'src', 'pages', 'Game.tsx'), `import { useSocket, useGameState } from '@mysys/game-sdk-client';

export default function Game() {
  const { connected, emit } = useSocket();
  const { gameState } = useGameState();

  return (
    <div className="game">
      <header>
        <span>Status: {connected ? '🟢 Conectado' : '🔴 Desconectado'}</span>
      </header>

      <main>
        <p>Implemente seu jogo aqui!</p>
        {/* Seu jogo vai aqui */}
      </main>
    </div>
  );
}
`);

  // styles
  await fs.ensureDir(path.join(clientPath, 'src', 'styles'));
  await fs.writeFile(path.join(clientPath, 'src', 'styles', 'global.css'), `* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #1a1a2e;
  color: #ffffff;
  min-height: 100vh;
}

.loading {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
}

.home, .game {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.login-buttons {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}
`);

  // vite-env.d.ts
  await fs.writeFile(path.join(clientPath, 'src', 'vite-env.d.ts'), `/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_API_URL: string;
  readonly VITE_GAME_CODE: string;
  readonly VITE_AUTH_URL: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
`);
}

async function createServer(projectPath: string, options: CreateGameOptions): Promise<void> {
  const serverPath = path.join(projectPath, 'src', 'server');
  await fs.ensureDir(path.join(serverPath, 'src', 'socket'));
  await fs.ensureDir(path.join(serverPath, 'src', 'services'));
  await fs.ensureDir(path.join(serverPath, 'prisma'));

  // package.json
  await fs.writeJson(path.join(serverPath, 'package.json'), {
    name: `@${options.projectName}/server`,
    private: true,
    type: 'module',
    scripts: {
      dev: 'tsx watch src/index.ts',
      build: 'tsc',
      start: 'node dist/index.js',
      'db:migrate': 'prisma migrate dev',
      'db:push': 'prisma db push',
      'db:studio': 'prisma studio',
    },
    dependencies: {
      '@mysys/game-sdk-server': '^1.0.0',
      '@mysys/game-sdk-shared': '^1.0.0',
      '@prisma/client': '^5.7.0',
      dotenv: '^16.3.1',
    },
    devDependencies: {
      '@types/node': '^20.10.0',
      prisma: '^5.7.0',
      tsx: '^4.6.0',
      typescript: '^5.3.0',
    },
  }, { spaces: 2 });

  // tsconfig.json
  await fs.writeJson(path.join(serverPath, 'tsconfig.json'), {
    compilerOptions: {
      target: 'ES2022',
      module: 'NodeNext',
      moduleResolution: 'NodeNext',
      outDir: './dist',
      rootDir: './src',
      strict: true,
      esModuleInterop: true,
      skipLibCheck: true,
      declaration: true,
    },
    include: ['src/**/*'],
    exclude: ['node_modules', 'dist'],
  }, { spaces: 2 });

  // index.ts
  await fs.writeFile(path.join(serverPath, 'src', 'index.ts'), `import 'dotenv/config';
import { createGameServer, AuthService } from '@mysys/game-sdk-server';
import { registerGameHandlers } from './socket/game.handler';
import { GameService } from './services/game.service';

const { app, io, httpServer, config } = createGameServer({
  gameCode: process.env.GAME_CODE!,
  gamesAdminUrl: process.env.GAMES_ADMIN_URL!,
  jwtSecret: process.env.GAMES_ADMIN_JWT_SECRET!,
  cors: {
    origin: process.env.CLIENT_URL || 'http://localhost:5173',
  },
});

const authService = new AuthService({
  gamesAdminUrl: process.env.GAMES_ADMIN_URL!,
  jwtSecret: process.env.GAMES_ADMIN_JWT_SECRET!,
  gameCode: process.env.GAME_CODE!,
});

const gameService = new GameService();

// Registrar handlers do jogo
registerGameHandlers(io, authService, gameService);

const PORT = process.env.PORT || 3001;

httpServer.listen(PORT, () => {
  console.log(\`Server running on port \${PORT}\`);
  console.log(\`Game: \${process.env.GAME_CODE}\`);
});
`);

  // game.handler.ts
  await fs.writeFile(path.join(serverPath, 'src', 'socket', 'game.handler.ts'), `import type { Server } from 'socket.io';
import type { AuthService } from '@mysys/game-sdk-server';
import type { GameService } from '../services/game.service';

export function registerGameHandlers(
  io: Server,
  authService: AuthService,
  gameService: GameService
): void {
  io.on('connection', async (socket) => {
    // Autenticar
    const result = await authService.validateSocketAuth(socket.handshake.auth);

    if (!result.valid || !result.user) {
      socket.emit('error', { code: 'AUTH_FAILED', message: result.error });
      socket.disconnect(true);
      return;
    }

    console.log(\`Player connected: \${result.user.nickname}\`);

    // Registrar eventos do jogo
    socket.on('createRoom', (data, callback) => {
      // TODO: Implementar criação de sala
      callback({ success: false, error: 'Not implemented' });
    });

    socket.on('joinRoom', (data, callback) => {
      // TODO: Implementar entrada em sala
      callback({ success: false, error: 'Not implemented' });
    });

    socket.on('gameAction', (data, callback) => {
      // TODO: Implementar ações do jogo
      callback({ success: false, error: 'Not implemented' });
    });

    socket.on('disconnect', () => {
      console.log(\`Player disconnected: \${result.user?.nickname}\`);
    });
  });
}
`);

  // game.service.ts
  await fs.writeFile(path.join(serverPath, 'src', 'services', 'game.service.ts'), `// ==========================================
// GAME SERVICE
// Lógica principal do jogo
// ==========================================

export class GameService {
  // TODO: Implementar lógica do jogo

  constructor() {
    console.log('GameService initialized');
  }
}
`);

  // prisma/schema.prisma
  await fs.writeFile(path.join(serverPath, 'prisma', 'schema.prisma'), `generator client {
  provider = "prisma-client-js"
}

datasource db {
  provider = "mysql"
  url      = env("DATABASE_URL")
}

model User {
  id            String   @id @default(uuid())
  gameUserId    String   @unique @map("game_user_id")
  email         String
  username      String
  nickname      String
  avatarUrl     String?  @map("avatar_url")

  // Ranking
  tier          String   @default("Bronze")
  division      Int?     @default(4)
  lp            Int      @default(0)
  mmr           Int      @default(0)

  // Stats
  gamesPlayed   Int      @default(0) @map("games_played")
  gamesWon      Int      @default(0) @map("games_won")
  totalXp       Int      @default(0) @map("total_xp")

  createdAt     DateTime @default(now()) @map("created_at")
  updatedAt     DateTime @updatedAt @map("updated_at")

  @@map("users")
}

model Game {
  id          String   @id @default(uuid())
  roomCode    String   @unique @map("room_code")
  status      String   @default("WAITING")
  winnerId    String?  @map("winner_id")
  gameState   Json?    @map("game_state")
  createdAt   DateTime @default(now()) @map("created_at")
  updatedAt   DateTime @updatedAt @map("updated_at")

  @@map("games")
}
`);
}

async function createShared(projectPath: string, options: CreateGameOptions): Promise<void> {
  const sharedPath = path.join(projectPath, 'src', 'shared');
  await fs.ensureDir(path.join(sharedPath, 'src', 'types'));
  await fs.ensureDir(path.join(sharedPath, 'src', 'constants'));

  // package.json
  await fs.writeJson(path.join(sharedPath, 'package.json'), {
    name: `@${options.projectName}/shared`,
    private: true,
    main: 'src/index.ts',
    types: 'src/index.ts',
    dependencies: {
      '@mysys/game-sdk-shared': '^1.0.0',
    },
  }, { spaces: 2 });

  // index.ts
  await fs.writeFile(path.join(sharedPath, 'src', 'index.ts'), `export * from './types';
export * from './constants';
`);

  // types/index.ts
  await fs.writeFile(path.join(sharedPath, 'src', 'types', 'index.ts'), `export * from './game.types';
`);

  // types/game.types.ts
  await fs.writeFile(path.join(sharedPath, 'src', 'types', 'game.types.ts'), `import type { BaseGameState, BasePlayer } from '@mysys/game-sdk-shared';

// Estenda os tipos base para seu jogo
export interface ${options.gameCode}GameState extends BaseGameState {
  // TODO: Adicionar campos específicos do jogo
}

export interface ${options.gameCode}Player extends BasePlayer {
  // TODO: Adicionar campos específicos do jogador
}

// Eventos Socket.IO específicos do jogo
export interface ${options.gameCode}ClientEvents {
  // TODO: Adicionar eventos do cliente
}

export interface ${options.gameCode}ServerEvents {
  // TODO: Adicionar eventos do servidor
}
`);

  // constants/index.ts
  await fs.writeFile(path.join(sharedPath, 'src', 'constants', 'index.ts'), `export * from './game-rules';
`);

  // constants/game-rules.ts
  await fs.writeFile(path.join(sharedPath, 'src', 'constants', 'game-rules.ts'), `// Regras e constantes do jogo

export const GAME_CONFIG = {
  MAX_PLAYERS: ${options.maxPlayers},
  MIN_PLAYERS: 2,
  TURN_TIMEOUT: 120, // segundos
  RECONNECT_GRACE_PERIOD: 60, // segundos
} as const;
`);
}
