# Auto Battler Game

## Conceito

Auto battler competitivo estilo TFT/Auto Chess com elementos únicos.

---

## Ideias Principais

### Core Gameplay

```typescript
const autoBattler = {
  format: {
    players: 8,
    rounds: 30-40,
    duration: '25-35 min',
  },

  economy: {
    passiveGold: 5,
    interestCap: 5,
    winStreak: [1, 2, 3],
    lossStreak: [1, 2, 3],
    levelCost: 4,
  },

  shop: {
    slots: 5,
    rerollCost: 2,
    poolShared: true,
  },

  board: {
    hexes: true,
    rows: 4,
    cols: 7,
  },
};
```

### Diferencial

```typescript
const uniqueFeatures = {
  // Não copiar TFT exatamente
  differences: [
    'Faster matches (15 min)',
    'Simplified traits (max 3 active)',
    'Real-time draft phase',
    'Item crafting simplified',
  ],

  // Modo único
  turboMode: {
    duration: 10,          // minutos
    startLevel: 6,
    startGold: 30,
  },

  // Co-op mode
  duoMode: {
    sharedHealth: true,
    sharedShop: false,
    tagTeam: true,         // Revezar boards
  },
};
```

---

## Viabilidade Socket.IO

| Aspecto | Dificuldade | Notas |
|---------|-------------|-------|
| 8 players | Média | State sync |
| Combat simulation | Fácil | Server-side |
| Shop/draft | Fácil | Turn-based |
| Spectate | Fácil | Broadcast |

---

## Estimativa

- **Protótipo:** 3 semanas
- **Alpha:** 6 semanas
- **Beta:** 10 semanas

---

## Referências

- Teamfight Tactics (economia)
- Super Auto Pets (simplicidade)
- Dota Underlords (items)
- Hearthstone Battlegrounds (combat)
