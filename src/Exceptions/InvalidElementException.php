<?php

namespace Pharaonic\RSS\Exceptions;

/**
 * Raised when an element, an extension, or a namespace receives an invalid value.
 */
class InvalidElementException extends RssException
{
    public static function emptyValue(string $element, string $field): self
    {
        return new self(sprintf('The %s %s must not be empty.', $element, $field));
    }

    public static function invalidEnclosureLength(int $length): self
    {
        return new self(sprintf('The enclosure length must be zero or a positive number of bytes, %d given.', $length));
    }

    public static function outOfRange(string $element, string $field, int $value, int $min, ?int $max = null): self
    {
        $range = $max === null
            ? sprintf('at least %d', $min)
            : sprintf('between %d and %d', $min, $max);

        return new self(sprintf('The %s %s must be %s, %d given.', $element, $field, $range, $value));
    }

    /**
     * @param list<string> $allowed
     */
    public static function invalidChoice(string $element, string $field, string $value, array $allowed): self
    {
        return new self(sprintf(
            'The %s %s must be one of [%s], "%s" given.',
            $element,
            $field,
            implode(', ', $allowed),
            $value
        ));
    }

    public static function invalidNamespace(string $prefix, string $uri, string $reason): self
    {
        return new self(sprintf('Invalid XML namespace "%s" => "%s": %s.', $prefix, $uri, $reason));
    }

    public static function namespaceConflict(string $prefix, string $registeredUri, string $conflictingUri): self
    {
        return new self(sprintf(
            'The XML namespace prefix "%s" is already bound to "%s" and cannot also be bound to "%s".',
            $prefix,
            $registeredUri,
            $conflictingUri
        ));
    }

    public static function invalidXmlCharacters(string $context): self
    {
        return new self(sprintf(
            'The value of %s contains invalid UTF-8 or characters that are not allowed in XML 1.0.',
            $context
        ));
    }
}
