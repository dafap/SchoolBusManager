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
 * @date 5 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Telephone extends AbstractHelper
{

    public function __invoke($data)
    {
        if (is_null($data)) {
            return '';
        } elseif (! is_string($data)) {
            throw new InvalidArgumentException('Un numéro de téléphone est attendu.');
        }
        $count = 0;
        $data = str_replace('+33', '0', $data, $count);
        if (substr($data, 0, 1) == '+') {
            return $data;
        }
        $render = implode(' ', str_split($data, 2));
        if ($count) {
            $render = '(+33) ' . ltrim($render, '0');
        }
        return $render;
    }
}
