<?php
/**
 * ViewHelper permettant d'afficher un label suivi d'un DateTimeSelect avec les messages d'erreurs, sans fieldset
 * (à déclarer dans module.config.php comme ceci : 'view_helpers' => array('invokables' => array('formRowDateTime' => 'SbmCommun\Form\View\Helper\FormRowDateTime'),)
 *
 * Usage dans une vue : echo $this->formRowDate($elementDate);
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form/View/Helper
 * @filesource FormRowDateTime.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Form\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Form\Element\DateTimeSelect;
use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormElementErrors;
use Zend\Form\View\Helper\FormLabel;
use Zend\Form\View\Helper\FormDateTimeSelect;

class FormRowDateTime extends FormDateTimeSelect
{

    public function __invoke(ElementInterface $element = NULL, $dateType = 1, $timeType = 1, 
        $locale = 'FR_fr')
    {
        $this->setLocale($locale);
        if (! $element) {
            return $this;
        }
        $label = new FormLabel();
        $errors = new FormElementErrors();
        $result = $label($element) . $this->render($element);
        $result .= $errors($element, $element->getOption('error_attributes'));
        return $result;
    }
}
 