import { useEffect, useRef, useCallback } from 'react';
import { GoogleAnalytics, GameAnalytics } from './analytics';
import type { GoogleAnalyticsConfig, GameMatchParams, GameEndParams } from './types';

/**
 * Hook to use Google Analytics
 */
export function useGoogleAnalytics(config: GoogleAnalyticsConfig): GoogleAnalytics {
  const analyticsRef = useRef<GoogleAnalytics | null>(null);

  if (!analyticsRef.current) {
    analyticsRef.current = new GoogleAnalytics(config);
  }

  useEffect(() => {
    analyticsRef.current?.initialize();
  }, []);

  return analyticsRef.current;
}

/**
 * Hook to use Game Analytics
 */
export function useGameAnalytics(
  config: GoogleAnalyticsConfig,
  gameName: string
): GameAnalytics {
  const analyticsRef = useRef<GameAnalytics | null>(null);

  if (!analyticsRef.current) {
    analyticsRef.current = new GameAnalytics(config, gameName);
  }

  useEffect(() => {
    analyticsRef.current?.initialize();
  }, []);

  return analyticsRef.current;
}

/**
 * Hook for tracking game matches with automatic cleanup
 */
export function useMatchTracking(analytics: GameAnalytics) {
  const matchIdRef = useRef<string | null>(null);

  const startMatch = useCallback(
    (params: GameMatchParams = {}) => {
      matchIdRef.current = analytics.trackMatchStart(params);
      return matchIdRef.current;
    },
    [analytics]
  );

  const endMatch = useCallback(
    (params: GameEndParams) => {
      const duration = analytics.trackMatchEnd(params);
      matchIdRef.current = null;
      return duration;
    },
    [analytics]
  );

  const trackAction = useCallback(
    (actionName: string, params: Record<string, unknown> = {}) => {
      analytics.trackAction(actionName, params);
    },
    [analytics]
  );

  const isInMatch = useCallback(() => {
    return matchIdRef.current !== null;
  }, []);

  const getMatchDuration = useCallback(() => {
    return analytics.getCurrentMatchDuration();
  }, [analytics]);

  // Cleanup on unmount - track abandon if match is still running
  useEffect(() => {
    return () => {
      if (matchIdRef.current) {
        analytics.trackMatchEnd({ result: 'abandon' });
      }
    };
  }, [analytics]);

  return {
    startMatch,
    endMatch,
    trackAction,
    isInMatch,
    getMatchDuration,
    matchId: matchIdRef.current,
  };
}

/**
 * Hook for page view tracking
 */
export function usePageViewTracking(
  analytics: GoogleAnalytics,
  pagePath?: string,
  pageTitle?: string
) {
  useEffect(() => {
    analytics.pageView(pagePath, pageTitle);
  }, [analytics, pagePath, pageTitle]);
}
