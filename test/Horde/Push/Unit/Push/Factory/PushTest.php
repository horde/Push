<?php

/**
 * Test the push factory.
 *
 * PHP version 5
 *
 * @category   Horde
 * @package    Push
 * @subpackage UnitTests
 * @author     Gunnar Wrobel <wrobel@pardus.de>
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @link       http://www.horde.org/libraries/Horde_Push
 */

/**
 * Test the push factory.
 *
 * Copyright 2011-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you did not
 * receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category   Horde
 * @package    Push
 * @subpackage UnitTests
 * @author     Gunnar Wrobel <wrobel@pardus.de>
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @link       http://www.horde.org/libraries/Horde_Push
 * @coversNothing
 */
class Horde_Push_Unit_Push_Factory_PushTest extends Horde_Push_TestCase
{
    public function tearDown()
    {
        $GLOBALS['push'] = null;
    }

    public function testEmpty()
    {
        $factory = new Horde_Push_Factory_Push();
        $push = $factory->create(
            [],
            [],
            []
        );
        $this->assertEquals('', $push[0]->getSummary());
    }

    public function testYaml()
    {
        $factory = new Horde_Push_Factory_Push();
        $push = $factory->create(
            ['yaml://' . __DIR__ . '/../../../fixtures/push.yaml'],
            [],
            []
        );
        $this->assertEquals('YAML', $push[0]->getSummary());
    }

    public function testPhp()
    {
        $factory = new Horde_Push_Factory_Push();
        $push = $factory->create(
            ['php://' . __DIR__ . '/../../../fixtures/push.php'],
            [],
            []
        );
        $this->assertEquals('PHP', $push[0]->getSummary());
    }

    public function testKolab()
    {
        $factory = new Horde_Push_Factory_Push();
        $push = $factory->create(
            ['kolab://INBOX/test/libkcal-543769073.132'],
            [],
            [
                'kolab' => [
                    'driver' => 'mock',
                    'logger' => $this->getMock('Horde_Log_Logger'),
                    'queryset' => ['list' => ['queryset' => 'horde']],
                    'params' => [
                        'username' => 'test',
                        'host' => 'localhost',
                        'port' => 143,
                        'data' => [
                            'format' => 'brief',
                            'user/test'  => [],
                            'user/test/test'  => [
                                't' => 'note.default',
                                'm' => [
                                    1 => [
                                        'structure' => __DIR__ . '/../../../fixtures/note.php',
                                        'parts' => [
                                            '2' => [
                                                'file' => __DIR__ . '/../../../fixtures/note.xml.qp',
                                            ],
                                        ],
                                    ],
                                ],
                                's' => [
                                    'uidvalidity' => '12346789',
                                    'uidnext' => 2,
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->assertEquals('Summary', $push[0]->getSummary());
    }

    public function testMultiple()
    {
        $factory = new Horde_Push_Factory_Push();
        $push = $factory->create(
            [
                'php://' . __DIR__ . '/../../../fixtures/push.php',
                'yaml://' . __DIR__ . '/../../../fixtures/push.yaml',
            ],
            [],
            []
        );
        $this->assertEquals('PHP', $push[0]->getSummary());
        $this->assertEquals('YAML', $push[1]->getSummary());
    }

    /**
     * @expectedException Horde_Push_Exception
     */
    public function testMissingPhp()
    {
        $factory = new Horde_Push_Factory_Push();
        $push = $factory->create(
            ['php://' . __DIR__ . '/../../../fixtures/DOES_NOT_EXIST'],
            [],
            []
        );
    }

    /**
     * @expectedException Horde_Push_Exception
     */
    public function testEmptySummary()
    {
        $factory = new Horde_Push_Factory_Push();
        $push = $factory->create(
            ['php://' . __DIR__ . '/../../../fixtures/empty.php'],
            [],
            []
        );
    }

    /**
     * @expectedException Horde_Push_Exception
     */
    public function testMissingYaml()
    {
        $factory = new Horde_Push_Factory_Push();
        $push = $factory->create(
            ['yaml://' . __DIR__ . '/../../../fixtures/DOES_NOT_EXIST'],
            [],
            []
        );
    }

    /**
     * @expectedException Horde_Push_Exception
     */
    public function testEmptyArgument()
    {
        $factory = new Horde_Push_Factory_Push();
        $push = $factory->create(
            ['yaml://'],
            [],
            []
        );
    }

    /**
     * @expectedException Horde_Push_Exception
     */
    public function testUnknownArgument()
    {
        $factory = new Horde_Push_Factory_Push();
        $push = $factory->create(
            ['NOSUCH://XYZ'],
            [],
            []
        );
    }
}
