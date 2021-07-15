<?php
/**
 * ViewHelper permettant de créer et d'afficher un checkbox qui pourra être traité
 * en ajax en dehors d'un form
 *
 * à déclarer dans module.config.php comme ceci :
 * 'view_helpers' => [
 * 'invokables' => [
 * 'renderCheckbox' => \SbmCommun\Form\View\Helper\RenderCheckbox::class,]
 * ]
 *
 * @project sbm
 * @package SbmCommun/src/Form/View/Helper
 * @filesource RenderCheckbox.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 juil. 2021
 * @version 2021-2.5.13
 */
namespace SbmCommun\Form\View\Helper;

use Zend\Form\Element\Checkbox;
use Zend\View\Helper\AbstractHelper;

class RenderCheckbox extends AbstractHelper
{

    public function __invoke($name, $op, $id, $value, $attributes = [])
    {
        $element = new Checkbox($name);
        $element->setUseHiddenElement(false)
            ->setAttribute('id', $op . $id)
            ->setAttribute('data-id', $id)
            ->setValue($value);
        foreach ($attributes as $key => $attribute_value) {
            $element->setAttribute($key, $attribute_value);
            ;
        }
        return $this->view->formCheckbox($element);
    }

    public function __old_invoke($name, $id, $value, $attributes = [])
    {
        $element = new Checkbox($name);
        $element->setUseHiddenElement(false)
            ->setAttribute('id', $id)
            ->setValue($value);
        foreach ($attributes as $key => $attribute_value) {
            $element->setAttribute($key, $attribute_value);
            ;
        }
        return $this->view->formCheckbox($element);
    }
}