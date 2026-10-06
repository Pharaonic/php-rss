<?php

namespace Pharaonic\Rss\Tests\Unit;

use ArrayIterator;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Generator;
use Pharaonic\Rss\Elements\Category;
use Pharaonic\Rss\Elements\Cloud;
use Pharaonic\Rss\Elements\Image;
use Pharaonic\Rss\Elements\TextInput;
use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Exceptions\InvalidFeedException;
use Pharaonic\Rss\Extensions\Atom\Link;
use Pharaonic\Rss\Feed;
use Pharaonic\Rss\Item;
use Pharaonic\Rss\Support\CloudProtocol;
use Pharaonic\Rss\Support\Day;
use Pharaonic\Rss\Tests\TestCase;

final class FeedTest extends TestCase
{
    public function testMakeCreatesAnEmptyFeed(): void
    {
        $feed = Feed::make();

        $this->assertNull($feed->getTitle());
        $this->assertNull($feed->getLink());
        $this->assertNull($feed->getDescription());
        $this->assertSame([], $feed->getItems());
        $this->assertSame([], $feed->getCategories());
        $this->assertSame([], $feed->getExtensions());
    }

    public function testMinimalValidFeed(): void
    {
        $xpath = $this->feedXpath($this->minimalFeed());

        $this->assertXPathValue('2.0', $xpath, '/rss/@version');
        $this->assertXPathCount(1, $xpath, '/rss/channel');
        $this->assertSame(['title', 'link', 'description'], $this->childNames($xpath, '/rss/channel'));
        $this->assertXPathValue('Pharaonic', $xpath, '/rss/channel/title');
        $this->assertXPathValue('https://pharaonic.dev', $xpath, '/rss/channel/link');
        $this->assertXPathValue('Rooted in History. Engineering the Future.', $xpath, '/rss/channel/description');
    }

    public function testDoesNotAddDefaultValues(): void
    {
        $xpath = $this->feedXpath($this->minimalFeed()->addItem(Item::make()->title('Post')));

        $this->assertXPathMissing($xpath, '//pubDate');
        $this->assertXPathMissing($xpath, '//lastBuildDate');
        $this->assertXPathMissing($xpath, '//generator');
        $this->assertXPathMissing($xpath, '//docs');
        $this->assertXPathMissing($xpath, '//image');
    }

    public function testSettersCanBeCalledInAnyOrder(): void
    {
        $feed = Feed::make();

        $feed->description('Description');
        $feed->link('https://example.com');
        $feed->title('Title');

        $this->assertSame(['title', 'link', 'description'], $this->childNames($this->feedXpath($feed), '/rss/channel'));
    }

    public function testMissingTitle(): void
    {
        $this->expectException(InvalidFeedException::class);
        $this->expectExceptionMessage('The RSS channel requires a non-empty title.');

        Feed::make()->link('https://example.com')->description('Description')->toXml();
    }

    public function testMissingLink(): void
    {
        $this->expectException(InvalidFeedException::class);
        $this->expectExceptionMessage('The RSS channel requires a non-empty link.');

        Feed::make()->title('Title')->description('Description')->toXml();
    }

    public function testMissingDescription(): void
    {
        $this->expectException(InvalidFeedException::class);
        $this->expectExceptionMessage('The RSS channel requires a non-empty description.');

        Feed::make()->title('Title')->link('https://example.com')->toXml();
    }

    public function testBlankRequiredFieldsAreMissing(): void
    {
        $this->expectException(InvalidFeedException::class);
        $this->expectExceptionMessage('title');

        $this->minimalFeed()->title('   ')->toXml();
    }

    public function testRequiredFieldsCanBeUnset(): void
    {
        $this->expectException(InvalidFeedException::class);
        $this->expectExceptionMessage('link');

        $this->minimalFeed()->link(null)->toXml();
    }

    public function testAllOptionalFields(): void
    {
        $feed = $this->minimalFeed()
            ->language('en-US')
            ->copyright('Copyright 2026 Pharaonic')
            ->managingEditor('editor@pharaonic.dev (Editor)')
            ->webMaster('webmaster@pharaonic.dev (Webmaster)')
            ->publishedAt(new DateTimeImmutable('2026-10-06 10:00:00', new DateTimeZone('+03:00')))
            ->lastBuildAt(new DateTimeImmutable('2026-10-06 12:30:00', new DateTimeZone('+03:00')))
            ->category('PHP')
            ->generator('Pharaonic PHP RSS')
            ->docs('https://www.rssboard.org/rss-specification')
            ->cloud(Cloud::make('rpc.example.com', 80, '/RPC2', 'notify', CloudProtocol::XML_RPC))
            ->ttl(60)
            ->image(Image::make('https://pharaonic.dev/logo.png'))
            ->rating('(PICS-1.1 "http://www.rsac.org/ratingsv01.html" l r (n 0 s 0 v 0 l 0))')
            ->textInput(TextInput::make('Search', 'Search the site', 'q', 'https://pharaonic.dev/search'))
            ->skipHour(0)
            ->skipDay(Day::SATURDAY);

        $xpath = $this->feedXpath($feed);

        $this->assertSame([
            'title', 'link', 'description', 'language', 'copyright', 'managingEditor', 'webMaster', 'pubDate',
            'lastBuildDate', 'category', 'generator', 'docs', 'cloud', 'ttl', 'image', 'rating', 'textInput',
            'skipHours', 'skipDays',
        ], $this->childNames($xpath, '/rss/channel'));

        $this->assertXPathValue('en-US', $xpath, '/rss/channel/language');
        $this->assertXPathValue('Copyright 2026 Pharaonic', $xpath, '/rss/channel/copyright');
        $this->assertXPathValue('editor@pharaonic.dev (Editor)', $xpath, '/rss/channel/managingEditor');
        $this->assertXPathValue('webmaster@pharaonic.dev (Webmaster)', $xpath, '/rss/channel/webMaster');
        $this->assertXPathValue('Tue, 06 Oct 2026 10:00:00 +0300', $xpath, '/rss/channel/pubDate');
        $this->assertXPathValue('Tue, 06 Oct 2026 12:30:00 +0300', $xpath, '/rss/channel/lastBuildDate');
        $this->assertXPathValue('Pharaonic PHP RSS', $xpath, '/rss/channel/generator');
        $this->assertXPathValue('https://www.rssboard.org/rss-specification', $xpath, '/rss/channel/docs');
        $this->assertXPathValue('60', $xpath, '/rss/channel/ttl');
        $this->assertStringStartsWith('(PICS-1.1 "http://', $this->xpathValue($xpath, '/rss/channel/rating'));
    }

    public function testGettersExposeTheConfiguredValues(): void
    {
        $image = Image::make('https://pharaonic.dev/logo.png');
        $cloud = Cloud::make('rpc.example.com', 80, '/RPC2', 'notify', CloudProtocol::SOAP);
        $textInput = TextInput::make('Search', 'Search the site', 'q', 'https://pharaonic.dev/search');

        $feed = $this->minimalFeed()
            ->language('ar-EG')
            ->copyright('©')
            ->managingEditor('editor@pharaonic.dev')
            ->webMaster('webmaster@pharaonic.dev')
            ->generator('Generator')
            ->docs('https://example.com/docs')
            ->cloud($cloud)
            ->ttl(0)
            ->image($image)
            ->rating('PICS')
            ->textInput($textInput);

        $this->assertSame('ar-EG', $feed->getLanguage());
        $this->assertSame('©', $feed->getCopyright());
        $this->assertSame('editor@pharaonic.dev', $feed->getManagingEditor());
        $this->assertSame('webmaster@pharaonic.dev', $feed->getWebMaster());
        $this->assertSame('Generator', $feed->getGenerator());
        $this->assertSame('https://example.com/docs', $feed->getDocs());
        $this->assertSame($cloud, $feed->getCloud());
        $this->assertSame(0, $feed->getTtl());
        $this->assertSame($image, $feed->getImage());
        $this->assertSame('PICS', $feed->getRating());
        $this->assertSame($textInput, $feed->getTextInput());
    }

    public function testEmptyOptionalStringsAreOmittedButZeroIsKept(): void
    {
        $xpath = $this->feedXpath(
            $this->minimalFeed()->language('')->copyright('0')->generator(null)->rating('')
        );

        $this->assertXPathMissing($xpath, '/rss/channel/language');
        $this->assertXPathMissing($xpath, '/rss/channel/generator');
        $this->assertXPathMissing($xpath, '/rss/channel/rating');
        $this->assertXPathValue('0', $xpath, '/rss/channel/copyright');
    }

    public function testOptionalElementsCanBeRemoved(): void
    {
        $feed = $this->minimalFeed()
            ->image(Image::make('https://pharaonic.dev/logo.png'))
            ->ttl(5)
            ->publishedAt(new DateTimeImmutable())
            ->image(null)
            ->ttl(null)
            ->publishedAt(null)
            ->lastBuildAt(null)
            ->cloud(null)
            ->textInput(null);

        $this->assertSame(['title', 'link', 'description'], $this->childNames($this->feedXpath($feed), '/rss/channel'));
    }

    public function testMultipleItemsKeepInsertionOrder(): void
    {
        $feed = $this->minimalFeed()
            ->addItem(Item::make()->title('First'))
            ->addItem(Item::make()->title('Second'))
            ->addItem(Item::make()->title('Third'));

        $this->assertCount(3, $feed->getItems());
        $this->assertSame(['First', 'Second', 'Third'], $this->xpathValues($this->feedXpath($feed), '//item/title'));
    }

    public function testAddItemsAcceptsAnyIterable(): void
    {
        $generator = (static function (): Generator {
            yield Item::make()->title('From generator');
        })();

        $feed = $this->minimalFeed()
            ->addItems([Item::make()->title('From array')])
            ->addItems(new ArrayIterator([Item::make()->title('From iterator')]))
            ->addItems($generator);

        $this->assertSame(
            ['From array', 'From iterator', 'From generator'],
            $this->xpathValues($this->feedXpath($feed), '//item/title')
        );
    }

    public function testFeedWithoutItemsIsValid(): void
    {
        $this->assertXPathMissing($this->feedXpath($this->minimalFeed()), '//item');
    }

    public function testMultipleCategories(): void
    {
        $feed = $this->minimalFeed()
            ->category('PHP')
            ->category(Category::make('Open Source')->domain('https://pharaonic.dev/topics'))
            ->category('0');

        $this->assertCount(3, $feed->getCategories());

        $xpath = $this->feedXpath($feed);

        $this->assertSame(['PHP', 'Open Source', '0'], $this->xpathValues($xpath, '/rss/channel/category'));
        $this->assertXPathValue('https://pharaonic.dev/topics', $xpath, '/rss/channel/category[2]/@domain');
    }

    public function testStringCategoriesAreValidated(): void
    {
        $this->expectException(InvalidElementException::class);

        $this->minimalFeed()->category('');
    }

    public function testImage(): void
    {
        $xpath = $this->feedXpath($this->minimalFeed()->image(
            Image::make('https://pharaonic.dev/logo.png')->title('Logo')->link('https://pharaonic.dev/home')
        ));

        $this->assertXPathValue('https://pharaonic.dev/logo.png', $xpath, '/rss/channel/image/url');
        $this->assertXPathValue('Logo', $xpath, '/rss/channel/image/title');
        $this->assertXPathValue('https://pharaonic.dev/home', $xpath, '/rss/channel/image/link');
    }

    public function testCloud(): void
    {
        $xpath = $this->feedXpath($this->minimalFeed()->cloud(
            Cloud::make('rpc.example.com', 443, '/notify', '', CloudProtocol::HTTP_POST)
        ));

        $this->assertXPathValue('http-post', $xpath, '/rss/channel/cloud/@protocol');
        $this->assertXPathValue('', $xpath, '/rss/channel/cloud/@registerProcedure');
    }

    public function testTextInput(): void
    {
        $xpath = $this->feedXpath($this->minimalFeed()->textInput(
            TextInput::make('Go', 'Search', 'q', 'https://pharaonic.dev/search')
        ));

        $this->assertXPathValue('q', $xpath, '/rss/channel/textInput/name');
    }

    public function testTtlRejectsNegativeValues(): void
    {
        $this->expectException(InvalidFeedException::class);
        $this->expectExceptionMessage('The RSS channel ttl must be zero or a positive number of minutes, -1 given.');

        $this->minimalFeed()->ttl(-1);
    }

    public function testSkipHours(): void
    {
        $feed = $this->minimalFeed()->skipHour(2)->skipHour(3)->skipHour(0)->skipHour(23)->skipHour(2);

        $this->assertSame([2, 3, 0, 23], $feed->getSkipHours());
        $this->assertSame(
            ['2', '3', '0', '23'],
            $this->xpathValues($this->feedXpath($feed), '/rss/channel/skipHours/hour')
        );
    }

    /**
     * @return array<string, array{int}>
     */
    public static function invalidHourProvider(): array
    {
        return ['negative' => [-1], 'twenty four' => [24], 'large' => [100]];
    }

    /**
     * @dataProvider invalidHourProvider
     */
    public function testSkipHoursRejectsOutOfRangeValues(int $hour): void
    {
        $this->expectException(InvalidFeedException::class);
        $this->expectExceptionMessage(sprintf('An RSS skip hour must be between 0 and 23, %d given.', $hour));

        $this->minimalFeed()->skipHour($hour);
    }

    public function testSkipDays(): void
    {
        $feed = $this->minimalFeed()->skipDay('Sunday')->skipDay(Day::SATURDAY)->skipDay(Day::SUNDAY);

        $this->assertSame(['Sunday', 'Saturday'], $feed->getSkipDays());
        $this->assertSame(
            ['Sunday', 'Saturday'],
            $this->xpathValues($this->feedXpath($feed), '/rss/channel/skipDays/day')
        );
    }

    public function testSkipDaysRejectsUnknownDays(): void
    {
        $this->expectException(InvalidFeedException::class);
        $this->expectExceptionMessage(
            'An RSS skip day must be one of '
            . '[Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday], "Funday" given.'
        );

        $this->minimalFeed()->skipDay('Funday');
    }

    public function testDatesKeepTheirTimezone(): void
    {
        $feed = $this->minimalFeed()
            ->publishedAt(new DateTimeImmutable('2026-10-06 10:00:00', new DateTimeZone('+03:00')))
            ->lastBuildAt(new DateTimeImmutable('2026-10-06 07:00:00', new DateTimeZone('UTC')));

        $xpath = $this->feedXpath($feed);

        $this->assertXPathValue('Tue, 06 Oct 2026 10:00:00 +0300', $xpath, '/rss/channel/pubDate');
        $this->assertXPathValue('Tue, 06 Oct 2026 07:00:00 +0000', $xpath, '/rss/channel/lastBuildDate');
    }

    public function testMutableDatesAreCopied(): void
    {
        $date = new DateTime('2026-10-06 10:00:00', new DateTimeZone('UTC'));
        $feed = $this->minimalFeed()->publishedAt($date);

        $date->modify('+1 day');

        $this->assertSame('2026-10-06', $feed->getPublishedAt()?->format('Y-m-d'));
        $this->assertXPathValue('Tue, 06 Oct 2026 10:00:00 +0000', $this->feedXpath($feed), '/rss/channel/pubDate');
        $this->assertSame('2026-10-07', $date->format('Y-m-d'), 'The caller date must not be changed by the feed.');
    }

    public function testUnicode(): void
    {
        $feed = Feed::make()
            ->title('فرعوني')
            ->link('https://pharaonic.dev/ar')
            ->description('هندسة المستقبل 🚀')
            ->language('ar')
            ->addItem(Item::make()->title('مرحبا بالعالم')->description('Ünïcödé & émojis 🎉'));

        $xml = $feed->toXml();
        $xpath = $this->xpath($xml);

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        $this->assertStringContainsString('<title>فرعوني</title>', $xml);
        $this->assertXPathValue('هندسة المستقبل 🚀', $xpath, '/rss/channel/description');
        $this->assertXPathValue('مرحبا بالعالم', $xpath, '//item/title');
        $this->assertXPathValue('Ünïcödé & émojis 🎉', $xpath, '//item/description');
    }

    public function testExtensions(): void
    {
        $self = Link::self('https://pharaonic.dev/rss.xml');
        $hub = Link::make('https://hub.example.com')->rel('hub');

        $feed = $this->minimalFeed()->extension($self)->extension($hub);

        $this->assertSame([$self, $hub], $feed->getExtensions());
        $this->assertSame(
            ['self', 'hub'],
            $this->xpathValues($this->feedXpath($feed), '/rss/channel/atom:link/@rel')
        );
    }

    public function testToXmlIsRepeatable(): void
    {
        $feed = $this->minimalFeed()->addItem(Item::make()->title('Post'));

        $this->assertSame($feed->toXml(), $feed->toXml());
    }
}
