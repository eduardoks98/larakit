"use strict";
// ==========================================
// CREATE GAME
// Funcao principal para criar novo jogo
// Baseado na estrutura do Buckshot Roulette
// ==========================================
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.createGame = createGame;
const fs_extra_1 = __importDefault(require("fs-extra"));
const path_1 = __importDefault(require("path"));
const chalk_1 = __importDefault(require("chalk"));
async function createGame(options) {
    const { projectName } = options;
    const projectPath = path_1.default.resolve(process.cwd(), projectName);
    // Verificar se diretorio ja existe
    if (await fs_extra_1.default.pathExists(projectPath)) {
        throw new Error(`Diretorio "${projectName}" ja existe`);
    }
    // Criar estrutura de diretorios
    await fs_extra_1.default.ensureDir(projectPath);
    console.log(chalk_1.default.gray('  Criando estrutura de diretorios...'));
    // Criar arquivos raiz
    await createRootFiles(projectPath, options);
    // Criar client
    await createClient(projectPath, options);
    // Criar server
    await createServer(projectPath, options);
    // Criar shared
    await createShared(projectPath, options);
}
async function createRootFiles(projectPath, options) {
    const { projectName, gameCode, gameName, gamesAdminUrl } = options;
    // package.json
    await fs_extra_1.default.writeJson(path_1.default.join(projectPath, 'package.json'), {
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
            'db:studio': 'npm run db:studio -w src/server',
        },
        devDependencies: {
            concurrently: '^8.2.2',
        },
    }, { spaces: 2 });
    // .env.example
    await fs_extra_1.default.writeFile(path_1.default.join(projectPath, '.env.example'), `# Jogo
GAME_CODE=${gameCode}
GAME_NAME="${gameName}"

# games-admin (Portal de Login)
GAMES_ADMIN_URL=${gamesAdminUrl}
GAMES_ADMIN_JWT_SECRET=your_jwt_secret_here

# Server
PORT=3001
NODE_ENV=development
CLIENT_URL=http://localhost:5173

# Database (MySQL remoto do games-admin)
DATABASE_URL=mysql://user:password@your-server:3306/${projectName.replace(/-/g, '_')}

# Client
VITE_API_URL=http://localhost:3001
VITE_GAME_CODE=${gameCode}
VITE_GAMES_ADMIN_URL=${gamesAdminUrl}
`);
    // .gitignore
    await fs_extra_1.default.writeFile(path_1.default.join(projectPath, '.gitignore'), `node_modules/
dist/
.env
.env.local
*.log
.DS_Store
`);
    // README.md
    await fs_extra_1.default.writeFile(path_1.default.join(projectPath, 'README.md'), `# ${gameName}

Jogo multiplayer desenvolvido com MySys Game SDK.

## Desenvolvimento

\`\`\`bash
# Instalar dependencias
npm install

# Configurar ambiente
cp .env.example .env
# Editar .env com as credenciais do games-admin

# Rodar migracoes (primeira vez)
npm run db:push

# Iniciar desenvolvimento
npm run dev
\`\`\`

## Estrutura

- \`src/client/\` - Frontend React + Vite + Socket.IO
- \`src/server/\` - Backend Node.js + Express + Socket.IO + Prisma
- \`src/shared/\` - Tipos e constantes compartilhados

## Autenticacao

O login e integrado com o Portal (games-admin) via Google OAuth.
Os usuarios fazem login uma vez no portal e podem jogar qualquer jogo.
`);
}
async function createClient(projectPath, options) {
    const clientPath = path_1.default.join(projectPath, 'src', 'client');
    await fs_extra_1.default.ensureDir(path_1.default.join(clientPath, 'src', 'pages'));
    await fs_extra_1.default.ensureDir(path_1.default.join(clientPath, 'src', 'components', 'game'));
    await fs_extra_1.default.ensureDir(path_1.default.join(clientPath, 'src', 'components', 'common'));
    await fs_extra_1.default.ensureDir(path_1.default.join(clientPath, 'src', 'hooks'));
    await fs_extra_1.default.ensureDir(path_1.default.join(clientPath, 'src', 'context'));
    await fs_extra_1.default.ensureDir(path_1.default.join(clientPath, 'src', 'services'));
    await fs_extra_1.default.ensureDir(path_1.default.join(clientPath, 'src', 'styles'));
    // package.json
    await fs_extra_1.default.writeJson(path_1.default.join(clientPath, 'package.json'), {
        name: `@${options.projectName}/client`,
        private: true,
        type: 'module',
        scripts: {
            dev: 'vite',
            build: 'tsc && vite build',
            preview: 'vite preview',
        },
        dependencies: {
            react: '^18.2.0',
            'react-dom': '^18.2.0',
            'react-router-dom': '^6.20.0',
            'socket.io-client': '^4.7.2',
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
    await fs_extra_1.default.writeFile(path_1.default.join(clientPath, 'vite.config.ts'), `import { defineConfig } from 'vite';
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
    await fs_extra_1.default.writeJson(path_1.default.join(clientPath, 'tsconfig.json'), {
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
    await fs_extra_1.default.writeFile(path_1.default.join(clientPath, 'index.html'), `<!DOCTYPE html>
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
    await fs_extra_1.default.writeFile(path_1.default.join(clientPath, 'src', 'main.tsx'), `import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { SocketProvider } from './context/SocketContext';
import App from './App';
import './styles/global.css';

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <BrowserRouter>
      <SocketProvider>
        <App />
      </SocketProvider>
    </BrowserRouter>
  </React.StrictMode>
);
`);
    // App.tsx
    await fs_extra_1.default.writeFile(path_1.default.join(clientPath, 'src', 'App.tsx'), `import { Routes, Route } from 'react-router-dom';
import Home from './pages/Home';
import Game from './pages/Game';

function App() {
  return (
    <Routes>
      <Route path="/" element={<Home />} />
      <Route path="/game/:roomCode" element={<Game />} />
    </Routes>
  );
}

export default App;
`);
    // context/SocketContext.tsx
    await fs_extra_1.default.writeFile(path_1.default.join(clientPath, 'src', 'context', 'SocketContext.tsx'), `import { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { io, Socket } from 'socket.io-client';

interface SocketContextType {
  socket: Socket | null;
  connected: boolean;
  user: User | null;
}

interface User {
  id: string;
  username: string;
  display_name: string;
  avatar_url?: string;
}

const SocketContext = createContext<SocketContextType>({
  socket: null,
  connected: false,
  user: null,
});

export function SocketProvider({ children }: { children: ReactNode }) {
  const [socket, setSocket] = useState<Socket | null>(null);
  const [connected, setConnected] = useState(false);
  const [user, setUser] = useState<User | null>(null);

  useEffect(() => {
    // Pegar token do URL (vindo do games-admin)
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token') || localStorage.getItem('auth_token');

    if (token) {
      localStorage.setItem('auth_token', token);
      // Limpar token do URL
      if (params.get('token')) {
        window.history.replaceState({}, '', window.location.pathname);
      }
    }

    const newSocket = io(import.meta.env.VITE_API_URL || 'http://localhost:3001', {
      auth: { token },
    });

    newSocket.on('connect', () => {
      setConnected(true);
      console.log('Connected to server');
    });

    newSocket.on('disconnect', () => {
      setConnected(false);
      console.log('Disconnected from server');
    });

    newSocket.on('auth:success', (userData: User) => {
      setUser(userData);
    });

    newSocket.on('auth:error', (error: { message: string }) => {
      console.error('Auth error:', error.message);
      localStorage.removeItem('auth_token');
    });

    setSocket(newSocket);

    return () => {
      newSocket.close();
    };
  }, []);

  return (
    <SocketContext.Provider value={{ socket, connected, user }}>
      {children}
    </SocketContext.Provider>
  );
}

export function useSocket() {
  return useContext(SocketContext);
}
`);
    // pages/Home.tsx
    await fs_extra_1.default.writeFile(path_1.default.join(clientPath, 'src', 'pages', 'Home.tsx'), `import { useSocket } from '../context/SocketContext';

export default function Home() {
  const { connected, user } = useSocket();
  const gamesAdminUrl = import.meta.env.VITE_GAMES_ADMIN_URL || 'https://admin.mysys.shop';
  const gameCode = import.meta.env.VITE_GAME_CODE || '${options.gameCode}';

  const handleLogin = () => {
    // Redirecionar para o portal de login
    const returnUrl = encodeURIComponent(window.location.origin);
    window.location.href = \`\${gamesAdminUrl}/auth/game/\${gameCode}?returnUrl=\${returnUrl}\`;
  };

  return (
    <div className="home">
      <h1>${options.gameName}</h1>

      <div className="status">
        Status: {connected ? '🟢 Conectado' : '🔴 Desconectado'}
      </div>

      {!user ? (
        <div className="login-section">
          <p>Faca login para jogar</p>
          <button onClick={handleLogin} className="btn btn-primary">
            Entrar com Google
          </button>
        </div>
      ) : (
        <div className="user-section">
          <p>Ola, {user.display_name}!</p>
          {user.avatar_url && (
            <img src={user.avatar_url} alt="Avatar" className="avatar" />
          )}
          <button className="btn btn-primary">Jogar</button>
        </div>
      )}
    </div>
  );
}
`);
    // pages/Game.tsx
    await fs_extra_1.default.writeFile(path_1.default.join(clientPath, 'src', 'pages', 'Game.tsx'), `import { useParams } from 'react-router-dom';
import { useSocket } from '../context/SocketContext';

export default function Game() {
  const { roomCode } = useParams();
  const { socket, connected, user } = useSocket();

  return (
    <div className="game">
      <header>
        <span>Sala: {roomCode}</span>
        <span>Status: {connected ? '🟢' : '🔴'}</span>
        {user && <span>{user.display_name}</span>}
      </header>

      <main>
        <h2>${options.gameName}</h2>
        <p>Implemente seu jogo aqui!</p>
        {/* Seu jogo vai aqui */}
      </main>
    </div>
  );
}
`);
    // styles/global.css
    await fs_extra_1.default.writeFile(path_1.default.join(clientPath, 'src', 'styles', 'global.css'), `* {
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

.home, .game {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  text-align: center;
}

.status {
  margin: 1rem 0;
  font-size: 0.9rem;
  color: #888;
}

.login-section, .user-section {
  margin-top: 2rem;
}

.avatar {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  margin: 1rem 0;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: #4285f4;
  color: white;
}

.btn-primary:hover {
  background: #3367d6;
}

.game header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 8px;
  margin-bottom: 2rem;
}

.game main {
  padding: 2rem;
}
`);
    // vite-env.d.ts
    await fs_extra_1.default.writeFile(path_1.default.join(clientPath, 'src', 'vite-env.d.ts'), `/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_API_URL: string;
  readonly VITE_GAME_CODE: string;
  readonly VITE_GAMES_ADMIN_URL: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
`);
}
async function createServer(projectPath, options) {
    const serverPath = path_1.default.join(projectPath, 'src', 'server');
    await fs_extra_1.default.ensureDir(path_1.default.join(serverPath, 'src', 'socket'));
    await fs_extra_1.default.ensureDir(path_1.default.join(serverPath, 'src', 'services'));
    await fs_extra_1.default.ensureDir(path_1.default.join(serverPath, 'src', 'services', 'game'));
    await fs_extra_1.default.ensureDir(path_1.default.join(serverPath, 'prisma'));
    // package.json
    await fs_extra_1.default.writeJson(path_1.default.join(serverPath, 'package.json'), {
        name: `@${options.projectName}/server`,
        version: '1.0.0',
        private: true,
        main: 'dist/index.js',
        scripts: {
            dev: 'nodemon src/index.ts',
            build: 'tsc',
            start: 'node dist/index.js',
            'db:migrate': 'prisma migrate dev',
            'db:push': 'prisma db push',
            'db:studio': 'prisma studio',
            'db:generate': 'prisma generate',
        },
        dependencies: {
            '@prisma/client': '^5.8.0',
            cors: '^2.8.5',
            dotenv: '^16.3.1',
            express: '^4.18.2',
            jsonwebtoken: '^9.0.2',
            'socket.io': '^4.7.2',
        },
        devDependencies: {
            '@types/cors': '^2.8.17',
            '@types/express': '^4.17.21',
            '@types/jsonwebtoken': '^9.0.5',
            '@types/node': '^20.10.6',
            nodemon: '^3.0.2',
            prisma: '^5.8.0',
            'ts-node': '^10.9.2',
            typescript: '^5.3.3',
        },
    }, { spaces: 2 });
    // tsconfig.json
    await fs_extra_1.default.writeJson(path_1.default.join(serverPath, 'tsconfig.json'), {
        compilerOptions: {
            target: 'ES2022',
            module: 'commonjs',
            lib: ['ES2022'],
            outDir: './dist',
            rootDir: './src',
            strict: true,
            esModuleInterop: true,
            skipLibCheck: true,
            forceConsistentCasingInFileNames: true,
            resolveJsonModule: true,
            declaration: true,
        },
        include: ['src/**/*'],
        exclude: ['node_modules', 'dist'],
    }, { spaces: 2 });
    // nodemon.json
    await fs_extra_1.default.writeJson(path_1.default.join(serverPath, 'nodemon.json'), {
        watch: ['src'],
        ext: 'ts',
        exec: 'ts-node src/index.ts',
    }, { spaces: 2 });
    // src/index.ts
    await fs_extra_1.default.writeFile(path_1.default.join(serverPath, 'src', 'index.ts'), `import 'dotenv/config';
import express from 'express';
import { createServer } from 'http';
import { Server } from 'socket.io';
import cors from 'cors';
import { PrismaClient } from '@prisma/client';
import { registerAuthHandler } from './socket/auth.handler';
import { registerGameHandler } from './socket/game.handler';
import { registerRoomHandler } from './socket/room.handler';

// Prisma
export const prisma = new PrismaClient();

// Express
const app = express();
const httpServer = createServer(app);

// Socket.IO
export const io = new Server(httpServer, {
  cors: {
    origin: process.env.CLIENT_URL || 'http://localhost:5173',
    methods: ['GET', 'POST'],
  },
});

// Middleware
app.use(cors());
app.use(express.json());

// Health check
app.get('/health', (req, res) => {
  res.json({ status: 'ok', game: process.env.GAME_CODE });
});

// Socket handlers
io.on('connection', (socket) => {
  registerAuthHandler(socket);
  registerGameHandler(socket);
  registerRoomHandler(socket);
});

// Start server
const PORT = process.env.PORT || 3001;

httpServer.listen(PORT, () => {
  console.log(\`Server running on port \${PORT}\`);
  console.log(\`Game: \${process.env.GAME_CODE || '${options.gameCode}'}\`);
});
`);
    // src/socket/auth.handler.ts
    await fs_extra_1.default.writeFile(path_1.default.join(serverPath, 'src', 'socket', 'auth.handler.ts'), `import { Socket } from 'socket.io';
import jwt from 'jsonwebtoken';
import { prisma } from '../index';

interface JwtPayload {
  userId: string;
  email: string;
  username: string;
  displayName: string;
  avatarUrl?: string;
}

export function registerAuthHandler(socket: Socket) {
  const token = socket.handshake.auth.token;

  if (!token) {
    socket.emit('auth:error', { message: 'Token nao fornecido' });
    return;
  }

  try {
    const secret = process.env.GAMES_ADMIN_JWT_SECRET;
    if (!secret) {
      throw new Error('JWT secret not configured');
    }

    const payload = jwt.verify(token, secret) as JwtPayload;

    // Buscar ou criar usuario
    findOrCreateUser(payload).then((user) => {
      // Salvar user no socket para uso posterior
      (socket as any).user = user;

      socket.emit('auth:success', {
        id: user.id,
        username: user.username,
        display_name: user.display_name,
        avatar_url: user.avatar_url,
      });

      console.log(\`Player authenticated: \${user.display_name}\`);
    });

  } catch (error) {
    console.error('Auth error:', error);
    socket.emit('auth:error', { message: 'Token invalido' });
  }
}

async function findOrCreateUser(payload: JwtPayload) {
  let user = await prisma.user.findUnique({
    where: { game_user_id: payload.userId },
  });

  if (!user) {
    user = await prisma.user.create({
      data: {
        game_user_id: payload.userId,
        email: payload.email,
        username: payload.username,
        display_name: payload.displayName,
        avatar_url: payload.avatarUrl,
      },
    });
  }

  return user;
}
`);
    // src/socket/game.handler.ts
    await fs_extra_1.default.writeFile(path_1.default.join(serverPath, 'src', 'socket', 'game.handler.ts'), `import { Socket } from 'socket.io';

export function registerGameHandler(socket: Socket) {
  socket.on('game:action', (data, callback) => {
    // TODO: Implementar acoes do jogo
    callback({ success: false, error: 'Not implemented' });
  });

  socket.on('disconnect', () => {
    const user = (socket as any).user;
    if (user) {
      console.log(\`Player disconnected: \${user.display_name}\`);
    }
  });
}
`);
    // src/socket/room.handler.ts
    await fs_extra_1.default.writeFile(path_1.default.join(serverPath, 'src', 'socket', 'room.handler.ts'), `import { Socket } from 'socket.io';

export function registerRoomHandler(socket: Socket) {
  socket.on('room:create', (data, callback) => {
    // TODO: Implementar criacao de sala
    callback({ success: false, error: 'Not implemented' });
  });

  socket.on('room:join', (data, callback) => {
    // TODO: Implementar entrada em sala
    callback({ success: false, error: 'Not implemented' });
  });

  socket.on('room:leave', (data, callback) => {
    // TODO: Implementar saida de sala
    callback({ success: false, error: 'Not implemented' });
  });
}
`);
    // prisma/schema.prisma - Baseado no Buckshot
    await fs_extra_1.default.writeFile(path_1.default.join(serverPath, 'prisma', 'schema.prisma'), `// ==========================================
// PRISMA SCHEMA - ${options.gameName.toUpperCase()}
// Padronizado estilo Laravel (snake_case)
// ==========================================

generator client {
  provider = "prisma-client-js"
}

datasource db {
  provider = "mysql"
  url      = env("DATABASE_URL")
}

// ==========================================
// USERS & AUTHENTICATION
// ==========================================

model User {
  id String @id @default(uuid())

  // OAuth / External IDs
  google_id    String? @unique
  game_user_id String? @unique // Games Admin centralized auth

  // Profile
  email        String  @unique
  username     String  @unique
  display_name String
  avatar_url   String?

  // Flags
  is_admin Boolean @default(false)

  // Game Stats
  games_played Int @default(0)
  games_won    Int @default(0)

  // Ranking
  tier     String @default("Bronze")
  division Int?   @default(4)
  lp       Int    @default(0)
  mmr      Int    @default(0)

  // Progression
  total_xp Int @default(0)

  // Timestamps
  created_at    DateTime  @default(now())
  updated_at    DateTime  @updatedAt
  last_login_at DateTime?

  // Relations
  game_participants GameParticipant[]

  // Indexes
  @@index([tier, division, lp])
  @@index([mmr(sort: Desc)])
  @@index([total_xp(sort: Desc)])
  @@map("users")
}

// ==========================================
// GAMES
// ==========================================

model Game {
  id String @id @default(uuid())

  // Identifiers
  room_code String @unique

  // Foreign Keys
  winner_id String?

  // Settings
  max_players Int     @default(${options.maxPlayers})
  is_ranked   Boolean @default(false)

  // State
  status     GameStatus @default(WAITING)
  game_state String?    @db.LongText

  // Timestamps
  created_at DateTime  @default(now())
  started_at DateTime?
  ended_at   DateTime?

  // Relations
  game_participants GameParticipant[]

  // Indexes
  @@index([status])
  @@index([room_code])
  @@index([created_at(sort: Desc)])
  @@map("games")
}

enum GameStatus {
  WAITING
  IN_PROGRESS
  COMPLETED
  ABANDONED
}

model GameParticipant {
  id String @id @default(uuid())

  // Foreign Keys
  game_id String
  user_id String?

  // Guest Info
  guest_name String?

  // Socket
  socket_id String?

  // Results
  position Int?

  // Ranking Changes
  xp_earned  Int?
  lp_change  Int?
  mmr_change Int?

  // Timestamps
  joined_at DateTime  @default(now())
  left_at   DateTime?

  // Relations
  game Game  @relation(fields: [game_id], references: [id], onDelete: Cascade)
  user User? @relation(fields: [user_id], references: [id], onDelete: SetNull)

  // Indexes
  @@unique([game_id, user_id])
  @@index([game_id])
  @@index([user_id])
  @@map("game_participants")
}
`);
}
async function createShared(projectPath, options) {
    const sharedPath = path_1.default.join(projectPath, 'src', 'shared');
    await fs_extra_1.default.ensureDir(path_1.default.join(sharedPath, 'src', 'types'));
    await fs_extra_1.default.ensureDir(path_1.default.join(sharedPath, 'src', 'constants'));
    // package.json
    await fs_extra_1.default.writeJson(path_1.default.join(sharedPath, 'package.json'), {
        name: `@${options.projectName}/shared`,
        private: true,
        main: 'src/index.ts',
        types: 'src/index.ts',
    }, { spaces: 2 });
    // index.ts
    await fs_extra_1.default.writeFile(path_1.default.join(sharedPath, 'src', 'index.ts'), `export * from './types';
export * from './constants';
`);
    // types/index.ts
    await fs_extra_1.default.writeFile(path_1.default.join(sharedPath, 'src', 'types', 'index.ts'), `export * from './game.types';
export * from './socket-events.types';
`);
    // types/game.types.ts
    await fs_extra_1.default.writeFile(path_1.default.join(sharedPath, 'src', 'types', 'game.types.ts'), `// ==========================================
// GAME TYPES - ${options.gameName.toUpperCase()}
// ==========================================

export interface Player {
  id: string;
  username: string;
  displayName: string;
  avatarUrl?: string;
  isReady: boolean;
  isHost: boolean;
}

export interface Room {
  code: string;
  status: 'WAITING' | 'PLAYING' | 'FINISHED';
  players: Player[];
  maxPlayers: number;
  hostId: string;
}

export interface GameState {
  roomCode: string;
  status: 'WAITING' | 'PLAYING' | 'FINISHED';
  players: Player[];
  // TODO: Adicionar campos especificos do jogo
}
`);
    // types/socket-events.types.ts
    await fs_extra_1.default.writeFile(path_1.default.join(sharedPath, 'src', 'types', 'socket-events.types.ts'), `// ==========================================
// SOCKET EVENTS - ${options.gameName.toUpperCase()}
// ==========================================

// Client -> Server
export interface ClientToServerEvents {
  'room:create': (data: { maxPlayers?: number }, callback: (response: RoomResponse) => void) => void;
  'room:join': (data: { roomCode: string }, callback: (response: RoomResponse) => void) => void;
  'room:leave': (data: {}, callback: (response: BaseResponse) => void) => void;
  'game:action': (data: { action: string; payload: unknown }, callback: (response: BaseResponse) => void) => void;
}

// Server -> Client
export interface ServerToClientEvents {
  'auth:success': (user: AuthUser) => void;
  'auth:error': (error: { message: string }) => void;
  'room:updated': (room: RoomData) => void;
  'room:playerJoined': (player: PlayerData) => void;
  'room:playerLeft': (data: { playerId: string }) => void;
  'game:started': (state: GameStateData) => void;
  'game:updated': (state: GameStateData) => void;
  'game:ended': (result: GameResult) => void;
}

// Data types
export interface BaseResponse {
  success: boolean;
  error?: string;
}

export interface RoomResponse extends BaseResponse {
  room?: RoomData;
}

export interface AuthUser {
  id: string;
  username: string;
  display_name: string;
  avatar_url?: string;
}

export interface PlayerData {
  id: string;
  username: string;
  displayName: string;
  avatarUrl?: string;
  isReady: boolean;
  isHost: boolean;
}

export interface RoomData {
  code: string;
  status: string;
  players: PlayerData[];
  maxPlayers: number;
  hostId: string;
}

export interface GameStateData {
  // TODO: Definir estado do jogo
}

export interface GameResult {
  winnerId?: string;
  // TODO: Definir resultado do jogo
}
`);
    // constants/index.ts
    await fs_extra_1.default.writeFile(path_1.default.join(sharedPath, 'src', 'constants', 'index.ts'), `export * from './game-config';
`);
    // constants/game-config.ts
    await fs_extra_1.default.writeFile(path_1.default.join(sharedPath, 'src', 'constants', 'game-config.ts'), `// ==========================================
// GAME CONFIG - ${options.gameName.toUpperCase()}
// ==========================================

export const GAME_CONFIG = {
  CODE: '${options.gameCode}',
  NAME: '${options.gameName}',
  MAX_PLAYERS: ${options.maxPlayers},
  MIN_PLAYERS: 2,
  TURN_TIMEOUT: 120, // segundos
  RECONNECT_GRACE_PERIOD: 60, // segundos
} as const;

export const TIERS = [
  'Bronze',
  'Silver',
  'Gold',
  'Platinum',
  'Diamond',
  'Master',
  'Grandmaster',
] as const;

export type Tier = typeof TIERS[number];
`);
}
//# sourceMappingURL=index.js.map