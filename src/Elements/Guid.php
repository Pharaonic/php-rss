<?php

namespace Pharaonic\RSS\Elements;

use Pharaonic\RSS\Exceptions\InvalidElementException;

/**
 * RSS item <guid> element.
 *
 * Following RSS 2.0, a GUID is a permalink unless stated otherwise. Call
 * permalink(false) for identifiers that are not URLs.
 */
final class Guid
{
    private string $value;

    private bool $isPermaLink = true;

    /**
     * @throws InvalidElementException
     */
    public function __construct(string $value)
    {
        if (trim($value) === '') {
            throw InvalidElementException::emptyValue('guid', 'value');
        }

        $this->value = $value;
    }

    /**
     * @throws InvalidElementException
     */
    public static function make(string $value): self
    {
        return new self($value);
    }

    /**
     * Whether the GUID is a URL pointing to the full item.
     */
    public function permalink(bool $isPermaLink = true): self
    {
        $this->isPermaLink = $isPermaLink;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isPermaLink(): bool
    {
        return $this->isPermaLink;
    }
}
