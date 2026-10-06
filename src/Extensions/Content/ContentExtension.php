<?php

namespace Pharaonic\Rss\Extensions\Content;

use Pharaonic\Rss\Contracts\Extension;

/**
 * Base class for elements of the RSS 1.0 Content module.
 */
abstract class ContentExtension implements Extension
{
    public const PREFIX = 'content';
    public const NAMESPACE_URI = 'http://purl.org/rss/1.0/modules/content/';

    public function prefix(): string
    {
        return self::PREFIX;
    }

    public function namespaceUri(): string
    {
        return self::NAMESPACE_URI;
    }
}
