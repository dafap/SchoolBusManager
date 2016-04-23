<?php
/**
 * Injection des objets nécessaires au listener ScolariteOK
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmPaiement/Listener/Service
 * @filesource ScolariteOKFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 avr. 2016
 * @version 2016-2
 */
namespace SbmPaiement\Listener\Service;

use SbmPaiement\Listener\ScolariteOK;

class ScolariteOKFactory extends AbstractFactory
{
    protected function init($db_manager, $plateforme, $config_plateforme)
    {
        return new ScolariteOK($db_manager, $plateforme, $config_plateforme);
    }
}