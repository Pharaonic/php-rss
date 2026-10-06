## Installation

Install the package with Composer. There is no configuration file to publish and nothing to register.

### Requirements

- PHP >= 8.0
- The `xmlwriter` extension (`ext-xmlwriter`), bundled with most PHP builds

You can check that the extension is enabled with:

```bash title="Terminal" no-line-numbers
php -m | grep -i xmlwriter
```

### Composer Installation

```bash title="Terminal" no-line-numbers
composer require pharaonic/php-rss
```

### Namespace

Every class lives under the `Pharaonic\Rss` namespace:

| Namespace | Contains |
| --- | --- |
| `Pharaonic\Rss` | `Feed`, `Item` |
| `Pharaonic\Rss\Elements` | `Category`, `Guid`, `Enclosure`, `Image`, `Source`, `Cloud`, `TextInput` |
| `Pharaonic\Rss\Extensions\*` | Atom, Content, Dublin Core, and Media RSS elements |
| `Pharaonic\Rss\Contracts` | `Extension` |
| `Pharaonic\Rss\Support` | `Day`, `CloudProtocol`, `DateFormatter`, `Xml`, `NamespaceRegistry` |
| `Pharaonic\Rss\Exceptions` | `RssException` and its subclasses |
| `Pharaonic\Rss\Writer` | `RssWriter` |

:::success Installation Complete
You're all set! Head to [Basic Usage](#basic-usage) to build your first feed.
:::
