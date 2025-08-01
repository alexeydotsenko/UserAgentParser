<?php
namespace UserAgentParserTest;

use PHPUnit_Framework_TestCase;
use UserAgentParser\Model\Bot;

/**
 *
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Model\Bot
 */
class BotTest extends PHPUnit_Framework_TestCase
{
    public function testIsBot()
    {
        $bot = new Bot();

        $this->assertNull($bot->isBot);

        $bot->isBot = true;
        $this->assertTrue($bot->isBot);

        $bot->isBot = false;
        $this->assertFalse($bot->isBot);
    }

    public function testName()
    {
        $bot = new Bot();

        $this->assertNull($bot->name);

        $name = 'my bot name';
        $bot->name = $name;
        $this->assertEquals($name, $bot->name);
    }

    public function testType()
    {
        $bot = new Bot();

        $this->assertNull($bot->type);

        $type = 'crawler';
        $bot->type = $type;
        $this->assertEquals($type, $bot->type);
    }

    public function testToArray()
    {
        $bot = new Bot();

        $this->assertEquals([
            'isBot' => null,
            'name'  => null,
            'type'  => null,
        ], $bot->toArray());

        $bot->isBot = true;
        $bot->name = 'my bot name2';
        $bot->type = 'backlink';

        $this->assertEquals([
            'isBot' => true,
            'name'  => 'my bot name2',
            'type'  => 'backlink',
        ], $bot->toArray());
    }
}
