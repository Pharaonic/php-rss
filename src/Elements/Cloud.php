<?php

namespace Pharaonic\Rss\Elements;

use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Support\CloudProtocol;

/**
 * RSS channel <cloud> element: a web service supporting the rssCloud interface.
 */
final class Cloud
{
    private string $domain;

    private int $port;

    private string $path;

    private string $registerProcedure;

    private string $protocol;

    /**
     * @param string $registerProcedure May be empty for the http-post protocol, which has no procedure name.
     * @param string $protocol          One of the CloudProtocol constants.
     *
     * @throws InvalidElementException
     */
    public function __construct(string $domain, int $port, string $path, string $registerProcedure, string $protocol)
    {
        if (trim($domain) === '') {
            throw InvalidElementException::emptyValue('cloud', 'domain');
        }

        if ($port < 1 || $port > 65535) {
            throw InvalidElementException::outOfRange('cloud', 'port', $port, 1, 65535);
        }

        if (trim($path) === '') {
            throw InvalidElementException::emptyValue('cloud', 'path');
        }

        if (!CloudProtocol::isValid($protocol)) {
            throw InvalidElementException::invalidChoice('cloud', 'protocol', $protocol, CloudProtocol::all());
        }

        $this->domain = $domain;
        $this->port = $port;
        $this->path = $path;
        $this->registerProcedure = $registerProcedure;
        $this->protocol = $protocol;
    }

    /**
     * @throws InvalidElementException
     */
    public static function make(
        string $domain,
        int $port,
        string $path,
        string $registerProcedure,
        string $protocol
    ): self {
        return new self($domain, $port, $path, $registerProcedure, $protocol);
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getRegisterProcedure(): string
    {
        return $this->registerProcedure;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }
}
