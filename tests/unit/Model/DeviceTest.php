<?php
namespace UserAgentParserTest;

use PHPUnit_Framework_TestCase;
use UserAgentParser\Model\Device;

/**
 *
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Model\Device
 */
class DeviceTest extends PHPUnit_Framework_TestCase
{
    public function testModel()
    {
        $device = new Device();

        $this->assertNull($device->model);

        $name = 'OnePlus';
        $device->model = $name;
        $this->assertEquals($name, $device->model);
    }

    public function testBrand()
    {
        $device = new Device();

        $this->assertNull($device->brand);

        $name = 'Apple';
        $device->brand = $name;
        $this->assertEquals($name, $device->brand);
    }

    public function testType()
    {
        $device = new Device();

        $this->assertNull($device->type);

        $name = 'mobilephone';
        $device->type = $name;
        $this->assertEquals($name, $device->type);
    }

    public function testIsMobile()
    {
        $device = new Device();

        $this->assertNull($device->isMobile);

        $device->isMobile = true;
        $this->assertTrue($device->isMobile);
    }

    public function testIsTouch()
    {
        $device = new Device();

        $this->assertNull($device->isTouch);

        $device->isTouch = true;
        $this->assertTrue($device->isTouch);
    }

    public function testToArray()
    {
        $device = new Device();

        $this->assertEquals([
            'model'    => null,
            'brand'    => null,
            'type'     => null,
            'isMobile' => null,
            'isTouch'  => null,
        ], $device->toArray());

        $device->model = 'iPad';
        $device->brand = 'Apple';
        $device->type = 'tablet';
        $device->isMobile = false;
        $device->isTouch = true;

        $this->assertEquals([
            'model'    => 'iPad',
            'brand'    => 'Apple',
            'type'     => 'tablet',
            'isMobile' => false,
            'isTouch'  => true,
        ], $device->toArray());
    }
}
