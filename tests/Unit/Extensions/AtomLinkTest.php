<?php

namespace Pharaonic\Rss\Tests\Unit\Extensions;

use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Extensions\Atom\AtomExtension;
use Pharaonic\Rss\Extensions\Atom\Link;
use Pharaonic\Rss\Tests\TestCase;

final class AtomLinkTest extends TestCase
{
    public function testDeclaresTheAtomNamespace(): void
    {
        $link = Link::make('https://example.com/rss.xml');

        $this->assertInstanceOf(AtomExtension::class, $link);
        $this->assertSame('atom', $link->prefix());
        $this->assertSame('http://www.w3.org/2005/Atom', $link->namespaceUri());
    }

    public function testWritesOnlyTheHrefByDefault(): void
    {
        $xpath = $this->extensionXpath(Link::make('https://example.com/rss.xml'));

        $this->assertXPathValue('https://example.com/rss.xml', $xpath, '/root/atom:link/@href');
        $this->assertXPathCount(1, $xpath, '/root/atom:link/@*');
    }

    public function testSelfLinkShortcut(): void
    {
        $xpath = $this->extensionXpath(Link::self('https://example.com/rss.xml'));

        $this->assertXPathValue('https://example.com/rss.xml', $xpath, '/root/atom:link/@href');
        $this->assertXPathValue('self', $xpath, '/root/atom:link/@rel');
        $this->assertXPathValue('application/rss+xml', $xpath, '/root/atom:link/@type');
    }

    public function testWritesAllAttributesInOrder(): void
    {
        $xml = $this->renderExtension(
            Link::make('https://example.com/feed?a=1&b=2')
                ->rel('alternate')
                ->type('text/html')
                ->hreflang('ar')
                ->title('النسخة العربية')
                ->length(1024)
        );

        $this->assertStringContainsString(
            '<atom:link href="https://example.com/feed?a=1&amp;b=2" rel="alternate" type="text/html" '
            . 'hreflang="ar" title="النسخة العربية" length="1024"/>',
            $xml
        );
        $this->parse($xml);
    }

    public function testOptionalAttributesCanBeRemoved(): void
    {
        $xpath = $this->extensionXpath(
            Link::self('https://example.com/rss.xml')->rel(null)->type('')->length(null)
        );

        $this->assertXPathCount(1, $xpath, '/root/atom:link/@*');
    }

    public function testRejectsEmptyHref(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The atom:link href must not be empty.');

        Link::make('');
    }

    public function testRejectsNegativeLength(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The atom:link length must be at least 0, -1 given.');

        Link::make('https://example.com/rss.xml')->length(-1);
    }
}
