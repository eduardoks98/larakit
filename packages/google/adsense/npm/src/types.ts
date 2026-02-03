export interface AdSenseConfig {
  publisherId: string;
  testMode?: boolean;
}

export type AdFormat =
  | 'auto'
  | 'rectangle'
  | 'vertical'
  | 'horizontal'
  | 'fluid';

export type AdLayout =
  | 'in-article'
  | 'in-feed'
  | 'display';

export interface AdUnitProps {
  slotId: string;
  format?: AdFormat;
  layout?: AdLayout;
  responsive?: boolean;
  style?: React.CSSProperties;
  className?: string;
}

export interface AdSenseContextValue {
  publisherId: string;
  isLoaded: boolean;
  testMode: boolean;
}
