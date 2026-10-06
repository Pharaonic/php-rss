<?php

namespace Pharaonic\RSS\Tests\Unit\Elements;

use Pharaonic\RSS\Elements\Category;
use Pharaonic\RSS\Exceptions\InvalidElementException;
use Pharaonic\RSS\Item;
use Pharaonic\RSS\Tests\TestCase;

final class CategoryTest extends TestCase
{
    public function testMakeStoresTheValue(): void
    {
        $category = Category::make('PHP');

        $this->assertSame('PHP', $category->getValue());
        $this->assertNull($category->getDomain());
    }

    public function testDomainIsOptional(): void
    {
        $category = Category::make('PHP')->domain('https://example.com/categories');

        $this->assertSame('https://example.com/categories', $category->getDomain());
        $this->assertNull($category->domain('')->getDomain());
        $this->assertNull($category->domain(null)->getDomain());
    }

    public function testZeroIsAValidValue(): void
    {
        $this->assertSame('0', Category::make('0')->getValue());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function emptyValueProvider(): array
    {
        return ['empty' => [''], 'blank' => ['  ']];
    }

    /**
     * @dataProvider emptyValueProvider
     */
    public function testRejectsEmptyValues(string $value): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The category value must not be empty.');

        Category::make($value);
    }

    public function testSerialization(): void
    {
        $xpath = $this->feedXpath($this->minimalFeed()->addItem(
            Item::make()
                ->title('Post')
                ->category(Category::make('Web & PHP')->domain('https://example.com/tags?a=1&b=2'))
                ->category('Plain')
        ));

        $this->assertXPathValue('Web & PHP', $xpath, '//item/category[1]');
        $this->assertXPathValue('https://example.com/tags?a=1&b=2', $xpath, '//item/category[1]/@domain');
        $this->assertXPathValue('Plain', $xpath, '//item/category[2]');
        $this->assertXPathMissing($xpath, '//item/category[2]/@domain');
    }
}
