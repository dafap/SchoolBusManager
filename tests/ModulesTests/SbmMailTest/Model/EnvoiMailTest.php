<?php
/**
 * Test de la classe EnvoiMail
 * 
 * @project sbm
 * @package package_name
 * @filesource EnvoiMailTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 juil. 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmMailTest\Model;

use PHPUnit_Framework_TestCase;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceManager;
use SbmMail\Model\Service\EnvoiMailFactory;
use SbmMail\Model\EnvoiMail;

class EnvoiMailTest extends PHPUnit_Framework_TestCase
{
    use EventListenerIntrospectionTrait;

    protected $config_mail;

    public function setUp()
    {
        $config_application = include __DIR__ .
            '\..\..\..\..\config\autoload\sbm.local.php';
        $config_module = include __DIR__ .
            '\..\..\..\..\module\SbmMail\config\module.config.php';
        $this->config_mail = array_merge_recursive($config_application['sbm']['mail'],
            $config_module['sbm']['mail']);
    }

    public function testConfigMailStructure()
    {
        $this->assertArrayHasKey('transport', $this->config_mail,
            'La clé transport n\'a pas été trouvée dans la config de SbmMail.');
        if (array_key_exists('transport', $this->config_mail)) {
            $this->assertInternalType('array', $this->config_mail['transport'],
                'transport : Un tableau est attendu.');
            $this->assertArrayHasKey('mode', $this->config_mail['transport'],
                'Le mode de transport n\'est pas definie.');
            if (array_key_exists('mode', $this->config_mail['transport'])) {
                $this->assertContains($this->config_mail['transport']['mode'],
                    [
                        'smtp',
                        'sendmail'
                    ], 'Le mode de transport est incorrect.');
                if ($this->config_mail['transport']['mode'] == 'smtp') {
                    $this->assertArrayHasKey('smtpOptions',
                        $this->config_mail['transport'], 'smtpOptions n\'est pas definie.');
                }
            }
            $this->assertArrayHasKey('transportSsl', $this->config_mail['transport'],
                'transportSsl n\'est pas definie.');
        }
        $this->assertArrayHasKey('message', $this->config_mail,
            'La clé message n\'a pas été trouvée dans la config de SbmMail.');
        if (array_key_exists('message', $this->config_mail)) {
            $this->assertArrayHasKey('from', $this->config_mail['message'],
                'Manque le from dans message [email, name]');
            $this->assertArrayHasKey('replyTo', $this->config_mail['message'],
                'Manque le replyTo dans message [email, name]');
            $this->assertArrayHasKey('subject', $this->config_mail['message'],
                'Manque le subject dans message (string)');
            $this->assertArrayHasKey('body', $this->config_mail['message'],
                'Manque le paramétrage de body dans message (text, html)');
            $this->assertArrayHasKey('type', $this->config_mail['message'],
                'Manque le type dans message (text/html)');
            $this->assertArrayHasKey('html_encoding', $this->config_mail['message'],
                'Manque le html_encoding dans message (8bit)');
            $this->assertArrayHasKey('message_encoding', $this->config_mail['message'],
                'Manque le message_encoding dans message (utf-8)');
        }
        $this->assertArrayHasKey('destinataires', $this->config_mail,
            'La clé destinataires n\'a pas été trouvée dans la config de SbmMail.');
    }

    /*
     * Ne fonctionne pas : - getListeners est deprecated, - assertListenerAtPriority prend
     * un EventManager comme 4e paramètre et pas un SharedEventManager - ça bloque si je
     * fais un die(var_dump($sharedEvents)); / public function
     * testRegistersOnSendMailListenerOnExpectedPriority() { $events = new EventManager();
     * $serviceLocator = new ServiceManager();
     * $serviceLocator->setService('SbmMail\Config', $this->config_mail);
     * $envoiMailFactory = new EnvoiMailFactory(); $envoiMail =
     * $envoiMailFactory->createService($serviceLocator); $envoiMail->attach($events);
     * $listeners = $events->getListeners('*'); //die(var_dump($events)); $sharedEvents =
     * $events->getSharedManager(); $this->assertListenerAtPriority([$envoiMail,
     * 'onSendMail'], 1, 'sendMail', $sharedEvents); }//
     */
}