---
view: components.home.faq
badge: FAQ
title: "{package.name}"
highlight: Questions
subtitle: "Quick answers about installing and using {package.name}."
---

## What is {package.name}?

{card.description} It's a free, open-source {technology.name} package by Pharaonic.

## How do I install {package.name}?

Run `composer require {package.composer}` in your project's root directory.

## What does {package.name} require?

The latest release requires {package.requiresText}.

## Does it work with Laravel, Symfony, or plain PHP?

Yes. It's a framework-agnostic PHP library with no service provider or configuration. `toXml()` returns a string that you can return from any controller, response object, or script.

## Does it send HTTP headers?

No. The package never calls `header()` or prints anything. Send `Content-Type: application/rss+xml; charset=UTF-8` from your application.

## Can I build podcast feeds or add namespaces like iTunes?

Yes. Use enclosures and the built-in Media RSS extension for podcasts, and implement the `Extension` contract to add any other namespace, such as iTunes or GeoRSS.

## How do I upgrade from 1.x?

The `RSS` and `RSSItem` classes were replaced by `Feed` and `Item`, and setters lost their `set` prefix. The documentation has a full method map and a before-and-after example.

## Is {package.name} free to use?

Yes. {package.name} is open source under the {package.license} license, so you can use it in personal and commercial projects.

## Where can I find the {package.name} documentation?

Read the [{package.name} documentation]({package.docsUrl}) for setup, configuration, and usage examples.

## How do I report a bug or contribute to {package.name}?

Open an issue or a pull request on [GitHub]({package.githubUrl}), or ask in the [Pharaonic Discord](https://discord.gg/XQG9RhvEvf).
