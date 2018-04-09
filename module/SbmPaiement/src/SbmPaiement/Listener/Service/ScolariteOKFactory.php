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
 * @date 5 avr. 2018
 * @version 2018-2.4.0
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