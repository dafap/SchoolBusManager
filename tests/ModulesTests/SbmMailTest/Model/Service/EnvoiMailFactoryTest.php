<?php
/**
 * Test de création du service EnvoiMail de SbmMail
 * 
 * @project sbm
 * @package ModulesTests/SbmMailTest/Model/Service
 * @filesource EnvoiMailFactoryTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 juil. 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmMailTest\Model\Service;

use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;
use SbmMail\Model\Service\EnvoiMailFactory;
use SbmMail\Model\EnvoiMail;

class EnvoiMailFactoryTest extends PHPUnit_Framework_TestCase
{

    public function testCreateService()
    {
        $message = __METHOD__ .
             ' - EnvoiMailFactory ne renvoie pas un objet de type EnvoiMail.';
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService('SbmMail\Config', 
            [
                'transport' => [],
                'message' => [],
                'destinataires' => []
            ]);
        $envoiMailFactory = new EnvoiMailFactory();
        $envoiMail = $envoiMailFactory->createService($serviceLocator);
        $this->assertInstanceOf(EnvoiMail::class, $envoiMail, $message);
    }
}