import React, { createContext, useContext, useEffect, useState, useMemo, useCallback } from 'react';
import { FacebookPixel } from './pixel';
import type { FacebookPixelConfig, FacebookPixelContextValue } from './types';

const FacebookPixelContext = createContext<FacebookPixelContextValue | null>(null);

interface FacebookPixelProviderProps extends FacebookPixelConfig {
  children: React.ReactNode;
}

export function FacebookPixelProvider({ children, pixelId, autoConfig, debug }: FacebookPixelProviderProps) {
  const [isLoaded, setIsLoaded] = useState(false);

  const pixel = useMemo(
    () => new FacebookPixel({ pixelId, autoConfig, debug }),
    [pixelId, autoConfig, debug]
  );

  useEffect(() => {
    pixel.initialize();
    setIsLoaded(pixel.isLoaded());
  }, [pixel]);

  const track = useCallback(
    (event: string, data?: Record<string, unknown>) => {
      pixel.track(event, data);
    },
    [pixel]
  );

  const trackCustom = useCallback(
    (event: string, data?: Record<string, unknown>) => {
      pixel.trackCustom(event, data);
    },
    [pixel]
  );

  const value: FacebookPixelContextValue = {
    pixelId,
    isLoaded,
    track,
    trackCustom,
  };

  return (
    <FacebookPixelContext.Provider value={value}>
      {children}
    </FacebookPixelContext.Provider>
  );
}

export function useFacebookPixel(): FacebookPixelContextValue {
  const context = useContext(FacebookPixelContext);
  if (!context) {
    throw new Error('useFacebookPixel must be used within a FacebookPixelProvider');
  }
  return context;
}
