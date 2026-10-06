<?php

namespace Pharaonic\RSS\Tests\Unit\Elements;

use Pharaonic\RSS\Elements\Image;
use Pharaonic\RSS\Exceptions\InvalidElementException;
use Pharaonic\RSS\Tests\TestCase;

final class ImageTest extends TestCase
{
    public function testMakeStoresTheUrlAndLeavesOptionalFieldsUnset(): void
    {
        $image = Image::make('https://example.com/logo.png');

        $this->assertSame('https://example.com/logo.png', $image->getUrl());
        $this->assertNull($image->getTitle());
        $this->assertNull($image->getLink());
        $this->assertNull($image->getWidth());
        $this->assertNull($image->getHeight());
        $this->assertNull($image->getDescription());
    }

    public function testFluentSetters(): void
    {
        $image = Image::make('https://example.com/logo.png')
            ->title('Example')
            ->link('https://example.com')
            ->width(88)
            ->height(31)
            ->description('Example logo');

        $this->assertSame('Example', $image->getTitle());
        $this->assertSame('https://example.com', $image->getLink());
        $this->assertSame(88, $image->getWidth());
        $this->assertSame(31, $image->getHeight());
        $this->assertSame('Example logo', $image->getDescription());
    }

    public function testAcceptsTheMaximumSizes(): void
    {
        $image = Image::make('https://example.com/logo.png')->width(144)->height(400);

        $this->assertSame(144, $image->getWidth());
        $this->assertSame(400, $image->getHeight());
    }

    public function testRejectsEmptyUrl(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The image url must not be empty.');

        Image::make('');
    }

    /**
     * @return array<string, array{int}>
     */
    public static function invalidWidthProvider(): array
    {
        return ['zero' => [0], 'negative' => [-1], 'too wide' => [145]];
    }

    /**
     * @dataProvider invalidWidthProvider
     */
    public function testRejectsWidthOutsideTheRssLimit(int $width): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage(sprintf('The image width must be between 1 and 144, %d given.', $width));

        Image::make('https://example.com/logo.png')->width($width);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function invalidHeightProvider(): array
    {
        return ['zero' => [0], 'negative' => [-1], 'too tall' => [401]];
    }

    /**
     * @dataProvider invalidHeightProvider
     */
    public function testRejectsHeightOutsideTheRssLimit(int $height): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage(sprintf('The image height must be between 1 and 400, %d given.', $height));

        Image::make('https://example.com/logo.png')->height($height);
    }

    public function testSerializationWithAllFields(): void
    {
        $xpath = $this->feedXpath($this->minimalFeed()->image(
            Image::make('https://example.com/logo.png')
                ->title('Example')
                ->link('https://example.com')
                ->width(88)
                ->height(31)
                ->description('Example logo')
        ));

        $this->assertSame(
            ['url', 'title', 'link', 'width', 'height', 'description'],
            $this->childNames($xpath, '/rss/channel/image')
        );
        $this->assertXPathValue('https://example.com/logo.png', $xpath, '/rss/channel/image/url');
        $this->assertXPathValue('Example', $xpath, '/rss/channel/image/title');
        $this->assertXPathValue('https://example.com', $xpath, '/rss/channel/image/link');
        $this->assertXPathValue('88', $xpath, '/rss/channel/image/width');
        $this->assertXPathValue('31', $xpath, '/rss/channel/image/height');
        $this->assertXPathValue('Example logo', $xpath, '/rss/channel/image/description');
    }

    public function testRequiredTitleAndLinkFallBackToTheChannel(): void
    {
        $xpath = $this->feedXpath($this->minimalFeed()->image(Image::make('https://example.com/logo.png')));

        $this->assertSame(['url', 'title', 'link'], $this->childNames($xpath, '/rss/channel/image'));
        $this->assertXPathValue('Pharaonic', $xpath, '/rss/channel/image/title');
        $this->assertXPathValue('https://pharaonic.dev', $xpath, '/rss/channel/image/link');
    }
}
