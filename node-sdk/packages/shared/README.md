# @mysys/game-sdk-shared

Tipos, constantes e utilitários compartilhados para jogos MySys.

## Instalação

```bash
npm install @mysys/game-sdk-shared
```

## Uso

### Importação Completa

```typescript
import {
  // Types
  BaseGameState,
  BasePlayer,
  RankInfo,

  // Utils
  calculateEloChange,
  calculateXpGain,
  calculateLpChange,

  // Constants
  TIERS,
  LP_CONFIG,
} from '@mysys/game-sdk-shared';
```

### Importação por Módulo

```typescript
// Apenas tipos
import { BaseGameState, BasePlayer } from '@mysys/game-sdk-shared/types';

// Apenas utils
import { calculateEloChange } from '@mysys/game-sdk-shared/utils';

// Apenas constantes
import { TIERS } from '@mysys/game-sdk-shared/constants';
```

## Módulos

### Types (`@mysys/game-sdk-shared/types`)

Interfaces e tipos base para jogos:

| Tipo | Descrição |
|------|-----------|
| `BaseGameState` | Estado base de qualquer jogo |
| `BasePlayer` | Jogador base com info de conexão |
| `BaseSocketEvents` | Eventos Socket.IO genéricos |
| `RankInfo` | Informações de ranking (Tier + LP + MMR) |
| `PlayerPerformance` | Métricas de performance |

### Utils (`@mysys/game-sdk-shared/utils`)

Funções utilitárias:

| Função | Descrição |
|--------|-----------|
| `calculateEloChange()` | Calcula mudança de ELO |
| `calculatePerformanceBasedElo()` | ELO com modificadores de performance |
| `calculateXpGain()` | Calcula XP ganho em partida |
| `getLevelInfo()` | Obtém level/prestige de um total de XP |
| `calculateLpChange()` | Sistema LP + MMR (estilo LoL) |
| `getRankFromMmr()` | Tier/Divisão baseado no MMR |
| `shuffle()` | Fisher-Yates shuffle |

### Constants (`@mysys/game-sdk-shared/constants`)

Constantes do sistema:

| Constante | Descrição |
|-----------|-----------|
| `TIERS` | Lista de tiers (Bronze → Challenger) |
| `DIVISIONS` | Divisões (IV, III, II, I) |
| `LP_CONFIG` | Configurações de LP |
| `MMR_CONFIG` | Configurações de MMR |
| `XP_AWARDS` | Valores de XP por ação |

## Exemplos

### Sistema de Ranking (LP + MMR)

```typescript
import { calculateLpChange, getDisplayRank } from '@mysys/game-sdk-shared';

const result = calculateLpChange({
  currentTier: 'Gold',
  currentDivision: 3,
  currentLp: 75,
  currentMmr: 950,
  gamesSincePromo: 5,
  position: 1,           // 1º lugar
  totalPlayers: 4,
  allPlayersMmr: [950, 920, 880, 910],
  performanceScore: 0.7, // Acima da média
  wasQuitter: false,
});

console.log(result);
// {
//   newTier: 'Gold',
//   newDivision: 2,      // Promovido!
//   newLp: 5,
//   newMmr: 978,
//   lpChange: 30,
//   mmrChange: 28,
//   promoted: true,
//   demoted: false,
//   displayRank: 'Gold II'
// }
```

### Sistema de XP

```typescript
import { calculateXpGain, getLevelInfo } from '@mysys/game-sdk-shared';

const xpResult = calculateXpGain({
  position: 1,
  totalPlayers: 4,
  kills: 3,
  roundsWon: 2,
  totalRounds: 3,
  damageDealt: 8,
  itemsUsed: 5,
  selfDamage: 0,
  deaths: 1,
  prestigeLevel: 1,
});

console.log(xpResult);
// {
//   baseXp: 358,
//   prestigeMultiplier: 1.05,
//   totalXp: 376,
//   breakdown: { ... }
// }

// Obter info de level
const levelInfo = getLevelInfo(15000);
console.log(levelInfo);
// {
//   absoluteLevel: 25,
//   displayLevel: 26,
//   prestigeLevel: 0,
//   xpInCurrentLevel: 234,
//   xpForNextLevel: 513,
//   xpProgress: 0.456,
//   totalXp: 15000
// }
```

### Sistema ELO (Legacy)

```typescript
import { calculateEloChange, calculatePerformanceBasedElo } from '@mysys/game-sdk-shared';

// ELO simples
const eloChange = calculateEloChange(1200, 1150, true);
console.log(eloChange); // +14

// ELO com performance
const result = calculatePerformanceBasedElo({
  playerElo: 1200,
  allPlayersElo: [1200, 1150, 1180, 1220],
  playerPosition: 1,
  totalPlayers: 4,
  performance: {
    damageDealt: 12,
    damageTaken: 4,
    selfDamage: 0,
    kills: 3,
    deaths: 1,
    roundsWon: 2,
    totalRounds: 3,
    itemsUsed: 5,
    shotsFired: 8,
  },
  gameContext: {
    totalPlayers: 4,
    totalKills: 6,
    totalDamage: 28,
    totalRounds: 3,
  },
});

console.log(result);
// {
//   totalChange: 22,
//   baseChange: 18,
//   performanceModifier: 7,
//   performanceScore: 0.72,
//   breakdown: { ... }
// }
```

## Estendendo Tipos

```typescript
import { BaseGameState, BasePlayer } from '@mysys/game-sdk-shared';

// Extender para seu jogo específico
interface MyGameState extends BaseGameState {
  board: string[][];
  currentPiece: string;
}

interface MyPlayer extends BasePlayer {
  score: number;
  pieces: string[];
}
```
