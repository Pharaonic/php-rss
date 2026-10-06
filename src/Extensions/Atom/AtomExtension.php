<?php

namespace Pharaonic\RSS\Extensions\Atom;

use Pharaonic\RSS\Contracts\Extension;

/**
 * Base class for elements of the Atom namespace used inside RSS.
 */
abstract class AtomExtension implements Extension
{
    public const PREFIX = 'atom';
    public const NAMESPACE_URI = 'http://www.w3.org/2005/Atom';

    public function prefix(): string
    {
        return self::PREFIX;
    }

    public function namespaceUri(): string
    {
        return self::NAMESPACE_URI;
    }
}
