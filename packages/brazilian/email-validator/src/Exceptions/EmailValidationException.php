<?php

namespace Eduardoks98\EmailValidator\Exceptions;

use Exception;

/**
 * Email Validation Exception
 */
class EmailValidationException extends Exception
{
    /**
     * The email that failed validation
     */
    protected ?string $email = null;

    /**
     * The validation check that failed
     */
    protected ?string $failedCheck = null;

    /**
     * Create a new exception instance
     */
    public function __construct(
        string $message = 'Email validation failed',
        int $code = 0,
        ?Exception $previous = null,
        ?string $email = null,
        ?string $failedCheck = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->email = $email;
        $this->failedCheck = $failedCheck;
    }

    /**
     * Get the email that failed validation
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Get the validation check that failed
     */
    public function getFailedCheck(): ?string
    {
        return $this->failedCheck;
    }

    /**
     * Create exception for invalid syntax
     */
    public static function invalidSyntax(string $email): self
    {
        return new self(
            "Invalid email syntax: {$email}",
            422,
            null,
            $email,
            'syntax'
        );
    }

    /**
     * Create exception for DNS failure
     */
    public static function dnsFailure(string $email, string $domain): self
    {
        return new self(
            "DNS lookup failed for domain: {$domain}",
            422,
            null,
            $email,
            'dns'
        );
    }

    /**
     * Create exception for MX records failure
     */
    public static function noMxRecords(string $email, string $domain): self
    {
        return new self(
            "No MX records found for domain: {$domain}",
            422,
            null,
            $email,
            'mx'
        );
    }

    /**
     * Create exception for disposable email
     */
    public static function disposableEmail(string $email, string $domain): self
    {
        return new self(
            "Disposable email detected: {$domain}",
            422,
            null,
            $email,
            'disposable'
        );
    }

    /**
     * Create exception for role-based email
     */
    public static function roleBasedEmail(string $email, string $localPart): self
    {
        return new self(
            "Role-based email detected: {$localPart}",
            422,
            null,
            $email,
            'role'
        );
    }

    /**
     * Create exception for SMTP verification failure
     */
    public static function smtpVerificationFailed(string $email, string $reason): self
    {
        return new self(
            "SMTP verification failed: {$reason}",
            422,
            null,
            $email,
            'smtp'
        );
    }

    /**
     * Create exception for low quality score
     */
    public static function lowQualityScore(string $email, int $score): self
    {
        return new self(
            "Email quality score too low: {$score}",
            422,
            null,
            $email,
            'quality'
        );
    }

    /**
     * Convert exception to array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'email' => $this->email,
            'failed_check' => $this->failedCheck,
            'code' => $this->getCode(),
        ];
    }
}
