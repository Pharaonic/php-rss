<?php

namespace Pharaonic\RSS\Support;

use Pharaonic\RSS\Exceptions\InvalidElementException;

/**
 * Collects the XML namespaces required by a document.
 *
 * Identical declarations are deduplicated, and binding one prefix to two
 * different URIs is reported instead of producing an invalid document.
 */
final class NamespaceRegistry
{
    /**
     * @var array<string, string> URIs keyed by prefix, in registration order.
     */
    private array $namespaces = [];

    /**
     * @throws InvalidElementException
     */
    public function register(string $prefix, string $uri): self
    {
        if (!Xml::isNcName($prefix)) {
            throw InvalidElementException::invalidNamespace($prefix, $uri, 'the prefix is not a valid XML name');
        }

        if (in_array(strtolower($prefix), ['xml', 'xmlns'], true)) {
            throw InvalidElementException::invalidNamespace($prefix, $uri, 'the prefix is reserved by XML');
        }

        if (trim($uri) === '') {
            throw InvalidElementException::invalidNamespace($prefix, $uri, 'the URI must not be empty');
        }

        if (!Xml::isValidText($uri)) {
            throw InvalidElementException::invalidNamespace($prefix, $uri, 'the URI contains invalid characters');
        }

        if (isset($this->namespaces[$prefix]) && $this->namespaces[$prefix] !== $uri) {
            throw InvalidElementException::namespaceConflict($prefix, $this->namespaces[$prefix], $uri);
        }

        $this->namespaces[$prefix] = $uri;

        return $this;
    }

    public function has(string $prefix): bool
    {
        return isset($this->namespaces[$prefix]);
    }

    /**
     * @return array<string, string> URIs keyed by prefix, in registration order.
     */
    public function all(): array
    {
        return $this->namespaces;
    }
}
