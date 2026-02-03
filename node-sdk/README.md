# MySys Game SDK

SDK Node.js/React para desenvolvimento de jogos integrados com a plataforma MySys.

## Pacotes

| Pacote | Descrição |
|--------|-----------|
| `@mysys/game-sdk-shared` | Tipos, constantes e utilitários compartilhados |
| `@mysys/game-sdk-server` | SDK para backend Node.js (Express + Socket.IO) |
| `@mysys/game-sdk-client` | SDK para frontend React |
| `@mysys/create-game` | CLI para criar novos jogos |

## Instalação

### Configurar GitHub Packages

```bash
# Autenticar no GitHub Packages (uma vez por máquina)
npm login --registry=https://npm.pkg.github.com

# Criar .npmrc no projeto
echo "@mysys:registry=https://npm.pkg.github.com" >> .npmrc
```

### Instalar nos jogos

```bash
# Todos os pacotes
npm install @mysys/game-sdk-shared @mysys/game-sdk-server @mysys/game-sdk-client

# Apenas shared (para projetos que só precisam de tipos)
npm install @mysys/game-sdk-shared
```

## Criar Novo Jogo

```bash
npx @mysys/create-game meu-novo-jogo
cd meu-novo-jogo
npm install
npm run dev
```

## Arquitetura

```
┌─────────────────────────────────────────┐
│           games-admin (Laravel)          │
│  - OAuth central                        │
│  - Gestão de usuários                   │
│  - Beta tracking                        │
└─────────────────────────────────────────┘
                    │
        ┌───────────┴───────────┐
        ▼                       ▼
┌───────────────┐       ┌───────────────┐
│   Jogo 1      │       │   Jogo 2      │
│  (Node.js)    │       │  (Node.js)    │
└───────────────┘       └───────────────┘
        │                       │
        └───────────┬───────────┘
                    │
          ┌─────────────────┐
          │  @mysys/game-sdk │
          └─────────────────┘
```

## Documentação

- [Shared Package](./packages/shared/README.md)
- [Server Package](./packages/server/README.md)
- [Client Package](./packages/client/README.md)
- [Create Game CLI](./packages/create-game/README.md)

## Desenvolvimento

```bash
# Instalar dependências
npm install

# Build todos os pacotes
npm run build

# Build pacote específico
npm run build:shared
npm run build:server
npm run build:client

# Rodar testes
npm test

# Lint
npm run lint
```

## Publicação

```bash
# Build e publicar todos os pacotes
npm run build
npm publish --workspaces
```
