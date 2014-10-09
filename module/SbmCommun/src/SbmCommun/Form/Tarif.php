<?php
/**
 * Formulaire de saisie et modificationd d'un tarif
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Tarif
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

class Tarif extends AbstractSbmForm
{

    public function __construct($param = 'tarif')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'tarifId',
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
            'name' => 'nom',
            'type' => 'text',
            'attributes' => array(
                'id' => 'tarif-nom',
                'class' => 'sbm-text48'
            ),
            'options' => array(
                'label' => 'Libellé du tarif',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'montant',
            'type' => 'text',
            'attributes' => array(
                'id' => 'tarif-montant',
                'class' => 'sbm-text11'
            ),
            'options' => array(
                'label' => 'Montant',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'rythme',
            'attributes' => array(
                'id' => 'tarif-rytme',
                'class' => 'sbm-select2'
            ),
            'options' => array(
                'label' => 'Rythme de paiement',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'empty_option' => 'Choisissez un rythme de paiement',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'grille',
            'attributes' => array(
                'id' => 'tarif-grille',
                'class' => 'sbm-select2'
            ),
            'options' => array(
                'label' => 'Grille tarifaire',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'empty_option' => 'Choisissez une grille',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'mode',
            'attributes' => array(
                'id' => 'tarif-mode',
                'class' => 'sbm-select2'
            ),
            'options' => array(
                'label' => 'Mode de paiement',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'empty_option' => 'Choisissez un mode de paiement',
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
                'id' => 'tarif-submit',
                'autofocus' => 'autofocus',
                'class' => 'button submit left135'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'tarif-cancel',
                'class' => 'button cancel'
            )
        ));
    }
}