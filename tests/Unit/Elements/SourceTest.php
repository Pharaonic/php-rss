<?php

namespace Pharaonic\RSS\Tests\Unit\Elements;

use Pharaonic\RSS\Elements\Source;
use Pharaonic\RSS\Exceptions\InvalidElementException;
use Pharaonic\RSS\Item;
use Pharaonic\RSS\Tests\TestCase;

final class SourceTest extends TestCase
{
    public function testMakeStoresTitleAndUrl(): void
    {
        $source = Source::make('PHP News', 'https://example.com/rss.xml');

        $this->assertSame('PHP News', $source->getTitle());
        $this->assertSame('https://example.com/rss.xml', $source->getUrl());
    }

    public function testRejectsEmptyUrl(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The source url must not be empty.');

        Source::make('PHP News', '');
    }

    public function testRejectsEmptyTitle(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The source title must not be empty.');

        Source::make(' ', 'https://example.com/rss.xml');
    }

    public function testSerialization(): void
    {
        $xpath = $this->feedXpath($this->minimalFeed()->addItem(
            Item::make()->title('Post')->source(Source::make('PHP & News', 'https://example.com/rss.xml'))
        ));

        $this->assertXPathValue('PHP & News', $xpath, '//item/source');
        $this->assertXPathValue('https://example.com/rss.xml', $xpath, '//item/source/@url');
    }
}
