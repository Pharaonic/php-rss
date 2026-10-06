:::badges
- PHP Package {color=blue}
- {release.label} {color=green}
- {package.license} License {color=purple}
:::

# PHP RSS

PHP RSS is a lightweight RSS 2.0 feed generator for PHP 8. You describe the channel and its items with a fluent `Feed` and `Item` API, and `toXml()` returns a well-formed, specification-ordered XML document. It has no Composer dependencies beyond `ext-xmlwriter`, validates everything before writing, and supports the Atom, Content, Dublin Core, and Media RSS namespaces out of the box.

:::features
### Fluent Feed Builder {icon="code"}
Every RSS 2.0 channel and item element is one chained method on `Feed` or `Item`.

### Always Well-Formed {icon="shield-check"}
Invalid UTF-8, XML control characters, and `]]>` in CDATA are rejected or handled, never written as broken XML.

### Namespaced Extensions {icon="package"}
Built-in `atom:link`, `content:encoded`, `dc:creator`, and Media RSS elements, with namespaces declared once on the root.

### Your Own Extensions {icon="code-brackets"}
Implement one small `Extension` contract to add any XML namespace, such as iTunes or GeoRSS.

### Podcasts and Media {icon="device"}
Enclosures, `media:content`, and thumbnails cover podcast and video feeds.

### Clear Exceptions {icon="alert-circle"}
Every error extends `RssException` and tells you exactly which field is wrong.
:::

:::info Quick Tip
`toXml()` only returns a string. It never sends HTTP headers, so you stay in control of the `Content-Type` and caching headers your application sends.
:::
