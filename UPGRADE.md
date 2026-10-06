# Upgrade Guide

## From 1.x

The package was rebuilt from scratch. The old `RSS` and `RSSItem` classes are gone, and there is no compatibility layer. Most feeds can be migrated in a few minutes with the table and examples below.

### Requirements

- PHP 8.4.
- `ext-xmlwriter` (bundled with most PHP builds).

### Class and method map

| 1.x | Current |
| --- | --- |
| `Pharaonic\RSS\RSS` | `Pharaonic\RSS\Feed` |
| `Pharaonic\RSS\RSSItem` | `Pharaonic\RSS\Item` |
| `new RSS()` | `Feed::make()` |
| `new RSSItem()` | `Item::make()` |
| `setTitle()`, `setDescription()`, `setLink()` | `title()`, `description()`, `link()` |
| `setLanguage()`, `setCopyright()` | `language()`, `copyright()` |
| `RSS::setPublished(string)` | `Feed::publishedAt(DateTimeInterface)` |
| `RSS::setUpdated(string)` | `Feed::lastBuildAt(DateTimeInterface)` |
| `RSS::setImage($url, $width, $height)` | `Feed::image(Image::make($url)->width($width)->height($height))` |
| `RSS::setItem($item)` | `Feed::addItem($item)` |
| `RSSItem::appendToChannel($rss)` | `$feed->addItem($item)` |
| `RSSItem::setGUID(string)` | `Item::guid(string, ?bool $isPermaLink)` |
| `RSSItem::setAuthor()` | `Item::author()` |
| `RSSItem::setCategory()` | `Item::category()` |
| `RSSItem::setPublished(string)` | `Item::publishedAt(DateTimeInterface)` |
| `RSS::render()` / `(string) $rss` | `Feed::toXml()` |

### Before

```php
use Pharaonic\RSS\RSS;
use Pharaonic\RSS\RSSItem;

$rss = new RSS();
$rss->setTitle('Pharaonic')
    ->setDescription('Rooted in History. Engineering the Future.')
    ->setLink('https://pharaonic.dev')
    ->setImage('https://pharaonic.dev/logo.png')
    ->setPublished(date('r'));

(new RSSItem())
    ->setTitle('PHP RSS rebuilt')
    ->setDescription('A modern RSS 2.0 generator for PHP.')
    ->setLink('https://pharaonic.dev/packages/php/rss')
    ->setGUID('php-rss-rebuild')
    ->setPublished(date('r'))
    ->appendToChannel($rss);

echo $rss->render(); // also sent a Content-Type header
```

### After

```php
use Pharaonic\RSS\Elements\Image;
use Pharaonic\RSS\Feed;
use Pharaonic\RSS\Item;

$feed = Feed::make()
    ->title('Pharaonic')
    ->description('Rooted in History. Engineering the Future.')
    ->link('https://pharaonic.dev')
    ->image(Image::make('https://pharaonic.dev/logo.png'))
    ->publishedAt(new DateTimeImmutable());

$feed->addItem(
    Item::make()
        ->title('PHP RSS rebuilt')
        ->description('A modern RSS 2.0 generator for PHP.')
        ->link('https://pharaonic.dev/packages/php/rss')
        ->guid('php-rss-rebuild', false)
        ->publishedAt(new DateTimeImmutable())
);

header('Content-Type: application/rss+xml; charset=UTF-8');
echo $feed->toXml();
```

### Behavior changes to review

- **No HTTP headers.** `render()` used to send `Content-Type: text/xml`. `toXml()` only returns a string; send the header from your application or framework response.
- **No implicit values.** 1.x always wrote a `<generator>` and defaulted `<pubDate>` to the current time. Call `generator()` and `publishedAt()` explicitly if you need them.
- **Image size.** 1.x always wrote `width` 88 and `height` 31. They are now only written when set, and readers apply the same defaults when they are missing.
- **GUIDs.** 1.x wrote `<guid>` without `isPermaLink`, which RSS readers treat as a permalink. `guid('id', false)` now writes `isPermaLink="false"` for identifiers that are not URLs. `guid('https://…')` keeps the old meaning.
- **Item validation.** 1.x required a title, a description, and a link on every item. An item now needs only a title or a description.
- **Exceptions.** 1.x threw a generic `\Exception`. All errors now extend `Pharaonic\RSS\Exceptions\RssException`.
- **Element order.** Channel elements are written in RSS 2.0 specification order, so the output differs from 1.x even for identical content.
