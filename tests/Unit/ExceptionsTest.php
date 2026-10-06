<?php

namespace Pharaonic\Rss\Tests\Unit;

use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Exceptions\InvalidFeedException;
use Pharaonic\Rss\Exceptions\InvalidItemException;
use Pharaonic\Rss\Exceptions\RssException;
use Pharaonic\Rss\Tests\TestCase;
use RuntimeException;

final class ExceptionsTest extends TestCase
{
    public function testHierarchy(): void
    {
        $this->assertInstanceOf(RuntimeException::class, new RssException());
        $this->assertInstanceOf(RssException::class, InvalidFeedException::missingTitle());
        $this->assertInstanceOf(RssException::class, InvalidItemException::missingTitleOrDescription());
        $this->assertInstanceOf(RssException::class, InvalidElementException::emptyValue('guid', 'value'));
    }

    public function testNamedConstructorsReturnTheirOwnType(): void
    {
        $this->assertInstanceOf(InvalidFeedException::class, InvalidFeedException::missingLink());
        $this->assertInstanceOf(InvalidFeedException::class, InvalidFeedException::missingDescription());
        $this->assertInstanceOf(InvalidItemException::class, InvalidItemException::ambiguousGuidPermalink());
        $this->assertInstanceOf(InvalidElementException::class, InvalidElementException::invalidEnclosureLength(-1));
        $this->assertInstanceOf(
            InvalidElementException::class,
            InvalidElementException::invalidNamespace('xml', 'urn:x', 'the prefix is reserved by XML')
        );
    }

    public function testMessages(): void
    {
        $this->assertSame(
            'An RSS item requires at least a title or a description.',
            InvalidItemException::missingTitleOrDescription()->getMessage()
        );
        $this->assertSame(
            'The RSS item at position 3 requires at least a title or a description.',
            InvalidItemException::missingTitleOrDescription(3)->getMessage()
        );
        $this->assertSame(
            'Invalid XML namespace "xml" => "urn:x": the prefix is reserved by XML.',
            InvalidElementException::invalidNamespace('xml', 'urn:x', 'the prefix is reserved by XML')->getMessage()
        );
    }
}
