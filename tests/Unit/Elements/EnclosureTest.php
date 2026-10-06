<?php

namespace Pharaonic\Rss\Tests\Unit\Elements;

use Pharaonic\Rss\Elements\Enclosure;
use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Item;
use Pharaonic\Rss\Tests\TestCase;

final class EnclosureTest extends TestCase
{
    public function testMakeStoresAllValues(): void
    {
        $enclosure = Enclosure::make('https://example.com/audio.mp3', 123456, 'audio/mpeg');

        $this->assertSame('https://example.com/audio.mp3', $enclosure->getUrl());
        $this->assertSame(123456, $enclosure->getLength());
        $this->assertSame('audio/mpeg', $enclosure->getType());
    }

    public function testZeroLengthIsAllowedForUnknownSizes(): void
    {
        $this->assertSame(0, Enclosure::make('https://example.com/live.mp3', 0, 'audio/mpeg')->getLength());
    }

    public function testRejectsNegativeLength(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The enclosure length must be zero or a positive number of bytes, -1 given.');

        Enclosure::make('https://example.com/audio.mp3', -1, 'audio/mpeg');
    }

    public function testRejectsEmptyUrl(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The enclosure url must not be empty.');

        Enclosure::make('', 1, 'audio/mpeg');
    }

    public function testRejectsEmptyType(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The enclosure type must not be empty.');

        Enclosure::make('https://example.com/audio.mp3', 1, ' ');
    }

    public function testSerialization(): void
    {
        $xpath = $this->feedXpath($this->minimalFeed()->addItem(
            Item::make()
                ->title('Episode')
                ->enclosure(Enclosure::make('https://example.com/audio.mp3?a=1&b=2', 123456, 'audio/mpeg'))
        ));

        $this->assertXPathCount(1, $xpath, '//item/enclosure');
        $this->assertXPathValue('https://example.com/audio.mp3?a=1&b=2', $xpath, '//item/enclosure/@url');
        $this->assertXPathValue('123456', $xpath, '//item/enclosure/@length');
        $this->assertXPathValue('audio/mpeg', $xpath, '//item/enclosure/@type');
        $this->assertXPathValue('', $xpath, '//item/enclosure');
    }
}
