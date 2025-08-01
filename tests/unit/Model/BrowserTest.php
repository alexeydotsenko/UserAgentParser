<?php
namespace UserAgentParserTest;

use PHPUnit_Framework_TestCase;
use UserAgentParser\Model\Browser;
use UserAgentParser\Model\Version;

/**
 *
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Model\Browser
 */
class BrowserTest extends PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $browser = new Browser();

        $this->assertNull($browser->name);

        $name = 'Firefox';
        $browser->name = $name;
        $this->assertEquals($name, $browser->name);
    }

    public function testVersion()
    {
        $browser = new Browser();

        $this->assertInstanceOf('UserAgentParser\Model\Version', $browser->version);

        $version = new Version();
        $browser->version = $version;
        $this->assertSame($version, $browser->version);
    }

    public function testToArray()
    {
        $browser = new Browser();

        $this->assertEquals([
            'name'    => null,
            'version' => $browser->version
                ->toArray(),
        ], $browser->toArray());

        $browser->name = 'Chrome';
        $this->assertEquals([
            'name'    => 'Chrome',
            'version' => $browser->version
                ->toArray(),
        ], $browser->toArray());
    }
}
