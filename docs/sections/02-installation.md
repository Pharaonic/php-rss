## Installation

Install the package with Composer. There is no configuration file to publish and nothing to register.

### Requirements

- PHP 8.2
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

Every class lives under the `Pharaonic\RSS` namespace:

| Namespace | Contains |
| --- | --- |
| `Pharaonic\RSS` | `Feed`, `Item` |
| `Pharaonic\RSS\Elements` | `Category`, `Guid`, `Enclosure`, `Image`, `Source`, `Cloud`, `TextInput` |
| `Pharaonic\RSS\Extensions\*` | Atom, Content, Dublin Core, and Media RSS elements |
| `Pharaonic\RSS\Contracts` | `Extension` |
| `Pharaonic\RSS\Support` | `Day`, `CloudProtocol`, `DateFormatter`, `Xml`, `NamespaceRegistry` |
| `Pharaonic\RSS\Exceptions` | `RssException` and its subclasses |
| `Pharaonic\RSS\Writer` | `RssWriter` |

:::success Installation Complete
You're all set! Head to [Basic Usage](#basic-usage) to build your first feed.
:::
