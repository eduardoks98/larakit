// Core classes
export { GoogleAnalytics, GameAnalytics } from './analytics';

// React hooks
export {
  useGoogleAnalytics,
  useGameAnalytics,
  useMatchTracking,
  usePageViewTracking,
} from './hooks';

// React context
export {
  GoogleAnalyticsProvider,
  useAnalytics,
  GameAnalyticsProvider,
  useGameAnalyticsContext,
} from './context';

// Types
export type {
  GoogleAnalyticsConfig,
  GameMatchParams,
  GameEndParams,
  PurchaseParams,
  PurchaseItem,
  VirtualCurrencyParams,
  GtagFunction,
  GtagConfigParams,
  GtagEventParams,
  GtagConsentParams,
} from './types';
