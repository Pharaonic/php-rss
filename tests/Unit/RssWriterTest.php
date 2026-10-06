<?php

namespace Pharaonic\Rss\Tests\Unit;

use Pharaonic\Rss\Contracts\Extension;
use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Exceptions\RssException;
use Pharaonic\Rss\Extensions\Atom\Link;
use Pharaonic\Rss\Extensions\Content\Encoded;
use Pharaonic\Rss\Extensions\DublinCore\Creator;
use Pharaonic\Rss\Extensions\Media\Thumbnail;
use Pharaonic\Rss\Item;
use Pharaonic\Rss\Support\Xml;
use Pharaonic\Rss\Tests\TestCase;
use Pharaonic\Rss\Writer\RssWriter;
use XMLWriter;

final class RssWriterTest extends TestCase
{
    private function extension(string $prefix, string $uri, string $element = 'value'): Extension
    {
        return new class ($prefix, $uri, $element) implements Extension {
            public function __construct(private string $prefix, private string $uri, private string $element)
            {
            }

            public function prefix(): string
            {
                return $this->prefix;
            }

            public function namespaceUri(): string
            {
                return $this->uri;
            }

            public function write(XMLWriter $writer): void
            {
                Xml::writeElement($writer, $this->prefix . ':' . $this->element, 'custom');
            }
        };
    }

    public function testWritesTheXmlDeclarationAndRssRoot(): void
    {
        $xml = (new RssWriter())->write($this->minimalFeed());

        $this->assertStringStartsWith("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<rss version=\"2.0\">\n", $xml);
        $this->assertStringEndsWith("</rss>\n", $xml);
    }

    public function testPrettyOutputIsTheDefault(): void
    {
        $this->assertSame(
            <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0">
              <channel>
                <title>Pharaonic</title>
                <link>https://pharaonic.dev</link>
                <description>Rooted in History. Engineering the Future.</description>
                <item>
                  <title>PHP Hijri released</title>
                  <description>A new release is available.</description>
                </item>
              </channel>
            </rss>

            XML,
            $this->minimalFeed()
                ->addItem(Item::make()->title('PHP Hijri released')->description('A new release is available.'))
                ->toXml()
        );
    }

    public function testCompactOutput(): void
    {
        $feed = $this->minimalFeed()->addItem(Item::make()->title('Post'));

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<rss version="2.0"><channel><title>Pharaonic</title><link>https://pharaonic.dev</link>'
            . '<description>Rooted in History. Engineering the Future.</description>'
            . '<item><title>Post</title></item></channel></rss>' . "\n",
            (new RssWriter(false))->write($feed)
        );
        $this->assertSame((new RssWriter(false))->write($feed), $feed->toXml(false));
    }

    public function testOutputIsDeterministic(): void
    {
        $feed = $this->minimalFeed()
            ->extension(Link::self('https://pharaonic.dev/rss.xml'))
            ->addItem(Item::make()->title('A')->extension(Creator::make('Author')))
            ->addItem(Item::make()->title('B')->extension(Encoded::make('<p>B</p>')));

        $writer = new RssWriter();

        $this->assertSame($writer->write($feed), $writer->write($feed));
        $this->assertSame($writer->write($feed), (new RssWriter())->write($feed));
    }

    public function testDoesNotDeclareNamespacesWithoutExtensions(): void
    {
        $this->assertStringNotContainsString('xmlns', $this->minimalFeed()->toXml());
    }

    public function testDeclaresEachNamespaceOnceOnTheRootInDiscoveryOrder(): void
    {
        $feed = $this->minimalFeed()
            ->extension(Link::self('https://pharaonic.dev/rss.xml'))
            ->addItem(
                Item::make()
                    ->title('A')
                    ->extension(Creator::make('One'))
                    ->extension(Thumbnail::make('https://e.com/1.jpg'))
            )
            ->addItem(Item::make()->title('B')->extension(Creator::make('Two'))->extension(Encoded::make('<p>B</p>')));

        $xml = $feed->toXml();

        $this->assertStringContainsString(
            '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/" '
            . 'xmlns:media="http://search.yahoo.com/mrss/" xmlns:content="http://purl.org/rss/1.0/modules/content/">',
            $xml
        );
        $this->assertSame(1, substr_count($xml, 'xmlns:dc='));
        $this->assertSame(4, substr_count($xml, 'xmlns:'));

        $this->assertSame(['One', 'Two'], $this->xpathValues($this->xpath($xml), '//item/dc:creator'));
    }

    public function testFeedExtensionsAreWrittenBeforeItems(): void
    {
        $feed = $this->minimalFeed()
            ->addItem(Item::make()->title('Post'))
            ->extension(Link::self('https://pharaonic.dev/rss.xml'));

        $this->assertSame(
            ['title', 'link', 'description', 'atom:link', 'item'],
            $this->childNames($this->feedXpath($feed), '/rss/channel')
        );
    }

    public function testCustomExtensionNamespaceIsDeclared(): void
    {
        $feed = $this->minimalFeed()->addItem(
            Item::make()->title('Post')->extension($this->extension('custom', 'https://example.com/ns/custom'))
        );

        $xml = $feed->toXml();
        $xpath = $this->xpath($xml);
        $xpath->registerNamespace('c', 'https://example.com/ns/custom');

        $this->assertStringContainsString('xmlns:custom="https://example.com/ns/custom"', $xml);
        $this->assertXPathValue('custom', $xpath, '//item/c:value');
    }

    public function testConflictingNamespacePrefixesAreRejected(): void
    {
        $feed = $this->minimalFeed()
            ->extension(Link::self('https://pharaonic.dev/rss.xml'))
            ->addItem(Item::make()->title('Post')->extension($this->extension('atom', 'https://example.com/not-atom')));

        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage(
            'The XML namespace prefix "atom" is already bound to "http://www.w3.org/2005/Atom"'
        );

        $feed->toXml();
    }

    public function testInvalidExtensionPrefixIsRejected(): void
    {
        $feed = $this->minimalFeed()->extension($this->extension('xmlns', 'https://example.com/ns'));

        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('reserved');

        $feed->toXml();
    }

    public function testInvalidCharactersInCoreFieldsAreRejected(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The value of <title> contains invalid UTF-8');

        $this->minimalFeed()->title("Broken \x07 title")->toXml();
    }

    public function testInvalidUtf8IsRejected(): void
    {
        $this->expectException(InvalidElementException::class);

        $this->minimalFeed()->addItem(Item::make()->title("\xC3\x28"))->toXml();
    }

    public function testValidationHappensBeforeAnyOutput(): void
    {
        $feed = $this->minimalFeed()->addItem(Item::make());

        try {
            $feed->toXml();
            $this->fail('An exception was expected.');
        } catch (RssException $exception) {
            $this->assertStringContainsString('position 0', $exception->getMessage());
        }

        $this->expectOutputString('');
    }
}
