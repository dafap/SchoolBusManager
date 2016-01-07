<?php
/**
 * Formulaire permettant de paramétrer la préparation d'une simulation
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmGestion/Form
 * @filesource Simulation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 janv. 2016
 * @version 2016-1.7.1
 */
namespace SbmGestion\Form;

use SbmCommun\Form\AbstractSbmForm As Form;
use Zend\InputFilter\InputFilterProviderInterface;

class Simulation extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('simulation');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'millesime',
            'type' => 'text',
            'attributes' => array(
                'id' => 'simulation_millesime',
                'class' => 'sbm-width-5c'
            ),
            'options' => array(
                'label' => 'Millésime de base',
                'label_attributes' => array(
                    'class' => 'sbm-label sbm-form-auto'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error sbm-form-auto'
                )
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'decision-cancel',
                'autofocus' => 'autofocus',
                'class' => 'button default cancel left-10px'
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Valider',
                'id' => 'decision-submit',
                'class' => 'button default submit left-10px'
            )
        ));
    }
    
    public function getInputFilterSpecification()
    {
        return array(
            'millesime' => array(
                'name' => 'millesime',
                'required' => true
            )
        );
    }
} 