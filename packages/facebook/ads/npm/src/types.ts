export interface FacebookPixelConfig {
  pixelId: string;
  autoConfig?: boolean;
  debug?: boolean;
}

export interface FacebookPixelContextValue {
  pixelId: string;
  isLoaded: boolean;
  track: (event: string, data?: Record<string, unknown>) => void;
  trackCustom: (event: string, data?: Record<string, unknown>) => void;
}

// Standard Facebook Pixel Events
export type StandardEvent =
  | 'AddPaymentInfo'
  | 'AddToCart'
  | 'AddToWishlist'
  | 'CompleteRegistration'
  | 'Contact'
  | 'CustomizeProduct'
  | 'Donate'
  | 'FindLocation'
  | 'InitiateCheckout'
  | 'Lead'
  | 'PageView'
  | 'Purchase'
  | 'Schedule'
  | 'Search'
  | 'StartTrial'
  | 'SubmitApplication'
  | 'Subscribe'
  | 'ViewContent';

export interface PurchaseData {
  value: number;
  currency: string;
  content_ids?: string[];
  content_type?: 'product' | 'product_group';
  contents?: Array<{ id: string; quantity: number }>;
  num_items?: number;
}

export interface AddToCartData {
  value?: number;
  currency?: string;
  content_ids?: string[];
  content_type?: 'product' | 'product_group';
  contents?: Array<{ id: string; quantity: number }>;
}

export interface ViewContentData {
  value?: number;
  currency?: string;
  content_ids?: string[];
  content_type?: 'product' | 'product_group';
  content_name?: string;
  content_category?: string;
}

export interface LeadData {
  value?: number;
  currency?: string;
  content_name?: string;
  content_category?: string;
}

export interface CompleteRegistrationData {
  value?: number;
  currency?: string;
  content_name?: string;
  status?: string;
}
