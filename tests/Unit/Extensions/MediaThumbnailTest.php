<?php

namespace Pharaonic\RSS\Tests\Unit\Extensions;

use Pharaonic\RSS\Exceptions\InvalidElementException;
use Pharaonic\RSS\Extensions\Media\Thumbnail;
use Pharaonic\RSS\Tests\TestCase;

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

    public function testGettersReturnNullByDefault(): void
    {
        $thumbnail = Thumbnail::make('https://example.com/image.jpg');

        $this->assertSame('https://example.com/image.jpg', $thumbnail->getUrl());
        $this->assertNull($thumbnail->getWidth());
        $this->assertNull($thumbnail->getHeight());
        $this->assertNull($thumbnail->getTime());
    }

    public function testGettersReturnTheConfiguredValues(): void
    {
        $thumbnail = Thumbnail::make('https://example.com/image.jpg')
            ->width(1200)
            ->height(630)
            ->time('12:05:01.123');

        $this->assertSame(1200, $thumbnail->getWidth());
        $this->assertSame(630, $thumbnail->getHeight());
        $this->assertSame('12:05:01.123', $thumbnail->getTime());
    }

    public function testEmptyTimeIsReturnedAsNull(): void
    {
        $this->assertNull(Thumbnail::make('https://example.com/image.jpg')->time('')->getTime());
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
