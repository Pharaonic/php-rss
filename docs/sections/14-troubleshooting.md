## Troubleshooting

### "The RSS channel requires a non-empty title"

The channel is missing one of its three required elements. Call `title()`, `link()`, and `description()` with non-blank values before `toXml()`. Whitespace-only values count as blank.

### "The RSS item at position N requires at least a title or a description"

The item at zero-based position `N` has neither. This often happens when a database column is `null`. Set at least one of them, or skip the record.

### "contains invalid UTF-8 or characters that are not allowed in XML 1.0"

The text contains broken UTF-8 or control characters such as `\x0B` or `\x1F`, usually from pasted or legacy content. XML 1.0 can't represent them, so the package refuses to write them. Clean the value before adding it:

```php
$clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', mb_convert_encoding($text, 'UTF-8', 'UTF-8'));
```

### HTML Shows as Escaped Tags in My Reader

`description()` escapes HTML (`&lt;p&gt;`), which is correct RSS 2.0, and readers decode it. Some readers prefer the full body as CDATA. Add `Encoded::make($html)` from `Pharaonic\Rss\Extensions\Content` alongside the description.

### The Browser Downloads or Shows the Feed as Text

The response is missing a proper `Content-Type`. The package never sends headers. Send `Content-Type: application/rss+xml; charset=UTF-8` from your application, and make sure nothing is printed before the XML declaration.

### Dates Show the Wrong Time

Dates are formatted with the timezone of the object you pass, never the server's. A `DateTimeImmutable` created without a timezone uses `date.timezone` from `php.ini`. Pass an explicit `DateTimeZone`, or call `->setTimezone()` before adding the date.

### Readers Show Duplicate Items

The item has no GUID, or its GUID changes between builds. Set a stable `guid()` for every item, such as the permalink or a database ID with `false` as the second argument.

### "already bound to … and cannot also be bound to …"

Two extensions use the same prefix with different namespace URIs, often a custom extension reusing `media`, `dc`, `content`, or `atom`. Give your custom extension a different prefix, or use the same URI as the built-in one.

### "Item::guid() received a Guid object and an isPermaLink flag"

You passed both a `Guid` object and the second argument. Use either `guid('id', false)` or `guid(Guid::make('id')->permalink(false))`.

### "Call to undefined method … setTitle()"

You're calling the 1.x API. Setters lost their `set` prefix and the classes were renamed. See [Upgrading from 1.x](#upgrade).
