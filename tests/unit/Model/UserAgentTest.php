<?php
namespace UserAgentParserTest;

use PHPUnit_Framework_TestCase;
use UserAgentParser\Model\Browser;
use UserAgentParser\Model\OperatingSystem;
use UserAgentParser\Model\UserAgent;

/**
 *
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Model\UserAgent
 */
class UserAgentTest extends PHPUnit_Framework_TestCase
{
    public function testBrowser()
    {
        $ua = new UserAgent();

        $this->assertInstanceOf('UserAgentParser\Model\Browser', $ua->browser);

        $mock = self::createMock('UserAgentParser\Model\Browser');
        $ua->browser = $mock;
        $this->assertSame($mock, $ua->browser);
    }

    public function testRenderingEngine()
    {
        $ua = new UserAgent();

        $this->assertInstanceOf('UserAgentParser\Model\RenderingEngine', $ua->renderingEngine);

        $mock = self::createMock('UserAgentParser\Model\RenderingEngine');
        $ua->renderingEngine = $mock;
        $this->assertSame($mock, $ua->renderingEngine);
    }

    public function testOperatingSystem()
    {
        $ua = new UserAgent();

        $this->assertInstanceOf('UserAgentParser\Model\OperatingSystem', $ua->operatingSystem);

        $mock = self::createMock('UserAgentParser\Model\OperatingSystem');
        $ua->operatingSystem = $mock;
        $this->assertSame($mock, $ua->operatingSystem);
    }

    public function testDevice()
    {
        $ua = new UserAgent();

        $this->assertInstanceOf('UserAgentParser\Model\Device', $ua->device);

        $mock = self::createMock('UserAgentParser\Model\Device');
        $ua->device = $mock;
        $this->assertSame($mock, $ua->device);
    }

    public function testBot()
    {
        $ua = new UserAgent();

        $this->assertInstanceOf('UserAgentParser\Model\Bot', $ua->bot);

        $mock = self::createMock('UserAgentParser\Model\Bot');
        $ua->bot = $mock;
        $this->assertSame($mock, $ua->bot);
    }

    public function testIsBot()
    {
        $ua = new UserAgent();

        $this->assertFalse($ua->isBot());

        $ua->bot = false;
        $this->assertFalse($ua->isBot());

        $ua->bot = true;
        $this->assertTrue($ua->isBot());
    }

    public function testIsMobile()
    {
        $ua = new UserAgent();

        $this->assertFalse($ua->isMobile());

        $ua->device = false;
        $this->assertFalse($ua->isMobile());

        $ua->device = true;
        $this->assertTrue($ua->isMobile());
    }

    public function testProviderResultRaw()
    {
        $ua = new UserAgent();

        $this->assertNull($ua->providerResultRaw);

        $ua->providerResultRaw = ['test'];
        $this->assertEquals(['test'], $ua->providerResultRaw);
    }

    public function testToArray()
    {
        $ua = new UserAgent();

        $this->assertEquals([
            'browser'          => $ua->browser->toArray(),
            'renderingEngine'  => $ua->renderingEngine->toArray(),
            'operatingSystem'  => $ua->operatingSystem->toArray(),
            'device'           => $ua->device->toArray(),
            'bot'              => $ua->bot->toArray(),
        ], $ua->toArray());

        $this->assertEquals([
            'browser'           => $ua->browser->toArray(),
            'renderingEngine'   => $ua->renderingEngine->toArray(),
            'operatingSystem'   => $ua->operatingSystem->toArray(),
            'device'            => $ua->device->toArray(),
            'bot'               => $ua->bot->toArray(),
            'providerResultRaw' => null,
        ], $ua->toArray(true));
    }
}
