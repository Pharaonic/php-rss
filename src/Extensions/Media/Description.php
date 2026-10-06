<?php

namespace Pharaonic\RSS\Extensions\Media;

/**
 * <media:description> element: a short description of a media object.
 */
final class Description extends TextConstruct
{
    protected function elementName(): string
    {
        return 'description';
    }
}
