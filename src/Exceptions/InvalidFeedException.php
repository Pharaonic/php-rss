<?php

namespace Pharaonic\Rss\Exceptions;

/**
 * Raised when a feed (RSS channel) is incomplete or receives an invalid value.
 */
class InvalidFeedException extends RssException
{
    public static function missingTitle(): self
    {
        return new self('The RSS channel requires a non-empty title. Call Feed::title() before serializing.');
    }

    public static function missingLink(): self
    {
        return new self('The RSS channel requires a non-empty link. Call Feed::link() before serializing.');
    }

    public static function missingDescription(): self
    {
        return new self(
            'The RSS channel requires a non-empty description. Call Feed::description() before serializing.'
        );
    }

    public static function invalidTtl(int $ttl): self
    {
        return new self(sprintf('The RSS channel ttl must be zero or a positive number of minutes, %d given.', $ttl));
    }

    public static function invalidSkipHour(int $hour): self
    {
        return new self(sprintf('An RSS skip hour must be between 0 and 23, %d given.', $hour));
    }

    /**
     * @param list<string> $allowed
     */
    public static function invalidSkipDay(string $day, array $allowed): self
    {
        return new self(sprintf(
            'An RSS skip day must be one of [%s], "%s" given.',
            implode(', ', $allowed),
            $day
        ));
    }
}
