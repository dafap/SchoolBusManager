<?php
/**
 * Formulaire de choix du payeur (Famille, Gratuit ou Organisme)
 *
 * Le champ `organismeId` du formulaire n'est activé que si le bouton radio 
 * du champ `gratuit` est sur Organisme.
 * 
 * @project sbm
 * @package SbmGestion/Form/Eleve
 * @filesource PriseEnChargePaiement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 oct. 2015
 * @version 2015-1
 */
namespace SbmGestion\Form\Eleve;

use SbmCommun\Form\AbstractSbmForm as Form;
use Zend\InputFilter\InputFilterProviderInterface;

class PriseEnChargePaiement extends Form implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('formpaiement');
        $this->setAttribute('method', 'post');
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
            'type' => 'hidden',
            'name' => 'eleveId'
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'gratuit',
            'attributes' => array(),
            'options' => array(
                'label' => 'Choisissez le mode de prise en charge du paiement',
                'label_attributes' => array(
                    'class' => 'sbm-label-radio'
                ),
                'value_options' => array(
                    array(
                        'value' => '0',
                        'label' => 'Famille',
                        'attributes' => array(
                            'id' => 'gratuitradio0',
                            'checked' => 'checked'
                        )
                    ),
                    array(
                        'value' => '1',
                        'label' => 'Gratuit',
                        'attributes' => array(
                            'id' => 'gratuitradio1'
                        )
                    ),
                    array(
                        'value' => '2',
                        'label' => 'Organisme',
                        'attributes' => array(
                            'id' => 'gratuitradio2'
                        )
                    )
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'organismeId',
            'attributes' => array(
                'id' => 'scolarites-organismeId',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Organisme payeur',
                'label_attributes' => array(
                    'class' => 'sbm-form-auto'
                ),
                'empty_option' => 'Choisissez dans la liste',
                'allow_empty' => true,
                'disable_inarray_validator' => false,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'class' => 'button default cancel'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'organismeId' => array(
                'name' => 'organismeId',
                'required' => false
            )
        );
    }
}