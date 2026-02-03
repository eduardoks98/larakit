# @larakit/analytics-google

Google Analytics 4 (GA4) integration for React/TypeScript applications with game-specific tracking.

## Installation

```bash
npm install @larakit/analytics-google
# or
yarn add @larakit/analytics-google
# or
pnpm add @larakit/analytics-google
```

## Quick Start

### Basic Usage with Hook

```tsx
import { useGoogleAnalytics } from '@larakit/analytics-google';

function App() {
  const analytics = useGoogleAnalytics({
    measurementId: 'G-XXXXXXXXXX',
  });

  const handleClick = () => {
    analytics.event('button_click', { button_name: 'signup' });
  };

  return <button onClick={handleClick}>Sign Up</button>;
}
```

### Using Context Provider

```tsx
import { GoogleAnalyticsProvider, useAnalytics } from '@larakit/analytics-google';

// In your app root
function App() {
  return (
    <GoogleAnalyticsProvider config={{ measurementId: 'G-XXXXXXXXXX' }}>
      <MyComponent />
    </GoogleAnalyticsProvider>
  );
}

// In any component
function MyComponent() {
  const analytics = useAnalytics();

  useEffect(() => {
    analytics.trackLogin('google');
  }, []);

  return <div>...</div>;
}
```

## Game Analytics

For games like BangShot, use the `GameAnalytics` class or `GameAnalyticsProvider`:

### Setup

```tsx
import { GameAnalyticsProvider, useGameAnalyticsContext } from '@larakit/analytics-google';

function App() {
  return (
    <GameAnalyticsProvider
      config={{ measurementId: 'G-XXXXXXXXXX' }}
      gameName="BangShot"
    >
      <Game />
    </GameAnalyticsProvider>
  );
}
```

### Tracking Matches

```tsx
import { useGameAnalyticsContext, useMatchTracking } from '@larakit/analytics-google';

function GameRoom() {
  const analytics = useGameAnalyticsContext();
  const { startMatch, endMatch, trackAction, isInMatch } = useMatchTracking(analytics);

  const handleGameStart = () => {
    startMatch({
      mode: 'ranked',
      players: 2,
    });
  };

  const handleGameEnd = (won: boolean) => {
    endMatch({
      result: won ? 'win' : 'loss',
      score: playerScore,
      opponent_score: opponentScore,
    });
  };

  const handleShot = () => {
    trackAction('shot_fired', { weapon: 'shotgun' });
  };

  return (
    <div>
      {!isInMatch() ? (
        <button onClick={handleGameStart}>Start Game</button>
      ) : (
        <>
          <button onClick={handleShot}>Shoot</button>
          <button onClick={() => handleGameEnd(true)}>Win</button>
          <button onClick={() => handleGameEnd(false)}>Lose</button>
        </>
      )}
    </div>
  );
}
```

### Using Hook Directly

```tsx
import { useGameAnalytics } from '@larakit/analytics-google';

function Game() {
  const analytics = useGameAnalytics(
    { measurementId: 'G-XXXXXXXXXX' },
    'BangShot'
  );

  // Track match start
  const matchId = analytics.trackMatchStart({
    mode: 'casual',
    players: 2,
  });

  // Track in-game actions
  analytics.trackAction('item_used', { item: 'beer' });
  analytics.trackAction('shot_fired', { hit: true });

  // Track match end
  analytics.trackMatchEnd({
    result: 'win',
    score: 100,
  });
}
```

## Available Methods

### GoogleAnalytics

| Method | Description |
|--------|-------------|
| `initialize()` | Initialize GA4 (auto-called by hooks/providers) |
| `event(name, params)` | Track custom event |
| `pageView(path?, title?)` | Track page view |
| `setUserId(userId)` | Set user ID for cross-device tracking |
| `setUserProperties(props)` | Set user properties |
| `trackLogin(method)` | Track login event |
| `trackSignUp(method)` | Track sign-up event |
| `trackPurchase(params)` | Track purchase event |
| `trackEarnVirtualCurrency(params)` | Track earning virtual currency |
| `trackSpendVirtualCurrency(params)` | Track spending virtual currency |
| `trackError(description, fatal?)` | Track error/exception |

### GameAnalytics (extends GoogleAnalytics)

| Method | Description |
|--------|-------------|
| `trackMatchStart(params)` | Track game/match start |
| `trackMatchEnd(params)` | Track game/match end |
| `trackAction(name, params)` | Track in-game action |
| `getCurrentMatchId()` | Get current match ID |
| `getCurrentMatchDuration()` | Get match duration in seconds |
| `trackTutorialBegin()` | Track tutorial start |
| `trackTutorialComplete()` | Track tutorial completion |
| `trackLevelUp(level, params)` | Track level up |
| `trackUnlockAchievement(id, name?)` | Track achievement unlock |
| `trackPostScore(score, level?, character?)` | Post a score |
| `trackShare(method, contentType, itemId?)` | Track share action |
| `trackJoinGroup(groupId)` | Track joining a group |
| `trackAdImpression(format, unitId?)` | Track ad impression |
| `trackAdClick(format, unitId?)` | Track ad click |
| `trackAdReward(format, type, value)` | Track rewarded ad |

## Configuration Options

```typescript
interface GoogleAnalyticsConfig {
  measurementId: string;  // Required: GA4 Measurement ID
  debug?: boolean;        // Enable debug mode
  anonymizeIp?: boolean;  // Anonymize IP addresses (GDPR)
  cookieDomain?: string;  // Cookie domain
  cookieExpires?: number; // Cookie expiration in seconds
}
```

## TypeScript Support

Full TypeScript support with exported types:

```typescript
import type {
  GoogleAnalyticsConfig,
  GameMatchParams,
  GameEndParams,
  PurchaseParams,
} from '@larakit/analytics-google';
```

## License

MIT
