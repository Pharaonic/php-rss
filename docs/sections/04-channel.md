## Channel

`Feed` represents the RSS `<channel>`. It covers every channel element in RSS 2.0, and the writer outputs them in specification order whatever order you call the setters in.

### Required Elements

```php
use Pharaonic\RSS\Feed;

$feed = Feed::make()
    ->title('Pharaonic')                                  // <title>
    ->link('https://pharaonic.dev')                       // <link>
    ->description('Rooted in History. Engineering the Future.'); // <description>
```

`toXml()` throws an `InvalidFeedException` if any of the three is missing or blank.

### Text Elements

```php
$feed
    ->language('ar-EG')
    ->copyright('© Pharaonic')
    ->managingEditor('editor@pharaonic.dev (Editorial Team)')
    ->webMaster('webmaster@pharaonic.dev (Pharaonic Ops)')
    ->generator('My CMS')
    ->docs('https://www.rssboard.org/rss-specification')
    ->rating('(PICS-1.1 "http://www.rsac.org/ratingsv01.html" l by "webmaster@example.com" on "2026.10.06T10:00-0500" r (n 0 s 0 v 0 l 0))');
```

:::info No implicit values
The package doesn't add a `<generator>` or a default `<pubDate>`. Only what you set is written.
:::

### Dates

`publishedAt()` writes `<pubDate>` and `lastBuildAt()` writes `<lastBuildDate>`. Both accept any `DateTimeInterface` and keep its timezone offset:

```php
$feed
    ->publishedAt(new DateTimeImmutable('2026-10-06 10:00:00', new DateTimeZone('UTC')))
    ->lastBuildAt(new DateTime('2026-10-06 13:00:00', new DateTimeZone('Africa/Cairo')));

// <pubDate>Tue, 06 Oct 2026 10:00:00 +0000</pubDate>
// <lastBuildDate>Tue, 06 Oct 2026 13:00:00 +0300</lastBuildDate>
```

Mutable `DateTime` objects are copied, so changing them afterwards doesn't change the feed. Pass `null` to remove a date.

### Categories

`category()` adds a category each time you call it. Pass a string, or a `Category` with a `domain`:

```php
use Pharaonic\RSS\Elements\Category;

$feed
    ->category('Technology')
    ->category(Category::make('Software/PHP')->domain('https://pharaonic.dev/topics'));
```

### Image

The channel image is an `Image` object. When you don't set its title or link, the writer uses the channel title and link, as RSS 2.0 recommends.

```php
use Pharaonic\RSS\Elements\Image;

$feed->image(
    Image::make('https://pharaonic.dev/logo.png')
        ->width(144)       // 1 to 144 (Image::MAX_WIDTH)
        ->height(144)      // 1 to 400 (Image::MAX_HEIGHT)
        ->description('Pharaonic logo')
);
```

Width and height are only written when set. Readers assume 88×31 when they're missing.

### Caching Hints

```php
use Pharaonic\RSS\Support\Day;

$feed
    ->ttl(60)                 // readers may cache for 60 minutes
    ->skipHour(0)             // 0-23, GMT
    ->skipHour(1)
    ->skipDay(Day::SATURDAY)
    ->skipDay(Day::SUNDAY);
```

Duplicate hours and days are ignored. A negative `ttl`, an hour outside 0–23, or an unknown day name throws an `InvalidFeedException`.

### Cloud and Text Input

These two elements are rarely used, but both are supported:

```php
use Pharaonic\RSS\Elements\Cloud;
use Pharaonic\RSS\Elements\TextInput;
use Pharaonic\RSS\Support\CloudProtocol;

$feed
    ->cloud(Cloud::make('rpc.pharaonic.dev', 443, '/rpc', 'pleaseNotify', CloudProtocol::XML_RPC))
    ->textInput(TextInput::make('Search', 'Search the blog', 'q', 'https://pharaonic.dev/search'));
```

`CloudProtocol` offers `XML_RPC`, `SOAP`, and `HTTP_POST`. The register procedure may be an empty string with `HTTP_POST`.

### Channel Extensions

Namespaced elements such as an Atom self link are attached with `extension()`. See [Extensions](#extensions).

```php
use Pharaonic\RSS\Extensions\Atom\Link;

$feed->extension(Link::self('https://pharaonic.dev/rss.xml'));
```
