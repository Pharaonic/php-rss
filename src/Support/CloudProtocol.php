<?php

namespace Pharaonic\Rss\Support;

/**
 * Protocols accepted by the RSS <cloud> element.
 */
final class CloudProtocol
{
    public const XML_RPC = 'xml-rpc';
    public const SOAP = 'soap';
    public const HTTP_POST = 'http-post';

    private function __construct()
    {
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::XML_RPC,
            self::SOAP,
            self::HTTP_POST,
        ];
    }

    public static function isValid(string $protocol): bool
    {
        return in_array($protocol, self::all(), true);
    }
}
