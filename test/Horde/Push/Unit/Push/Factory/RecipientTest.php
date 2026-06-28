<?php

/**
 * Test the recipient factory.
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
 * Test the recipient factory.
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
class Horde_Push_Unit_Push_Factory_RecipientTest extends Horde_Push_TestCase
{
    public function testTwitter()
    {
        $factory = new Horde_Push_Factory_Recipients();
        $recipients = $factory->create(
            [],
            [
                'recipients' => ['twitter'],
                'twitter' => [
                    'key' => 'test',
                    'secret' => 'test',
                    'token_key' => 'test',
                    'token_secret' => 'test',
                ],
            ]
        );
        $this->assertInstanceOf(
            'Horde_Push_Recipient_Twitter',
            $recipients[0]
        );
    }

    public function testBlogger()
    {
        $factory = new Horde_Push_Factory_Recipients();
        $recipients = $factory->create(
            [],
            [
                'recipients' => ['blogger'],
                'blogger' => [],
                'http' => [
                    'proxy' => [
                        'proxy_host' => 'localhost',
                        'proxy_port' => 8080,
                        'proxy_user' => 'user',
                        'proxy_pass' => 'pass',
                    ],
                ],
            ]
        );
        $this->assertInstanceOf(
            'Horde_Push_Recipient_Blogger',
            $recipients[0]
        );
    }

    public function testMail()
    {
        $factory = new Horde_Push_Factory_Recipients();
        $recipients = $factory->create(
            [],
            [
                'recipients' => ['mail'],
                'mailer' => [
                    'type' => 'mock',
                    'from' => 'user@example.org',
                ],
            ]
        );
        $this->assertInstanceOf(
            'Horde_Push_Recipient_Mail',
            $recipients[0]
        );
    }

    /**
     * @expectedException Horde_Push_Exception
     */
    public function testUnknownTransport()
    {
        $factory = new Horde_Push_Factory_Recipients();
        $recipients = $factory->create(
            [],
            [
                'recipients' => ['mail'],
                'mailer' => [
                    'type' => 'UNKNOWN',
                    'from' => 'user@example.org',
                ],
            ]
        );
    }

    public function testMultipleConfigRecipients()
    {
        $factory = new Horde_Push_Factory_Recipients();
        $recipients = $factory->create(
            [],
            [
                'recipients' => ['blogger', 'blogger'],
                'blogger' => [],
            ]
        );
        $this->assertEquals(2, count($recipients));
    }

    public function testMultipleCommandLineRecipients()
    {
        $factory = new Horde_Push_Factory_Recipients();
        $recipients = $factory->create(
            ['recipients' => 'blogger,blogger'],
            ['blogger' => []]
        );
        $this->assertEquals(2, count($recipients));
    }

    public function testTrimmedRecipient()
    {
        $factory = new Horde_Push_Factory_Recipients();
        $recipients = $factory->create(
            ['recipients' => " blogger , blogger\n"],
            ['blogger' => []]
        );
        $this->assertEquals(2, count($recipients));
    }

    /**
     * @expectedException Horde_Push_Exception
     */
    public function testUnknownRecipient()
    {
        $factory = new Horde_Push_Factory_Recipients();
        $recipients = $factory->create(
            [],
            ['recipients' => ['UNKNOWN']]
        );
    }

    public function testNamedRecipient()
    {
        $factory = new Horde_Push_Factory_Recipients();
        $recipients = $factory->create(
            [],
            [
                'recipients' => ['personal-blog'],
                'recipient' => [
                    'personal-blog' => [
                        'type' => 'blogger',
                        'blogger' => [],
                    ],
                ],
            ]
        );
        $this->assertInstanceOf(
            'Horde_Push_Recipient_Blogger',
            $recipients[0]
        );
    }

    public function testFromHeader()
    {
        $factory = new Horde_Push_Factory_Recipients();
        $recipients = $factory->create(
            [],
            [
                'recipients' => ['mail-me'],
                'recipient' => [
                    'mail-me' => [
                        'type' => 'mail',
                        'mailer' => [
                            'type' => 'mock',
                            'from' => 'from@example.com',
                        ],
                    ],
                ],
            ]
        );
        $push = new Horde_Push();
        $push->setSummary('E-MAIL');
        foreach ($recipients as $recipient) {
            $push->addRecipient($recipient);
        }
        $result = $push->push(['pretend' => true]);
        $this->assertContains('from: from@example.com', $result[0]);
    }

    public function testToHeader()
    {
        $factory = new Horde_Push_Factory_Recipients();
        $recipients = $factory->create(
            [],
            [
                'recipients' => ['mail-me:recipient@example.com'],
                'recipient' => [
                    'mail-me' => [
                        'type' => 'mail',
                        'mailer' => [
                            'type' => 'mock',
                            'from' => 'from@example.com',
                        ],
                    ],
                ],
            ]
        );
        $push = new Horde_Push();
        $push->setSummary('E-MAIL');
        foreach ($recipients as $recipient) {
            $push->addRecipient($recipient);
        }
        $result = $push->push(['pretend' => true]);
        $this->assertContains('to: recipient@example.com', $result[0]);
    }

    public function testToConfiguredHeader()
    {
        $factory = new Horde_Push_Factory_Recipients();
        $recipients = $factory->create(
            [],
            [
                'recipients' => ['mail-me'],
                'recipient' => [
                    'mail-me' => [
                        'type' => 'mail',
                        'acl' => 'recipient@example.com',
                        'mailer' => [
                            'type' => 'mock',
                            'from' => 'from@example.com',
                        ],
                    ],
                ],
            ]
        );
        $push = new Horde_Push();
        $push->setSummary('E-MAIL');
        foreach ($recipients as $recipient) {
            $push->addRecipient($recipient);
        }
        $result = $push->push(['pretend' => true]);
        $this->assertContains('to: recipient@example.com', $result[0]);
    }

    public function testEmpty()
    {
        $factory = new Horde_Push_Factory_Recipients();
        $this->assertEquals([], $factory->create([], []));
    }
}
