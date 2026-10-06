<?php

namespace Pharaonic\Rss\Support;

use Pharaonic\Rss\Exceptions\InvalidElementException;
use XMLWriter;

/**
 * Safe XML writing primitives shared by the writer and by extensions.
 *
 * XMLWriter escapes markup characters, but it copies invalid UTF-8, XML 1.0
 * control characters, and the CDATA terminator "]]>" into the output as-is,
 * which produces documents no parser accepts. These helpers reject or split
 * such values so that every generated document stays well-formed.
 */
final class Xml
{
    /**
     * Characters allowed by the XML 1.0 "Char" production.
     */
    private const INVALID_CHARACTERS = '/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u';

    /**
     * Unprefixed XML name (NCName), restricted to letters, digits, "_", "-" and ".".
     */
    private const NC_NAME = '/^[\p{L}_][\p{L}\p{N}_.\-]*$/u';

    private function __construct()
    {
    }

    /**
     * Whether the value is valid UTF-8 made only of characters allowed in XML 1.0.
     */
    public static function isValidText(string $value): bool
    {
        // preg_match() returns false (not 0) when the subject is invalid UTF-8.
        return preg_match(self::INVALID_CHARACTERS, $value) === 0;
    }

    /**
     * @throws InvalidElementException
     */
    public static function assertValidText(string $value, string $context): void
    {
        if (!self::isValidText($value)) {
            throw InvalidElementException::invalidXmlCharacters($context);
        }
    }

    /**
     * Whether the value can be used as an XML namespace prefix or local name.
     */
    public static function isNcName(string $name): bool
    {
        return preg_match(self::NC_NAME, $name) === 1;
    }

    /**
     * Write an element containing escaped text, e.g. <title>A &amp; B</title>.
     *
     * @throws InvalidElementException
     */
    public static function writeElement(XMLWriter $writer, string $name, string $value): void
    {
        self::assertValidText($value, sprintf('<%s>', $name));

        $writer->writeElement($name, $value);
    }

    /**
     * Write escaped text inside the element that is currently open.
     *
     * @throws InvalidElementException
     */
    public static function writeText(XMLWriter $writer, string $value, string $context): void
    {
        self::assertValidText($value, $context);

        $writer->text($value);
    }

    /**
     * Write an escaped attribute on the element that is currently open.
     *
     * @throws InvalidElementException
     */
    public static function writeAttribute(XMLWriter $writer, string $name, string $value): void
    {
        self::assertValidText($value, sprintf('attribute "%s"', $name));

        $writer->writeAttribute($name, $value);
    }

    /**
     * Write the content as CDATA inside the element that is currently open.
     *
     * Any "]]>" sequence is split across adjacent CDATA sections, so the
     * parsed text is always identical to the given content.
     *
     * @throws InvalidElementException
     */
    public static function writeCdata(XMLWriter $writer, string $content): void
    {
        self::assertValidText($content, 'CDATA content');

        $pieces = explode(']]>', $content);
        $last = count($pieces) - 1;

        foreach ($pieces as $index => $piece) {
            $section = ($index > 0 ? '>' : '') . $piece . ($index < $last ? ']]' : '');

            $writer->writeCdata($section);
        }
    }
}
