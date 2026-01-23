<?php

namespace Eduardoks98\Helpers\Formatters;

use DateTime;

class DateFormatter
{
    /**
     * Converte data para formato brasileiro (dd/mm/yyyy).
     *
     * @param string|DateTime $date
     * @return string
     */
    public static function toBrazilian($date): string
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        }

        return $date->format('d/m/Y');
    }

    /**
     * Converte data para formato americano (yyyy-mm-dd).
     *
     * @param string|DateTime $date
     * @return string
     */
    public static function toAmerican($date): string
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        }

        return $date->format('Y-m-d');
    }

    /**
     * Converte data de um formato para outro.
     *
     * @param string $date
     * @param string $fromFormat
     * @param string $toFormat
     * @return string
     */
    public static function formatDateTo(string $date, string $fromFormat, string $toFormat): string
    {
        $dateTime = DateTime::createFromFormat($fromFormat, $date);

        if (!$dateTime) {
            throw new \InvalidArgumentException("Invalid date format: {$date}");
        }

        return $dateTime->format($toFormat);
    }

    /**
     * Formata data e hora para formato brasileiro.
     *
     * @param string|DateTime $datetime
     * @param bool $includeSeconds
     * @return string
     */
    public static function toBrazilianDateTime($datetime, bool $includeSeconds = false): string
    {
        if (is_string($datetime)) {
            $datetime = new DateTime($datetime);
        }

        $format = $includeSeconds ? 'd/m/Y H:i:s' : 'd/m/Y H:i';
        return $datetime->format($format);
    }
}
