<?php

namespace Pharaonic\Rss\Extensions\Media;

use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Support\Xml;
use XMLWriter;

/**
 * Shared behavior of the Media RSS text elements (<media:title>, <media:description>).
 */
abstract class TextConstruct extends MediaExtension
{
    public const TYPE_PLAIN = 'plain';
    public const TYPE_HTML = 'html';

    private string $text;

    private ?string $type = null;

    /**
     * @throws InvalidElementException
     */
    final public function __construct(string $text)
    {
        if (trim($text) === '') {
            throw InvalidElementException::emptyValue($this->qualifiedName(), 'text');
        }

        $this->text = $text;
    }

    /**
     * @throws InvalidElementException
     */
    public static function make(string $text): static
    {
        return new static($text);
    }

    /**
     * Whether the text is plain text or HTML. Null omits the attribute (readers assume plain).
     *
     * @throws InvalidElementException
     */
    public function type(?string $type): static
    {
        if ($type !== null && !in_array($type, [self::TYPE_PLAIN, self::TYPE_HTML], true)) {
            throw InvalidElementException::invalidChoice(
                $this->qualifiedName(),
                'type',
                $type,
                [self::TYPE_PLAIN, self::TYPE_HTML]
            );
        }

        $this->type = $type;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function write(XMLWriter $writer): void
    {
        $writer->startElement($this->qualifiedName());

        if ($this->type !== null) {
            Xml::writeAttribute($writer, 'type', $this->type);
        }

        Xml::writeText($writer, $this->text, sprintf('<%s>', $this->qualifiedName()));
        $writer->endElement();
    }

    /**
     * Local element name, e.g. "title".
     */
    abstract protected function elementName(): string;

    private function qualifiedName(): string
    {
        return $this->prefix() . ':' . $this->elementName();
    }
}
