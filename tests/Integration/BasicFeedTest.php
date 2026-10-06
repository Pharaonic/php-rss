<?php

namespace Pharaonic\Rss\Tests\Integration;

use DateTimeImmutable;
use DateTimeZone;
use Pharaonic\Rss\Feed;
use Pharaonic\Rss\Item;
use Pharaonic\Rss\Tests\TestCase;

/**
 * A typical website or blog feed using only common RSS 2.0 elements.
 */
final class BasicFeedTest extends TestCase
{
    private function feed(): Feed
    {
        $timezone = new DateTimeZone('+03:00');

        return Feed::make()
            ->title('Pharaonic Blog')
            ->link('https://pharaonic.dev/blog')
            ->description('News & releases from the Pharaonic team.')
            ->language('en-US')
            ->lastBuildAt(new DateTimeImmutable('2026-10-06 12:00:00', $timezone))
            ->addItems([
                Item::make()
                    ->title('PHP RSS rebuilt')
                    ->link('https://pharaonic.dev/blog/php-rss-rebuilt')
                    ->description('<p>A modern <strong>RSS 2.0</strong> generator for PHP.</p>')
                    ->category('PHP')
                    ->category('RSS')
                    ->guid('https://pharaonic.dev/blog/php-rss-rebuilt')
                    ->publishedAt(new DateTimeImmutable('2026-10-06 10:00:00', $timezone)),
                Item::make()
                    ->title('PHP Hijri released')
                    ->link('https://pharaonic.dev/blog/php-hijri-released')
                    ->description('A new release is available.')
                    ->category('PHP')
                    ->guid('php-hijri-release', false)
                    ->publishedAt(new DateTimeImmutable('2026-10-05 09:30:00', $timezone)),
            ]);
    }

    public function testMatchesTheExpectedDocument(): void
    {
        $this->assertStringEqualsFile(__DIR__ . '/../Fixtures/feeds/basic.xml', $this->feed()->toXml());
    }

    public function testStructure(): void
    {
        $xpath = $this->feedXpath($this->feed());

        $this->assertXPathValue('2.0', $xpath, '/rss/@version');
        $this->assertXPathValue('News & releases from the Pharaonic team.', $xpath, '/rss/channel/description');
        $this->assertXPathCount(2, $xpath, '/rss/channel/item');
        $this->assertSame(['PHP RSS rebuilt', 'PHP Hijri released'], $this->xpathValues($xpath, '//item/title'));
        $this->assertXPathValue(
            '<p>A modern <strong>RSS 2.0</strong> generator for PHP.</p>',
            $xpath,
            '//item[1]/description'
        );
        $this->assertXPathValue('Tue, 06 Oct 2026 10:00:00 +0300', $xpath, '//item[1]/pubDate');
        $this->assertXPathValue('false', $xpath, '//item[2]/guid/@isPermaLink');
    }
}
