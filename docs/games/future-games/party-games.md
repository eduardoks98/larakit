# Party Games

## Conceito

Jogos casuais multiplayer estilo Jackbox/Gartic - fáceis de entrar, divertidos em grupo.

---

## Ideias de Jogos

### 1. Quiz Multiplayer

```typescript
const quizGame = {
  players: { min: 2, max: 8 },
  duration: '5-10 min',

  rounds: 10,
  timePerQuestion: 15,

  categories: ['gaming', 'movies', 'general', 'music'],

  scoring: {
    correct: 100,
    speedBonus: 50,         // Mais rápido = mais pontos
    streak: [1.1, 1.2, 1.3],
  },

  special: {
    doubleOrNothing: true,
    steal: true,
  },
};
```

### 2. Drawing Game (Gartic Style)

```typescript
const drawingGame = {
  players: { min: 3, max: 12 },
  duration: '10-15 min',

  modes: {
    classic: 'One draws, others guess',
    telephone: 'Draw what previous described',
    copycat: 'Copy the reference drawing',
  },

  tools: {
    brushSizes: [1, 3, 5, 10],
    colors: 12,
    undo: true,
    fill: true,
  },
};
```

### 3. Bluffing Game

```typescript
const bluffingGame = {
  players: { min: 4, max: 10 },
  duration: '15-20 min',

  concept: 'Write fake definitions, vote for real one',

  rounds: 5,
  pointsForFooling: 100,
  pointsForCorrect: 200,

  themes: ['words', 'laws', 'inventions', 'movies'],
};
```

### 4. Reaction Games

```typescript
const reactionGames = {
  players: { min: 2, max: 8 },
  duration: '3-5 min',

  minigames: [
    'First to click',
    'Rhythm matching',
    'Memory sequence',
    'Quick math',
    'Word scramble',
  ],

  tournament: {
    rounds: 5,
    elimination: false,
    scoring: 'cumulative',
  },
};
```

---

## Viabilidade Socket.IO

| Aspecto | Dificuldade | Notas |
|---------|-------------|-------|
| Quiz sync | Fácil | Questions from server |
| Drawing sync | Média | Real-time strokes |
| Voting | Fácil | Simple events |
| Timing games | Fácil | Server timestamps |

---

## Diferencial do Buckshot

Já temos experiência com party games (Buckshot Roulette).
Podemos usar o mesmo SDK.

---

## Estimativa por Jogo

| Jogo | Protótipo | Alpha |
|------|-----------|-------|
| Quiz | 1 semana | 2 semanas |
| Drawing | 2 semanas | 4 semanas |
| Bluffing | 1 semana | 2 semanas |
| Reaction | 1 semana | 2 semanas |

---

## Referências

- Jackbox Games (party variety)
- Gartic Phone (drawing)
- Kahoot (quiz)
- Among Us (social deduction)
- Skribbl.io (web drawing)
