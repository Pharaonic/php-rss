---
name: RSS

action:
  label: View on Packagist
  href: "{package.packagistUrl}"

views: components.packages

breadcrumbs:
  - label: Home
    href: route:home
  - label: Packages
    href: route:packages.index
  - label: "{technology.name} Packages"
    href: "url:/packages/{technology.slug}"
  - label: "{package.name}"

card:
  topic: feeds
  icon: share
  tags: rss rss2 feed syndication xml atom podcast media-rss content-encoded dublin-core generator
  description: A lightweight, dependency-free RSS 2.0 feed generator for PHP 8, with a fluent API, strict validation, and built-in Atom, Content, Dublin Core, and Media RSS extensions.

seo:
  title: "{package.fullName} - RSS 2.0 Feed Generator for PHP"
  description: "{package.fullName} is a lightweight RSS 2.0 feed generator for PHP with a fluent API, strict validation, and Atom, Content, Dublin Core, and Media RSS support. {package.downloadsShort}+ downloads, {package.license} licensed."
  keywords: php rss, rss 2.0, rss feed generator, php feed, podcast feed, media rss, atom self link, content encoded, xml feed, syndication
  author: Pharaonic
  images:
    - "{package.cover}"
  openGraph:
    type: website
    siteName: Pharaonic
  twitter:
    card: summary_large_image

schema:
  "@type": SoftwareSourceCode
  name: "{package.name}"
  description: "{package.fullName} is a lightweight RSS 2.0 feed generator for PHP with a fluent API, strict validation, and built-in namespace extensions."
  image: "{package.cover}"
  codeRepository: "{package.githubUrl}"
  programmingLanguage: PHP
  runtimePlatform: "{technology.name}"
  version: "{package.version}"
  datePublished: "{package.publishedAt}"
  dateModified: "{package.updatedAt}"
  license: "https://opensource.org/licenses/{package.license}"
  isAccessibleForFree: true
  sameAs:
    - "{package.githubUrl}"
    - "{package.packagistUrl}"
  author:
    "@id": url:/#organization
  publisher:
    "@id": url:/#organization
  interactionStatistic:
    "@type": InteractionCounter
    interactionType: https://schema.org/DownloadAction
    userInteractionCount: "{package.downloads}"
---
