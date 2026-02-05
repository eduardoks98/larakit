# Mecânicas de Roguelike

## 1. PERMADEATH E PERSISTÊNCIA

### Morte Permanente
O que acontece quando o jogador morre:

```typescript
interface DeathHandler {
  // Roguelike puro: perde tudo
  hardReset: () => void;

  // Roguelite: mantém meta-progressão
  softReset: (keepItems: string[]) => void;
}

// Exemplo: Hades
const hadesDeathSystem = {
  lose: ['weapons', 'boons', 'gold'],
  keep: ['darkness', 'keys', 'nectar', 'permanent_upgrades'],
};

// Exemplo: Dead Cells
const deadCellsDeathSystem = {
  lose: ['weapons', 'gold', 'scrolls'],
  keep: ['cells', 'blueprints_found', 'permanent_unlocks'],
};
```

### Meta-Progressão

| Tipo | Descrição | Exemplo |
|------|-----------|---------|
| **Unlocks** | Novas armas/habilidades | Dead Cells blueprints |
| **Stats** | Aumentos permanentes | Hades mirror |
| **Lore** | História desbloqueável | Hades relationships |
| **Shortcuts** | Pular conteúdo inicial | Spelunky shortcuts |
| **Currencies** | Moedas que persistem | Rogue Legacy gold |

---

## 2. GERAÇÃO PROCEDURAL

### Níveis

```typescript
interface LevelGeneration {
  method: 'prefab' | 'cellular_automata' | 'bsp' | 'wave_function_collapse';

  // Prefabs: salas pré-desenhadas conectadas
  prefabs?: Room[];

  // Cellular Automata: bom para cavernas
  cellularAutomata?: {
    birthLimit: number;
    deathLimit: number;
    iterations: number;
  };

  // BSP: Binary Space Partition
  bsp?: {
    minRoomSize: number;
    maxRoomSize: number;
    splitRatio: number;
  };
}

// Exemplo de prefab system
const generateLevel = (seed: number, difficulty: number): Level => {
  const rng = new SeededRandom(seed);

  // Selecionar salas baseado em dificuldade
  const roomPool = rooms.filter(r => r.difficulty <= difficulty);

  // Conectar salas
  const layout = createRoomGraph(rng, roomPool);

  // Spawnar inimigos
  spawnEnemies(layout, difficulty, rng);

  return layout;
};
```

### Inimigos e Loot

```typescript
interface SpawnTable {
  enemy: EnemyType;
  weight: number;
  minDifficulty: number;
  maxDifficulty: number;
}

const getSpawnTable = (difficulty: number): SpawnTable[] => {
  return enemies.filter(e =>
    e.minDifficulty <= difficulty &&
    (e.maxDifficulty === -1 || e.maxDifficulty >= difficulty)
  );
};

// Weighted random selection
const selectEnemy = (table: SpawnTable[], rng: SeededRandom): EnemyType => {
  const totalWeight = table.reduce((sum, e) => sum + e.weight, 0);
  let roll = rng.float() * totalWeight;

  for (const entry of table) {
    roll -= entry.weight;
    if (roll <= 0) return entry.enemy;
  }

  return table[0].enemy;
};
```

---

## 3. SISTEMA DE BUILDS

### Upgrade Selection

```typescript
interface UpgradeOffer {
  options: Upgrade[];      // 3-4 opções
  rerollCost?: number;     // Custo para re-rolar
  removeCost?: number;     // Custo para remover upgrade existente
}

// Weighted selection baseado em raridade e sinergias
const generateUpgradeOptions = (
  player: Player,
  count: number = 3
): Upgrade[] => {
  const available = getAvailableUpgrades(player);

  // Peso base por raridade
  const weighted = available.map(u => ({
    upgrade: u,
    weight: getRarityWeight(u.rarity) * getSynergyBonus(u, player.upgrades),
  }));

  // Selecionar sem repetição
  return selectWeightedWithoutReplacement(weighted, count);
};

// Sinergias aumentam chance de aparecer
const getSynergyBonus = (upgrade: Upgrade, current: Upgrade[]): number => {
  let bonus = 1;

  for (const existing of current) {
    if (upgrade.synergies.includes(existing.id)) {
      bonus *= 1.5; // +50% chance por sinergia
    }
  }

  return bonus;
};
```

### Raridade

| Raridade | Peso Base | Chance (aprox) | Cor |
|----------|-----------|----------------|-----|
| Common | 100 | 50% | ⬜ Branco |
| Uncommon | 50 | 25% | 🟢 Verde |
| Rare | 30 | 15% | 🔵 Azul |
| Epic | 15 | 7.5% | 🟣 Roxo |
| Legendary | 5 | 2.5% | 🟡 Dourado |

---

## 4. CURVA DE DIFICULDADE

### Scaling Linear vs Exponencial

```typescript
interface DifficultyScaling {
  method: 'linear' | 'exponential' | 'stepped';

  // Linear: dificuldade += 1 por minuto
  linear?: { ratePerMinute: number };

  // Exponencial: dificuldade *= 1.1 a cada wave
  exponential?: { multiplierPerWave: number };

  // Stepped: plateaus de dificuldade
  stepped?: { thresholds: number[]; values: number[] };
}

// Vampire Survivors usa exponencial
const vampireSurvivorsScaling = (time: number): number => {
  return Math.pow(1.1, time / 60); // +10% a cada minuto
};

// Enter the Gungeon usa stepped
const enterTheGungeonScaling = (floor: number): number => {
  const thresholds = [1, 2, 3, 4, 5];
  const values = [1, 1.5, 2, 3, 5];
  return values[Math.min(floor - 1, thresholds.length - 1)];
};
```

### Rubber Banding

```typescript
// Ajustar dificuldade baseado em performance
const getDynamicDifficulty = (
  baseDifficulty: number,
  player: Player
): number => {
  let modifier = 1;

  // Jogador está morrendo muito? Facilitar
  if (player.recentDeaths > 3) {
    modifier *= 0.8;
  }

  // Jogador está dominando? Dificultar
  if (player.currentHP === player.maxHP && player.killStreak > 50) {
    modifier *= 1.2;
  }

  return baseDifficulty * modifier;
};
```

---

## 5. SISTEMAS DE COMBATE

### Tempo Real vs Turno

| Aspecto | Tempo Real | Turno |
|---------|------------|-------|
| Skill | Reflexo, mira | Planejamento |
| Ritmo | Intenso | Metódico |
| Multiplayer | Mais fácil sync | Async possível |
| Acessibilidade | Mais difícil | Mais fácil |

### Hitbox/Hurtbox em Roguelikes

```typescript
interface CombatFrame {
  frame: number;
  hitboxes: Hitbox[];
  hurtboxActive: boolean;
  invulnerable: boolean;
}

// Dead Cells: hitboxes precisas, i-frames generosas
const deadCellsAttack: CombatFrame[] = [
  { frame: 0, hitboxes: [], hurtboxActive: true, invulnerable: false },
  { frame: 3, hitboxes: [mainHitbox], hurtboxActive: true, invulnerable: false },
  { frame: 6, hitboxes: [], hurtboxActive: true, invulnerable: false },
];

// Hades: hitboxes maiores, mais forgiving
const hadesAttack: CombatFrame[] = [
  { frame: 0, hitboxes: [], hurtboxActive: true, invulnerable: false },
  { frame: 2, hitboxes: [wideHitbox], hurtboxActive: true, invulnerable: false },
  { frame: 5, hitboxes: [wideHitbox], hurtboxActive: true, invulnerable: false },
  { frame: 8, hitboxes: [], hurtboxActive: true, invulnerable: false },
];
```

---

## 6. RECURSOS E ECONOMIA

### Moedas Típicas

| Moeda | Uso | Persiste? |
|-------|-----|-----------|
| Gold | Compras in-run | ❌ |
| Keys | Abrir baús/portas | ❌ |
| Meta Currency | Unlocks permanentes | ✅ |
| Special Currency | Itens raros | ✅ |

### Shop System

```typescript
interface ShopItem {
  item: Item;
  price: number;
  currency: CurrencyType;
  stock: number;           // -1 = infinito
  requiresUnlock?: string;
}

// Preços dinâmicos
const calculatePrice = (
  basePrice: number,
  playerWealth: number,
  supplyDemand: number
): number => {
  // Aumentar preço se jogador tem muito gold
  const wealthModifier = 1 + (playerWealth / 1000) * 0.1;

  // Itens populares custam mais
  const demandModifier = 1 + (supplyDemand * 0.2);

  return Math.round(basePrice * wealthModifier * demandModifier);
};
```

---

## 7. EVENTOS E VARIAÇÃO

### Salas Especiais

| Tipo | Descrição | Frequência |
|------|-----------|------------|
| **Treasure** | Baú com loot | 1 por andar |
| **Shop** | Comprar itens | 1 por andar |
| **Challenge** | Difícil, bom loot | Raro |
| **Secret** | Escondida, puzzle | Muito raro |
| **Boss** | Fim de andar | Garantido |
| **Rest** | Curar, upgrade | Opcional |

### Modificadores de Run

```typescript
interface RunModifier {
  id: string;
  name: string;
  description: string;

  // Efeitos
  effects: {
    enemyHealthMultiplier?: number;
    enemyDamageMultiplier?: number;
    playerDamageMultiplier?: number;
    goldMultiplier?: number;
    spawnRateMultiplier?: number;
  };

  // Recompensa por completar
  reward: {
    xpMultiplier: number;
    extraLoot: boolean;
  };
}

// Exemplo: Heat System de Hades
const hadesHeat: RunModifier[] = [
  {
    id: 'hard_labor',
    name: 'Hard Labor',
    description: 'Inimigos causam mais dano',
    effects: { enemyDamageMultiplier: 1.2 },
    reward: { xpMultiplier: 1.1, extraLoot: false },
  },
  {
    id: 'lasting_consequences',
    name: 'Lasting Consequences',
    description: 'Cura reduzida',
    effects: { healingMultiplier: 0.75 },
    reward: { xpMultiplier: 1.15, extraLoot: false },
  },
];
```

---

## 8. FEEDBACK E JUICE

### Essencial para Roguelikes

```typescript
const roguelikeFeedback = {
  // Dano recebido
  hit: {
    screenShake: { intensity: 10, duration: 100 },
    flashColor: 'red',
    soundEffect: 'player_hit',
    slowMotion: { factor: 0.5, duration: 50 },
  },

  // Kill
  kill: {
    screenShake: { intensity: 5, duration: 50 },
    particles: { type: 'blood', count: 10 },
    soundEffect: 'enemy_death',
    xpPopup: true,
  },

  // Level up
  levelUp: {
    screenFlash: 'gold',
    soundEffect: 'level_up',
    slowMotion: { factor: 0.3, duration: 500 },
    upgradeMenu: true,
  },

  // Item pickup
  pickup: {
    soundEffect: 'pickup',
    floatingText: true,
    particles: { type: 'sparkle', count: 5 },
  },

  // Critical hit
  critical: {
    screenShake: { intensity: 15, duration: 100 },
    particles: { type: 'impact', count: 20, color: 'gold' },
    soundPitch: 1.3,
    damageNumberColor: 'gold',
    damageNumberScale: 1.5,
  },
};
```

---

## 9. INFORMAÇÕES AO JOGADOR

### O Que Mostrar

| Informação | Visibilidade | Razão |
|------------|--------------|-------|
| HP | Sempre | Crítico |
| Damage numbers | Sempre | Feedback |
| Enemy HP | Ao mirar | Estratégia |
| Cooldowns | Sempre | Timing |
| Minimap | Sempre | Navegação |
| Upgrade effects | Ao obter | Entendimento |
| Synergy hints | Ao escolher | Decisões |

### UI de Upgrade

```typescript
interface UpgradeUIConfig {
  // Mostrar preview do efeito
  showPreview: boolean;

  // Destacar sinergias com itens atuais
  highlightSynergies: boolean;

  // Mostrar raridade
  showRarity: boolean;

  // Comparar com stats atuais
  showComparison: boolean;

  // Timer para escolher (pressão)
  choiceTimer?: number;
}
```

---

## 10. BALANCEAMENTO

### Princípios

1. **Power Fantasy:** Jogador deve se sentir poderoso
2. **Risco/Recompensa:** Escolhas difíceis
3. **Curva de Poder:** Escalar sem quebrar
4. **Variance:** Runs diferentes = experiências diferentes
5. **Skill Cap:** Espaço para melhorar

### Fórmulas Comuns

```typescript
// Dano escala com level mas não linearmente
const calculateDamage = (baseDamage: number, level: number): number => {
  return baseDamage * (1 + Math.log2(level + 1) * 0.5);
};

// HP de inimigos escala mais rápido
const calculateEnemyHP = (baseHP: number, difficulty: number): number => {
  return baseHP * Math.pow(1.1, difficulty);
};

// Diminishing returns em stacking
const applyDiminishingReturns = (value: number, stacks: number): number => {
  // 100% primeiro, 50% segundo, 25% terceiro, etc.
  let total = 0;
  for (let i = 0; i < stacks; i++) {
    total += value * Math.pow(0.5, i);
  }
  return total;
};
```
