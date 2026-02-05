# MySys Games - Documentação

Documentação completa para desenvolvimento de jogos na plataforma MySys.

## 📁 Estrutura

```
docs/games/
├── README.md                   # Este arquivo
├── sdk-updates.md              # Mudanças necessárias no SDK
│
├── arena-gladiators/           # Primeiro jogo - Arena PvP
│   ├── README.md               # Overview do jogo
│   ├── GDD.md                  # Game Design Document
│   ├── CLASSES.md              # Sistema D&D de classes
│   ├── WEAPONS.md              # Armas e equipamentos
│   ├── COMBAT.md               # Mecânicas de combate
│   ├── NETWORKING.md           # Arquitetura Socket.IO
│   ├── RANKING.md              # Sistema de ranking/LP
│   └── ROADMAP.md              # Fases de desenvolvimento
│
├── roguelike-research/         # Pesquisa de roguelikes
│   ├── README.md               # Sumário da pesquisa
│   ├── MECHANICS.md            # Mecânicas de roguelike
│   ├── PROGRESSION.md          # Sistemas de progressão
│   ├── BOONS-SYNERGIES.md      # Upgrades e sinergias
│   ├── WAVE-SYSTEMS.md         # Spawning e waves
│   ├── MULTIPLAYER.md          # Como fazer roguelike multiplayer
│   └── INSPIRATIONS.md         # Jogos de inspiração
│
└── future-games/               # Ideias para futuros jogos
    ├── deck-builder.md         # Estilo Balatro
    ├── auto-battler.md         # Estilo TFT
    ├── tower-defense.md        # TD + Roguelike
    └── party-games.md          # Jogos estilo Buckshot
```

## 🎮 Jogos Planejados

### 1. Arena Gladiators (Primeiro)
- **Gênero:** Arena PvP com elementos D&D
- **Modo:** Competitivo 1v1, 2v2, FFA
- **Status:** Em planejamento
- **Estimativa:** 4-5 semanas para MVP

### 2. Roguelike (Futuro)
- **Gênero:** Arena Roguelike (NÃO Vampire Survivors)
- **Modo:** A definir (PvP/Co-op/Single)
- **Status:** Em pesquisa

## 🛠️ Stack Técnica

- **Backend:** Node.js + Express + Socket.IO
- **Frontend:** React + TypeScript + Canvas/Phaser
- **SDK:** `@eduardoks98/game-sdk-*`
- **Auth:** games-admin (Laravel) via JWT
- **Database:** MySQL + Prisma

## 📚 Links Úteis

- [SDK Documentation](../../node-sdk/README.md)
- [games-admin API](../../../games-admin/README.md)
- [Buckshot Reference](../../../buckshotcopy/README.md)

## 🚀 Criando um Novo Jogo

```bash
# Via CLI
npx @eduardoks98/create-game meu-jogo

# OU localmente
cd larakit/node-sdk
npm run build
node packages/create-game/dist/bin/create-game.js meu-jogo
```
