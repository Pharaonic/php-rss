## Custom Extensions

Any namespace the package doesn't ship (iTunes, GeoRSS, Slash, your own) can be added by implementing `Pharaonic\Rss\Contracts\Extension`.

### The Contract

```php
interface Extension
{
    public function prefix(): string;          // e.g. "geo"
    public function namespaceUri(): string;    // e.g. "http://www.w3.org/2003/01/geo/wgs84_pos#"
    public function write(XMLWriter $writer): void;
}
```

`write()` receives the `XMLWriter` positioned inside the parent element. Write prefixed element names such as `geo:lat`, and don't declare the namespace yourself, because the writer declares it on the root.

### Writing Values Safely

`XMLWriter` copies invalid UTF-8 and XML control characters into the output as they are. Use the helpers in `Pharaonic\Rss\Support\Xml` to keep the document well-formed:

| Helper | Writes |
| --- | --- |
| `Xml::writeElement($writer, $name, $value)` | An element with escaped text |
| `Xml::writeText($writer, $value, $context)` | Escaped text inside the open element |
| `Xml::writeAttribute($writer, $name, $value)` | An escaped attribute on the open element |
| `Xml::writeCdata($writer, $content)` | CDATA, splitting any `]]>` safely |

Each one throws an `InvalidElementException` instead of writing an invalid value.

### Example: GeoRSS Point

```php title="src/Rss/GeoPoint.php"
namespace App\Rss;

use Pharaonic\Rss\Contracts\Extension;
use Pharaonic\Rss\Support\Xml;
use XMLWriter;

final class GeoPoint implements Extension
{
    public function __construct(private float $latitude, private float $longitude)
    {
    }

    public function prefix(): string
    {
        return 'geo';
    }

    public function namespaceUri(): string
    {
        return 'http://www.w3.org/2003/01/geo/wgs84_pos#';
    }

    public function write(XMLWriter $writer): void
    {
        Xml::writeElement($writer, 'geo:lat', (string) $this->latitude);
        Xml::writeElement($writer, 'geo:long', (string) $this->longitude);
    }
}
```

Attach it like any built-in extension:

```php
$item->extension(new GeoPoint(29.9792, 31.1342));
// <geo:lat>29.9792</geo:lat>
// <geo:long>31.1342</geo:long>
```

### Example: iTunes Podcast Tags

One extension class can write several elements. This one writes any set of iTunes tags:

```php title="src/Rss/Itunes.php"
namespace App\Rss;

use Pharaonic\Rss\Contracts\Extension;
use Pharaonic\Rss\Support\Xml;
use XMLWriter;

final class Itunes implements Extension
{
    /** @param array<string, string> $tags e.g. ['author' => 'Pharaonic', 'explicit' => 'false'] */
    public function __construct(private array $tags)
    {
    }

    public function prefix(): string
    {
        return 'itunes';
    }

    public function namespaceUri(): string
    {
        return 'http://www.itunes.com/dtds/podcast-1.0.dtd';
    }

    public function write(XMLWriter $writer): void
    {
        foreach ($this->tags as $name => $value) {
            Xml::writeElement($writer, 'itunes:' . $name, $value);
        }
    }
}
```

### Namespace Rules

The writer validates namespaces with `NamespaceRegistry` before it writes anything:

- The prefix must be a valid XML name and can't be `xml` or `xmlns`.
- The URI can't be empty.
- One prefix can't be bound to two different URIs in the same feed. Registering the same prefix and URI twice is fine.

Breaking a rule throws an `InvalidElementException`.
