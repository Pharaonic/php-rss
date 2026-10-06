<?php

namespace Pharaonic\RSS\Writer;

use Pharaonic\RSS\Contracts\Extension;
use Pharaonic\RSS\Elements\Category;
use Pharaonic\RSS\Elements\Cloud;
use Pharaonic\RSS\Elements\Enclosure;
use Pharaonic\RSS\Elements\Guid;
use Pharaonic\RSS\Elements\Image;
use Pharaonic\RSS\Elements\Source;
use Pharaonic\RSS\Elements\TextInput;
use Pharaonic\RSS\Exceptions\InvalidFeedException;
use Pharaonic\RSS\Exceptions\InvalidItemException;
use Pharaonic\RSS\Exceptions\RssException;
use Pharaonic\RSS\Feed;
use Pharaonic\RSS\Item;
use Pharaonic\RSS\Support\DateFormatter;
use Pharaonic\RSS\Support\NamespaceRegistry;
use Pharaonic\RSS\Support\Xml;
use XMLWriter;

/**
 * Serializes a Feed into an RSS 2.0 XML document.
 *
 * Channel and item elements are written in the order of the RSS 2.0
 * specification. Items and extensions keep their insertion order, and
 * extensions are written after the core elements of their parent.
 */
final class RssWriter
{
    private bool $pretty;

    /**
     * @param bool $pretty Indent the output with two spaces per level.
     */
    public function __construct(bool $pretty = true)
    {
        $this->pretty = $pretty;
    }

    /**
     * @throws RssException When the feed or one of its items is invalid.
     */
    public function write(Feed $feed): string
    {
        $this->validate($feed);

        $namespaces = $this->collectNamespaces($feed);

        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent($this->pretty);
        $writer->setIndentString('  ');
        $writer->startDocument('1.0', 'UTF-8');

        $writer->startElement('rss');
        $writer->writeAttribute('version', '2.0');

        foreach ($namespaces->all() as $prefix => $uri) {
            $writer->writeAttribute('xmlns:' . $prefix, $uri);
        }

        $this->writeChannel($writer, $feed);

        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory();
    }

    /**
     * @throws RssException
     */
    private function validate(Feed $feed): void
    {
        if (self::isBlank($feed->getTitle())) {
            throw InvalidFeedException::missingTitle();
        }

        if (self::isBlank($feed->getLink())) {
            throw InvalidFeedException::missingLink();
        }

        if (self::isBlank($feed->getDescription())) {
            throw InvalidFeedException::missingDescription();
        }

        foreach ($feed->getItems() as $position => $item) {
            if (self::isBlank($item->getTitle()) && self::isBlank($item->getDescription())) {
                throw InvalidItemException::missingTitleOrDescription($position);
            }
        }
    }

    /**
     * @throws RssException
     */
    private function collectNamespaces(Feed $feed): NamespaceRegistry
    {
        $namespaces = new NamespaceRegistry();

        foreach ($feed->getExtensions() as $extension) {
            $namespaces->register($extension->prefix(), $extension->namespaceUri());
        }

        foreach ($feed->getItems() as $item) {
            foreach ($item->getExtensions() as $extension) {
                $namespaces->register($extension->prefix(), $extension->namespaceUri());
            }
        }

        return $namespaces;
    }

    /**
     * @throws RssException
     */
    private function writeChannel(XMLWriter $writer, Feed $feed): void
    {
        $writer->startElement('channel');

        Xml::writeElement($writer, 'title', (string) $feed->getTitle());
        Xml::writeElement($writer, 'link', (string) $feed->getLink());
        Xml::writeElement($writer, 'description', (string) $feed->getDescription());

        $this->writeOptional($writer, 'language', $feed->getLanguage());
        $this->writeOptional($writer, 'copyright', $feed->getCopyright());
        $this->writeOptional($writer, 'managingEditor', $feed->getManagingEditor());
        $this->writeOptional($writer, 'webMaster', $feed->getWebMaster());

        if ($feed->getPublishedAt() !== null) {
            Xml::writeElement($writer, 'pubDate', DateFormatter::format($feed->getPublishedAt()));
        }

        if ($feed->getLastBuildAt() !== null) {
            Xml::writeElement($writer, 'lastBuildDate', DateFormatter::format($feed->getLastBuildAt()));
        }

        $this->writeCategories($writer, $feed->getCategories());
        $this->writeOptional($writer, 'generator', $feed->getGenerator());
        $this->writeOptional($writer, 'docs', $feed->getDocs());

        if ($feed->getCloud() !== null) {
            $this->writeCloud($writer, $feed->getCloud());
        }

        if ($feed->getTtl() !== null) {
            Xml::writeElement($writer, 'ttl', (string) $feed->getTtl());
        }

        if ($feed->getImage() !== null) {
            $this->writeImage($writer, $feed->getImage(), $feed);
        }

        $this->writeOptional($writer, 'rating', $feed->getRating());

        if ($feed->getTextInput() !== null) {
            $this->writeTextInput($writer, $feed->getTextInput());
        }

        if ($feed->getSkipHours() !== []) {
            $writer->startElement('skipHours');
            foreach ($feed->getSkipHours() as $hour) {
                Xml::writeElement($writer, 'hour', (string) $hour);
            }
            $writer->endElement();
        }

        if ($feed->getSkipDays() !== []) {
            $writer->startElement('skipDays');
            foreach ($feed->getSkipDays() as $day) {
                Xml::writeElement($writer, 'day', $day);
            }
            $writer->endElement();
        }

        $this->writeExtensions($writer, $feed->getExtensions());

        foreach ($feed->getItems() as $item) {
            $this->writeItem($writer, $item);
        }

        $writer->endElement();
    }

    /**
     * @throws RssException
     */
    private function writeItem(XMLWriter $writer, Item $item): void
    {
        $writer->startElement('item');

        $this->writeOptional($writer, 'title', $item->getTitle());
        $this->writeOptional($writer, 'link', $item->getLink());
        $this->writeOptional($writer, 'description', $item->getDescription());
        $this->writeOptional($writer, 'author', $item->getAuthor());
        $this->writeCategories($writer, $item->getCategories());
        $this->writeOptional($writer, 'comments', $item->getComments());

        if ($item->getEnclosure() !== null) {
            $this->writeEnclosure($writer, $item->getEnclosure());
        }

        if ($item->getGuid() !== null) {
            $this->writeGuid($writer, $item->getGuid());
        }

        if ($item->getPublishedAt() !== null) {
            Xml::writeElement($writer, 'pubDate', DateFormatter::format($item->getPublishedAt()));
        }

        if ($item->getSource() !== null) {
            $this->writeSource($writer, $item->getSource());
        }

        $this->writeExtensions($writer, $item->getExtensions());

        $writer->endElement();
    }

    /**
     * @throws RssException
     */
    private function writeOptional(XMLWriter $writer, string $name, ?string $value): void
    {
        if ($value !== null) {
            Xml::writeElement($writer, $name, $value);
        }
    }

    /**
     * @param list<Category> $categories
     *
     * @throws RssException
     */
    private function writeCategories(XMLWriter $writer, array $categories): void
    {
        foreach ($categories as $category) {
            $writer->startElement('category');

            if ($category->getDomain() !== null) {
                Xml::writeAttribute($writer, 'domain', $category->getDomain());
            }

            Xml::writeText($writer, $category->getValue(), '<category>');
            $writer->endElement();
        }
    }

    /**
     * @throws RssException
     */
    private function writeCloud(XMLWriter $writer, Cloud $cloud): void
    {
        $writer->startElement('cloud');
        Xml::writeAttribute($writer, 'domain', $cloud->getDomain());
        Xml::writeAttribute($writer, 'port', (string) $cloud->getPort());
        Xml::writeAttribute($writer, 'path', $cloud->getPath());
        Xml::writeAttribute($writer, 'registerProcedure', $cloud->getRegisterProcedure());
        Xml::writeAttribute($writer, 'protocol', $cloud->getProtocol());
        $writer->endElement();
    }

    /**
     * @throws RssException
     */
    private function writeImage(XMLWriter $writer, Image $image, Feed $feed): void
    {
        $writer->startElement('image');
        Xml::writeElement($writer, 'url', $image->getUrl());
        Xml::writeElement($writer, 'title', $image->getTitle() ?? (string) $feed->getTitle());
        Xml::writeElement($writer, 'link', $image->getLink() ?? (string) $feed->getLink());

        if ($image->getWidth() !== null) {
            Xml::writeElement($writer, 'width', (string) $image->getWidth());
        }

        if ($image->getHeight() !== null) {
            Xml::writeElement($writer, 'height', (string) $image->getHeight());
        }

        $this->writeOptional($writer, 'description', $image->getDescription());
        $writer->endElement();
    }

    /**
     * @throws RssException
     */
    private function writeTextInput(XMLWriter $writer, TextInput $textInput): void
    {
        $writer->startElement('textInput');
        Xml::writeElement($writer, 'title', $textInput->getTitle());
        Xml::writeElement($writer, 'description', $textInput->getDescription());
        Xml::writeElement($writer, 'name', $textInput->getName());
        Xml::writeElement($writer, 'link', $textInput->getLink());
        $writer->endElement();
    }

    /**
     * @throws RssException
     */
    private function writeEnclosure(XMLWriter $writer, Enclosure $enclosure): void
    {
        $writer->startElement('enclosure');
        Xml::writeAttribute($writer, 'url', $enclosure->getUrl());
        Xml::writeAttribute($writer, 'length', (string) $enclosure->getLength());
        Xml::writeAttribute($writer, 'type', $enclosure->getType());
        $writer->endElement();
    }

    /**
     * @throws RssException
     */
    private function writeGuid(XMLWriter $writer, Guid $guid): void
    {
        $writer->startElement('guid');

        // isPermaLink defaults to true in RSS 2.0, so only the false case is written.
        if (!$guid->isPermaLink()) {
            $writer->writeAttribute('isPermaLink', 'false');
        }

        Xml::writeText($writer, $guid->getValue(), '<guid>');
        $writer->endElement();
    }

    /**
     * @throws RssException
     */
    private function writeSource(XMLWriter $writer, Source $source): void
    {
        $writer->startElement('source');
        Xml::writeAttribute($writer, 'url', $source->getUrl());
        Xml::writeText($writer, $source->getTitle(), '<source>');
        $writer->endElement();
    }

    /**
     * @param list<Extension> $extensions
     *
     * @throws RssException
     */
    private function writeExtensions(XMLWriter $writer, array $extensions): void
    {
        foreach ($extensions as $extension) {
            $extension->write($writer);
        }
    }

    private static function isBlank(?string $value): bool
    {
        return $value === null || trim($value) === '';
    }
}
