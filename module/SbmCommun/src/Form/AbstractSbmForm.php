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
 * @date 11 mars 2021
 * @version 2021-2.6.1
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
        return $this;
    }

    /**
     * Affecte les values_options à l'élément indiqué
     *
     * @param string $element
     * @param array $values_options
     */
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

    /**
     * Renvoie un tableau contenant les noms des champs du formulaire (sans submit)
     *
     * @return array
     */
    public function getElementNames()
    {
        $array = [];
        foreach ($this->getElements() as $element) {
            if ($element->getName() != 'submit') {
                $array[] = $element->getName();
            }
        }
        return $array;
    }

    /**
     * Affecte une classe css à tous les éléments du formulaire
     *
     * @param string $css_class
     */
    public function setCssClass($css_class)
    {
        foreach ($this->getElements() as $element) {
            $element->setAttribute('class', $css_class);
        }
    }
}