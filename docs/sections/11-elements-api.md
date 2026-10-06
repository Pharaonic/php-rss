## Elements API

Structured elements are small `final` value objects. Each has a static `make()` with the same arguments as its constructor, and required values are validated on creation.

### Pharaonic\Rss\Elements

| Class | Create with | Setters | Getters |
| --- | --- | --- | --- |
| `Category` | `make(string $value)` | `domain(?string)` | `getValue()`, `getDomain()` |
| `Guid` | `make(string $value)` | `permalink(bool $isPermaLink = true)` | `getValue()`, `isPermaLink()` |
| `Enclosure` | `make(string $url, int $length, string $type)` | | `getUrl()`, `getLength()`, `getType()` |
| `Image` | `make(string $url)` | `title(?string)`, `link(?string)`, `width(?int)`, `height(?int)`, `description(?string)` | `getUrl()`, `getTitle()`, `getLink()`, `getWidth()`, `getHeight()`, `getDescription()` |
| `Source` | `make(string $title, string $url)` | | `getTitle()`, `getUrl()` |
| `Cloud` | `make(string $domain, int $port, string $path, string $registerProcedure, string $protocol)` | | `getDomain()`, `getPort()`, `getPath()`, `getRegisterProcedure()`, `getProtocol()` |
| `TextInput` | `make(string $title, string $description, string $name, string $link)` | | `getTitle()`, `getDescription()`, `getName()`, `getLink()` |

| Constraint | Rule |
| --- | --- |
| `Enclosure` length | `>= 0` bytes |
| `Image` width | 1 to `Image::MAX_WIDTH` (144) |
| `Image` height | 1 to `Image::MAX_HEIGHT` (400) |
| `Cloud` port | 1 to 65535 |
| `Cloud` protocol | A `CloudProtocol` constant |

### Pharaonic\Rss\Extensions

| Class | Create with | Setters |
| --- | --- | --- |
| `Atom\Link` | `make(string $href)`, `self(string $href)` | `rel()`, `type()`, `hreflang()`, `title()`, `length(?int)` |
| `Content\Encoded` | `make(string $content)` | Getter: `getContent()` |
| `DublinCore\Creator` | `make(string $name)` | Getter: `getName()` |
| `Media\Content` | `make(string $url)` | `fileSize()`, `type()`, `medium()`, `isDefault()`, `expression()`, `bitrate()`, `duration()`, `width()`, `height()`, `lang()`, `title(?Title)`, `description(?Description)`, `thumbnail(Thumbnail)` |
| `Media\Thumbnail` | `make(string $url)` | `width()`, `height()`, `time(?string)` |
| `Media\Title` | `make(string $text)` | `type(?string)`; getters `getText()`, `getType()` |
| `Media\Description` | `make(string $text)` | `type(?string)`; getters `getText()`, `getType()` |

Each extension namespace has an abstract base class exposing `PREFIX` and `NAMESPACE_URI` constants: `AtomExtension`, `ContentExtension`, `DublinCoreExtension`, and `MediaExtension`.

### Pharaonic\Rss\Support

| Class | Members |
| --- | --- |
| `Day` | `MONDAY` … `SUNDAY`, `all()`, `isValid(string $day)` |
| `CloudProtocol` | `XML_RPC`, `SOAP`, `HTTP_POST`, `all()`, `isValid(string $protocol)` |
| `DateFormatter` | `format(DateTimeInterface $date)`, e.g. `"Tue, 06 Oct 2026 10:00:00 +0300"` |
| `Xml` | `isValidText()`, `assertValidText()`, `isNcName()`, `writeElement()`, `writeText()`, `writeAttribute()`, `writeCdata()` |
| `NamespaceRegistry` | `register(string $prefix, string $uri)`, `has(string $prefix)`, `all()` |
