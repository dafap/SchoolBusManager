<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail\Protocol;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Headers;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use ZendTest\Mail\TestAsset\SmtpProtocolSpy;

/**
 * @group      Zend_Mail
 * @covers Zend\Mail\Protocol\Smtp<extended>
 */
class SmtpTest extends TestCase
{
    /** @var Smtp */
    public $transport;
    /** @var SmtpProtocolSpy */
    public $connection;

    public function setUp()
    {
        $this->transport  = new Smtp();
        $this->connection = new SmtpProtocolSpy();
        $this->transport->setConnection($this->connection);
    }

    public function testSendMinimalMail()
    {
        $headers = new Headers();
        $headers->addHeaderLine('Date', 'Sun, 10 Jun 2012 20:07:24 +0200');

        $message = new Message();
        $message->setHeaders($headers);
        $message->setSender('ralph.schindler@zend.com', 'Ralph Schindler');
        $message->setBody('testSendMailWithoutMinimalHeaders');
        $message->addTo('zf-devteam@zend.com', 'ZF DevTeam');

        $expectedMessage = "EHLO localhost\r\n"
            . "MAIL FROM:<ralph.schindler@zend.com>\r\n"
            . "RCPT TO:<zf-devteam@zend.com>\r\n"
            . "DATA\r\n"
            . "Date: Sun, 10 Jun 2012 20:07:24 +0200\r\n"
            . "Sender: Ralph Schindler <ralph.schindler@zend.com>\r\n"
            . "To: ZF DevTeam <zf-devteam@zend.com>\r\n"
            . "\r\n"
            . "testSendMailWithoutMinimalHeaders\r\n"
            . ".\r\n";

        $this->transport->send($message);

        $this->assertEquals($expectedMessage, $this->connection->getLog());
    }

    public function testSendEscapedEmail()
    {
        $headers = new Headers();
        $headers->addHeaderLine('Date', 'Sun, 10 Jun 2012 20:07:24 +0200');

        $message = new Message();
        $message->setHeaders($headers);
        $message->setSender('ralph.schindler@zend.com', 'Ralph Schindler');
        $message->setBody("This is a test\n.");
        $message->addTo('zf-devteam@zend.com', 'ZF DevTeam');

        $expectedMessage = "EHLO localhost\r\n"
            . "MAIL FROM:<ralph.schindler@zend.com>\r\n"
            . "RCPT TO:<zf-devteam@zend.com>\r\n"
            . "DATA\r\n"
            . "Date: Sun, 10 Jun 2012 20:07:24 +0200\r\n"
            . "Sender: Ralph Schindler <ralph.schindler@zend.com>\r\n"
            . "To: ZF DevTeam <zf-devteam@zend.com>\r\n"
            . "\r\n"
            . "This is a test\r\n"
            . "..\r\n"
            . ".\r\n";

        $this->transport->send($message);

        $this->assertEquals($expectedMessage, $this->connection->getLog());
    }

    public function testDisconnectCallsQuit()
    {
        $this->connection->disconnect();
        $this->assertTrue($this->connection->calledQuit);
    }

    public function testDisconnectResetsAuthFlag()
    {
        $this->connection->connect();
        $this->connection->setSessionStatus(true);
        $this->connection->setAuth(true);
        $this->assertTrue($this->connection->getAuth());
        $this->connection->disconnect();
        $this->assertFalse($this->connection->getAuth());
    }

    public function testConnectHasVerboseErrors()
    {
        $smtp = new TestAsset\ErroneousSmtp();

        $this->expectException('Zend\Mail\Protocol\Exception\RuntimeException');
        $this->expectExceptionMessageRegExp('/nonexistentremote/');

        $smtp->connect('nonexistentremote');
    }

    public function testCanAvoidQuitRequest()
    {
        $this->assertTrue($this->connection->useCompleteQuit(), 'Default behaviour must be BC');

        $this->connection->resetLog();
        $this->connection->connect();
        $this->connection->helo();
        $this->connection->disconnect();

        $this->assertContains('QUIT', $this->connection->getLog());

        $this->connection->setUseCompleteQuit(false);
        $this->assertFalse($this->connection->useCompleteQuit());

        $this->connection->resetLog();
        $this->connection->connect();
        $this->connection->helo();
        $this->connection->disconnect();

        $this->assertNotContains('QUIT', $this->connection->getLog());

        $connection = new SmtpProtocolSpy([
            'use_complete_quit' => false,
        ]);
        $this->assertFalse($connection->useCompleteQuit());
    }
}
