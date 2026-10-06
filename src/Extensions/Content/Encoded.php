<?php

namespace Pharaonic\RSS\Extensions\Content;

use Pharaonic\RSS\Exceptions\InvalidElementException;
use Pharaonic\RSS\Support\Xml;
use XMLWriter;

/**
 * <content:encoded> element carrying the full HTML body of an item.
 *
 * The HTML is written as CDATA without modification. A "]]>" sequence in
 * the content is split across CDATA sections, so the document stays valid.
 */
final class Encoded extends ContentExtension
{
    private string $content;

    /**
     * @throws InvalidElementException
     */
    public function __construct(string $content)
    {
        if (trim($content) === '') {
            throw InvalidElementException::emptyValue('content:encoded', 'content');
        }

        $this->content = $content;
    }

    /**
     * @throws InvalidElementException
     */
    public static function make(string $content): self
    {
        return new self($content);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function write(XMLWriter $writer): void
    {
        $writer->startElement($this->prefix() . ':encoded');
        Xml::writeCdata($writer, $this->content);
        $writer->endElement();
    }
}
