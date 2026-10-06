<?php

namespace Pharaonic\Rss\Tests\Unit\Elements;

use Pharaonic\Rss\Elements\Cloud;
use Pharaonic\Rss\Exceptions\InvalidElementException;
use Pharaonic\Rss\Support\CloudProtocol;
use Pharaonic\Rss\Tests\TestCase;

final class CloudTest extends TestCase
{
    public function testMakeStoresAllValues(): void
    {
        $cloud = Cloud::make('rpc.example.com', 80, '/RPC2', 'myCloud.rssPleaseNotify', CloudProtocol::XML_RPC);

        $this->assertSame('rpc.example.com', $cloud->getDomain());
        $this->assertSame(80, $cloud->getPort());
        $this->assertSame('/RPC2', $cloud->getPath());
        $this->assertSame('myCloud.rssPleaseNotify', $cloud->getRegisterProcedure());
        $this->assertSame('xml-rpc', $cloud->getProtocol());
    }

    public function testHttpPostMayHaveAnEmptyRegisterProcedure(): void
    {
        $cloud = Cloud::make('rpc.example.com', 443, '/rsscloud/pleaseNotify', '', CloudProtocol::HTTP_POST);

        $this->assertSame('', $cloud->getRegisterProcedure());
    }

    /**
     * @return array<string, array{int}>
     */
    public static function invalidPortProvider(): array
    {
        return ['zero' => [0], 'negative' => [-80], 'too large' => [65536]];
    }

    /**
     * @dataProvider invalidPortProvider
     */
    public function testRejectsInvalidPorts(int $port): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage(sprintf('The cloud port must be between 1 and 65535, %d given.', $port));

        Cloud::make('rpc.example.com', $port, '/RPC2', 'notify', CloudProtocol::SOAP);
    }

    public function testRejectsUnknownProtocols(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The cloud protocol must be one of [xml-rpc, soap, http-post], "rest" given.');

        Cloud::make('rpc.example.com', 80, '/RPC2', 'notify', 'rest');
    }

    public function testRejectsEmptyDomain(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The cloud domain must not be empty.');

        Cloud::make('', 80, '/RPC2', 'notify', CloudProtocol::XML_RPC);
    }

    public function testRejectsEmptyPath(): void
    {
        $this->expectException(InvalidElementException::class);
        $this->expectExceptionMessage('The cloud path must not be empty.');

        Cloud::make('rpc.example.com', 80, '', 'notify', CloudProtocol::XML_RPC);
    }

    public function testSerialization(): void
    {
        $xpath = $this->feedXpath($this->minimalFeed()->cloud(
            Cloud::make('rpc.example.com', 80, '/RPC2', 'myCloud.rssPleaseNotify', CloudProtocol::XML_RPC)
        ));

        $this->assertXPathValue('rpc.example.com', $xpath, '/rss/channel/cloud/@domain');
        $this->assertXPathValue('80', $xpath, '/rss/channel/cloud/@port');
        $this->assertXPathValue('/RPC2', $xpath, '/rss/channel/cloud/@path');
        $this->assertXPathValue('myCloud.rssPleaseNotify', $xpath, '/rss/channel/cloud/@registerProcedure');
        $this->assertXPathValue('xml-rpc', $xpath, '/rss/channel/cloud/@protocol');
    }
}
