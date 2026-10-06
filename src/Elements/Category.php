<?php

namespace Pharaonic\Rss\Elements;

use Pharaonic\Rss\Exceptions\InvalidElementException;

/**
 * RSS <category> element, usable on both the channel and items.
 */
final class Category
{
    private string $value;

    private ?string $domain = null;

    /**
     * @throws InvalidElementException
     */
    public function __construct(string $value)
    {
        if (trim($value) === '') {
            throw InvalidElementException::emptyValue('category', 'value');
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
     * The taxonomy the category belongs to. Null or an empty string removes it.
     */
    public function domain(?string $domain): self
    {
        $this->domain = $domain === '' ? null : $domain;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }
}
