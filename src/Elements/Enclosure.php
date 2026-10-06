<?php

namespace Pharaonic\RSS\Elements;

use Pharaonic\RSS\Exceptions\InvalidElementException;

/**
 * RSS item <enclosure> element: a media object attached to the item.
 */
final class Enclosure
{
    private string $url;

    private int $length;

    private string $type;

    /**
     * @param string $url    Location of the media file.
     * @param int    $length Size of the media file in bytes.
     * @param string $type   MIME type of the media file.
     *
     * @throws InvalidElementException
     */
    public function __construct(string $url, int $length, string $type)
    {
        if (trim($url) === '') {
            throw InvalidElementException::emptyValue('enclosure', 'url');
        }

        if ($length < 0) {
            throw InvalidElementException::invalidEnclosureLength($length);
        }

        if (trim($type) === '') {
            throw InvalidElementException::emptyValue('enclosure', 'type');
        }

        $this->url = $url;
        $this->length = $length;
        $this->type = $type;
    }

    /**
     * @throws InvalidElementException
     */
    public static function make(string $url, int $length, string $type): self
    {
        return new self($url, $length, $type);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
