<?php

namespace Pharaonic\Rss\Tests\Unit;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Pharaonic\Rss\Elements\Category;
use Pharaonic\Rss\Elements\Enclosure;
use Pharaonic\Rss\Elements\Guid;
use Pharaonic\Rss\Elements\Source;
use Pharaonic\Rss\Exceptions\InvalidItemException;
use Pharaonic\Rss\Extensions\Content\Encoded;
use Pharaonic\Rss\Extensions\DublinCore\Creator;
use Pharaonic\Rss\Item;
use Pharaonic\Rss\Tests\TestCase;

final class ItemTest extends TestCase
{
    private function itemXpath(Item $item): \DOMXPath
    {
        return $this->feedXpath($this->minimalFeed()->addItem($item));
    }

    /**
     * @return array<string, array{Item, list<string>}>
     */
    public static function validItemProvider(): array
    {
        return [
            'title only' => [Item::make()->title('Title'), ['title']],
            'description only' => [Item::make()->description('Description'), ['description']],
            'title and description' => [
                Item::make()->title('Title')->description('Description'),
                ['title', 'description'],
            ],
            'title and link' => [Item::make()->title('Title')->link('https://example.com'), ['title', 'link']],
            'description and link' => [
                Item::make()->description('Description')->link('https://example.com'),
                ['link', 'description'],
            ],
            'zero title' => [Item::make()->title('0'), ['title']],
        ];
    }

    /**
     * @dataProvider validItemProvider
     *
     * @param list<string> $elements
     */
    public function testValidItems(Item $item, array $elements): void
    {
        $this->assertSame($elements, $this->childNames($this->itemXpath($item), '//item'));
    }

    /**
     * @return array<string, array{Item}>
     */
    public static function invalidItemProvider(): array
    {
        return [
            'empty item' => [Item::make()],
            'link only' => [Item::make()->link('https://example.com')],
            'empty title and description' => [Item::make()->title('')->description('')],
            'blank title and description' => [Item::make()->title(' ')->description("\n")],
            'everything but title and description' => [
                Item::make()
                    ->link('https://example.com')
                    ->author('author@example.com')
                    ->category('PHP')
                    ->guid('id', false)
                    ->publishedAt(new DateTimeImmutable()),
            ],
        ];
    }

    /**
     * @dataProvider invalidItemProvider
     */
    public function testItemsWithoutTitleAndDescriptionAreRejected(Item $item): void
    {
        $this->expectException(InvalidItemException::class);
        $this->expectExceptionMessage('requires at least a title or a description');

        $this->minimalFeed()->addItem($item)->toXml();
    }

    public function testErrorReportsThePositionOfTheInvalidItem(): void
    {
        $this->expectException(InvalidItemException::class);
        $this->expectExceptionMessage('The RSS item at position 1 requires at least a title or a description.');

        $this->minimalFeed()
            ->addItem(Item::make()->title('Valid'))
            ->addItem(Item::make()->link('https://example.com'))
            ->toXml();
    }

    public function testAllFieldsInSpecificationOrder(): void
    {
        $item = Item::make()
            ->guid('https://example.com/posts/1')
            ->source(Source::make('Origin', 'https://origin.example.com/rss.xml'))
            ->publishedAt(new DateTimeImmutable('2026-10-06 10:00:00', new DateTimeZone('+03:00')))
            ->enclosure(Enclosure::make('https://example.com/a.mp3', 10, 'audio/mpeg'))
            ->comments('https://example.com/posts/1#comments')
            ->category('PHP')
            ->author('author@example.com (Author)')
            ->description('Description')
            ->link('https://example.com/posts/1')
            ->title('Title');

        $xpath = $this->itemXpath($item);

        $this->assertSame(
            [
                'title', 'link', 'description', 'author', 'category', 'comments', 'enclosure', 'guid', 'pubDate',
                'source',
            ],
            $this->childNames($xpath, '//item')
        );
        $this->assertXPathValue('author@example.com (Author)', $xpath, '//item/author');
        $this->assertXPathValue('https://example.com/posts/1#comments', $xpath, '//item/comments');
        $this->assertXPathValue('Tue, 06 Oct 2026 10:00:00 +0300', $xpath, '//item/pubDate');
    }

    public function testAuthorIsPreservedAsGiven(): void
    {
        $item = Item::make()->title('Title')->author('Moamen Eltouny');

        $this->assertSame('Moamen Eltouny', $item->getAuthor());
        $this->assertXPathValue('Moamen Eltouny', $this->itemXpath($item), '//item/author');
    }

    public function testCategories(): void
    {
        $item = Item::make()
            ->title('Title')
            ->category('PHP')
            ->category(Category::make('Packages')->domain('https://example.com/tags'));

        $this->assertCount(2, $item->getCategories());
        $this->assertSame('PHP', $item->getCategories()[0]->getValue());

        $xpath = $this->itemXpath($item);

        $this->assertSame(['PHP', 'Packages'], $this->xpathValues($xpath, '//item/category'));
        $this->assertXPathValue('https://example.com/tags', $xpath, '//item/category[2]/@domain');
    }

    public function testComments(): void
    {
        $item = Item::make()->title('Title')->comments('https://example.com/comments');

        $this->assertSame('https://example.com/comments', $item->getComments());
    }

    public function testEnclosure(): void
    {
        $enclosure = Enclosure::make('https://example.com/a.mp3', 10, 'audio/mpeg');
        $item = Item::make()->title('Title')->enclosure($enclosure);

        $this->assertSame($enclosure, $item->getEnclosure());
        $this->assertXPathValue('audio/mpeg', $this->itemXpath($item), '//item/enclosure/@type');
        $this->assertNull($item->enclosure(null)->getEnclosure());
    }

    public function testGuidFromStringIsAPermalinkByDefault(): void
    {
        $item = Item::make()->title('Title')->guid('https://example.com/posts/1');

        $this->assertNotNull($item->getGuid());
        $this->assertTrue($item->getGuid()->isPermaLink());
        $this->assertXPathMissing($this->itemXpath($item), '//item/guid/@isPermaLink');
    }

    public function testGuidFromStringWithPermalinkFlag(): void
    {
        $item = Item::make()->title('Title')->guid('php-hijri-release', false);

        $this->assertNotNull($item->getGuid());
        $this->assertSame('php-hijri-release', $item->getGuid()->getValue());
        $this->assertFalse($item->getGuid()->isPermaLink());
        $this->assertXPathValue('false', $this->itemXpath($item), '//item/guid/@isPermaLink');
    }

    public function testGuidFromObject(): void
    {
        $guid = Guid::make('article-123')->permalink(false);
        $item = Item::make()->title('Title')->guid($guid);

        $this->assertSame($guid, $item->getGuid());
        $this->assertXPathValue('article-123', $this->itemXpath($item), '//item/guid');
    }

    public function testGuidObjectWithPermalinkFlagIsAmbiguous(): void
    {
        $this->expectException(InvalidItemException::class);
        $this->expectExceptionMessage('Set the flag on the Guid object with Guid::permalink() instead.');

        Item::make()->guid(Guid::make('article-123'), false);
    }

    public function testGuidCanBeRemoved(): void
    {
        $this->assertNull(Item::make()->guid('id')->guid(null)->getGuid());
    }

    public function testSource(): void
    {
        $source = Source::make('Origin', 'https://origin.example.com/rss.xml');
        $item = Item::make()->title('Title')->source($source);

        $this->assertSame($source, $item->getSource());
        $this->assertXPathValue('Origin', $this->itemXpath($item), '//item/source');
    }

    public function testPublishedDate(): void
    {
        $date = new DateTime('2026-02-01 23:59:59', new DateTimeZone('Asia/Riyadh'));
        $item = Item::make()->title('Title')->publishedAt($date);

        $date->setTimezone(new DateTimeZone('UTC'));

        $this->assertXPathValue('Sun, 01 Feb 2026 23:59:59 +0300', $this->itemXpath($item), '//item/pubDate');
    }

    public function testPublishedDateIsNotSetByDefault(): void
    {
        $item = Item::make()->title('Title');

        $this->assertNull($item->getPublishedAt());
        $this->assertXPathMissing($this->itemXpath($item), '//item/pubDate');
    }

    public function testHtmlDescriptionIsEscaped(): void
    {
        $html = '<p>Hello <a href="https://example.com?a=1&b=2">world</a></p>';
        $xml = $this->minimalFeed()->addItem(Item::make()->description($html))->toXml();

        $this->assertStringContainsString(
            '<description>&lt;p&gt;Hello &lt;a href=&quot;https://example.com?a=1&amp;b=2&quot;&gt;'
            . 'world&lt;/a&gt;&lt;/p&gt;</description>',
            $xml
        );
        $this->assertXPathValue($html, $this->xpath($xml), '//item/description');
    }

    public function testExtensionsAreWrittenAfterCoreElementsInInsertionOrder(): void
    {
        $creator = Creator::make('Moamen Eltouny');
        $encoded = Encoded::make('<p>Body</p>');

        $item = Item::make()->title('Title')->extension($creator)->link('https://example.com')->extension($encoded);

        $this->assertSame([$creator, $encoded], $item->getExtensions());
        $this->assertSame(
            ['title', 'link', 'dc:creator', 'content:encoded'],
            $this->childNames($this->itemXpath($item), '//item')
        );
    }

    public function testGetters(): void
    {
        $item = Item::make()->title('Title')->link('https://example.com')->description('Description');

        $this->assertSame('Title', $item->getTitle());
        $this->assertSame('https://example.com', $item->getLink());
        $this->assertSame('Description', $item->getDescription());
        $this->assertNull($item->getAuthor());
        $this->assertNull($item->getComments());
        $this->assertNull($item->getSource());
        $this->assertSame([], $item->getCategories());
        $this->assertSame([], $item->getExtensions());
    }
}
