<?php
/**
 * Formulaire permettantd de remplacer une station par une autre
 *
 * @project sbm
 * @package SbmGestion/Form
 * @filesource StationDoublon.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 juil. 2017
 * @version 2017-2.3.5
 */
namespace SbmGestion\Form;

use SbmCommun\Form\AbstractSbmForm As Form;
use Zend\InputFilter\InputFilterProviderInterface;

class StationDoublon extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('doublon');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'page',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 180
                )
            )
        ));

        $this->add(array(
            'name' => 'stationASupprId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'station-a-supprId',
                'class' => 'sbm-width-55c'
            ),
            'options' => array(
                'label' => 'Point d\'arrêt à supprimer',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Quel point d\'arrêt ?',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'stationAGarderId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'station-a-garderId',
                'class' => 'sbm-width-55c'
            ),
            'options' => array(
                'label' => 'Point d\'arrêt à garder',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Quel point d\'arrêt ?',
                'error_attributes' => array(
                    'class' => 'sbm-error'
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
        return [
            'stationASupprId' => array(
                'name' => 'stationASupprId',
                'required' => true
            ),
            'stationAGarderId' => array(
                'name' => 'stationAGarderId',
                'required' => true
            )
        ];
    }
}