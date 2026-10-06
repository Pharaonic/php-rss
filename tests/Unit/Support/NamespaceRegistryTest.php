<?php

namespace Pharaonic\Rss\Tests\Unit\Support;

use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Support\NamespaceRegistry;
use Pharaonic\Rss\Tests\TestCase;

final class NamespaceRegistryTest extends TestCase
{
    public function testStartsEmpty(): void
    {
        $registry = new NamespaceRegistry();

        $this->assertSame([], $registry->all());
        $this->assertFalse($registry->has('atom'));
    }

    public function testRegistersMultipleNamespacesInOrder(): void
    {
        $registry = (new NamespaceRegistry())
            ->register('media', self::NAMESPACES['media'])
            ->register('atom', self::NAMESPACES['atom'])
            ->register('dc', self::NAMESPACES['dc']);

        $this->assertSame([
            'media' => self::NAMESPACES['media'],
            'atom' => self::NAMESPACES['atom'],
            'dc' => self::NAMESPACES['dc'],
        ], $registry->all());
        $this->assertTrue($registry->has('atom'));
    }

    public function testDeduplicatesIdenticalDeclarations(): void
    {
        $registry = (new NamespaceRegistry())
            ->register('atom', self::NAMESPACES['atom'])
            ->register('atom', self::NAMESPACES['atom']);

        $this->assertSame(['atom' => self::NAMESPACES['atom']], $registry->all());
    }

    public function testRejectsAPrefixBoundToAnotherUri(): void
    {
        $registry = (new NamespaceRegistry())->register('atom', self::NAMESPACES['atom']);

        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage(
            'The XML namespace prefix "atom" is already bound to "http://www.w3.org/2005/Atom" '
            . 'and cannot also be bound to "https://example.com/not-atom".'
        );

        $registry->register('atom', 'https://example.com/not-atom');
    }

    public function testAllowsOneUriUnderDifferentPrefixes(): void
    {
        $registry = (new NamespaceRegistry())
            ->register('atom', self::NAMESPACES['atom'])
            ->register('a10', self::NAMESPACES['atom']);

        $this->assertCount(2, $registry->all());
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function invalidNamespaceProvider(): array
    {
        return [
            'empty prefix' => ['', 'https://example.com/ns', 'not a valid XML name'],
            'prefix with colon' => ['a:b', 'https://example.com/ns', 'not a valid XML name'],
            'prefix starting with digit' => ['1ns', 'https://example.com/ns', 'not a valid XML name'],
            'prefix with space' => ['my ns', 'https://example.com/ns', 'not a valid XML name'],
            'reserved xml prefix' => ['xml', 'https://example.com/ns', 'reserved'],
            'reserved xmlns prefix' => ['xmlns', 'https://example.com/ns', 'reserved'],
            'reserved prefix in uppercase' => ['XMLNS', 'https://example.com/ns', 'reserved'],
            'empty uri' => ['ns', '', 'must not be empty'],
            'blank uri' => ['ns', '   ', 'must not be empty'],
            'invalid uri characters' => ['ns', "https://example.com/\x01", 'invalid characters'],
        ];
    }

    /**
     * @dataProvider invalidNamespaceProvider
     */
    public function testRejectsInvalidNamespaces(string $prefix, string $uri, string $reason): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage($reason);

        (new NamespaceRegistry())->register($prefix, $uri);
    }
}
