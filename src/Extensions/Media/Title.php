<?php

namespace Pharaonic\Rss\Extensions\Media;

/**
 * <media:title> element: the title of a media object.
 */
final class Title extends TextConstruct
{
    protected function elementName(): string
    {
        return 'title';
    }
}
