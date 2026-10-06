---
view: components.packages.quick-look
title: A quick look
subtitle: A complete blog feed with an Atom self link, ready to serve.
file: public/rss.php
language: php
code: |
  $feed = Feed::make()
      ->title('Pharaonic Blog')
      ->link('https://pharaonic.dev/blog')
      ->description('News & releases from the Pharaonic team.')
      ->extension(Link::self('https://pharaonic.dev/rss.xml'));

  foreach ($posts as $post) {
      $feed->addItem(Item::make()
          ->title($post->title)
          ->link($post->url)
          ->guid($post->url)
          ->publishedAt($post->publishedAt));
  }

  echo $feed->toXml();
---
