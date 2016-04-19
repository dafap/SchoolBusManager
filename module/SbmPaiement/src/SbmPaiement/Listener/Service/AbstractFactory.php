<?php
/**
 * Injection des objets nécessaires aux listeners AbstractListener
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmPaiement/Listener/Service
 * @filesource AbstractFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 avr. 2016
 * @version 2016-2
 */
namespace SbmPaiement\Listener\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCommun\Model\StdLib;

abstract class AbstractFactory implements FactoryInterface
{
  public function createService(ServiceLocatorInterface $serviceLocator)
  {
      $config_application = $serviceLocator->get('config');
      $plateforme = strtolower(StdLib::getParamR(array(
          'sbm',
          'paiement',
          'plateforme'
      ), $config_application));
      $config_plateforme = StdLib::getParamR(array(
          'sbm',
          'paiement',
          $plateforme
      ), $config_application);
      $db_manager = $serviceLocator->get('Sbm\DbManager');
      return $this->init($db_manager, $plateforme, $config_plateforme);
  }
  
  abstract protected function init($db_manager, $plateforme, $config_plateforme);
}