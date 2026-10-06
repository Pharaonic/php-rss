## Basic Usage

A feed is a `Feed` (the RSS `<channel>`) holding any number of `Item` objects. Both are created with `make()` and configured with chained setters.

### Your First Feed

A channel needs a title, a link, and a description. Each item needs at least a title or a description.

```php title="public/rss.php"
use Pharaonic\Rss\Feed;
use Pharaonic\Rss\Item;

$cairo = new DateTimeZone('Africa/Cairo');

$feed = Feed::make()
    ->title('Pharaonic Blog')
    ->link('https://pharaonic.dev/blog')
    ->description('News & releases from the Pharaonic team.')
    ->language('en-US')
    ->lastBuildAt(new DateTimeImmutable('2026-10-06 12:00:00', $cairo));

$feed->addItem(
    Item::make()
        ->title('PHP RSS rebuilt')
        ->link('https://pharaonic.dev/blog/php-rss-rebuilt')
        ->description('<p>A modern <strong>RSS 2.0</strong> generator for PHP.</p>')
        ->category('PHP')
        ->guid('https://pharaonic.dev/blog/php-rss-rebuilt')
        ->publishedAt(new DateTimeImmutable('2026-10-06 10:00:00', $cairo))
);

header('Content-Type: application/rss+xml; charset=UTF-8');
echo $feed->toXml();
```

The output looks like this:

```xml title="Output" no-copy
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Pharaonic Blog</title>
    <link>https://pharaonic.dev/blog</link>
    <description>News &amp; releases from the Pharaonic team.</description>
    <language>en-US</language>
    <lastBuildDate>Tue, 06 Oct 2026 12:00:00 +0300</lastBuildDate>
    <item>
      <title>PHP RSS rebuilt</title>
      <link>https://pharaonic.dev/blog/php-rss-rebuilt</link>
      <description>&lt;p&gt;A modern &lt;strong&gt;RSS 2.0&lt;/strong&gt; generator for PHP.&lt;/p&gt;</description>
      <category>PHP</category>
      <guid>https://pharaonic.dev/blog/php-rss-rebuilt</guid>
      <pubDate>Tue, 06 Oct 2026 10:00:00 +0300</pubDate>
    </item>
  </channel>
</rss>
```

### Adding Many Items

`addItems()` accepts any iterable of `Item` objects, so you can map your records directly:

```php
$feed->addItems(array_map(
    fn (array $post) => Item::make()
        ->title($post['title'])
        ->link($post['url'])
        ->guid($post['url'])
        ->publishedAt(new DateTimeImmutable($post['published_at'])),
    $posts
));
```

Items are written in the order you add them. Sort your records (usually newest first) before adding them.

### Pretty or Compact Output

`toXml()` indents with two spaces by default. Pass `false` for a compact document:

```php
$feed->toXml();      // indented, easy to read
$feed->toXml(false); // compact, smallest size
```

### Rules Worth Knowing

- **Empty values are omitted.** Passing `null` or `''` to an optional setter removes the element from the output. Values such as `"0"` are kept.
- **Order doesn't matter.** Required fields are validated when you call `toXml()`, so you can call setters in any order.
- **Escaping is automatic.** Text is XML-escaped for you. HTML in `description()` is escaped, which is how RSS 2.0 carries HTML. Use [`content:encoded`](#extensions) to send it as CDATA instead.
- **Dates keep their timezone.** Any `DateTimeInterface` is formatted as RFC 822 using the offset of the date you pass, never the server timezone.
- **No HTTP side effects.** The package never calls `header()` or `echo`.

:::warning Content-Type
Send `application/rss+xml; charset=UTF-8` (or `application/xml`) yourself. Browsers and readers may not recognize the feed if it's served as `text/html`.
:::
