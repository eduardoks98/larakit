import React, { createContext, useContext, useEffect, useMemo } from 'react';
import { GoogleAnalytics, GameAnalytics } from './analytics';
import type { GoogleAnalyticsConfig } from './types';

// ==========================================
// Google Analytics Context
// ==========================================

const GoogleAnalyticsContext = createContext<GoogleAnalytics | null>(null);

interface GoogleAnalyticsProviderProps {
  config: GoogleAnalyticsConfig;
  children: React.ReactNode;
}

export function GoogleAnalyticsProvider({
  config,
  children,
}: GoogleAnalyticsProviderProps) {
  const analytics = useMemo(() => new GoogleAnalytics(config), [config]);

  useEffect(() => {
    analytics.initialize();
  }, [analytics]);

  return (
    <GoogleAnalyticsContext.Provider value={analytics}>
      {children}
    </GoogleAnalyticsContext.Provider>
  );
}

export function useAnalytics(): GoogleAnalytics {
  const context = useContext(GoogleAnalyticsContext);
  if (!context) {
    throw new Error('useAnalytics must be used within a GoogleAnalyticsProvider');
  }
  return context;
}

// ==========================================
// Game Analytics Context
// ==========================================

const GameAnalyticsContext = createContext<GameAnalytics | null>(null);

interface GameAnalyticsProviderProps {
  config: GoogleAnalyticsConfig;
  gameName: string;
  children: React.ReactNode;
}

export function GameAnalyticsProvider({
  config,
  gameName,
  children,
}: GameAnalyticsProviderProps) {
  const analytics = useMemo(
    () => new GameAnalytics(config, gameName),
    [config, gameName]
  );

  useEffect(() => {
    analytics.initialize();
  }, [analytics]);

  return (
    <GameAnalyticsContext.Provider value={analytics}>
      {children}
    </GameAnalyticsContext.Provider>
  );
}

export function useGameAnalyticsContext(): GameAnalytics {
  const context = useContext(GameAnalyticsContext);
  if (!context) {
    throw new Error(
      'useGameAnalyticsContext must be used within a GameAnalyticsProvider'
    );
  }
  return context;
}
