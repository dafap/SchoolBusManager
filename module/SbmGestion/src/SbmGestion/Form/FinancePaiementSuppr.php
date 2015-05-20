<?php
/**
 * Formulaire de confirmation de la suppression d'un paiement.
 *
 * Lors de la suppression d'un paiement la raison est demandée et est obligatoire.
 * 
 * @project dbm
 * @package SbmGestion/Form
 * @filesource FinancePaiementSuppr.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 févr. 2015
 * @version 2015-1
 */
namespace SbmGestion\Form;

use SbmCommun\Form\AbstractSbmForm As Form;
use Zend\InputFilter\InputFilterProviderInterface;

class FinancePaiementSuppr extends Form implements InputFilterProviderInterface
{

    public function __construct($param = 'finance-paiement-suppr')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'paiementId',
            'type' => 'hidden',
        ));
        
        $this->add(array(
            'type' => 'textarea',
            'name' => 'note',
            'attributes' => array(
                'id' => 'note',
                'class' => 'sbm-width-55c'
            ),
            'options' => array(
                'label' => 'Motif de la suppression',
                'label_attributes' => array(
                    'class' => 'sbm-label-top'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Confirmer',
                'id' => 'finance-paiement-suppr-submit',
                'autofocus' => 'autofocus',
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'finance-paiement-suppr-cancel',
                'class' => 'button default cancel'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'paiementId' => array(
                'name' => 'paiementId',
                'required' => true
            ),
            'note' => array(
                'name' => 'note',
                'required' => true
            )
        );
    }
}