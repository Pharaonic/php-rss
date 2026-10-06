## Examples

Real-world feeds built with PHP RSS.

### 1. Blog Feed in a Laravel App

Build the feed in a small class, return it from a controller with the right `Content-Type`, and cache the XML.

- ===Feed Builder

  ```php title="app/Rss/BlogFeed.php"
  namespace App\Rss;

  use App\Models\Post;
  use Pharaonic\RSS\Extensions\Atom\Link;
  use Pharaonic\RSS\Extensions\Content\Encoded;
  use Pharaonic\RSS\Extensions\DublinCore\Creator;
  use Pharaonic\RSS\Feed;
  use Pharaonic\RSS\Item;

  final class BlogFeed
  {
      public function toXml(): string
      {
          $posts = Post::query()->published()->latest('published_at')->limit(20)->get();

          return Feed::make()
              ->title(config('app.name') . ' Blog')
              ->link(url('/blog'))
              ->description('News & releases from our team.')
              ->language(app()->getLocale())
              ->lastBuildAt($posts->first()?->updated_at)
              ->ttl(60)
              ->extension(Link::self(route('blog.rss')))
              ->addItems($posts->map(fn (Post $post) => Item::make()
                  ->title($post->title)
                  ->link(route('blog.show', $post))
                  ->description($post->excerpt)
                  ->guid(route('blog.show', $post))
                  ->publishedAt($post->published_at)
                  ->extension(Creator::make($post->author->name))
                  ->extension(Encoded::make($post->body_html))))
              ->toXml();
      }
  }
  ```

- ===Controller

  ```php title="app/Http/Controllers/BlogFeedController.php"
  namespace App\Http\Controllers;

  use App\Rss\BlogFeed;
  use Illuminate\Support\Facades\Cache;

  class BlogFeedController
  {
      public function __invoke(BlogFeed $feed)
      {
          $xml = Cache::remember('blog.rss', now()->addHour(), fn () => $feed->toXml());

          return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
      }
  }
  ```

- ===Route

  ```php title="routes/web.php"
  use App\Http\Controllers\BlogFeedController;

  Route::get('/blog/rss.xml', BlogFeedController::class)->name('blog.rss');
  ```

`$posts->map()` returns a Laravel collection, which `addItems()` accepts because it's iterable. `lastBuildAt(null)` is fine when there are no posts yet.

### 2. Podcast Feed

Each episode carries an `<enclosure>` for classic podcast apps and a `media:content` for richer readers.

```php title="podcast.php"
use Pharaonic\RSS\Elements\Enclosure;
use Pharaonic\RSS\Elements\Image;
use Pharaonic\RSS\Extensions\Atom\Link;
use Pharaonic\RSS\Extensions\DublinCore\Creator;
use Pharaonic\RSS\Extensions\Media\Content;
use Pharaonic\RSS\Extensions\Media\Thumbnail;
use Pharaonic\RSS\Feed;
use Pharaonic\RSS\Item;

$feed = Feed::make()
    ->title('Pharaonic Podcast')
    ->link('https://pharaonic.dev/podcast')
    ->description('Conversations about building software.')
    ->language('en')
    ->image(Image::make('https://cdn.pharaonic.dev/podcast/cover.png'))
    ->extension(Link::self('https://pharaonic.dev/podcast/rss.xml'));

foreach ($episodes as $episode) {
    $feed->addItem(
        Item::make()
            ->title(sprintf('Episode %d: %s', $episode->number, $episode->title))
            ->enclosure(Enclosure::make($episode->audioUrl, $episode->bytes, 'audio/mpeg'))
            ->guid('pharaonic-podcast-' . $episode->number, false)
            ->publishedAt($episode->publishedAt)
            ->extension(Creator::make('Pharaonic'))
            ->extension(
                Content::make($episode->audioUrl)
                    ->fileSize($episode->bytes)
                    ->type('audio/mpeg')
                    ->medium(Content::MEDIUM_AUDIO)
                    ->expression(Content::EXPRESSION_FULL)
                    ->duration($episode->seconds)
            )
            ->extension(Thumbnail::make($episode->coverUrl))
    );
}

file_put_contents(__DIR__ . '/public/podcast/rss.xml', $feed->toXml());
```

Episodes without a description are valid because each one has a title.

### 3. Static Feed Generated at Deploy Time

For static sites, write the feed to disk once per build, compact, so the web server can serve it directly.

```php title="bin/build-feed.php"
use Pharaonic\RSS\Exceptions\RssException;
use Pharaonic\RSS\Feed;
use Pharaonic\RSS\Item;

require __DIR__ . '/../vendor/autoload.php';

$pages = json_decode(file_get_contents(__DIR__ . '/../content/pages.json'), true);

$feed = Feed::make()
    ->title('Docs Changelog')
    ->link('https://docs.example.com')
    ->description('Every documentation update.')
    ->lastBuildAt(new DateTimeImmutable('now', new DateTimeZone('UTC')));

foreach ($pages as $page) {
    $feed->addItem(
        Item::make()
            ->title($page['title'])
            ->link($page['url'])
            ->guid($page['url'])
            ->publishedAt(new DateTimeImmutable($page['updated_at']))
    );
}

try {
    file_put_contents(__DIR__ . '/../public/feed.xml', $feed->toXml(false));
} catch (RssException $e) {
    fwrite(STDERR, 'Feed build failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
```

Failing the build on an `RssException` keeps an invalid feed from ever reaching readers.

### 4. Aggregated Feed with Sources

When you republish items from other feeds, credit each origin with `source()` and keep the original GUID.

```php title="aggregate.php"
use Pharaonic\RSS\Elements\Category;
use Pharaonic\RSS\Elements\Source;
use Pharaonic\RSS\Feed;
use Pharaonic\RSS\Item;

$feed = Feed::make()
    ->title('PHP Weekly Picks')
    ->link('https://picks.example.com')
    ->description('The best PHP articles from around the web.');

foreach ($picks as $pick) {
    $feed->addItem(
        Item::make()
            ->title($pick['title'])
            ->link($pick['link'])
            ->description($pick['summary'])
            ->category(Category::make($pick['topic'])->domain('https://picks.example.com/topics'))
            ->guid($pick['guid'], $pick['guid_is_url'])
            ->source(Source::make($pick['site_name'], $pick['site_feed_url']))
    );
}

echo $feed->toXml();
```
