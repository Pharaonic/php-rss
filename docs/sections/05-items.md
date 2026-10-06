## Items

`Item` represents an RSS `<item>`. Every element is optional, but an item needs at least a title or a description.

### A Complete Item

```php
use Pharaonic\RSS\Elements\Enclosure;
use Pharaonic\RSS\Elements\Source;
use Pharaonic\RSS\Item;

$item = Item::make()
    ->title('PHP RSS rebuilt')
    ->link('https://pharaonic.dev/blog/php-rss-rebuilt')
    ->description('<p>A modern <strong>RSS 2.0</strong> generator for PHP.</p>')
    ->author('raggi@pharaonic.dev (Moamen Eltouny)')
    ->category('PHP')
    ->category('RSS')
    ->comments('https://pharaonic.dev/blog/php-rss-rebuilt#comments')
    ->enclosure(Enclosure::make('https://cdn.pharaonic.dev/cover.jpg', 48213, 'image/jpeg'))
    ->guid('https://pharaonic.dev/blog/php-rss-rebuilt')
    ->publishedAt(new DateTimeImmutable('2026-10-06 10:00:00'))
    ->source(Source::make('Pharaonic News', 'https://pharaonic.dev/news/rss.xml'));

$feed->addItem($item);
```

### Description and HTML

`description()` accepts HTML and escapes it in the output, which RSS readers decode and render. To ship the full article body as CDATA, add a [`content:encoded`](#extensions) extension alongside a short description.

### Author

RSS 2.0 expects an email address in `<author>`, conventionally `email (Name)`. If you only have a name, use the Dublin Core `Creator` extension instead:

```php
use Pharaonic\RSS\Extensions\DublinCore\Creator;

$item->extension(Creator::make('Moamen Eltouny')); // <dc:creator>Moamen Eltouny</dc:creator>
```

### GUIDs

A GUID uniquely identifies an item, so readers don't show it twice. RSS 2.0 treats a GUID as a permalink unless `isPermaLink="false"` is set.

```php
$item->guid('https://pharaonic.dev/blog/42');      // <guid>https://pharaonic.dev/blog/42</guid>
$item->guid('post-42', false);                     // <guid isPermaLink="false">post-42</guid>
```

You can also pass a `Guid` object:

```php
use Pharaonic\RSS\Elements\Guid;

$item->guid(Guid::make('post-42')->permalink(false));
```

:::warning Guid object and flag
Passing a `Guid` object together with the second argument throws an `InvalidItemException`. Set the flag on the object with `permalink()` instead.
:::

### Enclosures

An enclosure attaches a media file. It needs the URL, the size in bytes, and the MIME type:

```php
$item->enclosure(Enclosure::make('https://cdn.pharaonic.dev/podcast/1.mp3', 24986239, 'audio/mpeg'));
```

RSS 2.0 allows one enclosure per item. Use [Media RSS](#extensions) when you need several media objects.

### Source

`source()` credits the channel an item was republished from:

```php
$item->source(Source::make('Pharaonic News', 'https://pharaonic.dev/news/rss.xml'));
// <source url="https://pharaonic.dev/news/rss.xml">Pharaonic News</source>
```

### Item Extensions

Namespaced elements are added with `extension()` and written after the core item elements, in the order you add them:

```php
use Pharaonic\RSS\Extensions\Content\Encoded;

$item
    ->extension(Creator::make('Moamen Eltouny'))
    ->extension(Encoded::make('<article><h1>PHP RSS</h1><p>Full body…</p></article>'));
```
