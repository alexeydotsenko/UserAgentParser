<?php
namespace UserAgentParserTest;

use PHPUnit_Framework_TestCase;
use UserAgentParser\Model\RenderingEngine;
use UserAgentParser\Model\Version;

/**
 *
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Model\RenderingEngine
 */
class RenderingEngineTest extends PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $engine = new RenderingEngine();

        $this->assertNull($engine->name);

        $name = 'Webkit';
        $engine->name = $name;
        $this->assertEquals($name, $engine->name);
    }

    public function testVersion()
    {
        $engine = new RenderingEngine();

        $this->assertInstanceOf('UserAgentParser\Model\Version', $engine->version);

        $version = new Version();
        $engine->version = $version;
        $this->assertSame($version, $engine->version);
    }

    public function testToArray()
    {
        $engine = new RenderingEngine();

        $this->assertEquals([
            'name'    => null,
            'version' => $engine->version
                ->toArray(),
        ], $engine->toArray());

        $engine->name = 'Trident';
        $this->assertEquals([
            'name'    => 'Trident',
            'version' => $engine->version
                ->toArray(),
        ], $engine->toArray());
    }
}
