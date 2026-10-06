<?php

namespace Pharaonic\RSS\Elements;

use Pharaonic\RSS\Exceptions\InvalidElementException;

/**
 * RSS item <source> element: the channel the item came from.
 */
final class Source
{
    private string $title;

    private string $url;

    /**
     * @param string $title Name of the originating channel.
     * @param string $url   URL of the originating channel's RSS feed.
     *
     * @throws InvalidElementException
     */
    public function __construct(string $title, string $url)
    {
        if (trim($title) === '') {
            throw InvalidElementException::emptyValue('source', 'title');
        }

        if (trim($url) === '') {
            throw InvalidElementException::emptyValue('source', 'url');
        }

        $this->title = $title;
        $this->url = $url;
    }

    /**
     * @throws InvalidElementException
     */
    public static function make(string $title, string $url): self
    {
        return new self($title, $url);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
