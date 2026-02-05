# Tower Defense + Roguelike

## Conceito

Tower Defense com elementos roguelike - builds diferentes a cada run.

---

## Ideias Principais

### Core Gameplay

```typescript
const towerDefenseRoguelike = {
  structure: {
    waves: 20-30,
    duration: '15-25 min',
    between_waves: 'build_phase',
  },

  towers: {
    slots: 10-15,
    types: ['basic', 'aoe', 'slow', 'support', 'special'],
    upgradeable: true,
    synergies: true,
  },

  roguelike: {
    towerUnlocks: 'per_wave',
    randomUpgrades: true,
    buildVariety: 'high',
  },
};
```

### Multiplayer Modes

```typescript
const multiplayerModes = {
  coop: {
    players: 2-4,
    sharedMap: true,
    towerSharing: 'lane_based',
  },

  versus: {
    players: 2,
    sendCreeps: true,      // Gastar gold para enviar
    mirrorMap: true,
  },

  race: {
    players: 4,
    sameSeed: true,
    firstToWave30: true,
  },
};
```

---

## Viabilidade Socket.IO

| Aspecto | Dificuldade | Notas |
|---------|-------------|-------|
| Co-op sync | Média | Tower placement |
| Versus | Média | Creep sending |
| Wave sync | Fácil | Server-controlled |
| Build phase | Fácil | Turn-based |

---

## Estimativa

- **Protótipo:** 2 semanas
- **Alpha:** 5 semanas
- **Beta:** 8 semanas

---

## Referências

- Bloons TD (variety)
- Kingdom Rush (progression)
- Rogue Tower (roguelike TD)
- Legion TD (versus mode)
