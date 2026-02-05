# Sistemas de Waves e Spawning

## 1. TIPOS DE WAVE SYSTEMS

### Wave-Based (Discreto)

```typescript
interface WaveBasedSystem {
  type: 'discrete';

  waves: Wave[];
  currentWave: number;

  // Transição entre waves
  intermission: {
    duration: number;         // Segundos
    actions: IntermissionAction[];
  };
}

interface Wave {
  number: number;
  enemies: EnemySpawn[];
  duration: number | 'until_cleared';
  bossWave: boolean;
}

// Exemplo: Tower Defense style
const towerDefenseWaves: Wave[] = [
  {
    number: 1,
    enemies: [
      { type: 'goblin', count: 10, spawnRate: 1 },
    ],
    duration: 'until_cleared',
    bossWave: false,
  },
  {
    number: 5,
    enemies: [
      { type: 'goblin', count: 20, spawnRate: 2 },
      { type: 'orc', count: 5, spawnRate: 0.5 },
    ],
    duration: 'until_cleared',
    bossWave: false,
  },
  {
    number: 10,
    enemies: [
      { type: 'goblin_boss', count: 1, spawnRate: 0 },
    ],
    duration: 'until_cleared',
    bossWave: true,
  },
];
```

### Continuous (Contínuo)

```typescript
interface ContinuousSystem {
  type: 'continuous';

  // Spawn baseado em tempo
  spawnRate: (time: number) => number;  // Inimigos por segundo
  spawnTable: (time: number) => SpawnEntry[];

  // Events especiais
  events: TimedEvent[];
}

// Exemplo: Vampire Survivors style
const vampireSurvivorsSpawning: ContinuousSystem = {
  type: 'continuous',

  spawnRate: (time) => {
    // Começa lento, acelera
    const base = 0.5;
    const scaling = 0.1 * Math.floor(time / 60);
    return base + scaling;
  },

  spawnTable: (time) => {
    if (time < 60) return [{ enemy: 'bat', weight: 100 }];
    if (time < 120) return [
      { enemy: 'bat', weight: 70 },
      { enemy: 'skeleton', weight: 30 },
    ];
    // ... escala com tempo
  },

  events: [
    { time: 300, type: 'elite_spawn', enemy: 'mini_boss_1' },
    { time: 600, type: 'boss_spawn', enemy: 'final_boss' },
  ],
};
```

### Hybrid (Contínuo + Waves)

```typescript
interface HybridSystem {
  type: 'hybrid';

  // Spawn contínuo de base
  baseSpawn: ContinuousSystem;

  // Waves de elite/boss
  waves: Wave[];

  // Trigger de wave
  waveTrigger: 'time' | 'kill_count' | 'player_action';
}

// Exemplo: Risk of Rain 2 style
const riskOfRain2System: HybridSystem = {
  type: 'hybrid',

  baseSpawn: {
    spawnRate: (difficulty) => difficulty * 0.5,
    spawnTable: getDifficultyTable,
  },

  waves: [
    { trigger: 'teleporter_activated', enemies: teleporterEnemies },
    { trigger: 'boss_spawn', enemies: bossWave },
  ],

  waveTrigger: 'player_action',
};
```

---

## 2. SPAWN TABLES

### Estrutura Básica

```typescript
interface SpawnEntry {
  enemy: EnemyType;
  weight: number;           // Chance relativa
  minDifficulty?: number;   // Quando começa a aparecer
  maxDifficulty?: number;   // Quando para de aparecer
  conditions?: SpawnCondition[];
}

// Weighted random selection
const selectEnemy = (table: SpawnEntry[], rng: RNG): EnemyType => {
  const totalWeight = table.reduce((sum, e) => sum + e.weight, 0);
  let roll = rng.float() * totalWeight;

  for (const entry of table) {
    roll -= entry.weight;
    if (roll <= 0) return entry.enemy;
  }

  return table[0].enemy;
};
```

### Tabela por Dificuldade

```typescript
const SPAWN_TABLE: SpawnEntry[] = [
  // Early game (difficulty 0-5)
  { enemy: 'slime', weight: 100, minDifficulty: 0, maxDifficulty: 10 },
  { enemy: 'bat', weight: 80, minDifficulty: 0, maxDifficulty: 15 },

  // Mid game (difficulty 5-15)
  { enemy: 'skeleton', weight: 60, minDifficulty: 5, maxDifficulty: 20 },
  { enemy: 'zombie', weight: 50, minDifficulty: 8, maxDifficulty: 25 },
  { enemy: 'ghost', weight: 40, minDifficulty: 10 },

  // Late game (difficulty 15+)
  { enemy: 'demon', weight: 30, minDifficulty: 15 },
  { enemy: 'dragon', weight: 10, minDifficulty: 20 },
];

const getActiveTable = (difficulty: number): SpawnEntry[] => {
  return SPAWN_TABLE.filter(entry =>
    difficulty >= (entry.minDifficulty ?? 0) &&
    (entry.maxDifficulty === undefined || difficulty <= entry.maxDifficulty)
  );
};
```

---

## 3. CURVAS DE DIFICULDADE

### Linear

```typescript
const linearDifficulty = (time: number, rate: number = 1): number => {
  return time * rate;
};

// Gráfico: /
// Previsível, pode ficar tedioso
```

### Exponencial

```typescript
const exponentialDifficulty = (time: number, base: number = 1.05): number => {
  return Math.pow(base, time / 60);
};

// Gráfico: ⌒
// Começa lento, escala rápido
// Usado por Vampire Survivors
```

### Stepped (Degraus)

```typescript
const steppedDifficulty = (
  time: number,
  steps: { time: number; difficulty: number }[]
): number => {
  let current = 0;
  for (const step of steps) {
    if (time >= step.time) current = step.difficulty;
  }
  return current;
};

// Gráfico: ▂▃▄▅▆
// Momentos de respiro, picos de tensão
// Usado por jogos level-based
```

### Adaptativo

```typescript
const adaptiveDifficulty = (
  baseTime: number,
  playerPerformance: number  // -1 a 1
): number => {
  const base = linearDifficulty(baseTime);
  const modifier = 1 + (playerPerformance * 0.3);
  return base * modifier;
};

// Ajusta baseado em:
// - HP do jogador
// - Kill rate
// - Tempo sem morrer
```

---

## 4. SPAWN PATTERNS

### Posicionamento

```typescript
type SpawnPattern =
  | 'random'           // Posição aleatória
  | 'edge'             // Nas bordas da tela/arena
  | 'offscreen'        // Fora da tela, caminha para dentro
  | 'surround'         // Ao redor do jogador
  | 'formation'        // Padrão específico
  | 'portal';          // De portais fixos

const getSpawnPosition = (
  pattern: SpawnPattern,
  playerPos: Vector2,
  arenaSize: Vector2,
  rng: RNG
): Vector2 => {
  switch (pattern) {
    case 'random':
      return {
        x: rng.float() * arenaSize.x,
        y: rng.float() * arenaSize.y,
      };

    case 'edge':
      const edge = rng.pick(['top', 'bottom', 'left', 'right']);
      // ... retornar posição na borda

    case 'surround':
      const angle = rng.float() * Math.PI * 2;
      const distance = 300 + rng.float() * 100;
      return {
        x: playerPos.x + Math.cos(angle) * distance,
        y: playerPos.y + Math.sin(angle) * distance,
      };

    case 'formation':
      // Retornar próxima posição de formação
      break;
  }
};
```

### Formações

```typescript
const FORMATIONS = {
  line: (count: number, spacing: number): Vector2[] => {
    return Array.from({ length: count }, (_, i) => ({
      x: i * spacing - (count - 1) * spacing / 2,
      y: 0,
    }));
  },

  circle: (count: number, radius: number): Vector2[] => {
    return Array.from({ length: count }, (_, i) => {
      const angle = (i / count) * Math.PI * 2;
      return {
        x: Math.cos(angle) * radius,
        y: Math.sin(angle) * radius,
      };
    });
  },

  v_formation: (count: number, spacing: number): Vector2[] => {
    const positions: Vector2[] = [];
    for (let i = 0; i < count; i++) {
      const row = Math.floor(i / 2);
      const side = i % 2 === 0 ? -1 : 1;
      positions.push({
        x: side * row * spacing,
        y: row * spacing,
      });
    }
    return positions;
  },
};
```

---

## 5. SPAWN EVENTS

### Event Types

```typescript
interface SpawnEvent {
  trigger: EventTrigger;
  action: EventAction;
  cooldown?: number;
  maxOccurrences?: number;
}

type EventTrigger =
  | { type: 'time'; value: number }
  | { type: 'kill_count'; value: number }
  | { type: 'player_hp'; value: number; comparison: '<' | '>' }
  | { type: 'wave_clear' }
  | { type: 'boss_phase'; bossId: string; phase: number };

type EventAction =
  | { type: 'spawn_enemy'; enemy: EnemyType; count: number }
  | { type: 'spawn_boss'; boss: BossType }
  | { type: 'spawn_item'; item: ItemType }
  | { type: 'arena_change'; effect: string }
  | { type: 'difficulty_spike'; multiplier: number; duration: number };
```

### Exemplo de Event System

```typescript
class SpawnEventManager {
  private events: SpawnEvent[];
  private triggeredCounts: Map<string, number> = new Map();

  checkEvents(gameState: GameState): EventAction[] {
    const actions: EventAction[] = [];

    for (const event of this.events) {
      if (this.shouldTrigger(event, gameState)) {
        actions.push(event.action);
        this.recordTrigger(event);
      }
    }

    return actions;
  }

  private shouldTrigger(event: SpawnEvent, state: GameState): boolean {
    // Check max occurrences
    const count = this.triggeredCounts.get(event.id) ?? 0;
    if (event.maxOccurrences && count >= event.maxOccurrences) {
      return false;
    }

    // Check cooldown
    if (event.cooldown && this.isOnCooldown(event)) {
      return false;
    }

    // Check trigger condition
    switch (event.trigger.type) {
      case 'time':
        return state.time >= event.trigger.value;
      case 'kill_count':
        return state.kills >= event.trigger.value;
      case 'player_hp':
        return event.trigger.comparison === '<'
          ? state.playerHP < event.trigger.value
          : state.playerHP > event.trigger.value;
      // ...
    }
  }
}
```

---

## 6. ELITE/CHAMPION SYSTEM

### Modificadores de Elite

```typescript
interface EliteModifier {
  id: string;
  name: string;
  visualEffect: string;

  // Stat changes
  statMultipliers: {
    health?: number;
    damage?: number;
    speed?: number;
    size?: number;
  };

  // Abilities
  abilities?: EliteAbility[];

  // Spawn rules
  minDifficulty: number;
  spawnChance: number;
}

const ELITE_MODIFIERS: EliteModifier[] = [
  {
    id: 'berserker',
    name: 'Berserker',
    visualEffect: 'red_glow',
    statMultipliers: { damage: 1.5, speed: 1.3 },
    minDifficulty: 5,
    spawnChance: 0.1,
  },
  {
    id: 'tank',
    name: 'Tank',
    visualEffect: 'armor_plates',
    statMultipliers: { health: 3, speed: 0.7 },
    minDifficulty: 5,
    spawnChance: 0.1,
  },
  {
    id: 'teleporter',
    name: 'Teleporter',
    visualEffect: 'purple_sparkles',
    abilities: [{ type: 'teleport', cooldown: 5 }],
    minDifficulty: 10,
    spawnChance: 0.05,
  },
  {
    id: 'summoner',
    name: 'Summoner',
    visualEffect: 'necro_aura',
    abilities: [{ type: 'summon', enemy: 'skeleton', count: 3, cooldown: 10 }],
    minDifficulty: 15,
    spawnChance: 0.05,
  },
];
```

### Spawn de Elites

```typescript
const shouldSpawnElite = (
  difficulty: number,
  timeSinceLastElite: number,
  rng: RNG
): boolean => {
  // Base chance
  let chance = 0.05;

  // Aumenta com dificuldade
  chance += difficulty * 0.01;

  // Aumenta com tempo desde último
  if (timeSinceLastElite > 60) {
    chance += 0.1;
  }

  return rng.float() < chance;
};

const createElite = (
  baseEnemy: EnemyType,
  difficulty: number,
  rng: RNG
): Enemy => {
  const availableModifiers = ELITE_MODIFIERS.filter(
    m => difficulty >= m.minDifficulty
  );

  const modifier = rng.pick(availableModifiers);

  return {
    ...baseEnemy,
    isElite: true,
    modifier,
    health: baseEnemy.health * (modifier.statMultipliers.health ?? 1),
    damage: baseEnemy.damage * (modifier.statMultipliers.damage ?? 1),
    // ...
  };
};
```

---

## 7. BOSS WAVES

### Boss Design

```typescript
interface BossWave {
  boss: BossType;

  // Pre-boss
  preSpawn: {
    clearExisting: boolean;   // Limpar mobs antes
    warningTime: number;      // Tempo de aviso
    warningEffect: string;
  };

  // During boss
  duringFight: {
    additionalSpawns: boolean;
    spawnRate: number;
    spawnTypes: EnemyType[];
  };

  // Boss phases
  phases: BossPhase[];
}

interface BossPhase {
  healthThreshold: number;    // % de HP para ativar
  attacks: BossAttack[];
  spawnModifier?: number;
  arenaChange?: string;
}

// Exemplo de boss
const firstBoss: BossWave = {
  boss: 'goblin_king',

  preSpawn: {
    clearExisting: true,
    warningTime: 3,
    warningEffect: 'screen_shake',
  },

  duringFight: {
    additionalSpawns: true,
    spawnRate: 0.5,
    spawnTypes: ['goblin', 'goblin_archer'],
  },

  phases: [
    {
      healthThreshold: 1.0,
      attacks: ['slam', 'throw_barrel'],
    },
    {
      healthThreshold: 0.5,
      attacks: ['slam', 'throw_barrel', 'summon_guards'],
      spawnModifier: 2,
      arenaChange: 'add_fire_pits',
    },
    {
      healthThreshold: 0.25,
      attacks: ['enraged_slam', 'barrel_barrage'],
      arenaChange: 'arena_shrink',
    },
  ],
};
```

---

## 8. SPAWN BALANCING

### Métricas

```typescript
interface SpawnMetrics {
  // Por minuto
  enemiesPerMinute: number;
  elitesPerMinute: number;
  bossesPerRun: number;

  // Dificuldade
  averageDPS: number;          // Dano por segundo aos jogadores
  averageEnemyHP: number;

  // Distribuição
  enemyTypeDistribution: Record<string, number>;
}

const balanceSpawning = (metrics: SpawnMetrics): BalanceAdjustments => {
  const adjustments: BalanceAdjustments = {};

  // Muito dano? Reduzir spawns ou dano
  if (metrics.averageDPS > 50) {
    adjustments.spawnRateMultiplier = 0.8;
  }

  // Muito fácil? Aumentar
  if (metrics.averageDPS < 20) {
    adjustments.spawnRateMultiplier = 1.2;
  }

  // Variedade baixa? Ajustar weights
  const maxTypePercent = Math.max(...Object.values(metrics.enemyTypeDistribution));
  if (maxTypePercent > 0.5) {
    adjustments.diversityBoost = true;
  }

  return adjustments;
};
```

### Target Values

| Métrica | Early Game | Mid Game | Late Game |
|---------|------------|----------|-----------|
| Enemies/min | 30-50 | 60-100 | 100-200 |
| DPS to player | 10-20 | 30-50 | 50-100 |
| Elite % | 5% | 10% | 15% |
| Time to kill avg | 1-2s | 2-3s | 3-5s |

---

## 9. MULTIPLAYER SPAWNING

### Scaling para Co-op

```typescript
const getCoopScaling = (playerCount: number): SpawnScaling => {
  return {
    // HP não escala linearmente (seria muito difícil)
    enemyHealthMultiplier: 1 + (playerCount - 1) * 0.4,

    // Spawn rate escala mais
    spawnRateMultiplier: 1 + (playerCount - 1) * 0.6,

    // Mais variedade com mais jogadores
    enemyVarietyBonus: playerCount * 0.2,

    // Elites mais frequentes
    eliteChanceBonus: (playerCount - 1) * 0.05,
  };
};
```

### Spawn Targeting

```typescript
// Distribuir inimigos entre jogadores
const assignSpawnTargets = (
  enemies: Enemy[],
  players: Player[]
): void => {
  // Balancear threat entre jogadores
  const threatPerPlayer = new Map<string, number>();

  for (const enemy of enemies) {
    // Encontrar jogador com menos threat
    const lowestThreatPlayer = players.reduce((lowest, p) => {
      const threat = threatPerPlayer.get(p.id) ?? 0;
      const lowestThreat = threatPerPlayer.get(lowest.id) ?? 0;
      return threat < lowestThreat ? p : lowest;
    });

    enemy.target = lowestThreatPlayer;
    threatPerPlayer.set(
      lowestThreatPlayer.id,
      (threatPerPlayer.get(lowestThreatPlayer.id) ?? 0) + enemy.threatValue
    );
  }
};
```

---

## 10. IMPLEMENTAÇÃO EXEMPLO

```typescript
class WaveManager {
  private config: WaveConfig;
  private currentDifficulty: number = 0;
  private spawnTimer: number = 0;
  private waveNumber: number = 0;
  private rng: SeededRNG;

  update(deltaTime: number, gameState: GameState): SpawnCommand[] {
    const commands: SpawnCommand[] = [];

    // Atualizar dificuldade
    this.currentDifficulty = this.calculateDifficulty(gameState.time);

    // Spawn contínuo
    this.spawnTimer -= deltaTime;
    if (this.spawnTimer <= 0) {
      const spawnRate = this.getSpawnRate();
      this.spawnTimer = 1 / spawnRate;

      commands.push(this.createSpawnCommand());
    }

    // Checar wave events
    const waveEvents = this.checkWaveEvents(gameState);
    commands.push(...waveEvents);

    return commands;
  }

  private createSpawnCommand(): SpawnCommand {
    const table = this.getSpawnTable(this.currentDifficulty);
    const enemy = selectEnemy(table, this.rng);
    const position = getSpawnPosition('edge', this.playerPos, this.arenaSize, this.rng);

    // Chance de elite
    const isElite = shouldSpawnElite(this.currentDifficulty, this.timeSinceElite, this.rng);

    return {
      type: 'spawn',
      enemy: isElite ? createElite(enemy, this.currentDifficulty, this.rng) : enemy,
      position,
    };
  }
}
```
