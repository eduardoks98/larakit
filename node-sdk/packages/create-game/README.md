# @mysys/create-game

CLI para criar novos jogos MySys rapidamente.

## Uso

```bash
npx @mysys/create-game meu-novo-jogo
```

Ou globalmente:

```bash
npm install -g @mysys/create-game
create-game meu-novo-jogo
```

## O que é criado

```
meu-novo-jogo/
├── src/
│   ├── client/                 # Frontend React + Vite
│   │   ├── src/
│   │   │   ├── App.tsx
│   │   │   ├── main.tsx
│   │   │   ├── pages/
│   │   │   ├── components/
│   │   │   └── hooks/
│   │   ├── package.json
│   │   └── vite.config.ts
│   ├── server/                 # Backend Node.js + Express + Socket.IO
│   │   ├── src/
│   │   │   ├── index.ts
│   │   │   ├── socket/
│   │   │   └── services/
│   │   ├── prisma/
│   │   │   └── schema.prisma
│   │   └── package.json
│   └── shared/                 # Tipos compartilhados
│       ├── src/
│       │   ├── types/
│       │   └── constants/
│       └── package.json
├── .env.example
├── .gitignore
├── docker-compose.yml
├── package.json
└── README.md
```

## Opções

```bash
# Criar com nome específico
npx @mysys/create-game meu-jogo

# Modo interativo (perguntas sobre configuração)
npx @mysys/create-game

# Criar em diretório atual
npx @mysys/create-game .
```

## Após criar

```bash
cd meu-novo-jogo

# Instalar dependências
npm install

# Configurar banco de dados
cp .env.example .env
# Editar .env com suas configurações

# Rodar migrações
npm run db:migrate

# Iniciar desenvolvimento
npm run dev
```

## Configuração

Após criar o projeto, você precisa:

1. **Registrar o jogo no games-admin**
   - Acessar painel admin
   - Criar novo jogo
   - Copiar API Key e Secret

2. **Configurar .env**
   ```env
   GAME_CODE=MEUJOGO
   GAMES_ADMIN_URL=https://admin.mysys.shop
   GAMES_ADMIN_JWT_SECRET=seu_secret
   DATABASE_URL=mysql://user:pass@localhost:3306/meujogo
   ```

3. **Implementar lógica do jogo**
   - Editar `src/server/src/services/game.service.ts`
   - Editar `src/shared/src/types/game.types.ts`
   - Criar componentes em `src/client/src/components/game/`
