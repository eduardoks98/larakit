import React, { createContext, useContext, useEffect, useState, useMemo } from 'react';
import { AdSense } from './adsense';
import type { AdSenseConfig, AdSenseContextValue } from './types';

const AdSenseContext = createContext<AdSenseContextValue | null>(null);

interface AdSenseProviderProps extends AdSenseConfig {
  children: React.ReactNode;
}

export function AdSenseProvider({ children, publisherId, testMode = false }: AdSenseProviderProps) {
  const [isLoaded, setIsLoaded] = useState(false);

  const adsense = useMemo(
    () => new AdSense({ publisherId, testMode }),
    [publisherId, testMode]
  );

  useEffect(() => {
    adsense.initialize().then(() => {
      setIsLoaded(true);
    }).catch((error) => {
      console.error('Failed to initialize AdSense:', error);
    });
  }, [adsense]);

  const value: AdSenseContextValue = {
    publisherId,
    isLoaded,
    testMode,
  };

  return (
    <AdSenseContext.Provider value={value}>
      {children}
    </AdSenseContext.Provider>
  );
}

export function useAdSense(): AdSenseContextValue {
  const context = useContext(AdSenseContext);
  if (!context) {
    throw new Error('useAdSense must be used within an AdSenseProvider');
  }
  return context;
}
