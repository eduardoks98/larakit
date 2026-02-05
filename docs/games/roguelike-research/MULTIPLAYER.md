# Roguelike Multiplayer

## 1. MODOS DE MULTIPLAYER

### Co-op (Colaborativo)

```typescript
interface CoopMode {
  playerCount: { min: 2; max: 4 };

  // Recursos compartilhados
  shared: {
    health: boolean;      // HP pool única
    gold: boolean;        // Economia compartilhada
    upgrades: boolean;    // Escolhem juntos
    lives: boolean;       // Pool de respawns
  };

  // Scaling de dificuldade
  difficultyScaling: {
    enemyHealth: (players: number) => number;
    enemyCount: (players: number) => number;
    bossHealth: (players: number) => number;
  };

  // Revival
  revival: {
    enabled: boolean;
    reviveTime: number;   // Segundos segurando
    revivesPerPlayer: number;
  };
}

// Exemplo: Risk of Rain 2 style
const riskOfRain2Coop: CoopMode = {
  playerCount: { min: 1, max: 4 },
  shared: {
    health: false,    // HP individual
    gold: false,      // Gold individual
    upgrades: false,  // Itens individuais
    lives: true,      // Todos perdem juntos
  },
  difficultyScaling: {
    enemyHealth: (p) => 1 + (p - 1) * 0.3,   // +30% por jogador extra
    enemyCount: (p) => 1 + (p - 1) * 0.5,    // +50% spawns
    bossHealth: (p) => 1 + (p - 1) * 0.5,
  },
  revival: {
    enabled: true,
    reviveTime: 3,
    revivesPerPlayer: 3,
  },
};
```

### Versus (Competitivo)

```typescript
interface VersusMode {
  playerCount: number;

  // Formato
  format:
    | 'race'           // Primeiro a completar
    | 'score'          // Maior pontuação
    | 'survival'       // Último sobrevivente
    | 'pvp_arena';     // Combate direto

  // Interação entre jogadores
  interaction: {
    directPvP: boolean;      // Podem atacar uns aos outros
    sabotage: boolean;       // Enviar inimigos/maldições
    stealing: boolean;       // Roubar upgrades/gold
  };

  // Seed
  sharedSeed: boolean;       // Mesmo RNG para todos
}
```

---

## 2. SINCRONIZAÇÃO DE ESTADO

### Arquitetura para Roguelike Multiplayer

```typescript
// O que sincronizar
interface SyncedState {
  // Crítico (todo tick)
  playerPositions: Vector2[];
  playerHealth: number[];
  enemyPositions: Vector2[];

  // Importante (eventos)
  damageEvents: DamageEvent[];
  pickupEvents: PickupEvent[];
  upgradeChoices: UpgradeChoice[];

  // Menos frequente
  inventories: Inventory[];
  questProgress: QuestProgress[];
}

// Arquitetura recomendada
const multiplayerArchitecture = {
  // Autoritativo no servidor
  serverAuthoritative: [
    'enemy_spawning',
    'loot_drops',
    'damage_calculation',
    'upgrade_offers',
    'rng_seed',
  ],

  // Client-side com validação
  clientPredicted: [
    'player_movement',
    'attack_input',
    'ability_use',
  ],

  // Eventos broadcast
  broadcast: [
    'player_death',
    'boss_spawn',
    'upgrade_selected',
    'floor_complete',
  ],
};
```

### RNG Sincronizado

```typescript
// Todos os jogadores devem ver o mesmo "randomness"
class SyncedRNG {
  private seed: number;
  private callCount: number = 0;

  constructor(seed: number) {
    this.seed = seed;
  }

  // Chamadas devem ser determinísticas
  next(): number {
    this.callCount++;
    // Algoritmo determinístico (ex: Mulberry32)
    return this.mulberry32();
  }

  // Sincronizar estado
  getState(): { seed: number; calls: number } {
    return { seed: this.seed, calls: this.callCount };
  }

  // Restaurar estado
  setState(state: { seed: number; calls: number }): void {
    this.seed = state.seed;
    this.callCount = 0;
    // Avançar para o mesmo ponto
    for (let i = 0; i < state.calls; i++) {
      this.next();
    }
  }
}

// Uso: todos os clientes chamam RNG na mesma ordem
const sharedRng = new SyncedRNG(matchSeed);

// Spawns são determinísticos
const spawnEnemy = () => {
  const type = sharedRng.next() < 0.3 ? 'elite' : 'normal';
  const x = sharedRng.next() * mapWidth;
  const y = sharedRng.next() * mapHeight;
  return { type, x, y };
};
```

---

## 3. DESAFIOS ESPECÍFICOS

### Upgrade Selection em Co-op

```typescript
interface CoopUpgradeSelection {
  // Opções de como lidar
  method:
    | 'individual'      // Cada um escolhe para si
    | 'shared'          // Um escolhe, todos ganham
    | 'vote'            // Votação democrática
    | 'alternating';    // Reveza quem escolhe

  // Para votação
  voting?: {
    timeout: number;           // Segundos para votar
    tieBreaker: 'random' | 'host' | 'random_voter';
  };

  // Para individual
  individual?: {
    separateUpgradePools: boolean;  // Mesmas opções ou diferentes?
    waitForAll: boolean;             // Pausar jogo até todos escolherem?
  };
}

// Exemplo: Risk of Rain 2 (individual, não pausa)
const ror2UpgradeSystem = {
  method: 'individual',
  individual: {
    separateUpgradePools: true,   // Cada um vê opções diferentes
    waitForAll: false,            // Jogo continua
  },
};

// Exemplo: Hades co-op mod (vote)
const hadesCoopSystem = {
  method: 'vote',
  voting: {
    timeout: 30,
    tieBreaker: 'random',
  },
};
```

### Morte e Revival

```typescript
interface DeathRevivalSystem {
  // O que acontece ao morrer
  onDeath:
    | 'spectate'        // Assiste até próxima sala
    | 'ghost'           // Pode ajudar limitadamente
    | 'instant_revive'  // Volta imediatamente (com penalidade)
    | 'team_wipe';      // Todos perdem

  // Sistema de revival
  revival: {
    method: 'timer' | 'manual' | 'checkpoint' | 'resource';

    // Timer: revive automaticamente após X segundos
    timer?: { duration: number };

    // Manual: outro jogador segura botão
    manual?: {
      holdTime: number;
      vulnerableWhileReviving: boolean;
    };

    // Checkpoint: revive na próxima sala
    checkpoint?: { fullHealth: boolean };

    // Resource: gasta recurso para reviver
    resource?: {
      resourceType: string;
      cost: number;
    };
  };

  // Limite de mortes
  maxDeaths: number | 'unlimited';
}

// Exemplo balanceado
const balancedRevival: DeathRevivalSystem = {
  onDeath: 'ghost',
  revival: {
    method: 'manual',
    manual: {
      holdTime: 3,
      vulnerableWhileReviving: true,  // Risco
    },
  },
  maxDeaths: 3,  // 3 mortes por jogador, depois é espectador
};
```

---

## 4. MODOS COMPETITIVOS

### Race Mode

```typescript
interface RaceMode {
  objective: 'finish_first' | 'best_time' | 'most_floors';

  // Mesmo seed para fairness
  sharedSeed: true;

  // Podem ver progresso do oponente?
  visibleProgress: boolean;

  // Interação
  interaction: 'none' | 'see_ghost' | 'sabotage';

  // Sabotage (se habilitado)
  sabotage?: {
    sendEnemies: boolean;
    stealUpgrades: boolean;
    curses: boolean;
  };
}

// Spelunky 2 style race
const spelunkyRace: RaceMode = {
  objective: 'finish_first',
  sharedSeed: true,
  visibleProgress: true,
  interaction: 'see_ghost',  // Vê silhueta do oponente
};
```

### Arena PvP

```typescript
interface ArenaPvP {
  // Fase de build
  buildPhase: {
    duration: number;        // Segundos ou waves
    pve: boolean;            // PvE para ganhar upgrades
    sharedPool: boolean;     // Mesmos upgrades disponíveis
  };

  // Fase de combate
  combatPhase: {
    format: 'deathmatch' | 'last_standing' | 'rounds';
    respawns: number;
    timeLimit: number;
  };

  // Balanceamento
  balance: {
    normalizeStats: boolean;  // Igualar HP/dano base
    upgradeLimit: number;     // Máximo de upgrades
  };
}

// Conceito: Roguelike Arena
const roguelikeArena: ArenaPvP = {
  buildPhase: {
    duration: 300,           // 5 minutos de PvE
    pve: true,
    sharedPool: true,        // Fairness
  },
  combatPhase: {
    format: 'rounds',
    respawns: 0,             // Elimination
    timeLimit: 120,          // 2 min por round
  },
  balance: {
    normalizeStats: true,
    upgradeLimit: 10,
  },
};
```

---

## 5. NETWORKING CHALLENGES

### Latência em Roguelikes

```typescript
// Roguelikes são mais tolerantes que shooters
const latencyTolerance = {
  // Aceitável
  movement: 100,          // ms
  attacks: 150,           // ms
  upgrades: 500,          // ms (não crítico)
  pickups: 200,           // ms

  // Técnicas de compensação
  compensation: {
    movementPrediction: true,
    hitConfirmation: 'server',  // Servidor decide hits
    upgradeSync: 'lockstep',    // Esperar todos
  },
};
```

### Entidades Sincronizadas

```typescript
// Em roguelikes com muitos inimigos
interface EntitySync {
  // Inimigos importantes (bosses, elites)
  important: {
    syncRate: 60,            // Todo frame
    interpolation: true,
    serverAuthoritative: true,
  };

  // Inimigos comuns
  common: {
    syncRate: 20,            // Menos frequente
    interpolation: true,
    serverAuthoritative: true,
  };

  // Projéteis do jogador
  playerProjectiles: {
    syncRate: 30,
    clientPredicted: true,   // Predição local
    serverValidation: true,
  };

  // Efeitos visuais
  vfx: {
    syncRate: 0,             // Não sincronizar, recriar local
    localOnly: true,
  };
}

// Otimização: LOD para entidades distantes
const entityLOD = (distance: number, importance: string) => {
  if (importance === 'boss') return 'full';
  if (distance < 500) return 'full';
  if (distance < 1000) return 'reduced';
  return 'minimal';
};
```

---

## 6. MATCHMAKING

### Para Roguelike Co-op

```typescript
interface CoopMatchmaking {
  // Critérios
  criteria: {
    region: string;
    difficulty: string;
    voiceChat: boolean;
    experienceLevel: 'new' | 'intermediate' | 'expert';
  };

  // Preferências
  preferences: {
    friendsOnly: boolean;
    sameClass: boolean;       // Evitar duplicatas
    maxWaitTime: number;
  };

  // Lobby
  lobby: {
    maxSize: number;
    readyCheck: boolean;
    hostControls: string[];   // O que host pode mudar
  };
}
```

### Para Roguelike PvP

```typescript
interface PvPMatchmaking {
  // Ranking
  ranking: {
    type: 'elo' | 'mmr' | 'league';
    decayEnabled: boolean;
    placementGames: number;
  };

  // Queue
  queue: {
    modes: string[];
    crossplay: boolean;
    rangeExpansion: number;   // MMR range expande com tempo
  };

  // Anticheat
  anticheat: {
    seedValidation: true;     // Verificar que seed é a mesma
    replayValidation: true;   // Comparar replays
  };
}
```

---

## 7. EXEMPLOS DE JOGOS

### Risk of Rain 2 (Co-op)

```typescript
const riskOfRain2 = {
  mode: 'co-op',
  players: { min: 1, max: 4 },

  // O que é compartilhado
  shared: {
    difficulty: true,       // Dificuldade escala com todos
    teleporter: true,       // Todos devem estar para ativar
    lives: false,           // Cada um tem sua vida
  },

  // Único
  features: {
    respawnTimer: true,     // Revive automaticamente
    itemSharing: 'printers', // Pode converter itens
    scalingDifficulty: true, // Aumenta com tempo
  },
};
```

### Spelunky 2 (Co-op + Versus)

```typescript
const spelunky2 = {
  modes: ['co-op', 'versus'],

  coop: {
    sharedLives: true,      // Pool de vidas
    friendlyFire: true,     // Pode machucar aliados
    ghostMode: true,        // Morto vira fantasma
  },

  versus: {
    deathmatch: true,
    sharedSeed: true,
    sabotage: false,
  },
};
```

### Cult of the Lamb (Diferente)

```typescript
const cultOfTheLamb = {
  // Não é multiplayer tradicional
  // Mas tem elementos async

  features: {
    visitCults: true,       // Visitar bases de outros
    trade: true,            // Trocar recursos
    rankings: true,         // Leaderboards
  },
};
```

---

## 8. DESIGN RECOMMENDATIONS

### Para Nosso Jogo

```typescript
const ourRoguelikeMultiplayer = {
  // Foco principal
  primaryMode: 'co-op',

  // Secundário
  secondaryMode: 'pvp_arena',

  // Co-op design
  coop: {
    players: { min: 2, max: 4 },

    // Runs mais curtas para multiplayer
    runDuration: 15,  // minutos

    // Upgrades
    upgradeSystem: 'individual_synced',
    // Cada um escolhe, mas vê o que outros escolheram

    // Revival
    revival: {
      method: 'manual',
      limit: 3,
    },

    // Comunicação
    communication: {
      ping: true,
      quickChat: true,
      voice: 'optional',
    },
  },

  // PvP Arena
  pvpArena: {
    // 5 min PvE → 1v1 com builds
    format: 'build_then_fight',
    buildTime: 300,
    combatRounds: 3,
  },

  // Ranking
  ranking: {
    coopRating: true,       // Rating para co-op (clear time)
    pvpRating: true,        // ELO para PvP
  },
};
```

### Coisas a Evitar

| Problema | Porque | Solução |
|----------|--------|---------|
| **Downtime longo** | Jogador morto fica entediado | Ghost mode ou revive rápido |
| **Um jogador domina** | Outros não se divertem | Scaling individual |
| **Upgrade stealing** | Frustração | Drops individuais |
| **Dessync** | Experiências diferentes | Seed compartilhada |
| **Wait time** | Pausas longas para escolher | Timer ou async |
| **Carry** | Um jogador resolve tudo | Mecânicas cooperativas |

---

## 9. IMPLEMENTAÇÃO SOCKET.IO

### Eventos Necessários

```typescript
// Client → Server
interface ClientEvents {
  // Movimento e combate (frequente)
  input: (input: PlayerInput) => void;

  // Upgrades (evento)
  selectUpgrade: (upgradeId: string) => void;
  requestReroll: () => void;

  // Interação
  ping: (position: Vector2, type: PingType) => void;
  reviveStart: (targetId: string) => void;
  reviveCancel: () => void;

  // Lobby
  ready: () => void;
  startGame: () => void;
}

// Server → Client
interface ServerEvents {
  // Estado (frequente)
  gameState: (state: GameState) => void;

  // Eventos
  enemySpawned: (enemy: EnemyData) => void;
  playerDied: (playerId: string) => void;
  upgradeOffered: (options: Upgrade[]) => void;
  floorComplete: (stats: FloorStats) => void;

  // Feedback
  damageDealt: (data: DamageData) => void;
  itemPickedUp: (data: ItemData) => void;
}
```

### Estrutura de Room

```typescript
class RoguelikeRoom {
  id: string;
  players: Map<string, PlayerState>;
  rng: SyncedRNG;
  floor: number;
  difficulty: number;

  // Game loop
  tick(): void {
    this.updateEnemies();
    this.checkCollisions();
    this.spawnIfNeeded();
    this.broadcastState();
  }

  // Broadcast otimizado
  broadcastState(): void {
    const state = this.getCompressedState();
    for (const player of this.players.values()) {
      player.socket.emit('gameState', state);
    }
  }

  // Delta updates
  getCompressedState(): CompressedState {
    // Apenas mudanças desde último tick
    return {
      tick: this.currentTick,
      playerDeltas: this.getPlayerDeltas(),
      enemyDeltas: this.getEnemyDeltas(),
      events: this.pendingEvents,
    };
  }
}
```

---

## 10. CHECKLIST DE MULTIPLAYER

### Antes de Implementar

- [ ] Decidir modo principal (co-op vs pvp)
- [ ] Definir número de jogadores
- [ ] Escolher sistema de upgrades multiplayer
- [ ] Planejar revival/morte
- [ ] Definir o que é sincronizado

### Durante Implementação

- [ ] Sincronizar RNG
- [ ] Implementar prediction
- [ ] Testar com latência alta
- [ ] Otimizar bandwidth
- [ ] Implementar reconexão

### Após Implementar

- [ ] Load testing
- [ ] Balancing co-op vs solo
- [ ] Testar edge cases (disconnect, etc)
- [ ] Analytics de multiplayer
