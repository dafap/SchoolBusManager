<?php
/**
 * Injection des objets nécessaires au listener PaiementOK
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmPaiement/Listener/Service
 * @filesource PaiementOKFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 avr. 2016
 * @version 2016-2
 */
namespace SbmPaiement\Listener\Service;

use SbmPaiement\Listener\PaiementOK;

class PaiementOKFactory extends AbstractFactory
{
    protected function init($db_manager, $plateforme, $config_plateforme)
    {
        return new PaiementOK($db_manager, $plateforme, $config_plateforme);
    }
}