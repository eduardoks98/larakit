# @mysys/game-sdk-server

SDK para backend de jogos MySys com Node.js, Express e Socket.IO.

## Instalação

```bash
npm install @mysys/game-sdk-server
```

## Uso Básico

### Criar Servidor de Jogo

```typescript
import { createGameServer } from '@mysys/game-sdk-server';

const { app, io, httpServer } = createGameServer({
  gameCode: process.env.GAME_CODE!,
  gamesAdminUrl: process.env.GAMES_ADMIN_URL!,
  jwtSecret: process.env.JWT_SECRET!,
  cors: {
    origin: process.env.CLIENT_URL,
  },
});

// Registrar handlers do seu jogo
io.on('connection', (socket) => {
  // Seu código aqui
});

httpServer.listen(3001, () => {
  console.log('Game server running on port 3001');
});
```

## Módulos

### Auth (`@mysys/game-sdk-server/auth`)

Autenticação com games-admin:

```typescript
import { AuthService, authMiddleware } from '@mysys/game-sdk-server/auth';

const authService = new AuthService({
  gamesAdminUrl: 'https://admin.mysys.shop',
  jwtSecret: process.env.JWT_SECRET!,
  gameCode: 'MYGAME',
});

// Validar token
const user = await authService.validateToken(token);

// Middleware Express
app.get('/api/profile', authMiddleware(authService), (req, res) => {
  res.json(req.user);
});
```

### Socket (`@mysys/game-sdk-server/socket`)

Gestão de salas e conexões:

```typescript
import { RoomManager, SocketManager } from '@mysys/game-sdk-server/socket';

// Criar room manager customizado
class MyRoomManager extends RoomManager<MyRoom, MyPlayer> {
  createRoom(hostId: string, options: RoomOptions): MyRoom {
    // Implementação específica do jogo
  }
}

const roomManager = new MyRoomManager();
const socketManager = new SocketManager(io, roomManager, authService);
```

## AuthService

### Métodos

| Método | Descrição |
|--------|-----------|
| `validateToken(token)` | Valida JWT e retorna usuário |
| `syncUser(user)` | Sincroniza usuário com banco local |
| `getOrCreateUser(gameUserId)` | Obtém ou cria usuário local |

### Configuração

```typescript
interface AuthConfig {
  gamesAdminUrl: string;   // URL do games-admin
  jwtSecret: string;       // Secret para validar JWT
  gameCode: string;        // Código do jogo (BANGSHOT, etc.)
  tokenExpiration?: number; // Expiração em segundos (default: 7 dias)
}
```

## RoomManager

Classe abstrata para gestão de salas:

```typescript
abstract class RoomManager<TRoom, TPlayer> {
  // Métodos abstratos (implemente no seu jogo)
  abstract createRoom(hostId: string, options: unknown): TRoom;
  abstract addPlayer(room: TRoom, player: TPlayer): void;
  abstract removePlayer(room: TRoom, playerId: string): void;

  // Métodos prontos
  generateRoomCode(): string;
  getRoomByCode(code: string): TRoom | null;
  getRoomByPlayer(playerId: string): TRoom | null;
  getAllRooms(): RoomInfo[];
}
```

## SocketManager

Gerencia conexões Socket.IO:

```typescript
const socketManager = new SocketManager(io, roomManager, authService);

// Eventos emitidos automaticamente:
// - playerJoined
// - playerLeft
// - roomCreated
// - roomDeleted
// - playerDisconnected
// - playerReconnected
```

## GamesAdminClient

Cliente REST para comunicação com games-admin:

```typescript
import { GamesAdminClient } from '@mysys/game-sdk-server';

const client = new GamesAdminClient({
  baseUrl: 'https://admin.mysys.shop',
  apiKey: process.env.API_KEY!,
  apiSecret: process.env.API_SECRET!,
});

// Sincronizar stats do jogador
await client.syncUserStats('BANGSHOT', userId, {
  gamesPlayed: 10,
  gamesWon: 5,
  // ...
});

// Registrar sessão
await client.trackSession('BANGSHOT', userId, 'start');
```

## Prisma Schema Base

Estenda este schema para seu jogo:

```prisma
// prisma/schema.prisma

generator client {
  provider = "prisma-client-js"
}

datasource db {
  provider = "mysql"
  url      = env("DATABASE_URL")
}

// Importe ou copie o schema base de:
// node_modules/@mysys/game-sdk-server/prisma/schema.base.prisma

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
```

## Exemplo Completo

```typescript
// src/index.ts
import { createGameServer, AuthService, RoomManager } from '@mysys/game-sdk-server';
import { MyGameService } from './services/game.service';
import { registerGameHandlers } from './socket/game.handler';

// Criar servidor
const { app, io, httpServer } = createGameServer({
  gameCode: process.env.GAME_CODE!,
  gamesAdminUrl: process.env.GAMES_ADMIN_URL!,
  jwtSecret: process.env.JWT_SECRET!,
});

// Criar serviços
const authService = new AuthService({
  gamesAdminUrl: process.env.GAMES_ADMIN_URL!,
  jwtSecret: process.env.JWT_SECRET!,
  gameCode: process.env.GAME_CODE!,
});

const gameService = new MyGameService();

// Registrar handlers
registerGameHandlers(io, authService, gameService);

// Iniciar
httpServer.listen(process.env.PORT || 3001, () => {
  console.log(`Server running on port ${process.env.PORT || 3001}`);
});
```
