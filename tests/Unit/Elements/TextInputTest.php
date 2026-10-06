<?php

namespace Pharaonic\Rss\Tests\Unit\Elements;

use Pharaonic\Rss\Elements\TextInput;
use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Tests\TestCase;

final class TextInputTest extends TestCase
{
    public function testMakeStoresAllValues(): void
    {
        $textInput = TextInput::make('Search', 'Search the archive', 'q', 'https://example.com/search');

        $this->assertSame('Search', $textInput->getTitle());
        $this->assertSame('Search the archive', $textInput->getDescription());
        $this->assertSame('q', $textInput->getName());
        $this->assertSame('https://example.com/search', $textInput->getLink());
    }

    /**
     * @return array<string, array{string, string, string, string, string}>
     */
    public static function missingFieldProvider(): array
    {
        return [
            'title' => ['', 'Search the archive', 'q', 'https://example.com/search', 'title'],
            'description' => ['Search', ' ', 'q', 'https://example.com/search', 'description'],
            'name' => ['Search', 'Search the archive', '', 'https://example.com/search', 'name'],
            'link' => ['Search', 'Search the archive', 'q', '', 'link'],
        ];
    }

    /**
     * @dataProvider missingFieldProvider
     */
    public function testAllFieldsAreRequired(
        string $title,
        string $description,
        string $name,
        string $link,
        string $field
    ): void {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage(sprintf('The textInput %s must not be empty.', $field));

        TextInput::make($title, $description, $name, $link);
    }

    public function testSerialization(): void
    {
        $xpath = $this->feedXpath($this->minimalFeed()->textInput(
            TextInput::make('Search', 'Search the archive', 'q', 'https://example.com/search')
        ));

        $this->assertSame(
            ['title', 'description', 'name', 'link'],
            $this->childNames($xpath, '/rss/channel/textInput')
        );
        $this->assertXPathValue('Search', $xpath, '/rss/channel/textInput/title');
        $this->assertXPathValue('Search the archive', $xpath, '/rss/channel/textInput/description');
        $this->assertXPathValue('q', $xpath, '/rss/channel/textInput/name');
        $this->assertXPathValue('https://example.com/search', $xpath, '/rss/channel/textInput/link');
    }
}
