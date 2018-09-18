<?php
/**
 * Classe abstraite de ces ViewHelpers
 *
 * méthodes communes
 * 
 * @project sbm
 * @package SbmAdmin/Model/View/Helper
 * @filesource AbstractHelper.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 juin 2018
 * @version 2018-2.4.1
 */
namespace SbmAdmin\Model\View\Helper;

use Zend\View\Helper\AbstractHelper as ZendAbstractHelper;

abstract class AbstractHelper extends ZendAbstractHelper
{

    /**
     * Nom du bouton qui sera placé dans l'attribut data-button
     *
     * @var string
     */
    protected $button_name;

    protected function btnSuppr($array, $nom)
    {
        $title = "Retirer $nom de cette liste";
        $css = 'fam-delete';
        $href = '/op:delete';
        foreach ($array as $key => $value) {
            $href .= "/$key:$value";
        }
        $template = '<div class="menu"><i class="%s" data-button="%s" data-href="%s" title="%s"></i></div>';
        return sprintf($template, $css, $this->button_name, $href, $title);
    }

    protected function btnAjout($array, $nom)
    {
        $title = "Ajouter une $nom";
        $css = 'fam-add';
        $href = '/op:add';
        foreach ($array as $key => $value) {
            $href .= "/$key:$value";
        }
        $template = '<div class="menu centre"><i class="%s" data-button="%s" data-href="%s" title="%s"></i></div>';
        return sprintf($template, $css, $this->button_name, $href, $title);
    }
}