<?php
/**
 * Page de sélection du lot de cartes à créer
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmGestion/Form
 * @filesource SelectionCartes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 juil. 2015
 * @version 2015-1
 */
namespace SbmGestion\Form;

use SbmCommun\Form\AbstractSbmForm as Form;
use Zend\InputFilter\InputFilterProviderInterface;

class SelectionCartes extends Form implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('decision');
        $this->setAttribute('method', 'post');
        $this->setAttribute('target', '_blank');
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'selection',
            'attributes' => array(
                'class' => 'sbm-radio'
            ),
            'options' => array(
                'label' => 'Quel lot de cartes ou d\'étiquettes voulez-vous obtenir ?',
                'label_attributes' => array(
                    'class' => 'sbm-label-radio'
                ),
                'value_options' => array(
                    'nouvelle' => array(
                        'value' => 'nouvelle',
                        'label' => 'Dernière préparation',
                        'attributes' => array(
                            'id' => 'selectionradio0'
                        )
                    ),
                    'reprise' => array(
                        'value' => 'reprise',
                        'label' => 'Reprise d\'une préparation',
                        'attributes' => array(
                            'id' => 'selectionradio1'
                        )
                    ),
                    'selection' => array(
                        'value' => 'selection',
                        'label' => 'Fiches sélectionnées',
                        'attributes' => array(
                            'id' => 'selectionradio2'
                        )
                    )
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'critere',
            'attributes' => array(
                'class' => 'sbm-radio'
            ),
            'options' => array(
                'label' => 'Quels élèves voulez-vous traiter ?',
                'label_attributes' => array(
                    'class' => 'sbm-label-radio'
                ),
                'value_options' => array(
                    'inscrits' => array(
                        'value' => 'inscrits',
                        'label' => 'Inscrits',
                        'attributes' => array(
                            'id' => 'critereradio0'
                        )
                    ),
                    'preinscrits' => array(
                        'value' => 'preinscrits',
                        'label' => 'Préinscrits',
                        'attributes' => array(
                            'id' => 'critereradio1'
                        )
                    ),
                    'tous' => array(
                        'value' => 'tous',
                        'label' => 'Tous',
                        'attributes' => array(
                            'id' => 'critereradio2'
                        )
                    )
                )
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'document',
            'attributes' => array(
                'class' => 'sbm-radio'
            ),
            'options' => array(
                'label' => 'Quels document voulez-vous créer ?',
                'label_attributes' => array(
                    'class' => 'sbm-label-radio'
                ),
                'value_options' => array(
                    'carte' => array(
                        'value' => 'Cartes',
                        'label' => 'Cartes',
                        'attributes' => array(
                            'id' => 'documentradio0'
                        )
                    ),
                    'etiquette' => array(
                        'value' => 'Etiquettes pour les cartes',
                        'label' => 'Etiquettes',
                        'attributes' => array(
                            'id' => 'documentradio1'
                        )
                    ),
                    'liste' => array(
                        'value' => 'Liste de contrôle des cartes',
                        'label' => 'Liste de contrôle',
                        'attributes' => array(
                            'id' => 'documentradio2'
                        )
                    )
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'dateReprise',
            'attributes' => array(
                'id' => 'dateReprise',
                'class' => 'sbm-width-20c'
            ),
            'options' => array(
                'label' => 'Date de la reprise',
                'label_attributes' => array(),
                'empty_option' => 'Quelle date ?',
                'allow_empty' => true,
                'disable_inarray_validator' => false,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'nouvelle',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Préparer une nouvelle édition',
                'class' => 'button default submit left-95px'
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Lancer l\'édition',
                'class' => 'button default submit left-95px'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'autofocus' => 'autofocus',
                'value' => 'Abandonner',
                'class' => 'button default cancel'
            )
        ));
    }
    
    public function getInputFilterSpecification()
    {
        return array(
            'dateReprise' => array(
                'name' => 'dateReprise',
                'required' => false
            )
        );
    }
    
    public function isValid()
    {
        $ok = parent::isValid();
        if ($ok) {
            $data = $this->getData();
            if ($data['selection'] == 'reprise') {
                if (empty($data['dateReprise'])) {
                    $ok = false;
                    $dateRepriseElement = $this->get('dateReprise');
                    $dateRepriseElement->setMessages(array('dateInvalid' => 'Aucune date. Reprise impossible.'));
                }                
            }
        }
        return $ok;
    }
}