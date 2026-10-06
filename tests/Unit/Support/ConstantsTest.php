<?php

namespace Pharaonic\Rss\Tests\Unit\Support;

use Pharaonic\Rss\Support\CloudProtocol;
use Pharaonic\Rss\Support\Day;
use Pharaonic\Rss\Tests\TestCase;

final class ConstantsTest extends TestCase
{
    public function testDaysFollowTheRssSpecification(): void
    {
        $this->assertSame(
            ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            Day::all()
        );

        $this->assertTrue(Day::isValid(Day::SUNDAY));
        $this->assertFalse(Day::isValid('sunday'));
        $this->assertFalse(Day::isValid('Sun'));
    }

    public function testCloudProtocolsFollowTheRssSpecification(): void
    {
        $this->assertSame(['xml-rpc', 'soap', 'http-post'], CloudProtocol::all());

        $this->assertTrue(CloudProtocol::isValid(CloudProtocol::HTTP_POST));
        $this->assertFalse(CloudProtocol::isValid('XML-RPC'));
        $this->assertFalse(CloudProtocol::isValid('rest'));
    }
}
