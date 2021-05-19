<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Captcha;

use ArrayObject;
use Zend\Captcha\Figlet as FigletCaptcha;
use Zend\Session\Container as SessionContainer;

/**
 * @group      Zend_Captcha
 */
class FigletTest extends CommonWordTest
{
    protected $wordClass = 'Zend\Captcha\Figlet';

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        if (isset($this->word)) {
            unset($this->word);
        }

        $this->captcha = new FigletCaptcha([
            'sessionClass' => 'ZendTest\Captcha\TestAsset\SessionContainer'
        ]);
    }

    public function testTimeoutPopulatedByDefault()
    {
        $ttl = $this->captcha->getTimeout();
        $this->assertNotEmpty($ttl);
        $this->assertInternalType('integer', $ttl);
    }

    public function testCanSetTimeout()
    {
        $ttl = $this->captcha->getTimeout();
        $this->captcha->setTimeout(3600);
        $this->assertNotEquals($ttl, $this->captcha->getTimeout());
        $this->assertEquals(3600, $this->captcha->getTimeout());
    }

    public function testGenerateReturnsId()
    {
        $id = $this->captcha->generate();
        $this->assertNotEmpty($id);
        $this->assertInternalType('string', $id);
        $this->id = $id;
    }

    public function testGetWordReturnsWord()
    {
        $this->captcha->generate();
        $word = $this->captcha->getWord();
        $this->assertNotEmpty($word);
        $this->assertInternalType('string', $word);
        $this->assertEquals(8, strlen($word));
        $this->word = $word;
    }

    public function testGetWordLength()
    {
        $this->captcha->setWordLen(4);
        $this->captcha->generate();
        $word = $this->captcha->getWord();
        $this->assertInternalType('string', $word);
        $this->assertEquals(4, strlen($word));
        $this->word = $word;
    }

    public function testGenerateIsRandomised()
    {
        $id1 = $this->captcha->generate();
        $word1 = $this->captcha->getWord();
        $id2 = $this->captcha->generate();
        $word2 = $this->captcha->getWord();

        $this->assertNotEmpty($id1);
        $this->assertNotEmpty($id2);
        $this->assertNotEquals($id1, $id2);
        $this->assertNotEquals($word1, $word2);
    }

    public function testGenerateInitializesSessionData()
    {
        $this->captcha->generate();
        $session = $this->captcha->getSession();
        $this->assertEquals($this->captcha->getTimeout(), $session->setExpirationSeconds);
        $this->assertEquals(1, $session->setExpirationHops);
        $this->assertEquals($this->captcha->getWord(), $session->word);
    }

    public function testWordValidates()
    {
        $this->captcha->generate();
        $input = ['id' => $this->captcha->getId() , 'input' => $this->captcha->getWord()];
        $this->assertTrue($this->captcha->isValid($input));
    }

    public function testMissingNotValid()
    {
        $this->captcha->generate();
        $this->assertFalse($this->captcha->isValid(''));
        $this->assertFalse($this->captcha->isValid([]));
        $input = ['input' => 'blah'];
        $this->assertFalse($this->captcha->isValid($input));
    }

    public function testWrongWordNotValid()
    {
        $this->captcha->generate();
        $input = ["id" => $this->captcha->getId(), "input" => "blah"];
        $this->assertFalse($this->captcha->isValid($input));
    }

    public function testUsesFigletCaptchaHelperByDefault()
    {
        $this->assertEquals('captcha/figlet', $this->captcha->getHelperName());
    }

    public function testCaptchaShouldBeConfigurableViaTraversableObject()
    {
        $options = [
            'name'         => 'foo',
            'sessionClass' => 'ZendTest\Captcha\TestAsset\SessionContainer',
            'wordLen'      => 6,
            'timeout'      => 300,
        ];
        $config  = new ArrayObject($options);
        $captcha = new FigletCaptcha($config);
        $test = $captcha->getOptions();
        $this->assertEquals($options, $test);
    }

    public function testShouldAllowFigletsLargerThanFourteenCharacters()
    {
        $this->captcha->setName('foo')
                      ->setWordLen(14);
        $id = $this->captcha->generate();
    }

    public function testShouldNotValidateEmptyInputAgainstEmptySession()
    {
        // Regression Test for ZF-4245
        $this->captcha->setName('foo')
                      ->setWordLen(6)
                      ->setTimeout(300);
        $id = $this->captcha->generate();

        // Unset the generated word
        // we have to reset $this->captcha for that
        $this->captcha->getSession()->word = null;
        $this->setUp();
        $this->captcha->setName('foo')
                      ->setWordLen(6)
                      ->setTimeout(300);
        $empty = [$this->captcha->getName() => ['id' => $id, 'input' => '']];
        $this->assertEquals(false, $this->captcha->isValid(null, $empty));
    }

    /**
     * @group ZF-5728
     */
    public function testSetSessionWorks()
    {
        if (headers_sent($file, $line)) {
            $this->markTestSkipped("Cannot use sessions because headers already sent");
        }
        $session = new SessionContainer('captcha');
        $this->captcha->setSession($session);
        $this->captcha->generate();
        $input = ["id" => $this->captcha->getId(), "input" => $this->captcha->getWord()];
        $this->assertTrue($this->captcha->isValid($input));
        $this->assertEquals($session->word, $this->captcha->getWord());
    }
}
