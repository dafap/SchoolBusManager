<?php
/**
 * ViewHelper permettant d'afficher le formulaire d'actions au-dessus d'une liste dans la `zone-action`
 * (à déclarer dans module.config.php comme ceci : 'view_helpers' => array('invokables' => array('telephone' => 'SbmCommun\Form\View\Helper\Telephone',))
 *
 * Usage dans une vue : echo $this->telephone($data);
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form/View/Helper
 * @filesource Telephone.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 mai 2015
 * @version 2015-1
 */
namespace SbmCommun\Form\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Telephone extends AbstractHelper
{
    public function __invoke($data)
    {
        if (is_null($data)) {
            return '';
        } elseif (! is_string($data)) {
            throw new Exception('Un numéro de téléphone est attendu.');
        }
        return implode(' ', str_split($data, 2));
    }
}
 