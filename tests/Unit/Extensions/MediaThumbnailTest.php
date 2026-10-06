<?php

namespace Pharaonic\Rss\Tests\Unit\Extensions;

use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Extensions\Media\Thumbnail;
use Pharaonic\Rss\Tests\TestCase;

final class MediaThumbnailTest extends TestCase
{
    public function testDeclaresTheMediaNamespace(): void
    {
        $thumbnail = Thumbnail::make('https://example.com/image.jpg');

        $this->assertSame('media', $thumbnail->prefix());
        $this->assertSame('http://search.yahoo.com/mrss/', $thumbnail->namespaceUri());
    }

    public function testWritesOnlyTheUrlByDefault(): void
    {
        $xpath = $this->extensionXpath(Thumbnail::make('https://example.com/image.jpg'));

        $this->assertXPathValue('https://example.com/image.jpg', $xpath, '/root/media:thumbnail/@url');
        $this->assertXPathCount(1, $xpath, '/root/media:thumbnail/@*');
    }

    public function testWritesAllAttributes(): void
    {
        $xml = $this->renderExtension(
            Thumbnail::make('https://example.com/image.jpg')->width(1200)->height(630)->time('12:05:01.123')
        );

        $this->assertStringContainsString(
            '<media:thumbnail url="https://example.com/image.jpg" width="1200" height="630" time="12:05:01.123"/>',
            $xml
        );
    }

    public function testRejectsEmptyUrl(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The media:thumbnail url must not be empty.');

        Thumbnail::make('');
    }

    public function testRejectsNonPositiveWidth(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The media:thumbnail width must be at least 1, 0 given.');

        Thumbnail::make('https://example.com/image.jpg')->width(0);
    }

    public function testRejectsNonPositiveHeight(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The media:thumbnail height must be at least 1, -5 given.');

        Thumbnail::make('https://example.com/image.jpg')->height(-5);
    }
}
