<?php

namespace Pharaonic\Rss\Tests\Unit\Support;

use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Support\Xml;
use Pharaonic\Rss\Tests\TestCase;
use XMLWriter;

final class XmlTest extends TestCase
{
    /**
     * @param callable(XMLWriter): void $callback
     */
    private function document(callable $callback): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('root');
        $callback($writer);
        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function specialTextProvider(): array
    {
        return [
            'ampersand' => ['Tom & Jerry'],
            'less than' => ['1 < 2'],
            'greater than' => ['2 > 1'],
            'double quote' => ['say "hello"'],
            'single quote' => ["it's"],
            'html' => ['<p class="lead">Hello &amp; welcome</p>'],
            'unicode' => ['Ünïcödé — ñ ç ß'],
            'arabic' => ['فرعوني — هندسة المستقبل'],
            'emoji' => ['Launch 🚀🔥 day'],
            'cdata terminator' => ['before ]]> after'],
            'zero string' => ['0'],
            'whitespace control characters' => ["tab\tnew line\ncarriage return\r"],
        ];
    }

    /**
     * @dataProvider specialTextProvider
     */
    public function testWriteElementRoundTripsText(string $text): void
    {
        $xml = $this->document(static function (XMLWriter $writer) use ($text): void {
            Xml::writeElement($writer, 'value', $text);
        });

        $this->assertXPathValue($text, $this->xpath($xml), '/root/value');
    }

    /**
     * @dataProvider specialTextProvider
     */
    public function testWriteAttributeRoundTripsText(string $text): void
    {
        $xml = $this->document(static function (XMLWriter $writer) use ($text): void {
            Xml::writeAttribute($writer, 'value', $text);
        });

        $this->assertXPathValue($text, $this->xpath($xml), '/root/@value');
    }

    /**
     * @dataProvider specialTextProvider
     */
    public function testWriteCdataRoundTripsText(string $text): void
    {
        $xml = $this->document(static function (XMLWriter $writer) use ($text): void {
            Xml::writeCdata($writer, $text);
        });

        // CDATA cannot escape characters, so parsers normalize a raw "\r" to "\n".
        $this->assertXPathValue(str_replace("\r", "\n", $text), $this->xpath($xml), '/root');
    }

    public function testMarkupCharactersAreEscapedInText(): void
    {
        $xml = $this->document(static function (XMLWriter $writer): void {
            Xml::writeElement($writer, 'value', '<b>Tom & Jerry</b>');
        });

        $this->assertStringContainsString('<value>&lt;b&gt;Tom &amp; Jerry&lt;/b&gt;</value>', $xml);
    }

    public function testQuotesAreEscapedInAttributes(): void
    {
        $xml = $this->document(static function (XMLWriter $writer): void {
            Xml::writeAttribute($writer, 'value', 'a "b" & <c>');
        });

        $this->assertStringContainsString('value="a &quot;b&quot; &amp; &lt;c&gt;"', $xml);
    }

    public function testCdataTerminatorIsSplitAcrossSections(): void
    {
        $xml = $this->document(static function (XMLWriter $writer): void {
            Xml::writeCdata($writer, 'a]]>b]]>c');
        });

        $this->assertStringContainsString('<![CDATA[a]]]]><![CDATA[>b]]]]><![CDATA[>c]]>', $xml);
        $this->assertXPathValue('a]]>b]]>c', $this->xpath($xml), '/root');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function cdataEdgeCaseProvider(): array
    {
        return [
            'only terminator' => [']]>'],
            'leading terminator' => [']]>text'],
            'trailing terminator' => ['text]]>'],
            'consecutive terminators' => [']]>]]>'],
            'extra brackets' => ['a]]]>b'],
            'empty' => [''],
        ];
    }

    /**
     * @dataProvider cdataEdgeCaseProvider
     */
    public function testCdataEdgeCasesRoundTrip(string $text): void
    {
        $xml = $this->document(static function (XMLWriter $writer) use ($text): void {
            Xml::writeCdata($writer, $text);
        });

        $this->assertSame($text, $this->xpathValue($this->xpath($xml), '/root'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidTextProvider(): array
    {
        return [
            'null byte' => ["a\0b"],
            'control character' => ["a\x01b"],
            'escape character' => ["a\x1Bb"],
            'invalid utf-8' => ["\xC3\x28"],
            'truncated utf-8' => ["\xE2\x82"],
            'non-character' => ["a\u{FFFE}b"],
        ];
    }

    /**
     * @dataProvider invalidTextProvider
     */
    public function testInvalidCharactersAreRejectedInElements(string $text): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('<value>');

        $this->document(static function (XMLWriter $writer) use ($text): void {
            Xml::writeElement($writer, 'value', $text);
        });
    }

    /**
     * @dataProvider invalidTextProvider
     */
    public function testInvalidCharactersAreRejectedInAttributes(string $text): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('attribute "value"');

        $this->document(static function (XMLWriter $writer) use ($text): void {
            Xml::writeAttribute($writer, 'value', $text);
        });
    }

    /**
     * @dataProvider invalidTextProvider
     */
    public function testInvalidCharactersAreRejectedInCdata(string $text): void
    {
        $this->expectException(InvalidElementException::class);

        $this->document(static function (XMLWriter $writer) use ($text): void {
            Xml::writeCdata($writer, $text);
        });
    }

    /**
     * @dataProvider invalidTextProvider
     */
    public function testInvalidCharactersAreRejectedInText(string $text): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('<custom>');

        $this->document(static function (XMLWriter $writer) use ($text): void {
            Xml::writeText($writer, $text, '<custom>');
        });
    }

    public function testIsValidText(): void
    {
        $this->assertTrue(Xml::isValidText(''));
        $this->assertTrue(Xml::isValidText('فرعوني 🚀'));
        $this->assertFalse(Xml::isValidText("\x08"));
        $this->assertFalse(Xml::isValidText("\xFF"));
    }

    public function testIsNcName(): void
    {
        $this->assertTrue(Xml::isNcName('atom'));
        $this->assertTrue(Xml::isNcName('_private'));
        $this->assertTrue(Xml::isNcName('my-ext.v2'));
        $this->assertTrue(Xml::isNcName('itunes'));

        $this->assertFalse(Xml::isNcName(''));
        $this->assertFalse(Xml::isNcName('1abc'));
        $this->assertFalse(Xml::isNcName('-abc'));
        $this->assertFalse(Xml::isNcName('a:b'));
        $this->assertFalse(Xml::isNcName('a b'));
    }
}
