---
view: components.packages.features
variant: compact
badge: Key Features
title: Everything you need to publish a feed
subtitle: Build the channel, add items, call `toXml()`. That's the whole workflow.
items:
  - icon: code
    title: Fluent Builder
    text: Every RSS 2.0 channel and item element is one chained method on `Feed` or `Item`.
  - icon: shield-check
    title: Always Well-Formed
    text: Invalid UTF-8, XML control characters, and `]]>` in CDATA are caught before they break your feed.
  - icon: package
    title: Built-in Namespaces
    text: Atom self links, `content:encoded`, `dc:creator`, and Media RSS, declared once on the root.
  - icon: code-brackets
    title: Custom Extensions
    text: Implement the small `Extension` contract to add iTunes, GeoRSS, or any namespace you need.
  - icon: device
    title: Podcasts and Video
    text: Enclosures, `media:content`, thumbnails, durations, and bitrates for media feeds.
  - icon: clock
    title: Correct Dates
    text: Any `DateTimeInterface` is written as RFC 822 with its own timezone, never the server's.
---
