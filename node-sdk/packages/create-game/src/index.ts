// ==========================================
// CREATE GAME
// Funcao principal para criar novo jogo
// Baseado na estrutura do Buckshot Roulette
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

  // Verificar se diretorio ja existe
  if (await fs.pathExists(projectPath)) {
    throw new Error(`Diretorio "${projectName}" ja existe`);
  }

  // Criar estrutura de diretorios
  await fs.ensureDir(projectPath);

  console.log(chalk.gray('  Criando estrutura de diretorios...'));

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
      'db:studio': 'npm run db:studio -w src/server',
    },
    devDependencies: {
      concurrently: '^8.2.2',
    },
  }, { spaces: 2 });

  // .env.example
  await fs.writeFile(path.join(projectPath, '.env.example'), `# Jogo
GAME_CODE=${gameCode}
GAME_NAME="${gameName}"

# games-admin (Portal de Login)
GAMES_ADMIN_URL=${gamesAdminUrl}
GAMES_ADMIN_API_URL=${gamesAdminUrl}
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
VITE_AUTH_URL=${gamesAdminUrl}
VITE_PORTAL_URL=${gamesAdminUrl}
VITE_GAMES_ADMIN_URL=${gamesAdminUrl}

# Reverb WebSocket (Real-time auth sync)
VITE_REVERB_APP_KEY=your_reverb_app_key
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_FORCE_TLS=false
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

async function createClient(projectPath: string, options: CreateGameOptions): Promise<void> {
  const clientPath = path.join(projectPath, 'src', 'client');
  await fs.ensureDir(path.join(clientPath, 'src', 'pages'));
  await fs.ensureDir(path.join(clientPath, 'src', 'components', 'game'));
  await fs.ensureDir(path.join(clientPath, 'src', 'components', 'common'));
  await fs.ensureDir(path.join(clientPath, 'src', 'hooks'));
  await fs.ensureDir(path.join(clientPath, 'src', 'context'));
  await fs.ensureDir(path.join(clientPath, 'src', 'services'));
  await fs.ensureDir(path.join(clientPath, 'src', 'styles'));

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

  // index.html - Com Pusher.js para Reverb WebSocket (logout sync)
  await fs.writeFile(path.join(clientPath, 'index.html'), `<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>${options.gameName}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Pusher for Reverb WebSocket (real-time logout sync) -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
  </head>
  <body>
    <div id="root"></div>
    <script type="module" src="/src/main.tsx"></script>
  </body>
</html>
`);

  // main.tsx - Com AuthProvider e TabSyncProvider
  await fs.writeFile(path.join(clientPath, 'src', 'main.tsx'), `import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { SocketProvider } from './context/SocketContext';
import { TabSyncProvider } from './context/TabSyncContext';
import App from './App';
import './styles/global.css';

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <BrowserRouter>
      <AuthProvider>
        <TabSyncProvider>
          <SocketProvider>
            <App />
          </SocketProvider>
        </TabSyncProvider>
      </AuthProvider>
    </BrowserRouter>
  </React.StrictMode>
);
`);

  // App.tsx
  await fs.writeFile(path.join(clientPath, 'src', 'App.tsx'), `import { Routes, Route } from 'react-router-dom';
import Home from './pages/Home';
import Lobby from './pages/Lobby';
import Game from './pages/Game';

function App() {
  return (
    <Routes>
      <Route path="/" element={<Home />} />
      <Route path="/lobby" element={<Lobby />} />
      <Route path="/game/:roomCode" element={<Game />} />
    </Routes>
  );
}

export default App;
`);

  // pages/Lobby.tsx
  await fs.writeFile(path.join(clientPath, 'src', 'pages', 'Lobby.tsx'), `import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useSocket } from '../context/SocketContext';

export default function Lobby() {
  const { user, isAuthenticated, isLoading, logout } = useAuth();
  const { socket, connected, connect } = useSocket();
  const navigate = useNavigate();

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      navigate('/');
    }
  }, [isLoading, isAuthenticated, navigate]);

  useEffect(() => {
    if (isAuthenticated && !connected) {
      connect();
    }
  }, [isAuthenticated, connected, connect]);

  if (isLoading) {
    return (
      <div className="loading-splash">
        <h1 className="loading-splash__title">Carregando...</h1>
        <div className="loading-splash__spinner" />
      </div>
    );
  }

  return (
    <div className="lobby">
      <header className="lobby__header">
        <h1>Lobby</h1>
        <div className="lobby__user">
          {user?.avatar_url && (
            <img src={user.avatar_url} alt="Avatar" className="lobby__avatar" />
          )}
          <span>{user?.display_name}</span>
          <button onClick={logout} className="lobby__logout-btn">Sair</button>
        </div>
      </header>

      <main className="lobby__content">
        <div className="lobby__status">
          Status: {connected ? '🟢 Conectado' : '🔴 Conectando...'}
        </div>

        <div className="lobby__actions">
          <button className="landing__btn landing__btn--primary">
            Criar Sala
          </button>
          <button className="landing__btn landing__btn--gold">
            Entrar em Sala
          </button>
        </div>
      </main>
    </div>
  );
}
`);

  // context/AuthContext.tsx - SSO com MySys Portal + Reverb WebSocket para logout sync
  await fs.writeFile(path.join(clientPath, 'src', 'context', 'AuthContext.tsx'), `import { createContext, useContext, useState, useEffect, useRef, ReactNode, useCallback } from 'react';

// ==========================================
// TYPES
// ==========================================

interface User {
  id: string;
  game_user_id?: string;
  email: string;
  username: string;
  display_name: string;
  avatar_url: string | null;
  is_admin: boolean;
}

// ==========================================
// CONSTANTS
// ==========================================

const TOKEN_KEY = '${options.gameCode.toLowerCase()}_auth_token';
const COOKIE_NAME = 'mysys_token';
const AUTH_URL = import.meta.env.VITE_AUTH_URL || 'http://localhost:8000';
const GAME_CODE = import.meta.env.VITE_GAME_CODE || '${options.gameCode}';
const SYNC_CHANNEL = 'mysys_auth_sync';

// Reverb WebSocket config
const REVERB_KEY = import.meta.env.VITE_REVERB_APP_KEY || '';
const REVERB_HOST = import.meta.env.VITE_REVERB_HOST || 'localhost';
const REVERB_PORT = import.meta.env.VITE_REVERB_PORT || '8080';
const REVERB_FORCE_TLS = import.meta.env.VITE_REVERB_FORCE_TLS === 'true';

// ==========================================
// HELPERS
// ==========================================

function getCookie(name: string): string | null {
  const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  return match ? match[2] : null;
}

function deleteCookie(name: string): void {
  const hostname = window.location.hostname;
  document.cookie = \`\${name}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT\`;
  document.cookie = \`\${name}=; domain=\${hostname}; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT\`;
}

function decodeJWT(token: string): any | null {
  try {
    const payload = token.split('.')[1];
    const decoded = atob(payload.replace(/-/g, '+').replace(/_/g, '/'));
    return JSON.parse(decoded);
  } catch {
    return null;
  }
}

function isTokenExpired(token: string): boolean {
  const payload = decodeJWT(token);
  if (!payload || !payload.exp) return true;
  return payload.exp * 1000 < Date.now();
}

// ==========================================
// CONTEXT
// ==========================================

interface AuthContextType {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isAdmin: boolean;
  isLoading: boolean;
  login: () => void;
  logout: () => void;
}

const AuthContext = createContext<AuthContextType | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const fetchUserData = useCallback(async (authToken: string): Promise<User | null> => {
    try {
      const response = await fetch('/api/auth/me', {
        headers: { 'Authorization': \`Bearer \${authToken}\` },
      });
      if (response.ok) {
        const data = await response.json();
        return data.user;
      }
      return null;
    } catch {
      return null;
    }
  }, []);

  const handleUserLogin = useCallback(async (authToken: string) => {
    const jwtUser = decodeJWT(authToken);
    if (!jwtUser) {
      setUser(null);
      setToken(null);
      localStorage.removeItem(TOKEN_KEY);
      return;
    }

    localStorage.setItem(TOKEN_KEY, authToken);
    setToken(authToken);

    const fullUser = await fetchUserData(authToken);
    if (fullUser) {
      setUser(fullUser);
    } else {
      localStorage.removeItem(TOKEN_KEY);
      deleteCookie(COOKIE_NAME);
      setUser(null);
      setToken(null);
    }
  }, [fetchUserData]);

  const tokenProcessedRef = useRef(false);

  // Initialize auth state
  useEffect(() => {
    const initAuth = async () => {
      const urlParams = new URLSearchParams(window.location.search);
      const tokenFromUrl = urlParams.get('token');

      if (tokenProcessedRef.current && !tokenFromUrl) return;

      if (tokenFromUrl) {
        tokenProcessedRef.current = true;
        window.history.replaceState({}, document.title, window.location.pathname);
        await handleUserLogin(tokenFromUrl);
        try {
          new BroadcastChannel(SYNC_CHANNEL).postMessage({ type: 'LOGIN' });
        } catch (e) {}
        setIsLoading(false);
        return;
      }

      const cookieToken = getCookie(COOKIE_NAME);
      if (cookieToken && !isTokenExpired(cookieToken)) {
        await handleUserLogin(cookieToken);
        setIsLoading(false);
        return;
      }

      const storedToken = localStorage.getItem(TOKEN_KEY);
      if (storedToken && !isTokenExpired(storedToken)) {
        await handleUserLogin(storedToken);
        setIsLoading(false);
        return;
      }

      setIsLoading(false);
    };

    initAuth();
  }, [handleUserLogin]);

  // Tab sync via BroadcastChannel
  useEffect(() => {
    if (typeof BroadcastChannel === 'undefined') return;

    const channel = new BroadcastChannel(SYNC_CHANNEL);

    channel.onmessage = (event) => {
      if (event.data.type === 'LOGOUT') {
        localStorage.removeItem(TOKEN_KEY);
        deleteCookie(COOKIE_NAME);
        window.location.reload();
      } else if (event.data.type === 'LOGIN') {
        const currentToken = localStorage.getItem(TOKEN_KEY);
        if (!currentToken) window.location.reload();
      }
    };

    return () => channel.close();
  }, []);

  // Reverb WebSocket sync
  useEffect(() => {
    if (!user?.id || !REVERB_KEY) return;

    const Pusher = (window as any).Pusher;
    if (!Pusher) {
      console.warn('[Reverb] Pusher not loaded from CDN');
      return;
    }

    let pusher: any = null;
    let channel: any = null;

    try {
      pusher = new Pusher(REVERB_KEY, {
        cluster: 'mt1',
        wsHost: REVERB_HOST,
        wsPort: parseInt(REVERB_PORT),
        wssPort: parseInt(REVERB_PORT),
        forceTLS: REVERB_FORCE_TLS,
        disableStats: true,
        enabledTransports: REVERB_FORCE_TLS ? ['wss'] : ['ws'],
      });

      const channelId = user.game_user_id || user.id;
      channel = pusher.subscribe('auth.user.' + channelId);
      channel.bind('auth.sync', (data: { type: string }) => {
        console.log('[Reverb] Auth sync event:', data);
        if (data.type === 'LOGOUT') {
          localStorage.removeItem(TOKEN_KEY);
          deleteCookie(COOKIE_NAME);
          try {
            new BroadcastChannel(SYNC_CHANNEL).postMessage({ type: 'LOGOUT' });
          } catch (e) {}
          window.location.reload();
        } else if (data.type === 'LOGIN') {
          try {
            new BroadcastChannel(SYNC_CHANNEL).postMessage({ type: 'LOGIN' });
          } catch (e) {}
          window.location.reload();
        }
      });

      console.log('[Reverb] Connected to channel: auth.user.' + channelId);
    } catch (error) {
      console.error('[Reverb] Connection error:', error);
    }

    return () => {
      if (channel) try { channel.unbind_all(); } catch (e) {}
      if (pusher) try { pusher.disconnect(); } catch (e) {}
    };
  }, [user?.id, user?.game_user_id]);

  const login = useCallback(() => {
    const callbackUrl = \`\${window.location.origin}/lobby\`;
    const returnUrl = encodeURIComponent(callbackUrl);
    window.location.href = \`\${AUTH_URL}/login?source=\${GAME_CODE}&return_url=\${returnUrl}\`;
  }, []);

  const logout = useCallback(async () => {
    const currentToken = token || localStorage.getItem(TOKEN_KEY);

    if (currentToken) {
      try {
        await fetch('/api/auth/logout', {
          method: 'POST',
          headers: { 'Authorization': \`Bearer \${currentToken}\` },
        });
      } catch (error) {
        console.error('[Auth] Logout error:', error);
      }
    }

    localStorage.removeItem(TOKEN_KEY);
    deleteCookie(COOKIE_NAME);
    setUser(null);
    setToken(null);

    try {
      new BroadcastChannel(SYNC_CHANNEL).postMessage({ type: 'LOGOUT' });
    } catch (e) {}
  }, [token]);

  const value: AuthContextType = {
    user,
    token,
    isAuthenticated: !!user,
    isAdmin: user?.is_admin ?? false,
    isLoading,
    login,
    logout,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthContextType {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
}
`);

  // context/TabSyncContext.tsx - Splash de sincronizacao entre abas
  await fs.writeFile(path.join(clientPath, 'src', 'context', 'TabSyncContext.tsx'), `import { createContext, useContext, useState, useEffect, useCallback, useRef, ReactNode } from 'react';

interface TabSyncContextType {
  isFocused: boolean;
  isSyncing: boolean;
  showOverlay: boolean;
  overlayMessage: string;
}

interface TabSyncProviderProps {
  children: ReactNode;
  reloadOnFocus?: boolean;
  minBlurTime?: number;
  enabled?: boolean;
}

const TabSyncContext = createContext<TabSyncContextType | null>(null);

function SyncOverlay({ isVisible, message }: { isVisible: boolean; message: string }) {
  if (!isVisible) return null;

  return (
    <div style={{
      position: 'fixed',
      inset: 0,
      background: 'rgba(10, 10, 10, 0.95)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      zIndex: 9999,
      animation: 'fadeIn 0.3s ease',
    }}>
      <style>{\`
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
      \`}</style>
      <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '24px' }}>
        <div style={{ position: 'relative', width: '80px', height: '80px' }}>
          <div style={{
            position: 'absolute', inset: 0, border: '3px solid transparent',
            borderTopColor: '#ff0040', borderRadius: '50%', animation: 'spin 1.5s linear infinite',
          }} />
          <div style={{
            position: 'absolute', inset: '8px', border: '3px solid transparent',
            borderRightColor: '#ff3366', borderRadius: '50%', animation: 'spin 1.5s linear infinite reverse',
            animationDelay: '0.15s',
          }} />
          <div style={{
            position: 'absolute', inset: '16px', border: '3px solid transparent',
            borderBottomColor: '#ff6699', borderRadius: '50%', animation: 'spin 1.5s linear infinite',
            animationDelay: '0.3s',
          }} />
        </div>
        <p style={{
          fontFamily: "'Orbitron', sans-serif", fontSize: '1.125rem', fontWeight: 500,
          color: '#ffffff', textTransform: 'uppercase', letterSpacing: '2px',
          animation: 'pulse 2s ease-in-out infinite', margin: 0,
        }}>{message}</p>
      </div>
    </div>
  );
}

export function TabSyncProvider({
  children, reloadOnFocus = true, minBlurTime = 3000, enabled = true,
}: TabSyncProviderProps) {
  const [isFocused, setIsFocused] = useState(!document.hidden);
  const [isSyncing, setIsSyncing] = useState(false);
  const blurTimestampRef = useRef<number | null>(null);
  const reloadTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const safetyTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  const clearTimeouts = useCallback(() => {
    if (reloadTimeoutRef.current) { clearTimeout(reloadTimeoutRef.current); reloadTimeoutRef.current = null; }
    if (safetyTimeoutRef.current) { clearTimeout(safetyTimeoutRef.current); safetyTimeoutRef.current = null; }
  }, []);

  const handleVisibilityChange = useCallback(() => {
    if (!enabled) return;

    if (document.hidden) {
      console.log('[TabSync] Tab lost focus');
      setIsFocused(false);
      blurTimestampRef.current = Date.now();
    } else {
      console.log('[TabSync] Tab gained focus');
      setIsFocused(true);

      if (blurTimestampRef.current && reloadOnFocus) {
        const timeAway = Date.now() - blurTimestampRef.current;
        if (timeAway >= minBlurTime) {
          console.log(\`[TabSync] Was away for \${timeAway}ms, reloading...\`);
          setIsSyncing(true);
          clearTimeouts();
          reloadTimeoutRef.current = setTimeout(() => { window.location.reload(); }, 500);
          safetyTimeoutRef.current = setTimeout(() => { setIsSyncing(false); }, 3000);
          blurTimestampRef.current = null;
          return;
        }
      }
      blurTimestampRef.current = null;
    }
  }, [enabled, minBlurTime, reloadOnFocus, clearTimeouts]);

  useEffect(() => {
    if (!enabled) return;
    document.addEventListener('visibilitychange', handleVisibilityChange);
    return () => { document.removeEventListener('visibilitychange', handleVisibilityChange); clearTimeouts(); };
  }, [enabled, handleVisibilityChange, clearTimeouts]);

  const showOverlay = enabled && (!isFocused || isSyncing);
  const overlayMessage = isSyncing ? 'Sincronizando...' : 'Jogo pausado';

  return (
    <TabSyncContext.Provider value={{ isFocused, isSyncing, showOverlay, overlayMessage }}>
      <SyncOverlay isVisible={showOverlay} message={overlayMessage} />
      {children}
    </TabSyncContext.Provider>
  );
}

export function useTabSync(): TabSyncContextType {
  const context = useContext(TabSyncContext);
  if (!context) throw new Error('useTabSync must be used within TabSyncProvider');
  return context;
}

export default TabSyncProvider;
`);

  // context/SocketContext.tsx
  await fs.writeFile(path.join(clientPath, 'src', 'context', 'SocketContext.tsx'), `import { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { io, Socket } from 'socket.io-client';
import { useAuth } from './AuthContext';

interface SocketContextType {
  socket: Socket | null;
  connected: boolean;
  connect: () => void;
  disconnect: () => void;
}

const SocketContext = createContext<SocketContextType>({
  socket: null,
  connected: false,
  connect: () => {},
  disconnect: () => {},
});

export function SocketProvider({ children }: { children: ReactNode }) {
  const { token, isAuthenticated } = useAuth();
  const [socket, setSocket] = useState<Socket | null>(null);
  const [connected, setConnected] = useState(false);

  const connect = () => {
    if (socket?.connected || !token) return;

    const newSocket = io(import.meta.env.VITE_API_URL || 'http://localhost:3001', {
      auth: { token },
    });

    newSocket.on('connect', () => {
      setConnected(true);
      console.log('[Socket] Connected');
    });

    newSocket.on('disconnect', () => {
      setConnected(false);
      console.log('[Socket] Disconnected');
    });

    newSocket.on('connect_error', (error) => {
      console.error('[Socket] Connection error:', error);
    });

    setSocket(newSocket);
  };

  const disconnect = () => {
    if (socket) {
      socket.close();
      setSocket(null);
      setConnected(false);
    }
  };

  useEffect(() => {
    return () => {
      if (socket) socket.close();
    };
  }, [socket]);

  return (
    <SocketContext.Provider value={{ socket, connected, connect, disconnect }}>
      {children}
    </SocketContext.Provider>
  );
}

export function useSocket() {
  return useContext(SocketContext);
}
`);

  // pages/Home.tsx - Landing page completa com SSO
  await fs.writeFile(path.join(clientPath, 'src', 'pages', 'Home.tsx'), `import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useSocket } from '../context/SocketContext';

const PORTAL_URL = import.meta.env.VITE_PORTAL_URL || 'http://localhost:8000';

export default function Home() {
  const { isAuthenticated, isLoading, login, user } = useAuth();
  const { connect } = useSocket();
  const navigate = useNavigate();

  const handlePlay = () => {
    if (!isAuthenticated) {
      login();
      return;
    }
    connect();
    navigate('/lobby');
  };

  if (isLoading) {
    return (
      <div className="loading-splash">
        <h1 className="loading-splash__title">${options.gameName.toUpperCase()}</h1>
        <div className="loading-splash__spinner" />
      </div>
    );
  }

  return (
    <div className="landing">
      {/* Header */}
      <header className="landing__header">
        <div className="landing__header-content">
          <a href={PORTAL_URL} className="landing__logo">
            <div className="landing__logo-icon">M</div>
            <span>MySys Games</span>
          </a>
          <nav className="landing__nav">
            <a href="#features" className="landing__nav-link">Sobre</a>
          </nav>
        </div>
      </header>

      {/* Hero Section */}
      <section className="landing__hero">
        <div className="landing__hero-content">
          <h1 className="landing__title">${options.gameName.toUpperCase()}</h1>
          <p className="landing__tagline">Jogo Multiplayer Online</p>
          <p className="landing__description">
            Entre na arena e desafie outros jogadores em batalhas emocionantes.
          </p>

          <div className="landing__cta">
            {isAuthenticated ? (
              <button onClick={handlePlay} className="landing__btn landing__btn--primary">
                Jogar
              </button>
            ) : (
              <button onClick={login} className="landing__btn landing__btn--gold">
                Entrar / Criar Conta
              </button>
            )}
          </div>

          {user && (
            <p className="landing__welcome">Bem-vindo, {user.display_name}!</p>
          )}
        </div>
      </section>

      {/* Features Section */}
      <section id="features" className="landing__features-section">
        <h2 className="landing__section-title">Recursos</h2>
        <div className="landing__features">
          <div className="landing__feature">
            <h3>Multiplayer</h3>
            <p>Jogue com amigos ou desafie jogadores do mundo todo.</p>
          </div>
          <div className="landing__feature">
            <h3>Ranking</h3>
            <p>Suba no ranking e mostre suas habilidades.</p>
          </div>
          <div className="landing__feature">
            <h3>Tempo Real</h3>
            <p>Partidas em tempo real com baixa latencia.</p>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="landing__footer">
        <p>&copy; ${new Date().getFullYear()} MySys Games. Todos os direitos reservados.</p>
      </footer>
    </div>
  );
}
`);

  // pages/Game.tsx
  await fs.writeFile(path.join(clientPath, 'src', 'pages', 'Game.tsx'), `import { useParams } from 'react-router-dom';
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

  // styles/global.css - Estilos completos com landing page
  await fs.writeFile(path.join(clientPath, 'src', 'styles', 'global.css'), `* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #0a0a0f;
  color: #ffffff;
  min-height: 100vh;
}

/* ==========================================
   LOADING SPLASH
   ========================================== */

.loading-splash {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0a0f 0%, #1a1a2e 100%);
}

.loading-splash__title {
  font-family: 'Orbitron', sans-serif;
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 2rem;
  background: linear-gradient(135deg, #ff0040 0%, #ff6699 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.loading-splash__spinner {
  width: 50px;
  height: 50px;
  border: 3px solid transparent;
  border-top-color: #ff0040;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* ==========================================
   LANDING PAGE
   ========================================== */

.landing {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.landing__header {
  padding: 1rem 2rem;
  background: rgba(0, 0, 0, 0.5);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.landing__header-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.landing__logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
  color: #ffffff;
  font-family: 'Orbitron', sans-serif;
  font-weight: 600;
}

.landing__logo-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #ff0040 0%, #ff6699 100%);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 1.25rem;
}

.landing__nav-link {
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  transition: color 0.2s;
}

.landing__nav-link:hover {
  color: #ffffff;
}

.landing__hero {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  background: radial-gradient(ellipse at center, rgba(255, 0, 64, 0.1) 0%, transparent 70%);
}

.landing__hero-content {
  text-align: center;
  max-width: 600px;
}

.landing__title {
  font-family: 'Orbitron', sans-serif;
  font-size: 3.5rem;
  font-weight: 900;
  margin-bottom: 1rem;
  background: linear-gradient(135deg, #ffffff 0%, #888888 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.landing__tagline {
  font-family: 'Orbitron', sans-serif;
  font-size: 1.25rem;
  color: #ff0040;
  margin-bottom: 1.5rem;
  text-transform: uppercase;
  letter-spacing: 2px;
}

.landing__description {
  color: rgba(255, 255, 255, 0.7);
  font-size: 1.1rem;
  line-height: 1.6;
  margin-bottom: 2rem;
}

.landing__cta {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.landing__btn {
  padding: 1rem 2.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.landing__btn--primary {
  background: linear-gradient(135deg, #ff0040 0%, #ff3366 100%);
  color: #ffffff;
}

.landing__btn--primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(255, 0, 64, 0.4);
}

.landing__btn--gold {
  background: linear-gradient(135deg, #d4a418 0%, #f0c93d 100%);
  color: #000000;
}

.landing__btn--gold:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(212, 164, 24, 0.4);
}

.landing__welcome {
  margin-top: 1.5rem;
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.9rem;
}

.landing__features-section {
  padding: 4rem 2rem;
  background: rgba(0, 0, 0, 0.3);
}

.landing__section-title {
  font-family: 'Orbitron', sans-serif;
  font-size: 2rem;
  text-align: center;
  margin-bottom: 3rem;
}

.landing__features {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
  max-width: 1000px;
  margin: 0 auto;
}

.landing__feature {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  transition: all 0.3s;
}

.landing__feature:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateY(-4px);
}

.landing__feature h3 {
  font-family: 'Orbitron', sans-serif;
  font-size: 1.25rem;
  margin-bottom: 0.75rem;
  color: #ff0040;
}

.landing__feature p {
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.5;
}

.landing__footer {
  padding: 2rem;
  text-align: center;
  color: rgba(255, 255, 255, 0.4);
  font-size: 0.875rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

/* ==========================================
   GAME PAGE
   ========================================== */

.game {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
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

/* ==========================================
   RESPONSIVE
   ========================================== */

@media (max-width: 768px) {
  .landing__title {
    font-size: 2.5rem;
  }

  .landing__tagline {
    font-size: 1rem;
  }

  .landing__btn {
    width: 100%;
  }
}
`);

  // vite-env.d.ts
  await fs.writeFile(path.join(clientPath, 'src', 'vite-env.d.ts'), `/// <reference types="vite/client" />

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

async function createServer(projectPath: string, options: CreateGameOptions): Promise<void> {
  const serverPath = path.join(projectPath, 'src', 'server');
  await fs.ensureDir(path.join(serverPath, 'src', 'socket'));
  await fs.ensureDir(path.join(serverPath, 'src', 'services'));
  await fs.ensureDir(path.join(serverPath, 'src', 'services', 'game'));
  await fs.ensureDir(path.join(serverPath, 'prisma'));

  // package.json
  await fs.writeJson(path.join(serverPath, 'package.json'), {
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
  await fs.writeJson(path.join(serverPath, 'tsconfig.json'), {
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
  await fs.writeJson(path.join(serverPath, 'nodemon.json'), {
    watch: ['src'],
    ext: 'ts',
    exec: 'ts-node src/index.ts',
  }, { spaces: 2 });

  // src/index.ts
  await fs.writeFile(path.join(serverPath, 'src', 'index.ts'), `import 'dotenv/config';
import express from 'express';
import { createServer } from 'http';
import { Server } from 'socket.io';
import cors from 'cors';
import jwt from 'jsonwebtoken';
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

// Auth endpoints
app.get('/api/auth/me', async (req, res) => {
  const authHeader = req.headers.authorization;
  if (!authHeader?.startsWith('Bearer ')) {
    return res.status(401).json({ error: 'Token nao fornecido' });
  }

  const token = authHeader.split(' ')[1];
  try {
    const secret = process.env.GAMES_ADMIN_JWT_SECRET;
    if (!secret) throw new Error('JWT secret not configured');

    const payload = jwt.verify(token, secret) as any;

    // Validate with MySys API
    const validateRes = await fetch(
      \`\${process.env.GAMES_ADMIN_API_URL}/api/games/\${process.env.GAME_CODE}/auth/validate\`,
      {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token }),
      }
    );

    if (!validateRes.ok) {
      return res.status(401).json({ error: 'Token invalido' });
    }

    const data = await validateRes.json();
    if (!data.valid || !data.user) {
      return res.status(401).json({ error: 'Token invalido' });
    }

    res.json({ user: { ...data.user, game_user_id: payload.sub } });
  } catch (error) {
    console.error('[Auth] /me error:', error);
    res.status(401).json({ error: 'Token invalido' });
  }
});

app.post('/api/auth/logout', async (req, res) => {
  const authHeader = req.headers.authorization;
  if (authHeader?.startsWith('Bearer ')) {
    const token = authHeader.split(' ')[1];

    // CRITICAL: Call MySys API to trigger AuthSyncEvent broadcast
    try {
      const response = await fetch(
        \`\${process.env.GAMES_ADMIN_API_URL}/api/games/\${process.env.GAME_CODE}/auth/logout\`,
        {
          method: 'POST',
          headers: {
            'Authorization': \`Bearer \${token}\`,
            'Accept': 'application/json',
          },
        }
      );
      console.log('[Auth] MySys logout API called:', response.status);
    } catch (error) {
      console.error('[Auth] Failed to call MySys logout API:', error);
    }
  }

  res.json({ message: 'Logout realizado' });
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
  await fs.writeFile(path.join(serverPath, 'src', 'socket', 'auth.handler.ts'), `import { Socket } from 'socket.io';
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
  await fs.writeFile(path.join(serverPath, 'src', 'socket', 'game.handler.ts'), `import { Socket } from 'socket.io';

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
  await fs.writeFile(path.join(serverPath, 'src', 'socket', 'room.handler.ts'), `import { Socket } from 'socket.io';

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
  await fs.writeFile(path.join(serverPath, 'prisma', 'schema.prisma'), `// ==========================================
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
  }, { spaces: 2 });

  // index.ts
  await fs.writeFile(path.join(sharedPath, 'src', 'index.ts'), `export * from './types';
export * from './constants';
`);

  // types/index.ts
  await fs.writeFile(path.join(sharedPath, 'src', 'types', 'index.ts'), `export * from './game.types';
export * from './socket-events.types';
`);

  // types/game.types.ts
  await fs.writeFile(path.join(sharedPath, 'src', 'types', 'game.types.ts'), `// ==========================================
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
  await fs.writeFile(path.join(sharedPath, 'src', 'types', 'socket-events.types.ts'), `// ==========================================
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
  await fs.writeFile(path.join(sharedPath, 'src', 'constants', 'index.ts'), `export * from './game-config';
`);

  // constants/game-config.ts
  await fs.writeFile(path.join(sharedPath, 'src', 'constants', 'game-config.ts'), `// ==========================================
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
