<?php
/**
 * Implémente quelques méthodes utiles pour les formulaires
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource AbstractSbmForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

use Zend\Form\Form;

abstract class AbstractSbmForm extends Form
{
    public function setMaxLength(array $array)
    {
        foreach ($array as $elementName => $maxLength) {
            try {
                $e = $this->get($elementName);
                $type = $e->getAttribute('type');
                if (! is_null($type) && $type == 'text') {
                    $e->setAttribute('maxlength', $maxLength);
                }
            } catch (\Exception $e) {
            }
        }
    }
    
    public function setValueOptions($element, array $values_options)
    {
        $e = $this->get($element);
        $e->setValueOptions($values_options);
    }
    
    
}