<?php

namespace Pharaonic\Rss\Extensions\Media;

use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Support\Xml;
use XMLWriter;

/**
 * <media:thumbnail> element: an image representing a media object or an item.
 */
final class Thumbnail extends MediaExtension
{
    private string $url;

    private ?int $width = null;

    private ?int $height = null;

    private ?string $time = null;

    /**
     * @throws InvalidElementException
     */
    public function __construct(string $url)
    {
        if (trim($url) === '') {
            throw InvalidElementException::emptyValue('media:thumbnail', 'url');
        }

        $this->url = $url;
    }

    /**
     * @throws InvalidElementException
     */
    public static function make(string $url): self
    {
        return new self($url);
    }

    /**
     * @throws InvalidElementException
     */
    public function width(?int $width): self
    {
        self::assertPositive('media:thumbnail', 'width', $width);

        $this->width = $width;

        return $this;
    }

    /**
     * @throws InvalidElementException
     */
    public function height(?int $height): self
    {
        self::assertPositive('media:thumbnail', 'height', $height);

        $this->height = $height;

        return $this;
    }

    /**
     * Time offset of the thumbnail in the media object, in NTP format, e.g. "12:05:01.123".
     */
    public function time(?string $time): self
    {
        $this->time = $time === '' ? null : $time;

        return $this;
    }

    public function write(XMLWriter $writer): void
    {
        $writer->startElement($this->prefix() . ':thumbnail');
        Xml::writeAttribute($writer, 'url', $this->url);

        if ($this->width !== null) {
            Xml::writeAttribute($writer, 'width', (string) $this->width);
        }

        if ($this->height !== null) {
            Xml::writeAttribute($writer, 'height', (string) $this->height);
        }

        if ($this->time !== null) {
            Xml::writeAttribute($writer, 'time', $this->time);
        }

        $writer->endElement();
    }
}
