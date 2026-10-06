<?php

namespace Pharaonic\Rss\Tests\Unit\Extensions;

use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Extensions\Media\Content;
use Pharaonic\Rss\Extensions\Media\Description;
use Pharaonic\Rss\Extensions\Media\Thumbnail;
use Pharaonic\Rss\Extensions\Media\Title;
use Pharaonic\Rss\Tests\TestCase;

final class MediaContentTest extends TestCase
{
    public function testDeclaresTheMediaNamespace(): void
    {
        $content = Content::make('https://example.com/video.mp4');

        $this->assertSame('media', $content->prefix());
        $this->assertSame('http://search.yahoo.com/mrss/', $content->namespaceUri());
    }

    public function testWritesOnlyTheUrlByDefault(): void
    {
        $xpath = $this->extensionXpath(Content::make('https://example.com/video.mp4'));

        $this->assertXPathValue('https://example.com/video.mp4', $xpath, '/root/media:content/@url');
        $this->assertXPathCount(1, $xpath, '/root/media:content/@*');
        $this->assertXPathCount(0, $xpath, '/root/media:content/*');
    }

    public function testWritesAllAttributesInOrder(): void
    {
        $xml = $this->renderExtension(
            Content::make('https://example.com/video.mp4')
                ->fileSize(12216320)
                ->type('video/mp4')
                ->medium(Content::MEDIUM_VIDEO)
                ->isDefault()
                ->expression(Content::EXPRESSION_FULL)
                ->bitrate(128)
                ->duration(185)
                ->width(1920)
                ->height(1080)
                ->lang('en')
        );

        $this->assertStringContainsString(
            '<media:content url="https://example.com/video.mp4" fileSize="12216320" type="video/mp4" '
            . 'medium="video" isDefault="true" expression="full" bitrate="128" duration="185" '
            . 'width="1920" height="1080" lang="en"/>',
            $xml
        );
    }

    public function testIsDefaultCanBeFalse(): void
    {
        $xpath = $this->extensionXpath(Content::make('https://example.com/video.mp4')->isDefault(false));

        $this->assertXPathValue('false', $xpath, '/root/media:content/@isDefault');
    }

    public function testWritesNestedElements(): void
    {
        $xpath = $this->extensionXpath(
            Content::make('https://example.com/video.mp4')
                ->type('video/mp4')
                ->title(Title::make('Launch video'))
                ->description(Description::make('Behind the scenes'))
                ->thumbnail(Thumbnail::make('https://example.com/1.jpg'))
                ->thumbnail(Thumbnail::make('https://example.com/2.jpg'))
        );

        $this->assertSame(
            ['media:title', 'media:description', 'media:thumbnail', 'media:thumbnail'],
            $this->childNames($xpath, '/root/media:content')
        );
        $this->assertXPathValue('Launch video', $xpath, '/root/media:content/media:title');
        $this->assertXPathValue('Behind the scenes', $xpath, '/root/media:content/media:description');
        $this->assertSame(
            ['https://example.com/1.jpg', 'https://example.com/2.jpg'],
            $this->xpathValues($xpath, '/root/media:content/media:thumbnail/@url')
        );
    }

    public function testRejectsEmptyUrl(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The media:content url must not be empty.');

        Content::make('');
    }

    public function testRejectsUnknownMedium(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage(
            'The media:content medium must be one of [image, audio, video, document, executable], "movie" given.'
        );

        Content::make('https://example.com/video.mp4')->medium('movie');
    }

    public function testRejectsUnknownExpression(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The media:content expression must be one of [sample, full, nonstop]');

        Content::make('https://example.com/video.mp4')->expression('partial');
    }

    /**
     * @return array<string, array{callable(Content): Content, string}>
     */
    public static function invalidNumberProvider(): array
    {
        return [
            'fileSize' => [static fn (Content $content) => $content->fileSize(-1), 'fileSize must be at least 0, -1'],
            'bitrate' => [static fn (Content $content) => $content->bitrate(-1), 'bitrate must be at least 0, -1'],
            'duration' => [static fn (Content $content) => $content->duration(-1), 'duration must be at least 0, -1'],
            'width' => [static fn (Content $content) => $content->width(0), 'width must be at least 1, 0'],
            'height' => [static fn (Content $content) => $content->height(0), 'height must be at least 1, 0'],
        ];
    }

    /**
     * @dataProvider invalidNumberProvider
     *
     * @param callable(Content): Content $set
     */
    public function testRejectsInvalidNumbers(callable $set, string $message): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage(sprintf('The media:content %s given.', $message));

        $set(Content::make('https://example.com/video.mp4'));
    }

    public function testZeroIsAllowedForSizesAndDurations(): void
    {
        $xpath = $this->extensionXpath(
            Content::make('https://example.com/live.mp3')->fileSize(0)->bitrate(0)->duration(0)
        );

        $this->assertXPathValue('0', $xpath, '/root/media:content/@fileSize');
        $this->assertXPathValue('0', $xpath, '/root/media:content/@bitrate');
        $this->assertXPathValue('0', $xpath, '/root/media:content/@duration');
    }
}
