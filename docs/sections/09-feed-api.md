## Feed API

`Pharaonic\Rss\Feed` is a `final` class. Every setter returns the same `Feed` instance for chaining. Optional string setters treat `null` and `''` as "not set".

### Setters

| Method | Element | Notes |
| --- | --- | --- |
| `Feed::make()` | | Creates an empty feed |
| `title(?string $title)` | `<title>` | Required |
| `link(?string $link)` | `<link>` | Required |
| `description(?string $description)` | `<description>` | Required |
| `language(?string $language)` | `<language>` | e.g. `en-US`, `ar-EG` |
| `copyright(?string $copyright)` | `<copyright>` | |
| `managingEditor(?string $managingEditor)` | `<managingEditor>` | `email (Name)` |
| `webMaster(?string $webMaster)` | `<webMaster>` | `email (Name)` |
| `publishedAt(?DateTimeInterface $date)` | `<pubDate>` | RFC 822, keeps the offset |
| `lastBuildAt(?DateTimeInterface $date)` | `<lastBuildDate>` | RFC 822, keeps the offset |
| `category(Category\|string $category)` | `<category>` | Adds one per call |
| `generator(?string $generator)` | `<generator>` | Not set by default |
| `docs(?string $docs)` | `<docs>` | |
| `cloud(?Cloud $cloud)` | `<cloud>` | |
| `ttl(?int $minutes)` | `<ttl>` | Throws when negative |
| `image(?Image $image)` | `<image>` | |
| `rating(?string $rating)` | `<rating>` | PICS rating |
| `textInput(?TextInput $textInput)` | `<textInput>` | |
| `skipHour(int $hour)` | `<skipHours><hour>` | 0–23, duplicates ignored |
| `skipDay(string $day)` | `<skipDays><day>` | A `Day` constant, duplicates ignored |
| `addItem(Item $item)` | `<item>` | |
| `addItems(iterable $items)` | `<item>` | Any iterable of `Item` |
| `extension(Extension $extension)` | namespaced | Written after the core channel elements |

### Output

| Method | Description | Returns |
| --- | --- | --- |
| `toXml(bool $pretty = true)` | Validates and serializes the feed. `false` gives compact output | `string` |

`toXml()` is a shortcut for `(new RssWriter($pretty))->write($feed)`. You can use `Pharaonic\Rss\Writer\RssWriter` directly if you prefer to inject the writer.

### Getters

| Method | Returns |
| --- | --- |
| `getTitle()`, `getLink()`, `getDescription()` | `?string` |
| `getLanguage()`, `getCopyright()`, `getManagingEditor()`, `getWebMaster()` | `?string` |
| `getGenerator()`, `getDocs()`, `getRating()` | `?string` |
| `getPublishedAt()`, `getLastBuildAt()` | `?DateTimeImmutable` |
| `getCategories()` | `list<Category>` |
| `getCloud()` | `?Cloud` |
| `getTtl()` | `?int` |
| `getImage()` | `?Image` |
| `getTextInput()` | `?TextInput` |
| `getSkipHours()` | `list<int>` |
| `getSkipDays()` | `list<string>` |
| `getItems()` | `list<Item>` |
| `getExtensions()` | `list<Extension>` |
