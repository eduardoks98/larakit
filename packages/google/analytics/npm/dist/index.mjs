// src/analytics.ts
var GoogleAnalytics = class {
  constructor(config) {
    this.initialized = false;
    this.measurementId = config.measurementId;
    this.config = config;
  }
  /**
   * Initialize Google Analytics
   */
  initialize() {
    if (this.initialized || typeof window === "undefined") return;
    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag(...args) {
      window.dataLayer.push(args);
    };
    window.gtag("js", /* @__PURE__ */ new Date());
    const configParams = {};
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
    window.gtag("config", this.measurementId, configParams);
    const script = document.createElement("script");
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${this.measurementId}`;
    document.head.appendChild(script);
    this.initialized = true;
  }
  /**
   * Check if gtag is available
   */
  isAvailable() {
    return typeof window !== "undefined" && typeof window.gtag === "function";
  }
  /**
   * Track a custom event
   */
  event(eventName, params = {}) {
    if (!this.isAvailable()) return;
    window.gtag("event", eventName, params);
  }
  /**
   * Track page view
   */
  pageView(pagePath, pageTitle) {
    if (!this.isAvailable()) return;
    window.gtag("event", "page_view", {
      page_path: pagePath || window.location.pathname,
      page_title: pageTitle || document.title,
      page_location: window.location.href
    });
  }
  /**
   * Set user ID for cross-device tracking
   */
  setUserId(userId) {
    if (!this.isAvailable()) return;
    window.gtag("config", this.measurementId, { user_id: userId });
  }
  /**
   * Set user properties
   */
  setUserProperties(properties) {
    if (!this.isAvailable()) return;
    window.gtag("set", "user_properties", properties);
  }
  // ==========================================
  // Authentication Events
  // ==========================================
  trackLogin(method = "email") {
    this.event("login", { method });
  }
  trackSignUp(method = "email") {
    this.event("sign_up", { method });
  }
  // ==========================================
  // E-commerce / Purchase Events
  // ==========================================
  trackPurchase(params) {
    this.event("purchase", {
      transaction_id: params.transaction_id,
      value: params.value,
      currency: params.currency || "BRL",
      items: params.items || []
    });
  }
  // ==========================================
  // Virtual Currency Events
  // ==========================================
  trackEarnVirtualCurrency(params) {
    this.event("earn_virtual_currency", {
      virtual_currency_name: params.virtual_currency_name,
      value: params.value,
      source: params.source
    });
  }
  trackSpendVirtualCurrency(params) {
    this.event("spend_virtual_currency", {
      virtual_currency_name: params.virtual_currency_name,
      value: params.value,
      item_name: params.item_name
    });
  }
  // ==========================================
  // Error Tracking
  // ==========================================
  trackError(description, fatal = false) {
    this.event("exception", { description, fatal });
  }
};
var GameAnalytics = class extends GoogleAnalytics {
  constructor(config, gameName) {
    super(config);
    this.matchStartTime = null;
    this.currentMatchId = null;
    this.gameName = gameName;
  }
  /**
   * Track event with game context
   */
  gameEvent(eventName, params = {}) {
    this.event(eventName, {
      game_name: this.gameName,
      ...params
    });
  }
  // ==========================================
  // Match/Game Events
  // ==========================================
  /**
   * Track match start
   */
  trackMatchStart(params = {}) {
    this.matchStartTime = Date.now();
    this.currentMatchId = `${this.gameName}_${Date.now()}`;
    this.gameEvent("game_start", {
      match_id: this.currentMatchId,
      ...params
    });
    return this.currentMatchId;
  }
  /**
   * Track match end
   */
  trackMatchEnd(params) {
    const duration = this.matchStartTime ? Math.round((Date.now() - this.matchStartTime) / 1e3) : 0;
    this.gameEvent("game_end", {
      match_id: this.currentMatchId,
      duration_seconds: params.duration_seconds ?? duration,
      ...params
    });
    this.matchStartTime = null;
    this.currentMatchId = null;
    return duration;
  }
  /**
   * Track in-game action
   */
  trackAction(actionName, params = {}) {
    this.gameEvent("game_action", {
      action_name: actionName,
      match_id: this.currentMatchId,
      ...params
    });
  }
  /**
   * Get current match ID
   */
  getCurrentMatchId() {
    return this.currentMatchId;
  }
  /**
   * Get match duration so far (in seconds)
   */
  getCurrentMatchDuration() {
    if (!this.matchStartTime) return 0;
    return Math.round((Date.now() - this.matchStartTime) / 1e3);
  }
  // ==========================================
  // Tutorial Events
  // ==========================================
  trackTutorialBegin() {
    this.gameEvent("tutorial_begin");
  }
  trackTutorialComplete() {
    this.gameEvent("tutorial_complete");
  }
  // ==========================================
  // Progression Events
  // ==========================================
  trackLevelUp(level, params = {}) {
    this.gameEvent("level_up", {
      level,
      ...params
    });
  }
  trackUnlockAchievement(achievementId, achievementName) {
    this.gameEvent("unlock_achievement", {
      achievement_id: achievementId,
      achievement_name: achievementName
    });
  }
  trackPostScore(score, level, character) {
    this.gameEvent("post_score", {
      score,
      level,
      character
    });
  }
  // ==========================================
  // Engagement Events
  // ==========================================
  trackShare(method, contentType, itemId) {
    this.gameEvent("share", {
      method,
      content_type: contentType,
      item_id: itemId
    });
  }
  trackJoinGroup(groupId) {
    this.gameEvent("join_group", { group_id: groupId });
  }
  // ==========================================
  // Ad Events
  // ==========================================
  trackAdImpression(adFormat, adUnitId) {
    this.gameEvent("ad_impression", {
      ad_format: adFormat,
      ad_unit_id: adUnitId
    });
  }
  trackAdClick(adFormat, adUnitId) {
    this.gameEvent("ad_click", {
      ad_format: adFormat,
      ad_unit_id: adUnitId
    });
  }
  trackAdReward(adFormat, rewardType, rewardValue) {
    this.gameEvent("ad_reward", {
      ad_format: adFormat,
      reward_type: rewardType,
      reward_value: rewardValue
    });
  }
};

// src/hooks.ts
import { useEffect, useRef, useCallback } from "react";
function useGoogleAnalytics(config) {
  const analyticsRef = useRef(null);
  if (!analyticsRef.current) {
    analyticsRef.current = new GoogleAnalytics(config);
  }
  useEffect(() => {
    analyticsRef.current?.initialize();
  }, []);
  return analyticsRef.current;
}
function useGameAnalytics(config, gameName) {
  const analyticsRef = useRef(null);
  if (!analyticsRef.current) {
    analyticsRef.current = new GameAnalytics(config, gameName);
  }
  useEffect(() => {
    analyticsRef.current?.initialize();
  }, []);
  return analyticsRef.current;
}
function useMatchTracking(analytics) {
  const matchIdRef = useRef(null);
  const startMatch = useCallback(
    (params = {}) => {
      matchIdRef.current = analytics.trackMatchStart(params);
      return matchIdRef.current;
    },
    [analytics]
  );
  const endMatch = useCallback(
    (params) => {
      const duration = analytics.trackMatchEnd(params);
      matchIdRef.current = null;
      return duration;
    },
    [analytics]
  );
  const trackAction = useCallback(
    (actionName, params = {}) => {
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
  useEffect(() => {
    return () => {
      if (matchIdRef.current) {
        analytics.trackMatchEnd({ result: "abandon" });
      }
    };
  }, [analytics]);
  return {
    startMatch,
    endMatch,
    trackAction,
    isInMatch,
    getMatchDuration,
    matchId: matchIdRef.current
  };
}
function usePageViewTracking(analytics, pagePath, pageTitle) {
  useEffect(() => {
    analytics.pageView(pagePath, pageTitle);
  }, [analytics, pagePath, pageTitle]);
}

// src/context.tsx
import { createContext, useContext, useEffect as useEffect2, useMemo } from "react";
import { jsx } from "react/jsx-runtime";
var GoogleAnalyticsContext = createContext(null);
function GoogleAnalyticsProvider({
  config,
  children
}) {
  const analytics = useMemo(() => new GoogleAnalytics(config), [config]);
  useEffect2(() => {
    analytics.initialize();
  }, [analytics]);
  return /* @__PURE__ */ jsx(GoogleAnalyticsContext.Provider, { value: analytics, children });
}
function useAnalytics() {
  const context = useContext(GoogleAnalyticsContext);
  if (!context) {
    throw new Error("useAnalytics must be used within a GoogleAnalyticsProvider");
  }
  return context;
}
var GameAnalyticsContext = createContext(null);
function GameAnalyticsProvider({
  config,
  gameName,
  children
}) {
  const analytics = useMemo(
    () => new GameAnalytics(config, gameName),
    [config, gameName]
  );
  useEffect2(() => {
    analytics.initialize();
  }, [analytics]);
  return /* @__PURE__ */ jsx(GameAnalyticsContext.Provider, { value: analytics, children });
}
function useGameAnalyticsContext() {
  const context = useContext(GameAnalyticsContext);
  if (!context) {
    throw new Error(
      "useGameAnalyticsContext must be used within a GameAnalyticsProvider"
    );
  }
  return context;
}
export {
  GameAnalytics,
  GameAnalyticsProvider,
  GoogleAnalytics,
  GoogleAnalyticsProvider,
  useAnalytics,
  useGameAnalytics,
  useGameAnalyticsContext,
  useGoogleAnalytics,
  useMatchTracking,
  usePageViewTracking
};
