<?php

namespace Pharaonic\Rss\Tests\Unit\Extensions;

use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Extensions\DublinCore\Creator;
use Pharaonic\Rss\Extensions\DublinCore\DublinCoreExtension;
use Pharaonic\Rss\Tests\TestCase;

final class DublinCoreCreatorTest extends TestCase
{
    public function testDeclaresTheDublinCoreNamespace(): void
    {
        $creator = Creator::make('Moamen Eltouny');

        $this->assertInstanceOf(DublinCoreExtension::class, $creator);
        $this->assertSame('dc', $creator->prefix());
        $this->assertSame('http://purl.org/dc/elements/1.1/', $creator->namespaceUri());
        $this->assertSame('Moamen Eltouny', $creator->getName());
    }

    public function testWritesTheCreatorName(): void
    {
        $xml = $this->renderExtension(Creator::make('Tom & Jerry'));

        $this->assertStringContainsString('<dc:creator>Tom &amp; Jerry</dc:creator>', $xml);
        $this->assertXPathValue('Tom & Jerry', $this->xpath($xml), '/root/dc:creator');
    }

    public function testWritesArabicNames(): void
    {
        $this->assertXPathValue('مؤمن الطوني', $this->extensionXpath(Creator::make('مؤمن الطوني')), '/root/dc:creator');
    }

    public function testRejectsEmptyName(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The dc:creator name must not be empty.');

        Creator::make('');
    }
}
