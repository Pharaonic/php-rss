## Validation and Errors

The package refuses to produce an invalid document. Value objects validate their input as soon as you create them, and `toXml()` validates the feed as a whole before it writes anything.

### Exception Hierarchy

Every exception extends `Pharaonic\RSS\Exceptions\RssException`, which extends `RuntimeException`, so one `catch` covers them all.

| Exception | Thrown when |
| --- | --- |
| `InvalidFeedException` | The channel is missing a title, link, or description, or gets a negative `ttl`, an hour outside 0–23, or an unknown skip day |
| `InvalidItemException` | An item has neither a title nor a description, or `guid()` gets a `Guid` object together with a permalink flag |
| `InvalidElementException` | An element or extension gets an empty required value, an out-of-range number, an unknown choice, an invalid namespace, or text that isn't valid XML |

### When Validation Runs

| Check | Runs at |
| --- | --- |
| Empty URL, value, or name in an element or extension | Construction (`make()` / `new`) |
| Numeric ranges, choices (`medium`, `protocol`, `width`…) | The setter call |
| Channel `title`, `link`, `description` | `toXml()` |
| Item title-or-description | `toXml()` |
| Namespace prefixes and conflicts | `toXml()` |
| Invalid UTF-8 and XML control characters | `toXml()` |

### Catching Errors

```php
use Pharaonic\RSS\Exceptions\RssException;

try {
    $xml = $feed->toXml();
} catch (RssException $e) {
    error_log($e->getMessage());
    // "The RSS item at position 3 requires at least a title or a description."
}
```

Messages name the element, the field, and the value received, for example:

```text title="Messages" no-copy
The RSS channel requires a non-empty title. Call Feed::title() before serializing.
The image width must be between 1 and 144, 300 given.
The cloud protocol must be one of [xml-rpc, soap, http-post], "https" given.
The value of <title> contains invalid UTF-8 or characters that are not allowed in XML 1.0.
The XML namespace prefix "media" is already bound to "http://search.yahoo.com/mrss/" and cannot also be bound to "https://example.com/media".
```

:::warning Untrusted text
Content copied from user input, legacy databases, or Word documents often contains control characters (such as `\x0B`) or broken UTF-8. Clean it before adding it to the feed, for example with `mb_convert_encoding($text, 'UTF-8', 'UTF-8')` and `preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text)`.
:::
