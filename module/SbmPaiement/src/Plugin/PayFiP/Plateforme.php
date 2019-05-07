<?php
/**
 * Plugin pour la plateforme de paiement PayFiP
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package SbmPaiement/Plugin/PayFiP
 * @filesource Plateforme.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmPaiement\Plugin\PayFiP;

use SbmPaiement\Plugin;
use Zend\Stdlib\Parameters;

class Plateforme extends Plugin\AbstractPlateforme implements Plugin\PlateformeInterface
{
    protected function init()
    {
    }

    public function getUrl()
    {
    }

    protected function validPaiement()
    {
    }

    public function prepareAppel($params)
    {
    }

    protected function validNotification(Parameters $data)
    {
    }

    protected function prepareData()
    {
    }

    public function getUniqueId(array $params)
    {
    }
}