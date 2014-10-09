<?php
/**
 * Ce petit formulaire présente un bouton submit et autant de hidden que nécessaire
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource ButtonForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

use Zend\Form\Form;

class ButtonForm extends Form
{

    /**
     * Constructeur du formulaire
     *
     * @param array() $submits
     *            Le tableau $submits est de la forme <pre>
     *            array(
     *            'name-button1' => array(validTagAttribute => valeur de l'attribut, ...)
     *            ...
     *            )
     *            </pre>
     *            où validTagAttribute est dans validGlobalAttributes (@see Zend\Form\View\Helper\AbstractHelper)
     *            ou dans validTagAttributes (@see Zend\View\Helper\FormButton)
     *            
     * @param array() $hiddens
     *            Le tableau $hiddens est de la forme <pre>
     *            array(
     *            'name-hidden1' => 'valeur-hidden1',
     *            ...
     *            )
     *            </pre>
     */
    public function __construct(array $submits, $hiddens = array())
    {
        parent::__construct('Form');
        $this->setAttribute('method', 'post');
        
        // les hiddens
        foreach ($hiddens as $name => $value) {
            $description = array(
                'name' => $name,
                'attributes' => array(
                    'type' => 'hidden'
                )
            );
            if (! is_null($value)) {
                $description['attributes']['value'] = $value;
            }
            $this->add($description);
        }
        // les boutons submits
        foreach ($submits as $name => $options) {
            $description = array(
                'name' => $name,
                'attributes' => array(
                    'type' => 'submit'
                )
            );
            foreach ($options as $option => $value) {
                if (is_array($value)) {
                    $value = implode(' ', $value);
                }
                if (is_int($option)) {
                    $description['attributes'][$value] = true;
                } else {
                    $description['attributes'][$option] = $value;
                }
            }
            $this->add($description);
        }
    }

/**
 * Ajout de l'option au bouton submit
 * (non-PHPdoc)
 *
 * @see \Zend\Form\Element::setOption()
 */
    /*
     * public function setOption($submit, $option, $valeur) { $button = $this->get($submit); $button->setOption($option, $valeur); }
     */
}