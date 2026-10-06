<?php

namespace Pharaonic\Rss\Tests\Unit\Extensions;

use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Extensions\Media\Description;
use Pharaonic\Rss\Extensions\Media\MediaExtension;
use Pharaonic\Rss\Extensions\Media\TextConstruct;
use Pharaonic\Rss\Extensions\Media\Title;
use Pharaonic\Rss\Tests\TestCase;

final class MediaTextTest extends TestCase
{
    public function testTitleDeclaresTheMediaNamespace(): void
    {
        $title = Title::make('Launch video');

        $this->assertInstanceOf(MediaExtension::class, $title);
        $this->assertSame('media', $title->prefix());
        $this->assertSame('http://search.yahoo.com/mrss/', $title->namespaceUri());
        $this->assertSame('Launch video', $title->getText());
        $this->assertNull($title->getType());
    }

    public function testMakeReturnsTheConcreteClass(): void
    {
        $this->assertInstanceOf(Title::class, Title::make('A'));
        $this->assertInstanceOf(Description::class, Description::make('A'));
        $this->assertInstanceOf(Title::class, Title::make('A')->type(TextConstruct::TYPE_PLAIN));
    }

    public function testWritesTitle(): void
    {
        $xpath = $this->extensionXpath(Title::make('Launch & demo'));

        $this->assertXPathValue('Launch & demo', $xpath, '/root/media:title');
        $this->assertXPathMissing($xpath, '/root/media:title/@type');
    }

    public function testWritesHtmlDescription(): void
    {
        $html = '<p>A <b>bold</b> demo</p>';
        $xpath = $this->extensionXpath(Description::make($html)->type(Description::TYPE_HTML));

        $this->assertXPathValue($html, $xpath, '/root/media:description');
        $this->assertXPathValue('html', $xpath, '/root/media:description/@type');
    }

    public function testRejectsUnknownTypes(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The media:title type must be one of [plain, html], "markdown" given.');

        Title::make('A')->type('markdown');
    }

    public function testRejectsEmptyText(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The media:description text must not be empty.');

        Description::make('');
    }
}
