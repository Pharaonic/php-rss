<?php

namespace Pharaonic\RSS\Tests\Unit\Support;

use Pharaonic\RSS\Support\CloudProtocol;
use Pharaonic\RSS\Support\Day;
use Pharaonic\RSS\Tests\TestCase;

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
