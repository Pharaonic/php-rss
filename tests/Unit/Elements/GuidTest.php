<?php

namespace Pharaonic\Rss\Tests\Unit\Elements;

use Pharaonic\Rss\Elements\Guid;
use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Item;
use Pharaonic\Rss\Tests\TestCase;

final class GuidTest extends TestCase
{
    public function testIsAPermalinkByDefaultAsInRss(): void
    {
        $guid = Guid::make('https://example.com/posts/1');

        $this->assertSame('https://example.com/posts/1', $guid->getValue());
        $this->assertTrue($guid->isPermaLink());
    }

    public function testPermalinkCanBeDisabled(): void
    {
        $guid = Guid::make('article-123')->permalink(false);

        $this->assertFalse($guid->isPermaLink());
        $this->assertTrue($guid->permalink()->isPermaLink());
    }

    public function testValueIsNotReinterpreted(): void
    {
        $this->assertSame(' Article 123 ', Guid::make(' Article 123 ')->getValue());
    }

    public function testRejectsEmptyValues(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The guid value must not be empty.');

        Guid::make(' ');
    }

    public function testPermalinkGuidOmitsTheDefaultAttribute(): void
    {
        $xpath = $this->feedXpath($this->minimalFeed()->addItem(
            Item::make()->title('Post')->guid(Guid::make('https://example.com/posts/1'))
        ));

        $this->assertXPathValue('https://example.com/posts/1', $xpath, '//item/guid');
        $this->assertXPathMissing($xpath, '//item/guid/@isPermaLink');
    }

    public function testNonPermalinkGuidWritesTheAttribute(): void
    {
        $xpath = $this->feedXpath($this->minimalFeed()->addItem(
            Item::make()->title('Post')->guid(Guid::make('article-123')->permalink(false))
        ));

        $this->assertXPathValue('article-123', $xpath, '//item/guid');
        $this->assertXPathValue('false', $xpath, '//item/guid/@isPermaLink');
    }
}
