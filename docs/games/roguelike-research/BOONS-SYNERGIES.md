# Sistema de Boons e Sinergias

## 1. ANATOMIA DE UM UPGRADE

### Estrutura Básica

```typescript
interface Upgrade {
  id: string;
  name: string;
  description: string;
  icon: string;

  // Classificação
  rarity: 'common' | 'uncommon' | 'rare' | 'epic' | 'legendary';
  category: UpgradeCategory;
  tags: string[];

  // Efeitos
  effects: Effect[];

  // Regras de stacking
  maxStacks: number;
  stackingBehavior: 'additive' | 'multiplicative' | 'diminishing';

  // Sinergias
  synergies: string[];      // IDs de upgrades que combinam
  antiSynergies: string[];  // IDs que não combinam
  exclusiveWith: string[];  // Não pode ter ambos
}

type UpgradeCategory =
  | 'damage'      // Aumentar dano
  | 'defense'     // Reduzir dano
  | 'utility'     // Movimento, cooldown
  | 'healing'     // Recuperar HP
  | 'special'     // Efeitos únicos
  | 'curse';      // Negative (Binding of Isaac)
```

### Exemplo Completo

```typescript
const fireStrike: Upgrade = {
  id: 'fire_strike',
  name: 'Fire Strike',
  description: 'Attacks deal +15 fire damage',
  icon: '🔥',

  rarity: 'common',
  category: 'damage',
  tags: ['fire', 'attack', 'elemental'],

  effects: [
    { type: 'flat_damage', element: 'fire', value: 15 },
  ],

  maxStacks: 5,
  stackingBehavior: 'additive',

  synergies: ['burning_touch', 'flame_burst', 'ignite'],
  antiSynergies: ['ice_attack'],
  exclusiveWith: [],
};
```

---

## 2. TIPOS DE EFEITOS

### Efeitos Numéricos

```typescript
type NumericEffect =
  | { type: 'flat_damage'; value: number; element?: Element }
  | { type: 'percent_damage'; value: number }
  | { type: 'flat_defense'; value: number }
  | { type: 'percent_defense'; value: number }
  | { type: 'attack_speed'; value: number }
  | { type: 'move_speed'; value: number }
  | { type: 'cooldown_reduction'; value: number }
  | { type: 'crit_chance'; value: number }
  | { type: 'crit_damage'; value: number };

// Aplicar efeitos
const applyNumericEffects = (
  stats: PlayerStats,
  effects: NumericEffect[]
): PlayerStats => {
  let result = { ...stats };

  // Flat bonuses primeiro
  for (const effect of effects) {
    if (effect.type === 'flat_damage') {
      result.damage += effect.value;
    }
    if (effect.type === 'flat_defense') {
      result.defense += effect.value;
    }
  }

  // Percent bonuses depois
  for (const effect of effects) {
    if (effect.type === 'percent_damage') {
      result.damage *= (1 + effect.value / 100);
    }
    if (effect.type === 'percent_defense') {
      result.defense *= (1 + effect.value / 100);
    }
  }

  return result;
};
```

### Efeitos Condicionais

```typescript
type ConditionalEffect =
  | { type: 'on_hit'; chance: number; effect: Effect }
  | { type: 'on_kill'; effect: Effect }
  | { type: 'on_damage_taken'; effect: Effect }
  | { type: 'when_low_hp'; threshold: number; effect: Effect }
  | { type: 'when_full_hp'; effect: Effect }
  | { type: 'every_x_hits'; count: number; effect: Effect }
  | { type: 'after_ability'; abilityId: string; effect: Effect };

// Exemplo: On-kill heal
const vampiricStrike: Upgrade = {
  id: 'vampiric_strike',
  name: 'Vampiric Strike',
  effects: [
    {
      type: 'on_kill',
      effect: { type: 'heal', value: 10 },
    },
  ],
};
```

### Efeitos de Status

```typescript
type StatusEffect =
  | { type: 'burn'; damage: number; duration: number }
  | { type: 'freeze'; duration: number }
  | { type: 'stun'; duration: number }
  | { type: 'slow'; percent: number; duration: number }
  | { type: 'poison'; damage: number; duration: number; stacks: boolean }
  | { type: 'bleed'; damage: number; duration: number }
  | { type: 'marked'; damageAmp: number; duration: number };
```

---

## 3. SISTEMA DE SINERGIAS

### Detecção de Sinergias

```typescript
interface SynergyResult {
  upgrades: Upgrade[];
  synergyName: string;
  bonusEffect: Effect;
}

const detectSynergies = (playerUpgrades: Upgrade[]): SynergyResult[] => {
  const results: SynergyResult[] = [];

  // Checar cada definição de sinergia
  for (const synergy of SYNERGY_DEFINITIONS) {
    const hasAll = synergy.required.every(id =>
      playerUpgrades.some(u => u.id === id)
    );

    if (hasAll) {
      results.push({
        upgrades: playerUpgrades.filter(u => synergy.required.includes(u.id)),
        synergyName: synergy.name,
        bonusEffect: synergy.bonus,
      });
    }
  }

  return results;
};

// Definições de sinergias
const SYNERGY_DEFINITIONS = [
  {
    name: 'Inferno',
    required: ['fire_strike', 'burning_touch', 'flame_burst'],
    bonus: { type: 'percent_damage', value: 50, element: 'fire' },
  },
  {
    name: 'Frost Lord',
    required: ['ice_attack', 'freeze_chance', 'cold_snap'],
    bonus: { type: 'freeze_duration', value: 2 },
  },
  {
    name: 'Glass Cannon',
    required: ['damage_up_large', 'crit_boost', 'fragile'],
    bonus: { type: 'crit_damage', value: 100 },
  },
];
```

### Sinergias Implícitas (Tags)

```typescript
// Sinergias baseadas em tags compartilhadas
const getTagSynergies = (playerUpgrades: Upgrade[]): Effect[] => {
  const tagCounts: Record<string, number> = {};

  for (const upgrade of playerUpgrades) {
    for (const tag of upgrade.tags) {
      tagCounts[tag] = (tagCounts[tag] || 0) + 1;
    }
  }

  const bonuses: Effect[] = [];

  // +5% por item com tag compartilhada
  for (const [tag, count] of Object.entries(tagCounts)) {
    if (count >= 3) {
      bonuses.push({
        type: 'tag_bonus',
        tag,
        value: (count - 2) * 5, // 5%, 10%, 15%...
      });
    }
  }

  return bonuses;
};
```

---

## 4. CATEGORIAS DE UPGRADES (Hades Style)

### Por Fonte/Deus

```typescript
// Hades: cada deus tem tema
const UPGRADE_SOURCES = {
  zeus: {
    theme: 'lightning',
    color: '#FFD700',
    effects: ['chain_lightning', 'lightning_strike', 'storm'],
  },
  poseidon: {
    theme: 'knockback',
    color: '#00CED1',
    effects: ['push', 'rupture', 'wave'],
  },
  athena: {
    theme: 'reflect',
    color: '#FFD700',
    effects: ['deflect', 'expose', 'divine_dash'],
  },
  ares: {
    theme: 'doom',
    color: '#8B0000',
    effects: ['doom', 'blade_rift', 'battle_rage'],
  },
};

// Duo boons: combinação de 2 deuses
const DUO_BOONS = [
  {
    sources: ['zeus', 'poseidon'],
    name: 'Sea Storm',
    effect: 'Knockback causes lightning strikes',
  },
  {
    sources: ['athena', 'ares'],
    name: 'Merciful End',
    effect: 'Deflect triggers Doom instantly',
  },
];
```

### Por Slot (Binding of Isaac Style)

```typescript
type ItemSlot =
  | 'passive'    // Sempre ativo
  | 'active'     // Usar manualmente
  | 'weapon'     // Equipamento principal
  | 'trinket'    // Efeito menor
  | 'consumable'; // Uso único

interface SlottedUpgrade extends Upgrade {
  slot: ItemSlot;
  chargeTime?: number;  // Para active items
}

// Limites de slots
const SLOT_LIMITS = {
  passive: Infinity,
  active: 1,
  weapon: 2,
  trinket: 1,
  consumable: 5,
};
```

---

## 5. STACKING E SCALING

### Comportamentos de Stack

```typescript
const applyStacking = (
  baseValue: number,
  stacks: number,
  behavior: 'additive' | 'multiplicative' | 'diminishing'
): number => {
  switch (behavior) {
    case 'additive':
      // +10, +10, +10 = +30
      return baseValue * stacks;

    case 'multiplicative':
      // 1.1^3 = 1.331
      return Math.pow(1 + baseValue / 100, stacks) - 1;

    case 'diminishing':
      // +10, +5, +2.5 = +17.5
      let total = 0;
      for (let i = 0; i < stacks; i++) {
        total += baseValue * Math.pow(0.5, i);
      }
      return total;
  }
};

// Exemplo:
// baseValue = 10, stacks = 3
// additive: 30
// multiplicative: 33.1
// diminishing: 17.5
```

### Caps e Limites

```typescript
interface StatCaps {
  critChance: { soft: 50, hard: 75 };     // Soft cap + diminishing
  attackSpeed: { hard: 200 };              // Hard cap
  moveSpeed: { hard: 150 };
  cooldownReduction: { hard: 80 };
  armor: { hard: 90 };                     // Nunca 100% reduction
}

const applySoftCap = (
  value: number,
  softCap: number,
  efficiency: number = 0.5
): number => {
  if (value <= softCap) return value;

  const excess = value - softCap;
  return softCap + (excess * efficiency);
};
```

---

## 6. BUILD ARCHETYPES

### Archetypes Comuns

```typescript
const BUILD_ARCHETYPES = {
  glass_cannon: {
    name: 'Glass Cannon',
    focus: ['damage', 'crit'],
    weakness: ['defense', 'hp'],
    upgrades: ['power_up', 'crit_boost', 'berserker', 'fragile'],
  },

  tank: {
    name: 'Tank',
    focus: ['defense', 'hp', 'healing'],
    weakness: ['damage', 'speed'],
    upgrades: ['armor_up', 'regeneration', 'shield', 'slow_but_steady'],
  },

  speedster: {
    name: 'Speedster',
    focus: ['speed', 'attack_speed', 'cooldown'],
    weakness: ['single_hit_damage'],
    upgrades: ['haste', 'quick_attack', 'dash_master', 'cooldown_redux'],
  },

  elementalist: {
    name: 'Elementalist',
    focus: ['status_effects', 'combos'],
    weakness: ['raw_damage'],
    upgrades: ['fire_attack', 'ice_attack', 'lightning', 'elemental_mastery'],
  },

  lifesteal: {
    name: 'Lifesteal',
    focus: ['healing', 'sustain'],
    weakness: ['burst_damage'],
    upgrades: ['vampiric', 'on_kill_heal', 'regeneration', 'life_leech'],
  },
};
```

### Detectar Build do Jogador

```typescript
const detectBuildArchetype = (upgrades: Upgrade[]): string => {
  const scores: Record<string, number> = {};

  for (const [archetypeId, archetype] of Object.entries(BUILD_ARCHETYPES)) {
    let score = 0;

    for (const upgrade of upgrades) {
      // Pontos por upgrade que combina
      if (archetype.upgrades.includes(upgrade.id)) {
        score += 10;
      }

      // Pontos por tags que combinam
      for (const tag of upgrade.tags) {
        if (archetype.focus.includes(tag)) {
          score += 5;
        }
      }
    }

    scores[archetypeId] = score;
  }

  // Retornar archetype com maior score
  return Object.entries(scores)
    .sort(([, a], [, b]) => b - a)[0][0];
};
```

---

## 7. UPGRADE UI/UX

### Apresentação de Escolhas

```typescript
interface UpgradeChoiceUI {
  // Mostrar 3 opções
  options: UpgradeOption[];

  // Tempo para escolher (opcional)
  timer?: number;

  // Permitir reroll
  rerollCost?: number;
  rerollsRemaining?: number;

  // Mostrar sinergias
  synergyHighlights: boolean;
}

interface UpgradeOption {
  upgrade: Upgrade;

  // Visual
  rarityBorder: string;
  iconSize: 'small' | 'medium' | 'large';

  // Info adicional
  currentStacks?: number;
  synergyWith?: Upgrade[]; // Itens que o jogador tem que combinam
  comparison?: StatComparison;
}
```

### Highlight de Sinergias

```
┌─────────────────────────────────────────────────┐
│  🔥 FIRE STRIKE                    [RARE]       │
│                                                 │
│  Attacks deal +15 fire damage                   │
│                                                 │
│  ✨ SYNERGY: Burning Touch, Flame Burst         │
│     → +50% fire damage when combined            │
│                                                 │
│  Current stacks: 2/5                            │
└─────────────────────────────────────────────────┘
```

---

## 8. BALANCEAMENTO DE UPGRADES

### Tier List Internal

```typescript
// Developers mantêm tier list para balancing
const UPGRADE_TIERS = {
  S: ['broken_combo', 'instant_win'],           // Nerf candidates
  A: ['strong_synergy', 'versatile'],           // Sweet spot
  B: ['solid_choice', 'niche_but_good'],        // Balanced
  C: ['situational', 'requires_setup'],         // Okay
  D: ['weak', 'too_conditional'],               // Buff candidates
  F: ['never_pick', 'trap'],                    // Rework needed
};

// Tracking de pick rates
interface UpgradeAnalytics {
  upgradeId: string;
  offerRate: number;      // Quantas vezes apareceu
  pickRate: number;       // % de vezes escolhido quando oferecido
  winRateWith: number;    // Win rate quando jogador tinha
  winRateWithout: number; // Win rate sem
  avgPickOrder: number;   // Em que ponto da run é escolhido
}

// Flags de balanceamento
const needsBalance = (stats: UpgradeAnalytics): string | null => {
  if (stats.pickRate > 0.8) return 'too_strong';
  if (stats.pickRate < 0.1) return 'too_weak';
  if (stats.winRateWith > stats.winRateWithout + 0.2) return 'overpowered';
  if (stats.winRateWith < stats.winRateWithout - 0.1) return 'underpowered';
  return null;
};
```

### Power Budget

```typescript
// Cada upgrade tem um "power budget"
const POWER_BUDGETS = {
  common: 100,
  uncommon: 150,
  rare: 200,
  epic: 300,
  legendary: 500,
};

// Custo de efeitos
const EFFECT_COSTS = {
  flat_damage_per_point: 5,
  percent_damage_per_point: 10,
  crit_chance_per_point: 8,
  crit_damage_per_point: 3,
  on_hit_proc: 50,
  on_kill_proc: 30,
};

// Validar que upgrade está dentro do budget
const validateUpgrade = (upgrade: Upgrade): boolean => {
  const budget = POWER_BUDGETS[upgrade.rarity];
  let cost = 0;

  for (const effect of upgrade.effects) {
    cost += calculateEffectCost(effect);
  }

  return cost <= budget;
};
```

---

## 9. EXEMPLOS COMPLETOS

### Set de Fire Upgrades

```typescript
const FIRE_UPGRADES: Upgrade[] = [
  // Common
  {
    id: 'fire_strike',
    name: 'Fire Strike',
    rarity: 'common',
    effects: [{ type: 'flat_damage', element: 'fire', value: 10 }],
    tags: ['fire', 'attack'],
    synergies: ['burning_touch'],
  },
  {
    id: 'burning_touch',
    name: 'Burning Touch',
    rarity: 'common',
    effects: [{ type: 'on_hit', chance: 0.2, effect: { type: 'burn', damage: 5, duration: 3 } }],
    tags: ['fire', 'status'],
    synergies: ['fire_strike', 'flame_burst'],
  },

  // Rare
  {
    id: 'flame_burst',
    name: 'Flame Burst',
    rarity: 'rare',
    effects: [
      { type: 'on_kill', effect: { type: 'aoe_damage', element: 'fire', value: 30, radius: 100 } },
    ],
    tags: ['fire', 'aoe'],
    synergies: ['burning_touch', 'inferno_mastery'],
  },

  // Epic
  {
    id: 'inferno_mastery',
    name: 'Inferno Mastery',
    rarity: 'epic',
    effects: [
      { type: 'percent_damage', value: 30, condition: { element: 'fire' } },
      { type: 'status_duration', status: 'burn', value: 2 },
    ],
    tags: ['fire', 'mastery'],
    synergies: ['fire_strike', 'burning_touch', 'flame_burst'],
  },

  // Legendary
  {
    id: 'phoenix_flame',
    name: 'Phoenix Flame',
    rarity: 'legendary',
    effects: [
      { type: 'all_damage_to_fire' },
      { type: 'on_death', effect: { type: 'revive', hp_percent: 30, cooldown: 300 } },
      { type: 'burn_immunity' },
    ],
    tags: ['fire', 'survival', 'legendary'],
    synergies: ['*fire*'], // Synergizes com todos de fire
  },
];
```

---

## 10. ANTI-PATTERNS

### O Que Evitar

| Anti-Pattern | Problema | Solução |
|--------------|----------|---------|
| **Must-Pick** | Sempre escolher X | Nerf ou oferecer menos |
| **Never-Pick** | Nunca escolher Y | Buff ou remover |
| **No Synergy** | Upgrades não combinam | Adicionar tags/synergies |
| **RNG Dependente** | Build só funciona com sorte | Pity system para key items |
| **Power Creep** | Novos sempre melhores | Manter power budget |
| **Trap Options** | Parecem bons mas são ruins | Rebalancear ou renomear |

### Design Guidelines

1. **Cada upgrade deve ser a melhor escolha em ALGUMA situação**
2. **Sinergias devem ser descobríveis mas não óbvias**
3. **Builds diferentes devem ser igualmente viáveis**
4. **Jogador deve sentir que suas escolhas importam**
5. **RNG não deve determinar vitória, apenas variedade**
