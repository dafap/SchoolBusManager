<?php
/**
 * ViewHelper permettant d'afficher un label suivi d'un DateSelect avec les 
 * messages d'erreurs, sans fieldset, à déclarer dans module.config.php comme 
 * ceci : 
 * 'view_helpers' => ['invokables' => ['formRowDate' => 'SbmCommun\Form\View\Helper\FormRowDate'),)
 *
 * Usage dans une vue : echo $this->formRowDate($elementDate);
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form/View/Helper
 * @filesource FormRowDate.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormDateSelect;
use Zend\Form\View\Helper\FormElementErrors;
use Zend\Form\View\Helper\FormLabel;

class FormRowDate extends FormDateSelect
{

    public function __invoke(ElementInterface $element = NULL, $dateType = 1, $locale = 'FR_fr')
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
 