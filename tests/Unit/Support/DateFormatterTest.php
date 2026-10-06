<?php

namespace Pharaonic\RSS\Tests\Unit\Support;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Pharaonic\RSS\Support\DateFormatter;
use Pharaonic\RSS\Tests\TestCase;

final class DateFormatterTest extends TestCase
{
    public function testFormatsAsRfc822(): void
    {
        $date = new DateTimeImmutable('2026-10-06 10:00:00', new DateTimeZone('+03:00'));

        $this->assertSame('Tue, 06 Oct 2026 10:00:00 +0300', DateFormatter::format($date));
    }

    public function testPreservesTheDateTimezoneInsteadOfTheServerTimezone(): void
    {
        $previous = date_default_timezone_get();
        date_default_timezone_set('Pacific/Auckland');

        try {
            $utc = new DateTimeImmutable('2026-01-15 08:30:15', new DateTimeZone('UTC'));
            $cairo = new DateTimeImmutable('2026-01-15 08:30:15', new DateTimeZone('Africa/Cairo'));
            $negative = new DateTimeImmutable('2026-01-15 08:30:15', new DateTimeZone('-05:30'));

            $this->assertSame('Thu, 15 Jan 2026 08:30:15 +0000', DateFormatter::format($utc));
            $this->assertSame('Thu, 15 Jan 2026 08:30:15 +0200', DateFormatter::format($cairo));
            $this->assertSame('Thu, 15 Jan 2026 08:30:15 -0530', DateFormatter::format($negative));
        } finally {
            date_default_timezone_set($previous);
        }
    }

    public function testDoesNotMutateMutableDates(): void
    {
        $date = new DateTime('2026-10-06 10:00:00', new DateTimeZone('UTC'));
        $before = $date->format(DATE_ATOM);

        $this->assertSame('Tue, 06 Oct 2026 10:00:00 +0000', DateFormatter::format($date));
        $this->assertSame($before, $date->format(DATE_ATOM));
    }

    public function testOutputIsIndependentOfLocale(): void
    {
        $previous = setlocale(LC_TIME, '0');
        setlocale(LC_TIME, 'ar_EG.UTF-8', 'fr_FR.UTF-8', 'de_DE.UTF-8');

        try {
            $date = new DateTimeImmutable('2026-03-01 00:00:00', new DateTimeZone('UTC'));

            $this->assertSame('Sun, 01 Mar 2026 00:00:00 +0000', DateFormatter::format($date));
        } finally {
            setlocale(LC_TIME, $previous === false ? 'C' : $previous);
        }
    }
}
