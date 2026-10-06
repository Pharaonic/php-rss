<?php

namespace Pharaonic\RSS\Tests;

use DOMDocument;
use DOMNode;
use DOMXPath;
use LibXMLError;
use Pharaonic\RSS\Contracts\Extension;
use Pharaonic\RSS\Feed;
use PHPUnit\Framework\TestCase as BaseTestCase;
use XMLWriter;

abstract class TestCase extends BaseTestCase
{
    protected const NAMESPACES = [
        'atom' => 'http://www.w3.org/2005/Atom',
        'content' => 'http://purl.org/rss/1.0/modules/content/',
        'dc' => 'http://purl.org/dc/elements/1.1/',
        'media' => 'http://search.yahoo.com/mrss/',
    ];

    protected function minimalFeed(): Feed
    {
        return Feed::make()
            ->title('Pharaonic')
            ->link('https://pharaonic.dev')
            ->description('Rooted in History. Engineering the Future.');
    }

    /**
     * Parse the XML, failing the test when it is not well-formed.
     */
    protected function parse(string $xml): DOMDocument
    {
        $document = new DOMDocument();

        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadXML($xml);
        $errors = array_map(static function (LibXMLError $error): string {
            return trim($error->message);
        }, libxml_get_errors());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $this->assertTrue($loaded, 'The XML is not well-formed: ' . implode('; ', $errors));

        return $document;
    }

    protected function xpath(string $xml): DOMXPath
    {
        $xpath = new DOMXPath($this->parse($xml));

        foreach (self::NAMESPACES as $prefix => $uri) {
            $xpath->registerNamespace($prefix, $uri);
        }

        return $xpath;
    }

    protected function feedXpath(Feed $feed): DOMXPath
    {
        return $this->xpath($feed->toXml());
    }

    /**
     * Write a single extension inside a root element declaring its namespace.
     */
    protected function renderExtension(Extension $extension): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('root');
        $writer->writeAttribute('xmlns:' . $extension->prefix(), $extension->namespaceUri());
        $extension->write($writer);
        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory();
    }

    protected function extensionXpath(Extension $extension): DOMXPath
    {
        return $this->xpath($this->renderExtension($extension));
    }

    protected function assertXPathCount(int $expected, DOMXPath $xpath, string $expression): void
    {
        $nodes = $xpath->query($expression);

        $this->assertNotFalse($nodes, sprintf('Invalid XPath expression "%s".', $expression));
        $this->assertCount($expected, $nodes, sprintf('Unexpected number of nodes for "%s".', $expression));
    }

    /**
     * Assert that exactly one node matches and that its text content is the expected value.
     */
    protected function assertXPathValue(string $expected, DOMXPath $xpath, string $expression): void
    {
        $this->assertXPathCount(1, $xpath, $expression);

        $this->assertSame($expected, $this->xpathValue($xpath, $expression));
    }

    protected function assertXPathMissing(DOMXPath $xpath, string $expression): void
    {
        $this->assertXPathCount(0, $xpath, $expression);
    }

    protected function xpathValue(DOMXPath $xpath, string $expression): string
    {
        $nodes = $this->nodes($xpath, $expression);

        $this->assertNotEmpty($nodes, sprintf('No node matches "%s".', $expression));

        return $nodes[0]->textContent;
    }

    /**
     * Text content of every node matching the expression, in document order.
     *
     * @return list<string>
     */
    protected function xpathValues(DOMXPath $xpath, string $expression): array
    {
        return array_map(static function (DOMNode $node): string {
            return $node->textContent;
        }, $this->nodes($xpath, $expression));
    }

    /**
     * Qualified names of the child elements of the nodes matching the expression, in document order.
     *
     * @return list<string>
     */
    protected function childNames(DOMXPath $xpath, string $expression): array
    {
        return array_map(static function (DOMNode $node): string {
            return $node->nodeName;
        }, $this->nodes($xpath, $expression . '/*'));
    }

    /**
     * @return list<DOMNode>
     */
    private function nodes(DOMXPath $xpath, string $expression): array
    {
        $result = $xpath->query($expression);

        $this->assertNotFalse($result, sprintf('Invalid XPath expression "%s".', $expression));

        $nodes = [];
        foreach ($result as $node) {
            if ($node instanceof DOMNode) {
                $nodes[] = $node;
            }
        }

        return $nodes;
    }
}
