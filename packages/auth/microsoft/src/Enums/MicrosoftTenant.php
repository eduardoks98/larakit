<?php

namespace Eduardoks98\MicrosoftAuth\Enums;

enum MicrosoftTenant: string
{
    /**
     * Multi-tenant and personal Microsoft accounts
     */
    case COMMON = 'common';

    /**
     * Multi-tenant Azure AD accounts only
     */
    case ORGANIZATIONS = 'organizations';

    /**
     * Personal Microsoft accounts only
     */
    case CONSUMERS = 'consumers';

    /**
     * Get tenant description
     */
    public function description(): string
    {
        return match($this) {
            self::COMMON => 'Multi-tenant and personal Microsoft accounts',
            self::ORGANIZATIONS => 'Multi-tenant Azure AD accounts only',
            self::CONSUMERS => 'Personal Microsoft accounts only',
        };
    }

    /**
     * Get authorization URL pattern
     */
    public function authUrl(): string
    {
        return "https://login.microsoftonline.com/{$this->value}/oauth2/v2.0/authorize";
    }

    /**
     * Get token URL pattern
     */
    public function tokenUrl(): string
    {
        return "https://login.microsoftonline.com/{$this->value}/oauth2/v2.0/token";
    }
}
