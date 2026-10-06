<?php

namespace Pharaonic\Rss\Tests\Integration;

use DateTimeImmutable;
use DateTimeZone;
use Pharaonic\Rss\Contracts\Extension;
use Pharaonic\Rss\Extensions\Atom\Link;
use Pharaonic\Rss\Extensions\Content\Encoded;
use Pharaonic\Rss\Extensions\DublinCore\Creator;
use Pharaonic\Rss\Extensions\Media\Content;
use Pharaonic\Rss\Extensions\Media\Description;
use Pharaonic\Rss\Extensions\Media\Thumbnail;
use Pharaonic\Rss\Extensions\Media\Title;
use Pharaonic\Rss\Feed;
use Pharaonic\Rss\Item;
use Pharaonic\Rss\Support\Xml;
use Pharaonic\Rss\Tests\TestCase;
use XMLWriter;

/**
 * A feed combining the Atom, Content, Dublin Core, and Media RSS extensions
 * with a user-defined extension.
 */
final class NamespacedFeedTest extends TestCase
{
    private const GEO = 'http://www.w3.org/2003/01/geo/wgs84_pos#';

    private const ARTICLE = '<article><h1>Giza</h1><p>Code like <code>a[b[0]]>1</code>.</p></article>';

    private function geoPoint(float $latitude, float $longitude): Extension
    {
        return new class ($latitude, $longitude) implements Extension {
            public function __construct(private float $latitude, private float $longitude)
            {
            }

            public function prefix(): string
            {
                return 'geo';
            }

            public function namespaceUri(): string
            {
                return 'http://www.w3.org/2003/01/geo/wgs84_pos#';
            }

            public function write(XMLWriter $writer): void
            {
                Xml::writeElement($writer, 'geo:lat', (string) $this->latitude);
                Xml::writeElement($writer, 'geo:long', (string) $this->longitude);
            }
        };
    }

    private function feed(): Feed
    {
        return Feed::make()
            ->title('Pharaonic')
            ->link('https://pharaonic.dev')
            ->description('Rooted in History. Engineering the Future.')
            ->extension(Link::self('https://pharaonic.dev/rss.xml'))
            ->extension(Link::make('https://pubsubhubbub.appspot.com/')->rel('hub'))
            ->addItem(
                Item::make()
                    ->title('Visiting Giza')
                    ->link('https://pharaonic.dev/blog/giza')
                    ->publishedAt(new DateTimeImmutable('2026-10-06 10:00:00', new DateTimeZone('+03:00')))
                    ->extension(Creator::make('Moamen Eltouny'))
                    ->extension(Encoded::make(self::ARTICLE))
                    ->extension(
                        Content::make('https://cdn.pharaonic.dev/giza.mp4')
                            ->type('video/mp4')
                            ->medium(Content::MEDIUM_VIDEO)
                            ->width(1920)
                            ->height(1080)
                            ->duration(95)
                            ->title(Title::make('Giza pyramids'))
                            ->description(Description::make('<b>Drone</b> footage')->type(Description::TYPE_HTML))
                            ->thumbnail(Thumbnail::make('https://cdn.pharaonic.dev/giza.jpg')->width(1200)->height(630))
                    )
                    ->extension($this->geoPoint(29.9792, 31.1342))
            )
            ->addItem(
                Item::make()
                    ->description('A short note.')
                    ->extension(Creator::make('فريق فرعوني'))
                    ->extension(Thumbnail::make('https://cdn.pharaonic.dev/note.jpg'))
            );
    }

    public function testDeclaresEveryNamespaceOnceOnTheRoot(): void
    {
        $xml = $this->feed()->toXml();

        $this->assertStringContainsString(
            '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/" '
            . 'xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:media="http://search.yahoo.com/mrss/" '
            . 'xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#">',
            $xml
        );

        foreach (['atom', 'dc', 'content', 'media', 'geo'] as $prefix) {
            $this->assertSame(1, substr_count($xml, 'xmlns:' . $prefix . '='), $prefix);
        }
    }

    public function testNamespacedElementsResolveToTheirNamespaces(): void
    {
        $xpath = $this->feedXpath($this->feed());
        $xpath->registerNamespace('geo', self::GEO);

        $this->assertSame(['self', 'hub'], $this->xpathValues($xpath, '/rss/channel/atom:link/@rel'));
        $this->assertSame(['Moamen Eltouny', 'فريق فرعوني'], $this->xpathValues($xpath, '//item/dc:creator'));
        $this->assertXPathValue(self::ARTICLE, $xpath, '//item[1]/content:encoded');
        $this->assertXPathValue('video', $xpath, '//item[1]/media:content/@medium');
        $this->assertXPathValue('Giza pyramids', $xpath, '//item[1]/media:content/media:title');
        $this->assertXPathValue('<b>Drone</b> footage', $xpath, '//item[1]/media:content/media:description');
        $this->assertXPathValue('630', $xpath, '//item[1]/media:content/media:thumbnail/@height');
        $this->assertXPathValue('https://cdn.pharaonic.dev/note.jpg', $xpath, '//item[2]/media:thumbnail/@url');
        $this->assertXPathValue('29.9792', $xpath, '//item[1]/geo:lat');
        $this->assertXPathValue('31.1342', $xpath, '//item[1]/geo:long');
    }

    public function testExtensionsFollowCoreElementsInInsertionOrder(): void
    {
        $xpath = $this->feedXpath($this->feed());

        $this->assertSame(
            ['title', 'link', 'description', 'atom:link', 'atom:link', 'item', 'item'],
            $this->childNames($xpath, '/rss/channel')
        );
        $this->assertSame(
            ['title', 'link', 'pubDate', 'dc:creator', 'content:encoded', 'media:content', 'geo:lat', 'geo:long'],
            $this->childNames($xpath, '//item[1]')
        );
        $this->assertSame(['description', 'dc:creator', 'media:thumbnail'], $this->childNames($xpath, '//item[2]'));
    }

    public function testRootOnlyDeclaresNamespacesInUse(): void
    {
        $xml = Feed::make()
            ->title('T')
            ->link('https://example.com')
            ->description('D')
            ->addItem(Item::make()->title('A')->extension(Creator::make('Author')))
            ->toXml();

        $this->assertStringContainsString('<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">', $xml);
        $this->assertSame(1, substr_count($xml, 'xmlns'));
    }
}
