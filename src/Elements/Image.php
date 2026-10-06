<?php

namespace Pharaonic\Rss\Elements;

use Pharaonic\Rss\Exceptions\InvalidElementException;

/**
 * RSS channel <image> element.
 *
 * RSS 2.0 requires the image title and link. When they are not set, the
 * writer uses the channel title and link, as the specification recommends.
 */
final class Image
{
    public const MAX_WIDTH = 144;
    public const MAX_HEIGHT = 400;

    private string $url;

    private ?string $title = null;

    private ?string $link = null;

    private ?int $width = null;

    private ?int $height = null;

    private ?string $description = null;

    /**
     * @throws InvalidElementException
     */
    public function __construct(string $url)
    {
        if (trim($url) === '') {
            throw InvalidElementException::emptyValue('image', 'url');
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
     * Alternative text of the image. Null or an empty string falls back to the channel title.
     */
    public function title(?string $title): self
    {
        $this->title = $title === '' ? null : $title;

        return $this;
    }

    /**
     * URL the image links to. Null or an empty string falls back to the channel link.
     */
    public function link(?string $link): self
    {
        $this->link = $link === '' ? null : $link;

        return $this;
    }

    /**
     * Width in pixels, at most 144. Null removes it (readers then assume 88).
     *
     * @throws InvalidElementException
     */
    public function width(?int $width): self
    {
        if ($width !== null && ($width < 1 || $width > self::MAX_WIDTH)) {
            throw InvalidElementException::outOfRange('image', 'width', $width, 1, self::MAX_WIDTH);
        }

        $this->width = $width;

        return $this;
    }

    /**
     * Height in pixels, at most 400. Null removes it (readers then assume 31).
     *
     * @throws InvalidElementException
     */
    public function height(?int $height): self
    {
        if ($height !== null && ($height < 1 || $height > self::MAX_HEIGHT)) {
            throw InvalidElementException::outOfRange('image', 'height', $height, 1, self::MAX_HEIGHT);
        }

        $this->height = $height;

        return $this;
    }

    /**
     * Text for the title attribute of the link formed around the image.
     */
    public function description(?string $description): self
    {
        $this->description = $description === '' ? null : $description;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
