<?php

namespace Pharaonic\RSS\Extensions\Media;

use Pharaonic\RSS\Exceptions\InvalidElementException;
use Pharaonic\RSS\Support\Xml;
use XMLWriter;

/**
 * <media:content> element: a media object attached to an item.
 *
 * A title, a description, and thumbnails can be nested to describe the
 * media object itself rather than the item.
 */
final class Content extends MediaExtension
{
    public const MEDIUM_IMAGE = 'image';
    public const MEDIUM_AUDIO = 'audio';
    public const MEDIUM_VIDEO = 'video';
    public const MEDIUM_DOCUMENT = 'document';
    public const MEDIUM_EXECUTABLE = 'executable';

    public const EXPRESSION_SAMPLE = 'sample';
    public const EXPRESSION_FULL = 'full';
    public const EXPRESSION_NONSTOP = 'nonstop';

    private const MEDIUMS = [
        self::MEDIUM_IMAGE,
        self::MEDIUM_AUDIO,
        self::MEDIUM_VIDEO,
        self::MEDIUM_DOCUMENT,
        self::MEDIUM_EXECUTABLE,
    ];

    private const EXPRESSIONS = [
        self::EXPRESSION_SAMPLE,
        self::EXPRESSION_FULL,
        self::EXPRESSION_NONSTOP,
    ];

    private string $url;

    private ?int $fileSize = null;

    private ?string $type = null;

    private ?string $medium = null;

    private ?bool $isDefault = null;

    private ?string $expression = null;

    private ?int $bitrate = null;

    private ?int $duration = null;

    private ?int $width = null;

    private ?int $height = null;

    private ?string $lang = null;

    private ?Title $title = null;

    private ?Description $description = null;

    /** @var list<Thumbnail> */
    private array $thumbnails = [];

    /**
     * @throws InvalidElementException
     */
    public function __construct(string $url)
    {
        if (trim($url) === '') {
            throw InvalidElementException::emptyValue('media:content', 'url');
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
     * Size of the media object in bytes.
     *
     * @throws InvalidElementException
     */
    public function fileSize(?int $bytes): self
    {
        self::assertNotNegative('media:content', 'fileSize', $bytes);

        $this->fileSize = $bytes;

        return $this;
    }

    /**
     * MIME type of the media object, e.g. "video/mp4".
     */
    public function type(?string $type): self
    {
        $this->type = $type === '' ? null : $type;

        return $this;
    }

    /**
     * Kind of media object: one of the MEDIUM_* constants.
     *
     * @throws InvalidElementException
     */
    public function medium(?string $medium): self
    {
        if ($medium !== null && !in_array($medium, self::MEDIUMS, true)) {
            throw InvalidElementException::invalidChoice('media:content', 'medium', $medium, self::MEDIUMS);
        }

        $this->medium = $medium;

        return $this;
    }

    /**
     * Whether this is the default object when several are grouped together.
     */
    public function isDefault(?bool $isDefault = true): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Whether the object is a sample or the full version: one of the EXPRESSION_* constants.
     *
     * @throws InvalidElementException
     */
    public function expression(?string $expression): self
    {
        if ($expression !== null && !in_array($expression, self::EXPRESSIONS, true)) {
            throw InvalidElementException::invalidChoice('media:content', 'expression', $expression, self::EXPRESSIONS);
        }

        $this->expression = $expression;

        return $this;
    }

    /**
     * Kilobits per second rate of the media object.
     *
     * @throws InvalidElementException
     */
    public function bitrate(?int $kilobitsPerSecond): self
    {
        self::assertNotNegative('media:content', 'bitrate', $kilobitsPerSecond);

        $this->bitrate = $kilobitsPerSecond;

        return $this;
    }

    /**
     * Duration of the media object in seconds.
     *
     * @throws InvalidElementException
     */
    public function duration(?int $seconds): self
    {
        self::assertNotNegative('media:content', 'duration', $seconds);

        $this->duration = $seconds;

        return $this;
    }

    /**
     * @throws InvalidElementException
     */
    public function width(?int $width): self
    {
        self::assertPositive('media:content', 'width', $width);

        $this->width = $width;

        return $this;
    }

    /**
     * @throws InvalidElementException
     */
    public function height(?int $height): self
    {
        self::assertPositive('media:content', 'height', $height);

        $this->height = $height;

        return $this;
    }

    /**
     * Primary language of the media object, e.g. "en" or "ar".
     */
    public function lang(?string $lang): self
    {
        $this->lang = $lang === '' ? null : $lang;

        return $this;
    }

    public function title(?Title $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function description(?Description $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function thumbnail(Thumbnail $thumbnail): self
    {
        $this->thumbnails[] = $thumbnail;

        return $this;
    }

    public function write(XMLWriter $writer): void
    {
        $writer->startElement($this->prefix() . ':content');
        Xml::writeAttribute($writer, 'url', $this->url);

        $attributes = [
            'fileSize' => $this->fileSize === null ? null : (string) $this->fileSize,
            'type' => $this->type,
            'medium' => $this->medium,
            'isDefault' => $this->isDefault === null ? null : ($this->isDefault ? 'true' : 'false'),
            'expression' => $this->expression,
            'bitrate' => $this->bitrate === null ? null : (string) $this->bitrate,
            'duration' => $this->duration === null ? null : (string) $this->duration,
            'width' => $this->width === null ? null : (string) $this->width,
            'height' => $this->height === null ? null : (string) $this->height,
            'lang' => $this->lang,
        ];

        foreach ($attributes as $name => $value) {
            if ($value !== null) {
                Xml::writeAttribute($writer, $name, $value);
            }
        }

        if ($this->title !== null) {
            $this->title->write($writer);
        }

        if ($this->description !== null) {
            $this->description->write($writer);
        }

        foreach ($this->thumbnails as $thumbnail) {
            $thumbnail->write($writer);
        }

        $writer->endElement();
    }
}
