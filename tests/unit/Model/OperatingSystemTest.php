<?php
namespace UserAgentParserTest;

use PHPUnit_Framework_TestCase;
use UserAgentParser\Model\OperatingSystem;
use UserAgentParser\Model\Version;

/**
 *
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Model\OperatingSystem
 */
class OperatingSystemTest extends PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $os = new OperatingSystem();

        $this->assertNull($os->name);

        $name = 'Windows';
        $os->name = $name;
        $this->assertEquals($name, $os->name);
    }

    public function testVersion()
    {
        $os = new OperatingSystem();

        $this->assertInstanceOf('UserAgentParser\Model\Version', $os->version);

        $version = new Version();
        $os->version = $version;
        $this->assertSame($version, $os->version);
    }

    public function testToArray()
    {
        $os = new OperatingSystem();

        $this->assertEquals([
            'name'    => null,
            'version' => $os->version
                ->toArray(),
        ], $os->toArray());

        $os->name = 'Linux';
        $this->assertEquals([
            'name'    => 'Linux',
            'version' => $os->version
                ->toArray(),
        ], $os->toArray());
    }
}
