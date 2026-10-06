## Extensions

RSS 2.0 is extended with XML namespaces. The package ships four of the most common ones. Attach them to the feed or to an item with `extension()`.

Before writing, the writer collects the namespace of every extension in the feed and its items, then declares each one **once** on the `<rss>` root. Only the namespaces you actually use are declared.

| Extension | Namespace prefix | Classes |
| --- | --- | --- |
| Atom | `atom` | `Pharaonic\RSS\Extensions\Atom\Link` |
| Content | `content` | `Pharaonic\RSS\Extensions\Content\Encoded` |
| Dublin Core | `dc` | `Pharaonic\RSS\Extensions\DublinCore\Creator` |
| Media RSS | `media` | `Pharaonic\RSS\Extensions\Media\Content`, `Thumbnail`, `Title`, `Description` |

### Atom Self Link

Feed validators recommend that a feed links to its own URL. `Link::self()` sets `rel="self"` and `type="application/rss+xml"` for you:

```php
use Pharaonic\RSS\Extensions\Atom\Link;

$feed->extension(Link::self('https://pharaonic.dev/rss.xml'));
// <atom:link href="https://pharaonic.dev/rss.xml" rel="self" type="application/rss+xml"/>
```

`Link::make()` builds any other Atom link, for example a WebSub hub:

```php
$feed->extension(Link::make('https://pubsubhubbub.appspot.com/')->rel('hub'));
```

`Link` also has `type()`, `hreflang()`, `title()`, and `length()`.

### Full Content (content:encoded)

`Encoded` carries the full HTML body of an item as CDATA, without escaping. A `]]>` inside the content is split safely across CDATA sections, so the document always stays valid.

```php
use Pharaonic\RSS\Extensions\Content\Encoded;

$item
    ->description('A short summary for list views.')
    ->extension(Encoded::make($post->html));
```

### Creator (dc:creator)

Unlike `<author>`, `dc:creator` accepts a plain name:

```php
use Pharaonic\RSS\Extensions\DublinCore\Creator;

$item->extension(Creator::make('Moamen Eltouny'));
```

### Media RSS

`Content` describes a media object, with optional nested title, description, and thumbnails:

```php
use Pharaonic\RSS\Extensions\Media\Content;
use Pharaonic\RSS\Extensions\Media\Description;
use Pharaonic\RSS\Extensions\Media\Thumbnail;
use Pharaonic\RSS\Extensions\Media\Title;

$item->extension(
    Content::make('https://cdn.pharaonic.dev/giza.mp4')
        ->type('video/mp4')
        ->medium(Content::MEDIUM_VIDEO)
        ->expression(Content::EXPRESSION_FULL)
        ->fileSize(52428800)
        ->duration(95)
        ->width(1920)
        ->height(1080)
        ->lang('en')
        ->isDefault()
        ->title(Title::make('Giza pyramids'))
        ->description(Description::make('<b>Drone</b> footage')->type(Description::TYPE_HTML))
        ->thumbnail(Thumbnail::make('https://cdn.pharaonic.dev/giza.jpg')->width(1200)->height(630))
);
```

A `Thumbnail` can also be attached directly to an item, which many readers use as the item's preview image:

```php
$item->extension(Thumbnail::make('https://cdn.pharaonic.dev/note.jpg'));
```

| Constant | Values |
| --- | --- |
| `Content::MEDIUM_*` | `IMAGE`, `AUDIO`, `VIDEO`, `DOCUMENT`, `EXECUTABLE` |
| `Content::EXPRESSION_*` | `SAMPLE`, `FULL`, `NONSTOP` |
| `Title::TYPE_*` / `Description::TYPE_*` | `PLAIN`, `HTML` |

An unknown medium, expression, or text type throws an `InvalidElementException`.

:::info Order of extensions
Extensions are written after the core elements of their parent (channel or item), in the order you added them.
:::
