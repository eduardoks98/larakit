# @mysys/game-sdk-client

SDK React para frontend de jogos MySys.

## Instalação

```bash
npm install @mysys/game-sdk-client
```

## Uso Básico

### Setup do Provider

```tsx
// main.tsx
import { GameProvider } from '@mysys/game-sdk-client';
import App from './App';

const config = {
  gameCode: import.meta.env.VITE_GAME_CODE,
  authUrl: import.meta.env.VITE_AUTH_URL,
  serverUrl: import.meta.env.VITE_SERVER_URL,
};

ReactDOM.createRoot(document.getElementById('root')!).render(
  <GameProvider config={config}>
    <App />
  </GameProvider>
);
```

### Usando Hooks

```tsx
import { useAuth, useSocket, useGameState } from '@mysys/game-sdk-client';

function GameComponent() {
  const { user, isAuthenticated, login, logout } = useAuth();
  const { socket, connected, emit } = useSocket();
  const { gameState, updateState } = useGameState();

  if (!isAuthenticated) {
    return <button onClick={() => login('google')}>Login com Google</button>;
  }

  return (
    <div>
      <p>Olá, {user.nickname}!</p>
      <p>Socket: {connected ? 'Conectado' : 'Desconectado'}</p>
    </div>
  );
}
```

## Providers

### GameProvider

Provider principal que combina Auth + Socket:

```tsx
<GameProvider config={config}>
  {children}
</GameProvider>
```

### AuthProvider

Apenas autenticação:

```tsx
<AuthProvider config={authConfig}>
  {children}
</AuthProvider>
```

### SocketProvider

Apenas Socket.IO:

```tsx
<SocketProvider config={socketConfig}>
  {children}
</SocketProvider>
```

## Hooks

### useAuth

```tsx
const {
  user,              // AuthUser | null
  isAuthenticated,   // boolean
  isLoading,         // boolean
  error,             // string | null
  login,             // (provider: AuthProvider) => void
  logout,            // () => Promise<void>
  updateNickname,    // (nickname: string) => Promise<void>
} = useAuth();
```

### useSocket

```tsx
const {
  socket,     // Socket | null
  connected,  // boolean
  emit,       // (event: string, data: unknown) => void
  on,         // (event: string, handler: Function) => void
  off,        // (event: string, handler: Function) => void
} = useSocket();
```

### useGameState

```tsx
const {
  gameState,    // TState | null
  updateState,  // (partial: Partial<TState>) => void
  resetState,   // () => void
} = useGameState<MyGameState>();
```

### useLocalStorage

```tsx
const [value, setValue] = useLocalStorage('key', defaultValue);
```

### useTimer

```tsx
const { seconds, isRunning, start, stop, reset } = useTimer({
  initialSeconds: 120,
  onExpire: () => console.log('Tempo esgotado!'),
});
```

## Componentes

### LoginButton

```tsx
import { LoginButton } from '@mysys/game-sdk-client/components';

<LoginButton provider="google" />
<LoginButton provider="discord" />
<LoginButton provider="facebook" />
```

### SessionInvalidatedModal

```tsx
import { SessionInvalidatedModal } from '@mysys/game-sdk-client/components';

// Exibe automaticamente quando sessão é invalidada
<SessionInvalidatedModal />
```

## Configuração

```typescript
interface GameConfig {
  // Obrigatório
  gameCode: string;        // Código do jogo (BANGSHOT, etc.)
  authUrl: string;         // URL do games-admin
  serverUrl: string;       // URL do servidor do jogo

  // Opcional
  autoConnect?: boolean;   // Conectar socket automaticamente (default: true)
  reconnectAttempts?: number; // Tentativas de reconexão (default: 5)
}
```

## Exemplo Completo

```tsx
// App.tsx
import { useAuth, useSocket } from '@mysys/game-sdk-client';
import { LoginButton, SessionInvalidatedModal } from '@mysys/game-sdk-client/components';

function App() {
  const { user, isAuthenticated, isLoading } = useAuth();
  const { connected } = useSocket();

  if (isLoading) {
    return <div>Carregando...</div>;
  }

  if (!isAuthenticated) {
    return (
      <div>
        <h1>Meu Jogo</h1>
        <LoginButton provider="google" />
        <LoginButton provider="discord" />
      </div>
    );
  }

  return (
    <div>
      <SessionInvalidatedModal />
      <header>
        <span>Olá, {user.nickname}!</span>
        <span>{connected ? '🟢' : '🔴'}</span>
      </header>
      <main>
        {/* Seu jogo aqui */}
      </main>
    </div>
  );
}
```
