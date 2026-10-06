<?php

namespace Pharaonic\RSS;

use DateTimeImmutable;
use DateTimeInterface;
use Pharaonic\RSS\Contracts\Extension;
use Pharaonic\RSS\Elements\Category;
use Pharaonic\RSS\Elements\Enclosure;
use Pharaonic\RSS\Elements\Guid;
use Pharaonic\RSS\Elements\Source;
use Pharaonic\RSS\Exceptions\InvalidItemException;
use Pharaonic\RSS\Exceptions\RssException;

/**
 * An RSS 2.0 <item>.
 *
 * An item needs at least a title or a description; this is validated when
 * the feed is serialized. Optional text fields set to null or to an empty
 * string are omitted from the output.
 */
final class Item
{
    private ?string $title = null;

    private ?string $link = null;

    private ?string $description = null;

    private ?string $author = null;

    /** @var list<Category> */
    private array $categories = [];

    private ?string $comments = null;

    private ?Enclosure $enclosure = null;

    private ?Guid $guid = null;

    private ?DateTimeImmutable $publishedAt = null;

    private ?Source $source = null;

    /** @var list<Extension> */
    private array $extensions = [];

    public static function make(): self
    {
        return new self();
    }

    public function title(?string $title): self
    {
        $this->title = self::normalize($title);

        return $this;
    }

    public function link(?string $link): self
    {
        $this->link = self::normalize($link);

        return $this;
    }

    /**
     * Item synopsis. HTML is allowed and is escaped in the output.
     */
    public function description(?string $description): self
    {
        $this->description = self::normalize($description);

        return $this;
    }

    /**
     * Email address of the author, conventionally "author@example.com (Author Name)".
     */
    public function author(?string $author): self
    {
        $this->author = self::normalize($author);

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
     * URL of the page holding comments about the item.
     */
    public function comments(?string $comments): self
    {
        $this->comments = self::normalize($comments);

        return $this;
    }

    public function enclosure(?Enclosure $enclosure): self
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * Set the unique identifier of the item.
     *
     * Pass a string with an optional isPermaLink flag (true when omitted, as
     * in RSS 2.0), or a Guid object configured with Guid::permalink().
     *
     * @throws RssException
     */
    public function guid(Guid|string|null $guid, ?bool $isPermaLink = null): self
    {
        if ($guid instanceof Guid && $isPermaLink !== null) {
            throw InvalidItemException::ambiguousGuidPermalink();
        }

        if (is_string($guid)) {
            $guid = Guid::make($guid)->permalink($isPermaLink ?? true);
        }

        $this->guid = $guid;

        return $this;
    }

    /**
     * Publication date of the item (<pubDate>).
     */
    public function publishedAt(?DateTimeInterface $date): self
    {
        $this->publishedAt = $date === null ? null : DateTimeImmutable::createFromInterface($date);

        return $this;
    }

    public function source(?Source $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Attach a namespaced extension to the item, e.g. content:encoded or dc:creator.
     */
    public function extension(Extension $extension): self
    {
        $this->extensions[] = $extension;

        return $this;
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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @return list<Category>
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function getEnclosure(): ?Enclosure
    {
        return $this->enclosure;
    }

    public function getGuid(): ?Guid
    {
        return $this->guid;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getSource(): ?Source
    {
        return $this->source;
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
