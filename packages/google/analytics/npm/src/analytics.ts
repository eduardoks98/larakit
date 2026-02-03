import type {
  GoogleAnalyticsConfig,
  GameMatchParams,
  GameEndParams,
  PurchaseParams,
  VirtualCurrencyParams,
  GtagFunction,
} from './types';

/**
 * Google Analytics 4 Service
 */
export class GoogleAnalytics {
  private measurementId: string;
  private initialized: boolean = false;
  private config: GoogleAnalyticsConfig;

  constructor(config: GoogleAnalyticsConfig) {
    this.measurementId = config.measurementId;
    this.config = config;
  }

  /**
   * Initialize Google Analytics
   */
  initialize(): void {
    if (this.initialized || typeof window === 'undefined') return;

    // Create dataLayer if it doesn't exist
    window.dataLayer = window.dataLayer || [];

    // Define gtag function
    window.gtag = function gtag(...args: unknown[]) {
      window.dataLayer.push(args);
    } as GtagFunction;

    // Initialize gtag
    window.gtag('js', new Date());

    // Configure with options
    const configParams: Record<string, unknown> = {};

    if (this.config.debug) {
      configParams.debug_mode = true;
    }

    if (this.config.anonymizeIp) {
      configParams.anonymize_ip = true;
    }

    if (this.config.cookieDomain) {
      configParams.cookie_domain = this.config.cookieDomain;
    }

    if (this.config.cookieExpires) {
      configParams.cookie_expires = this.config.cookieExpires;
    }

    window.gtag('config', this.measurementId, configParams);

    // Load the gtag.js script
    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${this.measurementId}`;
    document.head.appendChild(script);

    this.initialized = true;
  }

  /**
   * Check if gtag is available
   */
  isAvailable(): boolean {
    return typeof window !== 'undefined' && typeof window.gtag === 'function';
  }

  /**
   * Track a custom event
   */
  event(eventName: string, params: Record<string, unknown> = {}): void {
    if (!this.isAvailable()) return;
    window.gtag('event', eventName, params);
  }

  /**
   * Track page view
   */
  pageView(pagePath?: string, pageTitle?: string): void {
    if (!this.isAvailable()) return;

    window.gtag('event', 'page_view', {
      page_path: pagePath || window.location.pathname,
      page_title: pageTitle || document.title,
      page_location: window.location.href,
    });
  }

  /**
   * Set user ID for cross-device tracking
   */
  setUserId(userId: string): void {
    if (!this.isAvailable()) return;
    window.gtag('config', this.measurementId, { user_id: userId });
  }

  /**
   * Set user properties
   */
  setUserProperties(properties: Record<string, unknown>): void {
    if (!this.isAvailable()) return;
    window.gtag('set', 'user_properties', properties);
  }

  // ==========================================
  // Authentication Events
  // ==========================================

  trackLogin(method: string = 'email'): void {
    this.event('login', { method });
  }

  trackSignUp(method: string = 'email'): void {
    this.event('sign_up', { method });
  }

  // ==========================================
  // E-commerce / Purchase Events
  // ==========================================

  trackPurchase(params: PurchaseParams): void {
    this.event('purchase', {
      transaction_id: params.transaction_id,
      value: params.value,
      currency: params.currency || 'BRL',
      items: params.items || [],
    });
  }

  // ==========================================
  // Virtual Currency Events
  // ==========================================

  trackEarnVirtualCurrency(params: VirtualCurrencyParams): void {
    this.event('earn_virtual_currency', {
      virtual_currency_name: params.virtual_currency_name,
      value: params.value,
      source: params.source,
    });
  }

  trackSpendVirtualCurrency(params: VirtualCurrencyParams): void {
    this.event('spend_virtual_currency', {
      virtual_currency_name: params.virtual_currency_name,
      value: params.value,
      item_name: params.item_name,
    });
  }

  // ==========================================
  // Error Tracking
  // ==========================================

  trackError(description: string, fatal: boolean = false): void {
    this.event('exception', { description, fatal });
  }
}

/**
 * Game Analytics - Extended analytics for games
 */
export class GameAnalytics extends GoogleAnalytics {
  private gameName: string;
  private matchStartTime: number | null = null;
  private currentMatchId: string | null = null;

  constructor(config: GoogleAnalyticsConfig, gameName: string) {
    super(config);
    this.gameName = gameName;
  }

  /**
   * Track event with game context
   */
  private gameEvent(eventName: string, params: Record<string, unknown> = {}): void {
    this.event(eventName, {
      game_name: this.gameName,
      ...params,
    });
  }

  // ==========================================
  // Match/Game Events
  // ==========================================

  /**
   * Track match start
   */
  trackMatchStart(params: GameMatchParams = {}): string {
    this.matchStartTime = Date.now();
    this.currentMatchId = `${this.gameName}_${Date.now()}`;

    this.gameEvent('game_start', {
      match_id: this.currentMatchId,
      ...params,
    });

    return this.currentMatchId;
  }

  /**
   * Track match end
   */
  trackMatchEnd(params: GameEndParams): number {
    const duration = this.matchStartTime
      ? Math.round((Date.now() - this.matchStartTime) / 1000)
      : 0;

    this.gameEvent('game_end', {
      match_id: this.currentMatchId,
      duration_seconds: params.duration_seconds ?? duration,
      ...params,
    });

    // Reset match state
    this.matchStartTime = null;
    this.currentMatchId = null;

    return duration;
  }

  /**
   * Track in-game action
   */
  trackAction(actionName: string, params: Record<string, unknown> = {}): void {
    this.gameEvent('game_action', {
      action_name: actionName,
      match_id: this.currentMatchId,
      ...params,
    });
  }

  /**
   * Get current match ID
   */
  getCurrentMatchId(): string | null {
    return this.currentMatchId;
  }

  /**
   * Get match duration so far (in seconds)
   */
  getCurrentMatchDuration(): number {
    if (!this.matchStartTime) return 0;
    return Math.round((Date.now() - this.matchStartTime) / 1000);
  }

  // ==========================================
  // Tutorial Events
  // ==========================================

  trackTutorialBegin(): void {
    this.gameEvent('tutorial_begin');
  }

  trackTutorialComplete(): void {
    this.gameEvent('tutorial_complete');
  }

  // ==========================================
  // Progression Events
  // ==========================================

  trackLevelUp(level: number, params: Record<string, unknown> = {}): void {
    this.gameEvent('level_up', {
      level,
      ...params,
    });
  }

  trackUnlockAchievement(achievementId: string, achievementName?: string): void {
    this.gameEvent('unlock_achievement', {
      achievement_id: achievementId,
      achievement_name: achievementName,
    });
  }

  trackPostScore(score: number, level?: number, character?: string): void {
    this.gameEvent('post_score', {
      score,
      level,
      character,
    });
  }

  // ==========================================
  // Engagement Events
  // ==========================================

  trackShare(method: string, contentType: string, itemId?: string): void {
    this.gameEvent('share', {
      method,
      content_type: contentType,
      item_id: itemId,
    });
  }

  trackJoinGroup(groupId: string): void {
    this.gameEvent('join_group', { group_id: groupId });
  }

  // ==========================================
  // Ad Events
  // ==========================================

  trackAdImpression(adFormat: string, adUnitId?: string): void {
    this.gameEvent('ad_impression', {
      ad_format: adFormat,
      ad_unit_id: adUnitId,
    });
  }

  trackAdClick(adFormat: string, adUnitId?: string): void {
    this.gameEvent('ad_click', {
      ad_format: adFormat,
      ad_unit_id: adUnitId,
    });
  }

  trackAdReward(adFormat: string, rewardType: string, rewardValue: number): void {
    this.gameEvent('ad_reward', {
      ad_format: adFormat,
      reward_type: rewardType,
      reward_value: rewardValue,
    });
  }
}
