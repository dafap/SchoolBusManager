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
 * @date 3 avr. 2018
 * @version 2018-2.4.0
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
            } catch (\Exception $e) {}
        }
        return $this;
    }

    public function setValueOptions($element, array $values_options)
    {
        $e = $this->get($element);
        $e->setValueOptions($values_options);
        return $this;
    }

    /**
     * Place une classe 'required' aux labels des champs obligatoires
     * (non-PHPdoc)
     *
     * @see \Zend\Form\Form::prepare()
     */
    public function prepare()
    {
        parent::prepare();
        foreach ($this->getInputFilter()->getInputs() as $input) {
            if ($input->isRequired()) {
                $el = $this->get($input->getName());
                $labelAttributes = (array) $el->getLabelAttributes();
                if (array_key_exists('class', $labelAttributes)) {
                    $labelAttributes['class'] .= ' required';
                } else {
                    $labelAttributes['class'] = 'required';
                }
                $el->setLabelAttributes($labelAttributes);
            }
        }
        return $this;
    }
}