import type { FacebookPixelConfig, StandardEvent } from './types';

declare global {
  interface Window {
    fbq: (...args: unknown[]) => void;
    _fbq: unknown;
  }
}

/**
 * Facebook Pixel Service
 */
export class FacebookPixel {
  private pixelId: string;
  private autoConfig: boolean;
  private debug: boolean;
  private initialized: boolean = false;

  constructor(config: FacebookPixelConfig) {
    this.pixelId = config.pixelId;
    this.autoConfig = config.autoConfig ?? true;
    this.debug = config.debug ?? false;
  }

  /**
   * Initialize Facebook Pixel
   */
  initialize(): void {
    if (this.initialized || typeof window === 'undefined') return;

    // Facebook Pixel base code
    const fbq = (window.fbq = function (...args: unknown[]) {
      if (fbq.callMethod) {
        fbq.callMethod.apply(fbq, args);
      } else {
        fbq.queue.push(args);
      }
    } as typeof window.fbq & {
      callMethod?: (...args: unknown[]) => void;
      queue: unknown[];
      loaded: boolean;
      version: string;
      push: (...args: unknown[]) => void;
    });

    if (!window._fbq) window._fbq = fbq;
    fbq.push = fbq;
    fbq.loaded = true;
    fbq.version = '2.0';
    fbq.queue = [];

    // Load Facebook Pixel script
    const script = document.createElement('script');
    script.async = true;
    script.src = 'https://connect.facebook.net/en_US/fbevents.js';
    document.head.appendChild(script);

    // Initialize pixel
    if (this.autoConfig) {
      window.fbq('init', this.pixelId);
    } else {
      window.fbq('init', this.pixelId, {}, { autoConfig: false });
    }

    // Track initial page view
    window.fbq('track', 'PageView');

    this.initialized = true;

    if (this.debug) {
      console.log('[FacebookPixel] Initialized with ID:', this.pixelId);
    }
  }

  /**
   * Check if Pixel is loaded
   */
  isLoaded(): boolean {
    return this.initialized && typeof window !== 'undefined' && typeof window.fbq === 'function';
  }

  /**
   * Track a standard event
   */
  track(event: StandardEvent | string, data?: Record<string, unknown>): void {
    if (!this.isLoaded()) {
      if (this.debug) console.warn('[FacebookPixel] Not loaded, cannot track:', event);
      return;
    }

    if (data) {
      window.fbq('track', event, data);
    } else {
      window.fbq('track', event);
    }

    if (this.debug) {
      console.log('[FacebookPixel] Track:', event, data);
    }
  }

  /**
   * Track a custom event
   */
  trackCustom(event: string, data?: Record<string, unknown>): void {
    if (!this.isLoaded()) {
      if (this.debug) console.warn('[FacebookPixel] Not loaded, cannot trackCustom:', event);
      return;
    }

    if (data) {
      window.fbq('trackCustom', event, data);
    } else {
      window.fbq('trackCustom', event);
    }

    if (this.debug) {
      console.log('[FacebookPixel] TrackCustom:', event, data);
    }
  }

  /**
   * Track page view
   */
  pageView(): void {
    this.track('PageView');
  }

  /**
   * Track purchase
   */
  trackPurchase(value: number, currency: string = 'BRL', data?: Record<string, unknown>): void {
    this.track('Purchase', { value, currency, ...data });
  }

  /**
   * Track add to cart
   */
  trackAddToCart(data?: Record<string, unknown>): void {
    this.track('AddToCart', data);
  }

  /**
   * Track view content
   */
  trackViewContent(data?: Record<string, unknown>): void {
    this.track('ViewContent', data);
  }

  /**
   * Track lead
   */
  trackLead(data?: Record<string, unknown>): void {
    this.track('Lead', data);
  }

  /**
   * Track complete registration
   */
  trackCompleteRegistration(data?: Record<string, unknown>): void {
    this.track('CompleteRegistration', data);
  }

  /**
   * Track initiate checkout
   */
  trackInitiateCheckout(data?: Record<string, unknown>): void {
    this.track('InitiateCheckout', data);
  }

  /**
   * Track search
   */
  trackSearch(searchString: string, data?: Record<string, unknown>): void {
    this.track('Search', { search_string: searchString, ...data });
  }
}
