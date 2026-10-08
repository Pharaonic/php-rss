<?php

namespace Pharaonic\RSS\Extensions\Atom;

use Pharaonic\RSS\Exceptions\InvalidElementException;
use Pharaonic\RSS\Support\Xml;
use XMLWriter;

/**
 * Atom <atom:link> element, most commonly the feed's self reference:
 *
 *     <atom:link href="https://example.com/rss.xml" rel="self" type="application/rss+xml"/>
 */
final class Link extends AtomExtension
{
    private string $href;

    private ?string $rel = null;

    private ?string $type = null;

    private ?string $hreflang = null;

    private ?string $title = null;

    private ?int $length = null;

    /**
     * @throws InvalidElementException
     */
    public function __construct(string $href)
    {
        if (trim($href) === '') {
            throw InvalidElementException::emptyValue('atom:link', 'href');
        }

        $this->href = $href;
    }

    /**
     * @throws InvalidElementException
     */
    public static function make(string $href): self
    {
        return new self($href);
    }

    /**
     * The link to the feed itself, with rel="self" and type="application/rss+xml".
     *
     * @throws InvalidElementException
     */
    public static function self(string $href): self
    {
        return (new self($href))->rel('self')->type('application/rss+xml');
    }

    /**
     * Link relation, e.g. "self", "alternate", or "hub".
     */
    public function rel(?string $rel): self
    {
        $this->rel = $rel === '' ? null : $rel;

        return $this;
    }

    /**
     * MIME type of the linked resource.
     */
    public function type(?string $type): self
    {
        $this->type = $type === '' ? null : $type;

        return $this;
    }

    /**
     * Language of the linked resource.
     */
    public function hreflang(?string $hreflang): self
    {
        $this->hreflang = $hreflang === '' ? null : $hreflang;

        return $this;
    }

    public function title(?string $title): self
    {
        $this->title = $title === '' ? null : $title;

        return $this;
    }

    /**
     * Size of the linked resource in bytes.
     *
     * @throws InvalidElementException
     */
    public function length(?int $length): self
    {
        if ($length !== null && $length < 0) {
            throw InvalidElementException::outOfRange('atom:link', 'length', $length, 0);
        }

        $this->length = $length;

        return $this;
    }

    public function getHref(): string
    {
        return $this->href;
    }

    public function getRel(): ?string
    {
        return $this->rel;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getHreflang(): ?string
    {
        return $this->hreflang;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function write(XMLWriter $writer): void
    {
        $writer->startElement($this->prefix() . ':link');
        Xml::writeAttribute($writer, 'href', $this->href);

        $attributes = [
            'rel' => $this->rel,
            'type' => $this->type,
            'hreflang' => $this->hreflang,
            'title' => $this->title,
            'length' => $this->length === null ? null : (string) $this->length,
        ];

        foreach ($attributes as $name => $value) {
            if ($value !== null) {
                Xml::writeAttribute($writer, $name, $value);
            }
        }

        $writer->endElement();
    }
}
