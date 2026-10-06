## Upgrading from 1.x

The package was rebuilt from scratch. The old `RSS` and `RSSItem` classes are gone and there is no compatibility layer, but most feeds migrate in a few minutes.

### Class and Method Map

| 1.x | Current |
| --- | --- |
| `Pharaonic\RSS\RSS` | `Pharaonic\Rss\Feed` |
| `Pharaonic\RSS\RSSItem` | `Pharaonic\Rss\Item` |
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

### Before and After

- ===1.x

  ```php title="1.x" no-copy
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

- ===Current

  ```php title="Current"
  use Pharaonic\Rss\Elements\Image;
  use Pharaonic\Rss\Feed;
  use Pharaonic\Rss\Item;

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

### Behavior Changes to Review

- **No HTTP headers.** `render()` sent `Content-Type: text/xml`. `toXml()` only returns a string, so send the header from your application.
- **No implicit values.** 1.x always wrote a `<generator>` and defaulted `<pubDate>` to the current time. Call `generator()` and `publishedAt()` if you need them.
- **Image size.** 1.x always wrote width 88 and height 31. They're now only written when set, and readers apply the same defaults.
- **GUIDs.** `guid('id', false)` writes `isPermaLink="false"` for identifiers that aren't URLs. `guid('https://…')` keeps the old meaning.
- **Item validation.** An item now needs only a title or a description. A link is no longer required.
- **Exceptions.** Errors extend `Pharaonic\Rss\Exceptions\RssException` instead of a generic `\Exception`.
- **Element order.** Channel elements follow RSS 2.0 specification order, so the output differs from 1.x even for identical content.
