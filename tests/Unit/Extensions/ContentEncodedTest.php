<?php

namespace Pharaonic\Rss\Tests\Unit\Extensions;

use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Extensions\Content\ContentExtension;
use Pharaonic\Rss\Extensions\Content\Encoded;
use Pharaonic\Rss\Tests\TestCase;

final class ContentEncodedTest extends TestCase
{
    public function testDeclaresTheContentNamespace(): void
    {
        $encoded = Encoded::make('<p>Hello</p>');

        $this->assertInstanceOf(ContentExtension::class, $encoded);
        $this->assertSame('content', $encoded->prefix());
        $this->assertSame('http://purl.org/rss/1.0/modules/content/', $encoded->namespaceUri());
        $this->assertSame('<p>Hello</p>', $encoded->getContent());
    }

    public function testWritesHtmlAsCdata(): void
    {
        $html = '<article><h1>Title</h1><p>Tom &amp; Jerry & "friends"</p></article>';
        $xml = $this->renderExtension(Encoded::make($html));

        $this->assertStringContainsString('<content:encoded><![CDATA[' . $html . ']]></content:encoded>', $xml);
        $this->assertXPathValue($html, $this->xpath($xml), '/root/content:encoded');
    }

    public function testCdataTerminatorDoesNotBreakTheDocument(): void
    {
        $html = '<script>if (a[b[0]]>1) { run(); }</script> and ]]> again';
        $xml = $this->renderExtension(Encoded::make($html));

        $this->assertXPathValue($html, $this->xpath($xml), '/root/content:encoded');
    }

    public function testPreservesUnicodeContent(): void
    {
        $html = '<p dir="rtl">فرعوني — هندسة المستقبل 🚀</p>';

        $this->assertXPathValue($html, $this->extensionXpath(Encoded::make($html)), '/root/content:encoded');
    }

    public function testRejectsEmptyContent(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The content:encoded content must not be empty.');

        Encoded::make(' ');
    }

    public function testRejectsInvalidXmlCharacters(): void
    {
        $this->expectException(InvalidElementException::class);

        $this->renderExtension(Encoded::make("<p>\x00</p>"));
    }
}
