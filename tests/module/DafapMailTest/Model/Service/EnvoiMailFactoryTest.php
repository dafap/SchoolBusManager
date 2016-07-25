<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource EnvoiMailFactoryTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 juil. 2016
 * @version 2016-2.1.10
 */
namespace DafapMailTest\Model\Service;

use PHPUnit_Framework_TestCase;
use DafapMail\Model\Service\EnvoiMailFactory;
use DafapMail\Model\Service\ConfigServiceFactory;
use DafapMail\Model\EnvoiMail;

class EnvoiMailFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateService()
    {
        $ma_config = [
            'sbm' => [
                'mail' => [
                    'foo' => 'bar'
                ]
            ]
        ];
        $configMail = new ConfigServiceFactory();
        $factory = new EnvoiMailFactory();
        $serviceLocator1 = $this->createMock('Zend\\ServiceManager\\ServiceLocatorInterface');
        $serviceLocator1->expects($this->any())
            ->method('get')
            ->with('Config')
            ->will($this->returnValue($ma_config));
        $config_mail = $configMail->createService($serviceLocator1);
        $serviceLocator2 = $this->createMock('Zend\\ServiceManager\\ServiceLocatorInterface');
        $serviceLocator2->expects($this->any())
            ->method('get')
            ->with('DafapMail\Config')
            ->will($this->returnValue($config_mail));
        $this->assertInstanceOf(EnvoiMail::class, $factory->createService($serviceLocator2), 'Ce n\'est pas un objet de type EnvoiMail.');
    }
}