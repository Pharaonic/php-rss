<?php

namespace Pharaonic\Rss\Tests\Integration;

use DateTimeImmutable;
use DateTimeZone;
use Pharaonic\Rss\Elements\Enclosure;
use Pharaonic\Rss\Elements\Image;
use Pharaonic\Rss\Extensions\Atom\Link;
use Pharaonic\Rss\Extensions\DublinCore\Creator;
use Pharaonic\Rss\Extensions\Media\Content;
use Pharaonic\Rss\Extensions\Media\Thumbnail;
use Pharaonic\Rss\Feed;
use Pharaonic\Rss\Item;
use Pharaonic\Rss\Tests\TestCase;

/**
 * A podcast-style feed built with RSS enclosures and Media RSS.
 */
final class PodcastFeedTest extends TestCase
{
    private function feed(): Feed
    {
        $feed = Feed::make()
            ->title('Pharaonic Podcast')
            ->link('https://pharaonic.dev/podcast')
            ->description('Conversations about building software.')
            ->language('en')
            ->image(Image::make('https://cdn.pharaonic.dev/podcast/cover.png'))
            ->extension(Link::self('https://pharaonic.dev/podcast/rss.xml'));

        $episodes = [
            [1, 'Origins', 24986239, 1520],
            [2, 'Engineering the future', 31457280, 1934],
        ];

        $utc = new DateTimeZone('UTC');

        foreach ($episodes as [$number, $title, $bytes, $seconds]) {
            $audio = sprintf('https://cdn.pharaonic.dev/podcast/%d.mp3', $number);

            $feed->addItem(
                Item::make()
                    ->title(sprintf('Episode %d: %s', $number, $title))
                    ->enclosure(Enclosure::make($audio, $bytes, 'audio/mpeg'))
                    ->guid(sprintf('pharaonic-podcast-%d', $number), false)
                    ->publishedAt(new DateTimeImmutable(sprintf('2026-10-0%d 18:00:00', $number), $utc))
                    ->extension(Creator::make('Pharaonic'))
                    ->extension(
                        Content::make($audio)
                            ->fileSize($bytes)
                            ->type('audio/mpeg')
                            ->medium(Content::MEDIUM_AUDIO)
                            ->expression(Content::EXPRESSION_FULL)
                            ->duration($seconds)
                    )
                    ->extension(Thumbnail::make(sprintf('https://cdn.pharaonic.dev/podcast/%d.jpg', $number)))
            );
        }

        return $feed;
    }

    public function testEpisodesWithoutDescriptionAreValid(): void
    {
        $xpath = $this->feedXpath($this->feed());

        $this->assertXPathCount(2, $xpath, '//item');
        $this->assertXPathCount(0, $xpath, '//item/description');
    }

    public function testEnclosures(): void
    {
        $xpath = $this->feedXpath($this->feed());

        $this->assertSame(
            ['https://cdn.pharaonic.dev/podcast/1.mp3', 'https://cdn.pharaonic.dev/podcast/2.mp3'],
            $this->xpathValues($xpath, '//item/enclosure/@url')
        );
        $this->assertSame(['24986239', '31457280'], $this->xpathValues($xpath, '//item/enclosure/@length'));
        $this->assertSame(['audio/mpeg', 'audio/mpeg'], $this->xpathValues($xpath, '//item/enclosure/@type'));
    }

    public function testMediaContentMirrorsTheEnclosure(): void
    {
        $xpath = $this->feedXpath($this->feed());

        $this->assertXPathValue('https://cdn.pharaonic.dev/podcast/2.mp3', $xpath, '//item[2]/media:content/@url');
        $this->assertXPathValue('31457280', $xpath, '//item[2]/media:content/@fileSize');
        $this->assertXPathValue('audio', $xpath, '//item[2]/media:content/@medium');
        $this->assertXPathValue('1934', $xpath, '//item[2]/media:content/@duration');
        $this->assertXPathValue('https://cdn.pharaonic.dev/podcast/2.jpg', $xpath, '//item[2]/media:thumbnail/@url');
    }

    public function testChannelImageFallsBackToTheChannelTitleAndLink(): void
    {
        $xpath = $this->feedXpath($this->feed());

        $this->assertXPathValue('Pharaonic Podcast', $xpath, '/rss/channel/image/title');
        $this->assertXPathValue('https://pharaonic.dev/podcast', $xpath, '/rss/channel/image/link');
    }

    public function testItemOrderAndGuids(): void
    {
        $xpath = $this->feedXpath($this->feed());

        $this->assertSame(
            ['Episode 1: Origins', 'Episode 2: Engineering the future'],
            $this->xpathValues($xpath, '//item/title')
        );
        $this->assertSame(['pharaonic-podcast-1', 'pharaonic-podcast-2'], $this->xpathValues($xpath, '//item/guid'));
        $this->assertSame(
            ['Thu, 01 Oct 2026 18:00:00 +0000', 'Fri, 02 Oct 2026 18:00:00 +0000'],
            $this->xpathValues($xpath, '//item/pubDate')
        );
    }
}
