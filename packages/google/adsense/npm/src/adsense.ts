import type { AdSenseConfig } from './types';

declare global {
  interface Window {
    adsbygoogle: unknown[];
  }
}

/**
 * Google AdSense Service
 */
export class AdSense {
  private publisherId: string;
  private testMode: boolean;
  private initialized: boolean = false;

  constructor(config: AdSenseConfig) {
    this.publisherId = config.publisherId;
    this.testMode = config.testMode ?? false;
  }

  /**
   * Initialize AdSense by loading the script
   */
  initialize(): Promise<void> {
    return new Promise((resolve, reject) => {
      if (this.initialized || typeof window === 'undefined') {
        resolve();
        return;
      }

      // Check if already loaded
      if (document.querySelector('script[src*="adsbygoogle"]')) {
        this.initialized = true;
        resolve();
        return;
      }

      // Initialize adsbygoogle array
      window.adsbygoogle = window.adsbygoogle || [];

      // Create and load script
      const script = document.createElement('script');
      script.async = true;
      script.crossOrigin = 'anonymous';
      script.src = `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${this.publisherId}`;

      if (this.testMode) {
        script.dataset.adTest = 'on';
      }

      script.onload = () => {
        this.initialized = true;
        resolve();
      };

      script.onerror = () => {
        reject(new Error('Failed to load AdSense script'));
      };

      document.head.appendChild(script);
    });
  }

  /**
   * Check if AdSense is loaded
   */
  isLoaded(): boolean {
    return this.initialized && typeof window !== 'undefined' && Array.isArray(window.adsbygoogle);
  }

  /**
   * Push ad to display
   */
  pushAd(): void {
    if (!this.isLoaded()) return;

    try {
      window.adsbygoogle.push({});
    } catch (e) {
      console.error('AdSense push error:', e);
    }
  }

  /**
   * Get publisher ID
   */
  getPublisherId(): string {
    return this.publisherId;
  }

  /**
   * Check if in test mode
   */
  isTestMode(): boolean {
    return this.testMode;
  }
}
