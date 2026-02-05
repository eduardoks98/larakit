# Jogos de Inspiração

## 1. ACTION ROGUELITES

### Hades (Supergiant Games)

**O que faz bem:**
- Narrativa integrada ao loop de morte
- Boons com sinergias claras
- Combate fluido e responsivo
- Personagens memoráveis
- Meta-progressão significativa

**Mecânicas para estudar:**
```typescript
const hadesLessons = {
  // Boon system
  boonCategories: ['attack', 'special', 'cast', 'dash', 'call'],
  duoBoons: true,           // Combinações de 2 deuses
  legendaryBoons: true,     // Boons únicos poderosos

  // Meta progression
  mirrorOfNight: {
    type: 'permanent_upgrades',
    currency: 'darkness',
    alternatives: true,      // 2 opções por slot
  },

  // Heat system
  pactOfPunishment: {
    type: 'difficulty_modifiers',
    reward: 'bounties',
    stackable: true,
  },

  // Relationships
  npcRelationships: {
    gifts: true,
    story: true,
    unlocks: true,
  },
};
```

**Referências visuais:**
- Isometric 3D com 2D sprites
- Dash trails e efeitos de impacto
- UI minimalista durante combate

---

### Dead Cells (Motion Twin)

**O que faz bem:**
- Controles precisos (Castlevania-like)
- Permadeath justo
- Blueprint system para unlocks
- Biomes variados
- Boss design excelente

**Mecânicas para estudar:**
```typescript
const deadCellsLessons = {
  // Combat
  combatSystem: {
    weapons: 2,              // Dual wield
    skills: 2,               // Habilidades ativas
    parry: true,             // Timing-based defense
    roll: {
      iframes: true,
      distance: 'medium',
    },
  },

  // Unlocks
  blueprintSystem: {
    dropFromEnemies: true,
    requireCells: true,      // Moeda meta
    permanentUnlock: true,
  },

  // Difficulty
  bossCell: {
    levels: 5,               // BC0 a BC5
    modifiers: ['enemy_hp', 'enemy_damage', 'healing_nerf'],
  },

  // Exploration
  biomeRoutes: {
    branching: true,         // Múltiplos caminhos
    secrets: true,           // Áreas escondidas
    shortcuts: true,
  },
};
```

---

### Enter the Gungeon (Dodge Roll)

**O que faz bem:**
- Bullet hell acessível
- Armas criativas e únicas
- Humor e charm
- Synergies entre itens
- Co-op local

**Mecânicas para estudar:**
```typescript
const gungeonLessons = {
  // Weapons
  weaponDesign: {
    count: 243,              // Muitas armas
    uniqueMechanics: true,   // Cada uma diferente
    synergies: true,         // Combos entre armas
  },

  // Dodge
  dodgeRoll: {
    iframes: 'generous',
    tableFlip: true,         // Cover system
    blanks: true,            // Clear all bullets
  },

  // Shops
  shopSystem: {
    multipleShops: true,
    stealing: true,          // Risco/recompensa
    curses: true,            // Trade-offs
  },
};
```

---

## 2. SURVIVORS-LIKE (Para Evitar Clone)

### Vampire Survivors (poncle)

**O que faz (que NÃO queremos copiar):**
- Auto-attack 100%
- Movimento lento
- Runs de 30 minutos
- Principalmente single player

**O que podemos aprender:**
```typescript
const vampireSurvivorsLessons = {
  // O que funciona
  goodIdeas: {
    clearProgression: true,      // Sempre subindo de level
    constantRewards: true,       // Dopamina constante
    simpleControls: true,        // Acessível
    buildDiversity: true,        // Muitas builds viáveis
  },

  // O que fazer diferente
  ourDifferences: {
    activeControl: true,         // Jogador ataca manualmente
    shorterRuns: true,           // 10-15 min
    multiplayer: true,           // Co-op e PvP
    skillBasedCombat: true,      // Não só posicionamento
  },
};
```

---

### HoloCure (Kay Yu)

**Variação interessante:**
- Fan game de Hololive
- Mais ativo que Vampire Survivors
- Special attacks manuais
- Boss fights

---

## 3. DECK BUILDERS

### Slay the Spire (Mega Crit)

**O que faz bem:**
- Decisões significativas
- Synergies de cartas
- Risk vs reward
- Replayability infinita

**Mecânicas para estudar:**
```typescript
const slayTheSpireLessons = {
  // Core loop
  coreLoop: {
    fight: 'turn_based',
    reward: 'card_or_relic',
    map: 'branching_paths',
    boss: 'end_of_act',
  },

  // Deck building
  deckBuilding: {
    startingDeck: 'small',       // ~10 cards
    addCards: 'after_combat',
    removeCards: 'at_shops',
    upgrades: 'at_campfires',
  },

  // Relics
  relicSystem: {
    passive: true,
    buildDefining: true,
    synergies: true,
    rarity: ['common', 'uncommon', 'rare', 'boss', 'shop'],
  },
};
```

---

### Balatro (LocalThunk)

**O que faz bem:**
- Twist no poker
- Joker system brilhante
- Runs curtas e viciantes
- UI/UX perfeita

**Mecânicas para estudar:**
```typescript
const balatroLessons = {
  // Core mechanic
  pokerTwist: {
    standardRules: 'base',
    jokers: 'modifiers',
    scoring: 'multiplicative',
  },

  // Jokers (como upgrades)
  jokerSystem: {
    slots: 5,
    rarity: ['common', 'uncommon', 'rare', 'legendary'],
    synergies: 'extensive',
    sellForMoney: true,
  },

  // Ante system
  difficulty: {
    blinds: ['small', 'big', 'boss'],
    scoreRequirement: 'exponential',
    bonusForSkipping: true,
  },
};
```

---

## 4. AUTO BATTLERS

### Teamfight Tactics (Riot)

**O que faz bem:**
- Fácil de entender, difícil de masterizar
- Drafting competitivo
- Economia de gold
- Positioning matters

**Mecânicas para estudar:**
```typescript
const tftLessons = {
  // Economy
  goldSystem: {
    passive: 5,              // Por rodada
    winStreak: 'bonus',
    lossStreak: 'bonus',
    interest: '10% max 5',
    levelUp: 'spend_gold',
  },

  // Drafting
  shopMechanics: {
    reroll: 2,               // Gold para reroll
    freeze: true,            // Lock champions
    carousel: true,          // Shared draft
  },

  // Synergies
  traitSystem: {
    origins: true,
    classes: true,
    thresholds: [2, 4, 6],   // Mais = mais forte
  },
};
```

---

### Super Auto Pets (Team Wood)

**O que faz bem:**
- Ultra simples
- Free to play justo
- Cross-platform
- Runs rápidas

---

## 5. TWIN STICK SHOOTERS

### Nuclear Throne (Vlambeer)

**O que faz bem:**
- Screen shake perfeito
- Guns with personality
- Brutal difficulty
- Mutations (upgrades)

**Juice lessons:**
```typescript
const nuclearThroneFeedback = {
  screenShake: {
    onShoot: true,
    onHit: true,
    onKill: 'big',
  },
  particles: {
    blood: 'excessive',
    shells: 'physical',
    explosions: 'chunky',
  },
  sound: {
    bass: 'heavy',
    layered: true,
  },
};
```

---

### Binding of Isaac (Edmund McMillen)

**O que faz bem:**
- Item synergies absurdas
- Secrets everywhere
- Replay value infinito
- Humor dark

**Mecânicas para estudar:**
```typescript
const isaacLessons = {
  // Items
  itemSystem: {
    categories: ['passive', 'active', 'trinket', 'consumable'],
    pools: ['treasure', 'shop', 'boss', 'devil', 'angel'],
    synergies: 'emergent',    // Não planejadas explicitamente
  },

  // Secrets
  secretContent: {
    hiddenRooms: true,
    alternateFloors: true,
    multipleEndings: true,
    challengeRuns: true,
  },

  // Transformations
  transformations: {
    trigger: 'collect_3_items',
    bonus: 'significant',
  },
};
```

---

## 6. INDIE HITS 2024-2025

### Megabonk

**Destaque:** "Maior surpresa indie 2025"
- Vampire Survivors evoluído
- Mais mecânicas ativas
- Boss fights

### Tears of Metal

**Destaque:** Co-op roguelike hack and slash
- 4 player co-op
- Classes complementares
- Loot sharing

### Brotato

**Destaque:** Arena survivors diferente
- 6 armas simultâneas
- Runs de 20 minutos
- Builds extremas

---

## 7. O QUE COMBINAR

### Nossa Receita

```typescript
const ourGame = {
  // De Hades
  fromHades: {
    narrativeIntegration: true,
    boonSynergies: true,
    fluidCombat: true,
    npcRelationships: false,   // Simplificar
  },

  // De Dead Cells
  fromDeadCells: {
    preciseControls: true,
    blueprintUnlocks: true,
    biomeVariety: false,       // Arena única
  },

  // De Enter the Gungeon
  fromGungeon: {
    weaponCreativity: true,
    coopLocal: true,
    dodgeRoll: true,
  },

  // De Balatro
  fromBalatro: {
    shortRuns: true,
    clearUI: true,
    jokerLikeSynergies: true,
  },

  // Original
  unique: {
    multiplayerFocus: true,
    pvpArena: true,
    competitiveRanking: true,
    shorterRuns: true,         // 10-15 min
  },
};
```

---

## 8. ANÁLISE DE MERCADO

### Tendências 2025

| Tendência | Exemplos | Oportunidade |
|-----------|----------|--------------|
| **Co-op roguelikes** | Risk of Rain 2, Gunfire Reborn | Alta demanda |
| **Short sessions** | Balatro, Brotato | Mobile-friendly |
| **Deck building hybrid** | Slay the Spire 2 | Mecânica comprovada |
| **PvP roguelite** | Pouco | Nicho inexplorado |
| **Competitive roguelike** | Speedruns apenas | Oportunidade |

### Gaps no Mercado

1. **Roguelike PvP Arena** - Quase ninguém faz
2. **Roguelike Competitivo com Ranking** - Raro
3. **Co-op Roguelike com Runs Curtas** - Demanda
4. **Browser-based Roguelike Quality** - Poucos bons

---

## 9. REFERÊNCIAS VISUAIS

### Estilos que Funcionam

| Estilo | Exemplos | Prós | Contras |
|--------|----------|------|---------|
| **Pixel Art** | Dead Cells, Gungeon | Charm, fácil animar | Limitado |
| **Hand Drawn** | Hades, Cuphead | Único | Caro |
| **Low Poly 3D** | Risk of Rain 2 | Moderno | Mais complexo |
| **Vector/Clean** | Slay the Spire | Clear, escalável | Menos "juicy" |

### Nossa Recomendação

**Pixel art 32x32 ou 64x64**
- Nostalgia
- Mais rápido de produzir
- Animações claras
- Funciona bem em browser

---

## 10. RECURSOS PARA ESTUDAR

### GDC Talks

- "Designing Hades" - Supergiant
- "The Art of Screenshake" - Vlambeer
- "Slay the Spire: A Design Postmortem"
- "Dead Cells: What the F*ck is a Roguelike"

### Canais YouTube

- Mark Brown (Game Maker's Toolkit)
- Adam Millard (The Architect of Games)
- Design Doc

### Artigos

- Gamasutra roguelike postmortems
- Roguelike Celebration talks
