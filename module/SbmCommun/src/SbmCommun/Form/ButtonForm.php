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
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Form;

use Zend\Form\Form;

class ButtonForm extends Form
{

    /**
     * Constructeur du formulaire
     *
     * @param array() $hiddens
     *            Le tableau $hiddens est de la forme <pre>
     *            array(
     *            'name-hidden1' => 'valeur-hidden1' ou 'name-hidden1' => attributes (où attributes est un tableau d'attributs : id =>..., value =>...)
     *            ...
     *            )
     *            </pre>
     * @param array() $submits
     *            Le tableau $submits est de la forme <pre>
     *            array(
     *            'name-button1' => array(validTagAttribute => valeur de l'attribut, ...)
     *            ...
     *            )
     *            </pre>
     *            où validTagAttribute est dans validGlobalAttributes (@see Zend\Form\View\Helper\AbstractHelper)
     *            ou dans validTagAttributes (@see Zend\View\Helper\FormButton)
     */
    public function __construct($hiddens, array $submits, $nomform = 'Form', $avecCsrf = false)
    {
        parent::__construct($nomform);
        $this->setAttribute('method', 'post');
        // le Csrf
        if ($avecCsrf) {
            $this->add(
                [
                    'name' => 'csrf',
                    'type' => 'Zend\Form\Element\Csrf',
                    'options' => [
                        'csrf_options' => [
                            'timeout' => 180
                        ]
                    ]
                ]);
        }
        // les hiddens
        foreach ($hiddens as $name => $value) {
            $description = [
                'name' => $name,
                'type' => 'hidden'
            ];
            if (is_array($value)) {
                $description['attributes'] = $value;
            } elseif (! is_null($value)) {
                $description['attributes']['value'] = $value;
            }
            $this->add($description);
        }
        // les boutons submits
        foreach ($submits as $name => $options) {
            $description = [
                'name' => $name,
                'attributes' => [
                    'type' => 'submit'
                ]
            ];
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
}