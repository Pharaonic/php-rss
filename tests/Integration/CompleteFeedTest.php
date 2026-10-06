<?php

namespace Pharaonic\RSS\Tests\Integration;

use DateTimeImmutable;
use DateTimeZone;
use Pharaonic\RSS\Elements\Category;
use Pharaonic\RSS\Elements\Cloud;
use Pharaonic\RSS\Elements\Enclosure;
use Pharaonic\RSS\Elements\Guid;
use Pharaonic\RSS\Elements\Image;
use Pharaonic\RSS\Elements\Source;
use Pharaonic\RSS\Elements\TextInput;
use Pharaonic\RSS\Feed;
use Pharaonic\RSS\Item;
use Pharaonic\RSS\Support\CloudProtocol;
use Pharaonic\RSS\Support\Day;
use Pharaonic\RSS\Tests\TestCase;

/**
 * A feed using every RSS 2.0 channel and item element.
 */
final class CompleteFeedTest extends TestCase
{
    private function feed(): Feed
    {
        $utc = new DateTimeZone('UTC');

        return Feed::make()
            ->title('فرعوني — Pharaonic')
            ->link('https://pharaonic.dev')
            ->description('Rooted in History. Engineering the Future. هندسة المستقبل')
            ->language('en-US')
            ->copyright('Copyright © 2026 Pharaonic')
            ->managingEditor('editor@pharaonic.dev (Pharaonic Editor)')
            ->webMaster('webmaster@pharaonic.dev (Pharaonic Webmaster)')
            ->publishedAt(new DateTimeImmutable('2026-10-06 08:00:00', $utc))
            ->lastBuildAt(new DateTimeImmutable('2026-10-06 09:15:00', $utc))
            ->category('Software')
            ->category(Category::make('PHP')->domain('https://pharaonic.dev/topics'))
            ->generator('Pharaonic PHP RSS')
            ->docs('https://www.rssboard.org/rss-specification')
            ->cloud(Cloud::make('rpc.pharaonic.dev', 80, '/RPC2', 'pingMe', CloudProtocol::XML_RPC))
            ->ttl(60)
            ->image(
                Image::make('https://pharaonic.dev/logo.png')
                    ->title('Pharaonic')
                    ->link('https://pharaonic.dev')
                    ->width(144)
                    ->height(144)
                    ->description('The Pharaonic logo')
            )
            ->rating('(PICS-1.1 "http://www.classify.org/safesurf/" l r (SS~~000 1))')
            ->textInput(TextInput::make('Search', 'Search Pharaonic', 'q', 'https://pharaonic.dev/search'))
            ->skipHour(1)
            ->skipHour(2)
            ->skipDay(Day::SATURDAY)
            ->skipDay(Day::SUNDAY)
            ->addItem(
                Item::make()
                    ->title('Episode 1: "Origins" & <beginnings>')
                    ->link('https://pharaonic.dev/podcast/1')
                    ->description('The story of Pharaonic.')
                    ->author('host@pharaonic.dev (Podcast Host)')
                    ->category('Podcast')
                    ->category(Category::make('History')->domain('https://pharaonic.dev/topics'))
                    ->comments('https://pharaonic.dev/podcast/1#comments')
                    ->enclosure(Enclosure::make('https://cdn.pharaonic.dev/podcast/1.mp3', 24986239, 'audio/mpeg'))
                    ->guid(Guid::make('https://pharaonic.dev/podcast/1'))
                    ->publishedAt(new DateTimeImmutable('2026-10-01 18:00:00', new DateTimeZone('+03:00')))
                    ->source(Source::make('Pharaonic Podcast', 'https://pharaonic.dev/podcast/rss.xml'))
            );
    }

    public function testMatchesTheExpectedDocument(): void
    {
        $this->assertStringEqualsFile(__DIR__ . '/../Fixtures/feeds/complete.xml', $this->feed()->toXml());
    }

    public function testChannelStructure(): void
    {
        $xpath = $this->feedXpath($this->feed());

        $this->assertSame([
            'title', 'link', 'description', 'language', 'copyright', 'managingEditor', 'webMaster', 'pubDate',
            'lastBuildDate', 'category', 'category', 'generator', 'docs', 'cloud', 'ttl', 'image', 'rating',
            'textInput', 'skipHours', 'skipDays', 'item',
        ], $this->childNames($xpath, '/rss/channel'));

        $this->assertXPathValue('فرعوني — Pharaonic', $xpath, '/rss/channel/title');
        $this->assertXPathValue('Copyright © 2026 Pharaonic', $xpath, '/rss/channel/copyright');
        $this->assertXPathValue('Tue, 06 Oct 2026 08:00:00 +0000', $xpath, '/rss/channel/pubDate');
        $this->assertXPathValue('https://pharaonic.dev/topics', $xpath, '/rss/channel/category[2]/@domain');
        $this->assertXPathValue('pingMe', $xpath, '/rss/channel/cloud/@registerProcedure');
        $this->assertXPathValue('144', $xpath, '/rss/channel/image/width');
        $this->assertSame(['1', '2'], $this->xpathValues($xpath, '/rss/channel/skipHours/hour'));
        $this->assertSame(['Saturday', 'Sunday'], $this->xpathValues($xpath, '/rss/channel/skipDays/day'));
    }

    public function testItemStructure(): void
    {
        $xpath = $this->feedXpath($this->feed());

        $this->assertSame([
            'title', 'link', 'description', 'author', 'category', 'category', 'comments', 'enclosure', 'guid',
            'pubDate', 'source',
        ], $this->childNames($xpath, '//item'));

        $this->assertXPathValue('Episode 1: "Origins" & <beginnings>', $xpath, '//item/title');
        $this->assertXPathValue('24986239', $xpath, '//item/enclosure/@length');
        $this->assertXPathValue('Thu, 01 Oct 2026 18:00:00 +0300', $xpath, '//item/pubDate');
        $this->assertXPathValue('https://pharaonic.dev/podcast/rss.xml', $xpath, '//item/source/@url');
    }
}
