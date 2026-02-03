/**
 * Game Analytics Helper for Google Analytics 4
 *
 * Usage:
 * const analytics = new GameAnalytics('BangShot');
 * analytics.trackMatchStart({ mode: 'ranked', players: 2 });
 * analytics.trackMatchEnd({ result: 'win', duration: 120 });
 */

class GameAnalytics {
    constructor(gameName) {
        this.gameName = gameName;
        this.matchStartTime = null;
        this.currentMatchId = null;
    }

    /**
     * Check if gtag is available
     */
    isAvailable() {
        return typeof gtag === 'function';
    }

    /**
     * Track custom event
     */
    track(eventName, params = {}) {
        if (!this.isAvailable()) {
            console.warn('Google Analytics not available');
            return;
        }

        gtag('event', eventName, {
            game_name: this.gameName,
            ...params
        });
    }

    /**
     * Track match/game start
     */
    trackMatchStart(params = {}) {
        this.matchStartTime = Date.now();
        this.currentMatchId = `${this.gameName}_${Date.now()}`;

        this.track('game_start', {
            match_id: this.currentMatchId,
            ...params
        });

        return this.currentMatchId;
    }

    /**
     * Track match/game end
     */
    trackMatchEnd(params = {}) {
        const duration = this.matchStartTime
            ? Math.round((Date.now() - this.matchStartTime) / 1000)
            : 0;

        this.track('game_end', {
            match_id: this.currentMatchId,
            duration_seconds: duration,
            ...params
        });

        // Reset
        this.matchStartTime = null;
        this.currentMatchId = null;

        return duration;
    }

    /**
     * Track player action during match
     */
    trackAction(action, params = {}) {
        this.track('game_action', {
            action_name: action,
            match_id: this.currentMatchId,
            ...params
        });
    }

    /**
     * Track player login
     */
    trackLogin(method = 'email') {
        this.track('login', { method });
    }

    /**
     * Track new player signup
     */
    trackSignUp(method = 'email') {
        this.track('sign_up', { method });
    }

    /**
     * Track tutorial events
     */
    trackTutorialBegin() {
        this.track('tutorial_begin');
    }

    trackTutorialComplete() {
        this.track('tutorial_complete');
    }

    /**
     * Track level up
     */
    trackLevelUp(level, params = {}) {
        this.track('level_up', {
            level,
            ...params
        });
    }

    /**
     * Track achievement unlock
     */
    trackAchievement(achievementId, achievementName) {
        this.track('unlock_achievement', {
            achievement_id: achievementId,
            achievement_name: achievementName
        });
    }

    /**
     * Track in-game purchase
     */
    trackPurchase(transactionId, value, currency = 'BRL', items = []) {
        this.track('purchase', {
            transaction_id: transactionId,
            value,
            currency,
            items
        });
    }

    /**
     * Track virtual currency earned
     */
    trackEarnCurrency(currencyName, value, source) {
        this.track('earn_virtual_currency', {
            virtual_currency_name: currencyName,
            value,
            source
        });
    }

    /**
     * Track virtual currency spent
     */
    trackSpendCurrency(currencyName, value, itemName) {
        this.track('spend_virtual_currency', {
            virtual_currency_name: currencyName,
            value,
            item_name: itemName
        });
    }

    /**
     * Track ad impression
     */
    trackAdImpression(adFormat, adUnitId) {
        this.track('ad_impression', {
            ad_format: adFormat,
            ad_unit_id: adUnitId
        });
    }

    /**
     * Track error/exception
     */
    trackError(description, fatal = false) {
        if (!this.isAvailable()) return;

        gtag('event', 'exception', {
            description,
            fatal,
            game_name: this.gameName
        });
    }
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GameAnalytics;
}

// Also attach to window for direct script usage
if (typeof window !== 'undefined') {
    window.GameAnalytics = GameAnalytics;
}
