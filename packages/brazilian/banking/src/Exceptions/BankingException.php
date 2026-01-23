<?php

namespace Eduardoks98\Banking\Exceptions;

use Exception;

/**
 * Banking Exception
 */
class BankingException extends Exception
{
    /**
     * The operation that failed
     */
    protected ?string $operation = null;

    /**
     * Additional context
     */
    protected array $context = [];

    /**
     * Create a new exception instance
     */
    public function __construct(
        string $message = 'Banking operation failed',
        int $code = 0,
        ?Exception $previous = null,
        ?string $operation = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->operation = $operation;
        $this->context = $context;
    }

    /**
     * Get the operation that failed
     */
    public function getOperation(): ?string
    {
        return $this->operation;
    }

    /**
     * Get additional context
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Create exception for invalid PIX key
     */
    public static function invalidPixKey(string $key, string $reason): self
    {
        return new self(
            "Invalid PIX key: {$reason}",
            422,
            null,
            'pix_validation',
            ['key' => $key, 'reason' => $reason]
        );
    }

    /**
     * Create exception for invalid boleto
     */
    public static function invalidBoleto(string $code, string $reason): self
    {
        return new self(
            "Invalid boleto: {$reason}",
            422,
            null,
            'boleto_validation',
            ['code' => $code, 'reason' => $reason]
        );
    }

    /**
     * Create exception for bank not found
     */
    public static function bankNotFound(string $code): self
    {
        return new self(
            "Bank not found: {$code}",
            404,
            null,
            'bank_lookup',
            ['code' => $code]
        );
    }

    /**
     * Create exception for API error
     */
    public static function apiError(string $message, ?string $endpoint = null): self
    {
        return new self(
            "API error: {$message}",
            500,
            null,
            'api_request',
            ['endpoint' => $endpoint]
        );
    }

    /**
     * Create exception for PIX generation error
     */
    public static function pixGenerationError(string $reason): self
    {
        return new self(
            "PIX generation failed: {$reason}",
            500,
            null,
            'pix_generation',
            ['reason' => $reason]
        );
    }

    /**
     * Convert exception to array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'operation' => $this->operation,
            'context' => $this->context,
            'code' => $this->getCode(),
        ];
    }
}
