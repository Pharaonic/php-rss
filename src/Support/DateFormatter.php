<?php

namespace Pharaonic\RSS\Support;

use DateTimeInterface;

/**
 * Formats dates as RFC 822 date-times, as required by RSS 2.0.
 *
 * The timezone offset carried by the given date is preserved, and the
 * output never depends on the server timezone or locale.
 */
final class DateFormatter
{
    private function __construct()
    {
    }

    /**
     * Format a date, e.g. "Tue, 06 Oct 2026 10:00:00 +0300".
     */
    public static function format(DateTimeInterface $date): string
    {
        return $date->format(DateTimeInterface::RSS);
    }
}
