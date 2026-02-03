import React, { useEffect, useRef } from 'react';
import { useAdSense } from './context';
import type { AdUnitProps } from './types';

/**
 * AdSense Ad Unit Component
 */
export function AdUnit({
  slotId,
  format = 'auto',
  layout,
  responsive = true,
  style,
  className,
}: AdUnitProps) {
  const { publisherId, isLoaded, testMode } = useAdSense();
  const adRef = useRef<HTMLModElement>(null);
  const isAdPushed = useRef(false);

  useEffect(() => {
    if (!isLoaded || isAdPushed.current) return;

    // Small delay to ensure DOM is ready
    const timer = setTimeout(() => {
      try {
        if (window.adsbygoogle && adRef.current) {
          window.adsbygoogle.push({});
          isAdPushed.current = true;
        }
      } catch (e) {
        console.error('AdSense error:', e);
      }
    }, 100);

    return () => clearTimeout(timer);
  }, [isLoaded]);

  const adStyle: React.CSSProperties = {
    display: 'block',
    ...style,
  };

  if (responsive) {
    adStyle.width = '100%';
  }

  return (
    <ins
      ref={adRef}
      className={`adsbygoogle ${className || ''}`}
      style={adStyle}
      data-ad-client={publisherId}
      data-ad-slot={slotId}
      data-ad-format={format}
      data-ad-layout={layout}
      data-full-width-responsive={responsive ? 'true' : 'false'}
      data-adtest={testMode ? 'on' : undefined}
    />
  );
}

/**
 * In-Article Ad Unit
 */
export function InArticleAd({ slotId, className, style }: Omit<AdUnitProps, 'format' | 'layout'>) {
  return (
    <AdUnit
      slotId={slotId}
      format="fluid"
      layout="in-article"
      className={className}
      style={{ textAlign: 'center', ...style }}
    />
  );
}

/**
 * Display Ad Unit (Rectangle)
 */
export function DisplayAd({ slotId, className, style }: Omit<AdUnitProps, 'format' | 'layout'>) {
  return (
    <AdUnit
      slotId={slotId}
      format="rectangle"
      className={className}
      style={style}
    />
  );
}

/**
 * Sidebar Ad Unit (Vertical)
 */
export function SidebarAd({ slotId, className, style }: Omit<AdUnitProps, 'format' | 'layout'>) {
  return (
    <AdUnit
      slotId={slotId}
      format="vertical"
      className={className}
      style={style}
    />
  );
}

/**
 * Banner Ad Unit (Horizontal)
 */
export function BannerAd({ slotId, className, style }: Omit<AdUnitProps, 'format' | 'layout'>) {
  return (
    <AdUnit
      slotId={slotId}
      format="horizontal"
      className={className}
      style={style}
    />
  );
}
