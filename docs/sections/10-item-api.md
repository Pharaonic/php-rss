## Item API

`Pharaonic\RSS\Item` is a `final` class. Every setter returns the same `Item` instance for chaining. Optional string setters treat `null` and `''` as "not set".

### Setters

| Method | Element | Notes |
| --- | --- | --- |
| `Item::make()` | | Creates an empty item |
| `title(?string $title)` | `<title>` | Title or description is required |
| `link(?string $link)` | `<link>` | |
| `description(?string $description)` | `<description>` | HTML allowed, escaped in output |
| `author(?string $author)` | `<author>` | `email (Name)` |
| `category(Category\|string $category)` | `<category>` | Adds one per call |
| `comments(?string $comments)` | `<comments>` | URL of the comments page |
| `enclosure(?Enclosure $enclosure)` | `<enclosure>` | |
| `guid(Guid\|string\|null $guid, ?bool $isPermaLink = null)` | `<guid>` | A string is a permalink unless the flag is `false` |
| `publishedAt(?DateTimeInterface $date)` | `<pubDate>` | RFC 822, keeps the offset |
| `source(?Source $source)` | `<source>` | |
| `extension(Extension $extension)` | namespaced | Written after the core item elements |

### Getters

| Method | Returns |
| --- | --- |
| `getTitle()`, `getLink()`, `getDescription()`, `getAuthor()`, `getComments()` | `?string` |
| `getCategories()` | `list<Category>` |
| `getEnclosure()` | `?Enclosure` |
| `getGuid()` | `?Guid` |
| `getPublishedAt()` | `?DateTimeImmutable` |
| `getSource()` | `?Source` |
| `getExtensions()` | `list<Extension>` |
