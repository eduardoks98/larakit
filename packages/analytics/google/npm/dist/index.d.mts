import * as react_jsx_runtime from 'react/jsx-runtime';
import React from 'react';

interface GtagConfigParams {
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
interface GtagEventParams {
    [key: string]: unknown;
}
interface GtagConsentParams {
    ad_storage?: 'granted' | 'denied';
    analytics_storage?: 'granted' | 'denied';
    wait_for_update?: number;
}
type GtagFunction = (command: 'config' | 'set' | 'event' | 'js' | 'consent', targetOrParams: string | Date | GtagConsentParams, params?: GtagConfigParams | GtagEventParams | GtagConsentParams) => void;
declare global {
    interface Window {
        dataLayer: unknown[];
        gtag: GtagFunction;
    }
}
interface GoogleAnalyticsConfig {
    measurementId: string;
    debug?: boolean;
    anonymizeIp?: boolean;
    cookieDomain?: string;
    cookieExpires?: number;
}
interface GameMatchParams {
    mode?: string;
    players?: number;
    map?: string;
    difficulty?: string;
    [key: string]: unknown;
}
interface GameEndParams {
    result: 'win' | 'loss' | 'draw' | 'abandon';
    score?: number;
    opponent_score?: number;
    duration_seconds?: number;
    position?: number;
    [key: string]: unknown;
}
interface PurchaseParams {
    transaction_id: string;
    value: number;
    currency?: string;
    items?: PurchaseItem[];
}
interface PurchaseItem {
    item_id: string;
    item_name: string;
    price?: number;
    quantity?: number;
    item_category?: string;
}
interface VirtualCurrencyParams {
    virtual_currency_name: string;
    value: number;
    source?: string;
    item_name?: string;
}

/**
 * Google Analytics 4 Service
 */
declare class GoogleAnalytics {
    private measurementId;
    private initialized;
    private config;
    constructor(config: GoogleAnalyticsConfig);
    /**
     * Initialize Google Analytics
     */
    initialize(): void;
    /**
     * Check if gtag is available
     */
    isAvailable(): boolean;
    /**
     * Track a custom event
     */
    event(eventName: string, params?: Record<string, unknown>): void;
    /**
     * Track page view
     */
    pageView(pagePath?: string, pageTitle?: string): void;
    /**
     * Set user ID for cross-device tracking
     */
    setUserId(userId: string): void;
    /**
     * Set user properties
     */
    setUserProperties(properties: Record<string, unknown>): void;
    trackLogin(method?: string): void;
    trackSignUp(method?: string): void;
    trackPurchase(params: PurchaseParams): void;
    trackEarnVirtualCurrency(params: VirtualCurrencyParams): void;
    trackSpendVirtualCurrency(params: VirtualCurrencyParams): void;
    trackError(description: string, fatal?: boolean): void;
}
/**
 * Game Analytics - Extended analytics for games
 */
declare class GameAnalytics extends GoogleAnalytics {
    private gameName;
    private matchStartTime;
    private currentMatchId;
    constructor(config: GoogleAnalyticsConfig, gameName: string);
    /**
     * Track event with game context
     */
    private gameEvent;
    /**
     * Track match start
     */
    trackMatchStart(params?: GameMatchParams): string;
    /**
     * Track match end
     */
    trackMatchEnd(params: GameEndParams): number;
    /**
     * Track in-game action
     */
    trackAction(actionName: string, params?: Record<string, unknown>): void;
    /**
     * Get current match ID
     */
    getCurrentMatchId(): string | null;
    /**
     * Get match duration so far (in seconds)
     */
    getCurrentMatchDuration(): number;
    trackTutorialBegin(): void;
    trackTutorialComplete(): void;
    trackLevelUp(level: number, params?: Record<string, unknown>): void;
    trackUnlockAchievement(achievementId: string, achievementName?: string): void;
    trackPostScore(score: number, level?: number, character?: string): void;
    trackShare(method: string, contentType: string, itemId?: string): void;
    trackJoinGroup(groupId: string): void;
    trackAdImpression(adFormat: string, adUnitId?: string): void;
    trackAdClick(adFormat: string, adUnitId?: string): void;
    trackAdReward(adFormat: string, rewardType: string, rewardValue: number): void;
}

/**
 * Hook to use Google Analytics
 */
declare function useGoogleAnalytics(config: GoogleAnalyticsConfig): GoogleAnalytics;
/**
 * Hook to use Game Analytics
 */
declare function useGameAnalytics(config: GoogleAnalyticsConfig, gameName: string): GameAnalytics;
/**
 * Hook for tracking game matches with automatic cleanup
 */
declare function useMatchTracking(analytics: GameAnalytics): {
    startMatch: (params?: GameMatchParams) => string;
    endMatch: (params: GameEndParams) => number;
    trackAction: (actionName: string, params?: Record<string, unknown>) => void;
    isInMatch: () => boolean;
    getMatchDuration: () => number;
    matchId: string | null;
};
/**
 * Hook for page view tracking
 */
declare function usePageViewTracking(analytics: GoogleAnalytics, pagePath?: string, pageTitle?: string): void;

interface GoogleAnalyticsProviderProps {
    config: GoogleAnalyticsConfig;
    children: React.ReactNode;
}
declare function GoogleAnalyticsProvider({ config, children, }: GoogleAnalyticsProviderProps): react_jsx_runtime.JSX.Element;
declare function useAnalytics(): GoogleAnalytics;
interface GameAnalyticsProviderProps {
    config: GoogleAnalyticsConfig;
    gameName: string;
    children: React.ReactNode;
}
declare function GameAnalyticsProvider({ config, gameName, children, }: GameAnalyticsProviderProps): react_jsx_runtime.JSX.Element;
declare function useGameAnalyticsContext(): GameAnalytics;

export { GameAnalytics, GameAnalyticsProvider, type GameEndParams, type GameMatchParams, GoogleAnalytics, type GoogleAnalyticsConfig, GoogleAnalyticsProvider, type GtagConfigParams, type GtagConsentParams, type GtagEventParams, type GtagFunction, type PurchaseItem, type PurchaseParams, type VirtualCurrencyParams, useAnalytics, useGameAnalytics, useGameAnalyticsContext, useGoogleAnalytics, useMatchTracking, usePageViewTracking };
