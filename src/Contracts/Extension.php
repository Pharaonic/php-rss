<?php

namespace Pharaonic\RSS\Contracts;

use Pharaonic\RSS\Exceptions\RssException;
use XMLWriter;

/**
 * A namespaced element attached to a feed or to an item.
 *
 * Before opening the <rss> root element, the writer collects the namespace
 * of every extension in the feed and declares each one once on the root.
 * An extension therefore only writes prefixed element names such as
 * "atom:link"; it must not declare its namespace itself.
 *
 * Use the helpers in Pharaonic\RSS\Support\Xml to write text, attributes,
 * and CDATA safely.
 */
interface Extension
{
    /**
     * Namespace prefix used by the written elements, e.g. "atom".
     */
    public function prefix(): string;

    /**
     * Namespace URI bound to the prefix, e.g. "http://www.w3.org/2005/Atom".
     */
    public function namespaceUri(): string;

    /**
     * Write the extension's elements at the current writer position.
     *
     * @throws RssException
     */
    public function write(XMLWriter $writer): void;
}
