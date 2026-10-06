<?php

namespace Pharaonic\Rss\Extensions\DublinCore;

use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Support\Xml;
use XMLWriter;

/**
 * <dc:creator> element: the person or organization that created the resource.
 *
 * Unlike the RSS <author> element, it accepts a plain name.
 */
final class Creator extends DublinCoreExtension
{
    private string $name;

    /**
     * @throws InvalidElementException
     */
    public function __construct(string $name)
    {
        if (trim($name) === '') {
            throw InvalidElementException::emptyValue('dc:creator', 'name');
        }

        $this->name = $name;
    }

    /**
     * @throws InvalidElementException
     */
    public static function make(string $name): self
    {
        return new self($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function write(XMLWriter $writer): void
    {
        Xml::writeElement($writer, $this->prefix() . ':creator', $this->name);
    }
}
