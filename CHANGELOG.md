# Changelog

All notable changes to this project will be documented in this file.

## Unreleased

### Added

- Getters on `Atom\Link`, `Media\Content`, and `Media\Thumbnail`, matching their setters. `Content::isDefault()` is the setter, so its getter is `getIsDefault()`.

## 8.2.0 - 2026-10-06

The PHP 8.2 line of the package, with the same API and features as 8.1.0. See [UPGRADE.md](UPGRADE.md) for migrating from 1.x.

### Changed

- Requires PHP 8.2 (8.3 and newer are not supported on this branch).

### Compatibility

- PHP 8.2

## 8.1.0 - 2026-10-06

The PHP 8.1 line of the package, with the same API and features as 8.0.1. See [UPGRADE.md](UPGRADE.md) for migrating from 1.x.

### Changed

- Requires PHP 8.1 (8.2 and newer are not supported on this branch).

### Compatibility

- PHP 8.1

## 8.0.1 - 2026-10-06

### Changed

- The root namespace is `Pharaonic\RSS` again, matching 1.x. Replace `Pharaonic\Rss\` with `Pharaonic\RSS\` in your imports.

## 8.0.0 - 2026-10-06

This release is a full rebuild of the package. See [UPGRADE.md](UPGRADE.md) for migrating from 1.x.

### Added

- `Feed` and `Item` domain objects with a fluent API covering every RSS 2.0 channel and item element.
- Value objects for structured elements: `Category`, `Guid`, `Enclosure`, `Image`, `Source`, `Cloud`, and `TextInput`.
- `RssWriter`, which validates the feed and serializes it with `XMLWriter`, in RSS 2.0 element order.
- `Feed::toXml()` returning the XML document as a string, pretty-printed by default or compact with `toXml(false)`.
- Namespaced extension support through the `Pharaonic\Rss\Contracts\Extension` contract. Namespaces are collected from the feed and its items, declared once on the `<rss>` root, and prefix conflicts are reported.
- Built-in extensions: Atom (`atom:link`), Content (`content:encoded`), Dublin Core (`dc:creator`), and Media RSS (`media:content`, `media:thumbnail`, `media:title`, `media:description`).
- `DateTimeInterface` dates formatted as RFC 822, keeping the timezone offset of the given date.
- `Day` and `CloudProtocol` constants classes for `<skipDays>` and `<cloud>`.
- `RssException` hierarchy: `InvalidFeedException`, `InvalidItemException`, and `InvalidElementException`, with named constructors and descriptive messages.
- PHPUnit test suite, PHPStan (max level), PSR-12 code style checks, and GitHub Actions workflows.

### Changed

- Namespace renamed from `Pharaonic\RSS` to `Pharaonic\Rss`.
- `RSS` is replaced by `Feed`, and `RSSItem` by `Item`. Setters lost their `set` prefix (`setTitle()` → `title()`).
- Dates are passed as `DateTimeInterface` objects instead of preformatted strings.
- Images are configured with an `Image` object; width and height are only written when set.
- Requires PHP 8.0 (8.1 and newer are not supported on this branch).

### Fixed

- Items now only require a title or a description, as RSS 2.0 specifies. A link is no longer required.
- Values such as `"0"` are no longer treated as missing.
- Text that contains invalid UTF-8 or characters that XML 1.0 does not allow is rejected with an exception instead of producing a malformed document.
- `]]>` inside CDATA content no longer breaks the document.
- Mutable `DateTime` objects passed to the feed are copied, so later changes by the caller do not affect the feed.

### Removed

- HTTP header handling: the package no longer calls `header()`. Send the `Content-Type` header from your application.
- The default `<pubDate>` set to the current time. Dates are only written when you set them.
- The hard-coded `<generator>` element. Set it with `Feed::generator()` if you want one.
- `RSS::render()`, `RSS::__toString()`, `RSS::setItem()`, and `RSSItem::appendToChannel()`.
- The `ext-libxml` requirement; only `ext-xmlwriter` is required.

### Compatibility

- PHP 8.0
