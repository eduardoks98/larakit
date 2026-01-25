import React, { useEffect, useRef } from 'react';

declare global {
  interface Window {
    adsbygoogle: unknown[];
  }
}

export type AdFormat = 'banner' | 'leaderboard' | 'rectangle' | 'skyscraper' | 'large_rectangle' | 'responsive';

interface AdBannerProps {
  /**
   * Your AdSense publisher ID (ca-pub-XXXXXXXXXXXXXXXX)
   */
  publisherId: string;

  /**
   * The ad slot ID from AdSense
   */
  slotId: string;

  /**
   * The ad format/size
   */
  format?: AdFormat;

  /**
   * Custom styles for the ad container
   */
  style?: React.CSSProperties;

  /**
   * Custom class name for the ad container
   */
  className?: string;

  /**
   * Enable test mode (shows test ads)
   */
  testMode?: boolean;

  /**
   * Callback when ad is loaded
   */
  onLoad?: () => void;

  /**
   * Callback when ad fails to load
   */
  onError?: (error: Error) => void;
}

const formatDimensions: Record<AdFormat, { width: string; height: string }> = {
  banner: { width: '468px', height: '60px' },
  leaderboard: { width: '728px', height: '90px' },
  rectangle: { width: '300px', height: '250px' },
  skyscraper: { width: '120px', height: '600px' },
  large_rectangle: { width: '336px', height: '280px' },
  responsive: { width: '100%', height: 'auto' },
};

export function AdBanner({
  publisherId,
  slotId,
  format = 'responsive',
  style,
  className,
  testMode = false,
  onLoad,
  onError,
}: AdBannerProps) {
  const adRef = useRef<HTMLModElement>(null);
  const isLoaded = useRef(false);

  useEffect(() => {
    // Load AdSense script if not already loaded
    const loadScript = () => {
      const existingScript = document.querySelector(
        `script[src*="pagead2.googlesyndication.com"]`
      );

      if (!existingScript) {
        const script = document.createElement('script');
        script.src = `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${publisherId}`;
        script.async = true;
        script.crossOrigin = 'anonymous';

        script.onload = () => {
          pushAd();
        };

        script.onerror = () => {
          onError?.(new Error('Failed to load AdSense script'));
        };

        document.head.appendChild(script);
      } else {
        pushAd();
      }
    };

    const pushAd = () => {
      if (isLoaded.current) return;

      try {
        (window.adsbygoogle = window.adsbygoogle || []).push({});
        isLoaded.current = true;
        onLoad?.();
      } catch (error) {
        onError?.(error as Error);
      }
    };

    loadScript();

    return () => {
      isLoaded.current = false;
    };
  }, [publisherId, onLoad, onError]);

  const dimensions = formatDimensions[format];
  const isResponsive = format === 'responsive';

  const containerStyle: React.CSSProperties = {
    display: isResponsive ? 'block' : 'inline-block',
    width: dimensions.width,
    height: dimensions.height,
    overflow: 'hidden',
    ...style,
  };

  return (
    <ins
      ref={adRef}
      className={`adsbygoogle ${className || ''}`}
      style={containerStyle}
      data-ad-client={publisherId}
      data-ad-slot={slotId}
      data-ad-format={isResponsive ? 'auto' : undefined}
      data-full-width-responsive={isResponsive ? 'true' : undefined}
      data-adtest={testMode ? 'on' : undefined}
    />
  );
}

/**
 * Hook to fetch ad units from your Laravel API
 */
export function useAdUnits(gameId?: string | number) {
  const [adUnits, setAdUnits] = React.useState<AdUnit[]>([]);
  const [loading, setLoading] = React.useState(true);
  const [error, setError] = React.useState<Error | null>(null);

  useEffect(() => {
    const fetchAdUnits = async () => {
      try {
        const url = gameId
          ? `/api/ads/units?game=${gameId}`
          : '/api/ads/units';

        const response = await fetch(url);
        const data = await response.json();

        if (data.success) {
          setAdUnits(data.data);
        } else {
          throw new Error(data.message || 'Failed to fetch ad units');
        }
      } catch (err) {
        setError(err as Error);
      } finally {
        setLoading(false);
      }
    };

    fetchAdUnits();
  }, [gameId]);

  return { adUnits, loading, error };
}

interface AdUnit {
  id: number;
  name: string;
  slot_id: string;
  format: AdFormat;
  position: string;
  ad_client: string;
  style: string;
  dimensions: { width: string | number; height: string | number };
  is_responsive: boolean;
}

/**
 * Component that renders an ad unit by position
 */
interface AdByPositionProps {
  position: string;
  gameId?: string | number;
  fallback?: React.ReactNode;
  className?: string;
  style?: React.CSSProperties;
}

export function AdByPosition({
  position,
  gameId,
  fallback = null,
  className,
  style,
}: AdByPositionProps) {
  const { adUnits, loading, error } = useAdUnits(gameId);

  if (loading) {
    return <div className="ad-loading">Loading ad...</div>;
  }

  if (error) {
    console.error('Ad error:', error);
    return <>{fallback}</>;
  }

  const adUnit = adUnits.find((unit) => unit.position === position);

  if (!adUnit) {
    return <>{fallback}</>;
  }

  return (
    <AdBanner
      publisherId={adUnit.ad_client}
      slotId={adUnit.slot_id}
      format={adUnit.format}
      className={className}
      style={style}
    />
  );
}

export default AdBanner;
