# Sistemas de Progressão em Roguelikes

## 1. PROGRESSÃO INTRA-RUN

### Level Up System

```typescript
interface InRunProgression {
  // XP por kill
  xpPerKill: (enemy: Enemy) => number;

  // XP necessário por level
  xpForLevel: (level: number) => number;

  // Recompensa por level up
  onLevelUp: (player: Player) => void;
}

// Exemplo: XP exponencial
const xpForLevel = (level: number): number => {
  return Math.floor(100 * Math.pow(1.2, level - 1));
};

// Level 1: 100
// Level 5: 207
// Level 10: 516
// Level 20: 3,833
```

### Escolhas por Level

| Nível | Opções | Tipo |
|-------|--------|------|
| 1-5 | 3 | Upgrades básicos |
| 6-10 | 3 | Upgrades + 1 raro |
| 11-15 | 4 | Mistura de raridades |
| 16-20 | 4 | Mais épicos disponíveis |
| 21+ | 5 | Chance de lendário |

### Alternativas ao Level Up

1. **Por Floor/Wave** - Upgrade garantido após completar
2. **Por Gold** - Comprar upgrades em shops
3. **Por Baús** - Encontrar durante exploração
4. **Por Boss** - Drops de boss
5. **Por Tempo** - Upgrade automático a cada X minutos

---

## 2. PROGRESSÃO META (ENTRE RUNS)

### Tipos de Meta-Progressão

```typescript
interface MetaProgression {
  // Moedas que persistem
  currencies: {
    id: string;
    name: string;
    earnedPer: 'run' | 'kill' | 'boss' | 'achievement';
  }[];

  // Upgrades permanentes
  permanentUpgrades: PermanentUpgrade[];

  // Unlocks de conteúdo
  contentUnlocks: ContentUnlock[];
}

// Exemplo: Hades
const hadesMetaProgression: MetaProgression = {
  currencies: [
    { id: 'darkness', name: 'Darkness', earnedPer: 'run' },
    { id: 'keys', name: 'Chthonic Keys', earnedPer: 'boss' },
    { id: 'gems', name: 'Gemstones', earnedPer: 'run' },
    { id: 'nectar', name: 'Nectar', earnedPer: 'achievement' },
  ],
  permanentUpgrades: [
    // Mirror of Night
    { id: 'shadow_presence', maxLevel: 5, costPer: [10, 20, 40, 80, 160] },
    { id: 'chthonic_vitality', maxLevel: 3, costPer: [15, 30, 60] },
  ],
  contentUnlocks: [
    { id: 'weapon_bow', cost: { keys: 3 } },
    { id: 'weapon_shield', cost: { keys: 4 } },
  ],
};
```

### Curva de Desbloqueio

```typescript
// Garantir que jogador desbloqueia coisas regularmente
const unlockSchedule = {
  // Primeiras 10 runs: algo novo a cada run
  earlyGame: {
    runsRange: [1, 10],
    unlockFrequency: 1,
    unlockTypes: ['basic_upgrades', 'first_weapons'],
  },

  // 10-30 runs: algo a cada 2-3 runs
  midGame: {
    runsRange: [10, 30],
    unlockFrequency: 2.5,
    unlockTypes: ['advanced_upgrades', 'all_weapons', 'story_bits'],
  },

  // 30+ runs: conteúdo de endgame
  lateGame: {
    runsRange: [30, 100],
    unlockFrequency: 5,
    unlockTypes: ['cosmetics', 'challenges', 'true_ending'],
  },
};
```

---

## 3. UNLOCKS DE CONTEÚDO

### Weapons/Characters

```typescript
interface CharacterUnlock {
  id: string;
  name: string;
  unlockCondition: UnlockCondition;
  teaseBeforeUnlock: boolean;  // Mostrar silhueta?
}

type UnlockCondition =
  | { type: 'currency'; amount: number; currency: string }
  | { type: 'achievement'; achievementId: string }
  | { type: 'runs_completed'; count: number }
  | { type: 'story_progress'; checkpoint: string }
  | { type: 'secret'; hint: string };

// Exemplo: Dead Cells
const deadCellsWeaponUnlocks: CharacterUnlock[] = [
  {
    id: 'blood_sword',
    name: 'Blood Sword',
    unlockCondition: { type: 'currency', amount: 100, currency: 'cells' },
    teaseBeforeUnlock: true,
  },
  {
    id: 'ice_bow',
    name: 'Ice Bow',
    unlockCondition: { type: 'achievement', achievementId: 'kill_elite_with_ice' },
    teaseBeforeUnlock: true,
  },
];
```

### Áreas/Níveis

```typescript
interface AreaUnlock {
  id: string;
  name: string;
  accessFrom: string[];  // Áreas que conectam
  unlockMethod: 'key' | 'achievement' | 'secret_path' | 'default';
}

// Branch paths: múltiplos caminhos
const areaGraph = {
  'floor_1': { next: ['floor_2a', 'floor_2b'], unlockMethod: 'default' },
  'floor_2a': { next: ['floor_3'], unlockMethod: 'default' },
  'floor_2b': { next: ['floor_3', 'secret_floor'], unlockMethod: 'key' },
  'secret_floor': { next: ['floor_3'], unlockMethod: 'achievement' },
};
```

---

## 4. ACHIEVEMENTS

### Categorias

| Categoria | Exemplos | Recompensa |
|-----------|----------|------------|
| **Skill** | Matar boss sem dano | Cosmético raro |
| **Grind** | Matar 1000 inimigos | Moeda meta |
| **Discovery** | Encontrar área secreta | Unlock de conteúdo |
| **Mastery** | Completar com todas as armas | Título/Badge |
| **Challenge** | Completar em hard mode | Skin exclusiva |

### Design de Achievements

```typescript
interface Achievement {
  id: string;
  name: string;
  description: string;
  hidden: boolean;           // Não mostrar até desbloquear?
  progressTrackable: boolean; // Mostrar progresso?
  difficulty: 'easy' | 'medium' | 'hard' | 'insane';

  // Condição
  condition: AchievementCondition;

  // Recompensa
  reward: {
    type: 'currency' | 'unlock' | 'cosmetic' | 'title';
    value: string | number;
  };
}

// Bons achievements são específicos mas alcançáveis
const goodAchievements: Achievement[] = [
  {
    id: 'first_win',
    name: 'First Victory',
    description: 'Complete a run for the first time',
    hidden: false,
    progressTrackable: false,
    difficulty: 'medium',
    condition: { type: 'complete_run', count: 1 },
    reward: { type: 'currency', value: 100 },
  },
  {
    id: 'no_damage_boss',
    name: 'Untouchable',
    description: 'Defeat a boss without taking damage',
    hidden: true,
    progressTrackable: false,
    difficulty: 'hard',
    condition: { type: 'boss_no_damage', any: true },
    reward: { type: 'cosmetic', value: 'golden_aura' },
  },
];
```

---

## 5. DAILY/WEEKLY CHALLENGES

### Estrutura

```typescript
interface DailyChallenge {
  id: string;
  date: Date;
  seed: number;            // Mesma seed para todos

  // Modificadores fixos para o dia
  modifiers: RunModifier[];

  // Leaderboard separado
  leaderboard: 'time' | 'score' | 'distance';

  // Recompensa
  rewards: {
    participation: Reward;
    top10Percent: Reward;
    top1Percent: Reward;
  };
}

// Gerar challenge diário determinístico
const generateDailyChallenge = (date: Date): DailyChallenge => {
  const seed = dateToDailySeed(date);
  const rng = new SeededRandom(seed);

  // Selecionar 2-3 modificadores
  const modifiers = selectRandomModifiers(rng, 2, 3);

  return {
    id: `daily_${date.toISOString().split('T')[0]}`,
    date,
    seed,
    modifiers,
    leaderboard: rng.pick(['time', 'score', 'distance']),
    rewards: getDailyRewards(),
  };
};
```

### Weekly Events

| Evento | Descrição | Duração |
|--------|-----------|---------|
| **2x XP Weekend** | Dobro de XP meta | 3 dias |
| **Challenge Week** | Modificador especial | 7 dias |
| **Boss Rush** | Apenas bosses | 3 dias |
| **New Content** | Preview de novo item | 7 dias |

---

## 6. SEASON PASS / BATTLE PASS

### Estrutura para Roguelike

```typescript
interface RoguelikeBattlePass {
  seasonNumber: number;
  durationWeeks: number;
  totalLevels: number;

  // XP por ação
  xpSources: {
    runComplete: number;
    bossKill: number;
    dailyComplete: number;
    achievementUnlock: number;
  };

  // Rewards por nível
  freeTrack: BattlePassReward[];
  premiumTrack: BattlePassReward[];
}

// XP balanceado para 1-2 levels por dia casual
const calculateBPXPNeeded = (level: number): number => {
  const baseXP = 1000;
  const scaling = 1.02; // Leve aumento

  return Math.floor(baseXP * Math.pow(scaling, level - 1));
};

// Garantir milestone rewards
const battlePassMilestones = [
  { level: 1, reward: 'welcome_skin' },
  { level: 10, reward: 'emote_pack' },
  { level: 25, reward: 'weapon_skin' },
  { level: 50, reward: 'character_variant' },
  { level: 75, reward: 'effect_trail' },
  { level: 100, reward: 'prestige_badge' },
];
```

---

## 7. PRESTIGE / ASCENSION

### Sistema de Reset Voluntário

```typescript
interface PrestigeSystem {
  // Requisito para prestige
  requirement: {
    type: 'runs_completed' | 'total_xp' | 'achievement';
    value: number;
  };

  // O que reseta
  resets: string[];

  // O que mantém
  keeps: string[];

  // Bonus ganho
  bonuses: {
    level: number;
    bonus: PrestigeBonus;
  }[];
}

// Exemplo: Rogue Legacy style
const rogueLegacyPrestige: PrestigeSystem = {
  requirement: { type: 'runs_completed', value: 50 },
  resets: ['gold', 'equipment', 'level'],
  keeps: ['achievements', 'lore', 'max_stats_unlocked'],
  bonuses: [
    { level: 1, bonus: { type: 'xp_multiplier', value: 1.1 } },
    { level: 2, bonus: { type: 'starting_gold', value: 100 } },
    { level: 3, bonus: { type: 'new_class_unlock', value: 'prestige_class' } },
  ],
};
```

### Escalas de Dificuldade (Heat/Ascension)

```typescript
// Hades Heat System
const hadesHeatLevels = [
  { heat: 1, modifiers: ['hard_labor_1'] },
  { heat: 2, modifiers: ['hard_labor_1', 'lasting_consequences_1'] },
  // ... até heat 32+
];

// Slay the Spire Ascension
const slayTheSpireAscension = [
  { level: 1, effect: 'Elite monsters are stronger' },
  { level: 2, effect: 'Normal monsters are stronger' },
  { level: 3, effect: 'Elites have more HP' },
  // ... até level 20
];
```

---

## 8. ECONOMIA BALANCEADA

### Curva de Ganho

```typescript
// Evitar que jogador "complete" o jogo muito rápido
const economyBalance = {
  // Moeda meta por run (média)
  currencyPerRun: {
    beginner: 50,   // Runs curtas, mortes cedo
    average: 100,   // Runs médias
    expert: 150,    // Runs completas
  },

  // Custo total para "completar" meta
  totalMetaCost: 5000,

  // Runs esperadas para completar
  expectedRuns: 50,

  // Curva de custo (crescente)
  upgradeCostCurve: (upgradeNumber: number): number => {
    return 50 * Math.pow(1.5, upgradeNumber);
  },
};

// Verificar balanceamento
const verifyEconomy = () => {
  const avgCurrencyPerRun = 100;
  const totalCost = 5000;
  const expectedRuns = totalCost / avgCurrencyPerRun; // 50 runs
  console.log(`Player completa meta em ~${expectedRuns} runs`);
};
```

### Catch-up Mechanics

```typescript
// Ajudar jogadores que estão "atrasados"
const catchUpMechanics = {
  // Bonus para primeira run do dia
  firstRunBonus: 1.5,

  // Bonus para runs mal sucedidas (streak de mortes)
  pityBonus: (deathStreak: number): number => {
    return 1 + Math.min(0.5, deathStreak * 0.1);
  },

  // Eventos de 2x recompensa
  doubleRewardsEvents: ['weekend', 'holiday'],
};
```

---

## 9. VISUALIZAÇÃO DE PROGRESSO

### Elementos de UI

```typescript
interface ProgressionUI {
  // Profile page
  profile: {
    totalRuns: number;
    bestRun: RunStats;
    favoriteWeapon: string;
    playtime: number;
  };

  // Unlock tracker
  unlocks: {
    weapons: { unlocked: number; total: number };
    upgrades: { unlocked: number; total: number };
    achievements: { unlocked: number; total: number };
  };

  // Visual completion %
  overallCompletion: number;

  // Current goals
  nextUnlocks: {
    item: string;
    progress: number;
    requirement: number;
  }[];
}
```

### Feedback de Progresso

```
+50 Darkness                    ← Moeda ganha
[████████░░] 80/100            ← Próximo upgrade
🏆 New Achievement: First Win   ← Conquista
⬆️ Level 15 → 16               ← Level up
🔓 Unlocked: Blood Sword       ← Novo conteúdo
```

---

## 10. ANTI-PATTERNS

### O Que Evitar

| Anti-Pattern | Problema | Solução |
|--------------|----------|---------|
| **Grind Wall** | Progresso para | Múltiplas fontes de moeda |
| **Too Fast** | Sem motivação | Espaçar unlocks |
| **P2W** | Injusto | Apenas cosméticos pagos |
| **FOMO Extremo** | Ansiedade | Eventos retornam |
| **Prestige Obrigatório** | Forçado | Opcional, bonus leve |
| **Currency Bloat** | Confuso | Máximo 3-4 moedas |

### Balanceamento de Progressão

```typescript
// Checar se progressão está saudável
const validateProgression = (game: GameData) => {
  // 1. Primeiro unlock em < 5 runs
  assert(game.firstUnlockRuns <= 5);

  // 2. Não mais que 100 runs para "completion"
  assert(game.estimatedCompletionRuns <= 100);

  // 3. Sempre algo para trabalhar
  assert(game.maxSimultaneousGoals >= 3);

  // 4. Variedade de goals
  assert(game.goalTypes.includes('skill'));
  assert(game.goalTypes.includes('unlock'));
  assert(game.goalTypes.includes('collection'));
};
```
