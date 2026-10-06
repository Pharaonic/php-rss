<?php

namespace Pharaonic\RSS;

use DateTimeImmutable;
use DateTimeInterface;
use Pharaonic\RSS\Contracts\Extension;
use Pharaonic\RSS\Elements\Category;
use Pharaonic\RSS\Elements\Cloud;
use Pharaonic\RSS\Elements\Image;
use Pharaonic\RSS\Elements\TextInput;
use Pharaonic\RSS\Exceptions\InvalidFeedException;
use Pharaonic\RSS\Exceptions\RssException;
use Pharaonic\RSS\Support\Day;
use Pharaonic\RSS\Writer\RssWriter;

/**
 * An RSS 2.0 feed: the <channel> element and its items.
 *
 * Optional text fields set to null or to an empty string are omitted from
 * the output. Required fields (title, link, description) are validated when
 * the feed is serialized, so setters can be called in any order.
 */
final class Feed
{
    private ?string $title = null;

    private ?string $link = null;

    private ?string $description = null;

    private ?string $language = null;

    private ?string $copyright = null;

    private ?string $managingEditor = null;

    private ?string $webMaster = null;

    private ?DateTimeImmutable $publishedAt = null;

    private ?DateTimeImmutable $lastBuildAt = null;

    /** @var list<Category> */
    private array $categories = [];

    private ?string $generator = null;

    private ?string $docs = null;

    private ?Cloud $cloud = null;

    private ?int $ttl = null;

    private ?Image $image = null;

    private ?string $rating = null;

    private ?TextInput $textInput = null;

    /** @var list<int> */
    private array $skipHours = [];

    /** @var list<string> */
    private array $skipDays = [];

    /** @var list<Item> */
    private array $items = [];

    /** @var list<Extension> */
    private array $extensions = [];

    public static function make(): self
    {
        return new self();
    }

    /**
     * Name of the channel. Required.
     */
    public function title(?string $title): self
    {
        $this->title = self::normalize($title);

        return $this;
    }

    /**
     * URL of the website the channel corresponds to. Required.
     */
    public function link(?string $link): self
    {
        $this->link = self::normalize($link);

        return $this;
    }

    /**
     * Phrase or sentence describing the channel. Required.
     */
    public function description(?string $description): self
    {
        $this->description = self::normalize($description);

        return $this;
    }

    /**
     * Language of the channel, e.g. "en-US" or "ar-EG".
     */
    public function language(?string $language): self
    {
        $this->language = self::normalize($language);

        return $this;
    }

    public function copyright(?string $copyright): self
    {
        $this->copyright = self::normalize($copyright);

        return $this;
    }

    /**
     * Email address of the person responsible for the editorial content,
     * conventionally "editor@example.com (Editor Name)".
     */
    public function managingEditor(?string $managingEditor): self
    {
        $this->managingEditor = self::normalize($managingEditor);

        return $this;
    }

    /**
     * Email address of the person responsible for technical issues,
     * conventionally "webmaster@example.com (Webmaster Name)".
     */
    public function webMaster(?string $webMaster): self
    {
        $this->webMaster = self::normalize($webMaster);

        return $this;
    }

    /**
     * Publication date of the channel content (<pubDate>).
     */
    public function publishedAt(?DateTimeInterface $date): self
    {
        $this->publishedAt = $date === null ? null : DateTimeImmutable::createFromInterface($date);

        return $this;
    }

    /**
     * Last time the channel content changed (<lastBuildDate>).
     */
    public function lastBuildAt(?DateTimeInterface $date): self
    {
        $this->lastBuildAt = $date === null ? null : DateTimeImmutable::createFromInterface($date);

        return $this;
    }

    /**
     * Add a category, either as a plain name or as a Category with a domain.
     *
     * @throws RssException
     */
    public function category(Category|string $category): self
    {
        $this->categories[] = is_string($category) ? Category::make($category) : $category;

        return $this;
    }

    /**
     * Program used to generate the channel. Not set by default.
     */
    public function generator(?string $generator): self
    {
        $this->generator = self::normalize($generator);

        return $this;
    }

    /**
     * URL of the documentation for the RSS format used by the channel.
     */
    public function docs(?string $docs): self
    {
        $this->docs = self::normalize($docs);

        return $this;
    }

    public function cloud(?Cloud $cloud): self
    {
        $this->cloud = $cloud;

        return $this;
    }

    /**
     * Number of minutes the channel can be cached before refreshing.
     *
     * @throws InvalidFeedException
     */
    public function ttl(?int $minutes): self
    {
        if ($minutes !== null && $minutes < 0) {
            throw InvalidFeedException::invalidTtl($minutes);
        }

        $this->ttl = $minutes;

        return $this;
    }

    public function image(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * PICS rating of the channel.
     */
    public function rating(?string $rating): self
    {
        $this->rating = self::normalize($rating);

        return $this;
    }

    public function textInput(?TextInput $textInput): self
    {
        $this->textInput = $textInput;

        return $this;
    }

    /**
     * Add an hour (0-23, GMT) in which aggregators may skip reading the channel.
     *
     * @throws InvalidFeedException
     */
    public function skipHour(int $hour): self
    {
        if ($hour < 0 || $hour > 23) {
            throw InvalidFeedException::invalidSkipHour($hour);
        }

        if (!in_array($hour, $this->skipHours, true)) {
            $this->skipHours[] = $hour;
        }

        return $this;
    }

    /**
     * Add a day in which aggregators may skip reading the channel.
     *
     * @param string $day One of the Day constants, e.g. Day::SUNDAY or "Sunday".
     *
     * @throws InvalidFeedException
     */
    public function skipDay(string $day): self
    {
        if (!Day::isValid($day)) {
            throw InvalidFeedException::invalidSkipDay($day, Day::all());
        }

        if (!in_array($day, $this->skipDays, true)) {
            $this->skipDays[] = $day;
        }

        return $this;
    }

    public function addItem(Item $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @param iterable<Item> $items
     */
    public function addItems(iterable $items): self
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * Attach a namespaced extension to the channel, e.g. an Atom self link.
     */
    public function extension(Extension $extension): self
    {
        $this->extensions[] = $extension;

        return $this;
    }

    /**
     * Serialize the feed as an RSS 2.0 XML document.
     *
     * @param bool $pretty Indent the output with two spaces per level.
     *
     * @throws RssException When the feed or one of its items is invalid.
     */
    public function toXml(bool $pretty = true): string
    {
        return (new RssWriter($pretty))->write($this);
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function getManagingEditor(): ?string
    {
        return $this->managingEditor;
    }

    public function getWebMaster(): ?string
    {
        return $this->webMaster;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getLastBuildAt(): ?DateTimeImmutable
    {
        return $this->lastBuildAt;
    }

    /**
     * @return list<Category>
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getGenerator(): ?string
    {
        return $this->generator;
    }

    public function getDocs(): ?string
    {
        return $this->docs;
    }

    public function getCloud(): ?Cloud
    {
        return $this->cloud;
    }

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function getTextInput(): ?TextInput
    {
        return $this->textInput;
    }

    /**
     * @return list<int>
     */
    public function getSkipHours(): array
    {
        return $this->skipHours;
    }

    /**
     * @return list<string>
     */
    public function getSkipDays(): array
    {
        return $this->skipDays;
    }

    /**
     * @return list<Item>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return list<Extension>
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    private static function normalize(?string $value): ?string
    {
        return $value === '' ? null : $value;
    }
}
