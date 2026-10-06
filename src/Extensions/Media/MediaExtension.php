<?php

namespace Pharaonic\RSS\Extensions\Media;

use Pharaonic\RSS\Contracts\Extension;
use Pharaonic\RSS\Exceptions\InvalidElementException;

/**
 * Base class for elements of the Media RSS namespace.
 */
abstract class MediaExtension implements Extension
{
    public const PREFIX = 'media';
    public const NAMESPACE_URI = 'http://search.yahoo.com/mrss/';

    public function prefix(): string
    {
        return self::PREFIX;
    }

    public function namespaceUri(): string
    {
        return self::NAMESPACE_URI;
    }

    /**
     * @throws InvalidElementException
     */
    protected static function assertNotNegative(string $element, string $field, ?int $value): void
    {
        if ($value !== null && $value < 0) {
            throw InvalidElementException::outOfRange($element, $field, $value, 0);
        }
    }

    /**
     * @throws InvalidElementException
     */
    protected static function assertPositive(string $element, string $field, ?int $value): void
    {
        if ($value !== null && $value < 1) {
            throw InvalidElementException::outOfRange($element, $field, $value, 1);
        }
    }
}
