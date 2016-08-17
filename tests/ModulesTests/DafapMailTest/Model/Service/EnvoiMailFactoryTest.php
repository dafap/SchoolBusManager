<?php
/**
 * Test de création du service EnvoiMail de DafapMail
 * 
 * @project sbm
 * @package ModulesTests/DafapMailTest/Model/Service
 * @filesource EnvoiMailFactoryTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 juil. 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\DafapMailTest\Model\Service;

use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;
use DafapMail\Model\Service\EnvoiMailFactory;
use DafapMail\Model\EnvoiMail;

class EnvoiMailFactoryTest extends PHPUnit_Framework_TestCase
{

    public function testCreateService()
    {
        $message = __METHOD__ . ' - EnvoiMailFactory ne renvoie pas un objet de type EnvoiMail.';
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService('DafapMail\Config', [
            'transport' => [],
            'message' => [],
            'destinataires' => []
        ]);
        $envoiMailFactory = new EnvoiMailFactory();
        $envoiMail = $envoiMailFactory->createService($serviceLocator);
        $this->assertInstanceOf(EnvoiMail::class, $envoiMail, $message);
    }
}