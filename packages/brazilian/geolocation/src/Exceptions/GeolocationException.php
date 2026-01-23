<?php

namespace Eduardoks98\Geolocation\Exceptions;

use Exception;

/**
 * Geolocation Exception
 */
class GeolocationException extends Exception
{
    /**
     * Invalid CEP format.
     */
    public static function invalidCep(string $cep): self
    {
        return new self("Invalid CEP format: '{$cep}'. CEP must be 8 digits.", 422);
    }

    /**
     * CEP not found.
     */
    public static function cepNotFound(string $cep): self
    {
        return new self("CEP not found: '{$cep}'", 404);
    }

    /**
     * Invalid search parameters.
     */
    public static function invalidSearch(string $message): self
    {
        return new self("Invalid search: {$message}", 422);
    }

    /**
     * API error.
     */
    public static function apiError(string $provider, string $message): self
    {
        return new self("{$provider} API error: {$message}", 500);
    }

    /**
     * Missing API key.
     */
    public static function missingApiKey(string $provider): self
    {
        return new self("Missing API key for {$provider}. Please configure it in your environment.", 500);
    }

    /**
     * Invalid coordinates.
     */
    public static function invalidCoordinates(float $lat, float $lng): self
    {
        return new self("Invalid coordinates: lat={$lat}, lng={$lng}. Latitude must be between -90 and 90, longitude between -180 and 180.", 422);
    }

    /**
     * Rate limit exceeded.
     */
    public static function rateLimitExceeded(string $provider): self
    {
        return new self("{$provider} rate limit exceeded. Please try again later.", 429);
    }
}
