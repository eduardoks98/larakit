// Configuration for Google Analytics config command
export interface GtagConfigParams {
  page_title?: string;
  page_location?: string;
  page_path?: string;
  send_page_view?: boolean;
  debug_mode?: boolean;
  anonymize_ip?: boolean;
  cookie_domain?: string;
  cookie_expires?: number;
  user_id?: string;
  [key: string]: unknown;
}

// Event parameters
export interface GtagEventParams {
  [key: string]: unknown;
}

// Consent parameters
export interface GtagConsentParams {
  ad_storage?: 'granted' | 'denied';
  analytics_storage?: 'granted' | 'denied';
  wait_for_update?: number;
}

// The gtag function type
export type GtagFunction = (
  command: 'config' | 'set' | 'event' | 'js' | 'consent',
  targetOrParams: string | Date | GtagConsentParams,
  params?: GtagConfigParams | GtagEventParams | GtagConsentParams
) => void;

// Global gtag type declaration
declare global {
  interface Window {
    dataLayer: unknown[];
    gtag: GtagFunction;
  }
}

export interface GoogleAnalyticsConfig {
  measurementId: string;
  debug?: boolean;
  anonymizeIp?: boolean;
  cookieDomain?: string;
  cookieExpires?: number;
}

export interface GameMatchParams {
  mode?: string;
  players?: number;
  map?: string;
  difficulty?: string;
  [key: string]: unknown;
}

export interface GameEndParams {
  result: 'win' | 'loss' | 'draw' | 'abandon';
  score?: number;
  opponent_score?: number;
  duration_seconds?: number;
  position?: number;
  [key: string]: unknown;
}

export interface PurchaseParams {
  transaction_id: string;
  value: number;
  currency?: string;
  items?: PurchaseItem[];
}

export interface PurchaseItem {
  item_id: string;
  item_name: string;
  price?: number;
  quantity?: number;
  item_category?: string;
}

export interface VirtualCurrencyParams {
  virtual_currency_name: string;
  value: number;
  source?: string;
  item_name?: string;
}
