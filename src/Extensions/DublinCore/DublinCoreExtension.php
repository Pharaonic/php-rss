<?php

namespace Pharaonic\RSS\Extensions\DublinCore;

use Pharaonic\RSS\Contracts\Extension;

/**
 * Base class for elements of the Dublin Core Metadata Element Set.
 */
abstract class DublinCoreExtension implements Extension
{
    public const PREFIX = 'dc';
    public const NAMESPACE_URI = 'http://purl.org/dc/elements/1.1/';

    public function prefix(): string
    {
        return self::PREFIX;
    }

    public function namespaceUri(): string
    {
        return self::NAMESPACE_URI;
    }
}
