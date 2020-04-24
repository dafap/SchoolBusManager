<?php
/**
 * ViewHelper permettant d'afficher le formulaire d'actions au-dessus d'une liste dans la `zone-action`,
 * à déclarer dans module.config.php comme ceci :
 * 'view_helpers' => ['invokables' => ['telephone' => 'SbmCommun\Model\View\Helper\Telephone',]]
 *
 * Usage dans une vue : echo $this->telephone($data);
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/View/Helper
 * @filesource Telephone.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;
use SbmBase\Model\StdLib;

class Telephone extends AbstractHelper
{

    public function __invoke($data, $sep = ' ')
    {
        return StdLib::formatTelephone($data, $sep);
    }
}
