# Deck Builder Game

## Conceito

Jogo de cartas roguelike inspirado em Balatro e Slay the Spire.

---

## Ideias Principais

### Opção 1: Poker Roguelike (Balatro Style)

```typescript
const pokerRoguelike = {
  core: 'Standard poker hands with modifiers',

  jokers: {
    slots: 5,
    effects: [
      '+Mult when playing pairs',
      'All hearts count as wilds',
      'Retrigger all face cards',
    ],
  },

  progression: {
    antes: 8,              // 8 níveis de dificuldade
    blinds: 3,             // Small, Big, Boss por ante
    scoreRequirement: 'exponential',
  },

  monetization: 'Free + cosmetic DLC',
};
```

### Opção 2: Battle Cards (Slay the Spire Style)

```typescript
const battleCards = {
  core: 'Deck building combat RPG',

  combat: {
    type: 'turn_based',
    energy: 3,             // Per turn
    draw: 5,               // Per turn
  },

  cards: {
    types: ['attack', 'skill', 'power'],
    rarity: ['common', 'uncommon', 'rare'],
    upgradeable: true,
  },

  classes: 3,              // Different starting decks
};
```

### Opção 3: Multiplayer Card Battle

```typescript
const multiplayerCards = {
  core: 'Real-time card combat',

  realtime: {
    cooldowns: true,       // Cards have cooldowns
    mana: true,            // Resource management
    lanes: 3,              // Where to play cards
  },

  pvp: {
    matchmaking: true,
    ranking: true,
    formats: ['draft', 'constructed'],
  },
};
```

---

## Viabilidade Socket.IO

| Aspecto | Dificuldade | Notas |
|---------|-------------|-------|
| Turn-based PvP | Fácil | Sync por turno |
| Real-time cards | Média | Cooldowns/timing |
| Draft mode | Fácil | Shared pool |
| Spectate | Fácil | Broadcast state |

---

## Estimativa

- **Protótipo:** 2 semanas
- **Alpha:** 4 semanas
- **Beta:** 6 semanas

---

## Referências

- Balatro (poker twist)
- Slay the Spire (deck building)
- Inscryption (unique mechanics)
- Monster Train (lane-based)
