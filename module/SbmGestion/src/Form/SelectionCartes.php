<?php
/**
 * Page de sélection du lot de cartes à créer
 *
 * @project sbm
 * @package SbmGestion/Form
 * @filesource SelectionCartes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 fév. 2019
 * @version 2019-2.5.0
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
        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'selection',
                'attributes' => [
                    'class' => 'sbm-radio'
                ],
                'options' => [
                    'label' => 'Quel lot de cartes ou d\'étiquettes voulez-vous obtenir ?',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        'nouvelle' => [
                            'value' => 'nouvelle',
                            'label' => 'Dernière préparation',
                            'attributes' => [
                                'id' => 'selectionradio0'
                            ]
                        ],
                        'reprise' => [
                            'value' => 'reprise',
                            'label' => 'Reprise d\'une préparation',
                            'attributes' => [
                                'id' => 'selectionradio1'
                            ]
                        ],
                        'selection' => [
                            'value' => 'selection',
                            'label' => 'Fiches sélectionnées',
                            'attributes' => [
                                'id' => 'selectionradio2'
                            ]
                        ]
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'critere',
                'attributes' => [
                    'class' => 'sbm-radio'
                ],
                'options' => [
                    'label' => 'Quels élèves voulez-vous traiter ?',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        'inscrits' => [
                            'value' => 'inscrits',
                            'label' => 'Abonnements payés',
                            'attributes' => [
                                'id' => 'critereradio0'
                            ]
                        ],
                        'preinscrits' => [
                            'value' => 'preinscrits',
                            'label' => 'Abonnements impayés',
                            'attributes' => [
                                'id' => 'critereradio1'
                            ]
                        ],
                        'tous' => [
                            'value' => 'tous',
                            'label' => 'Tous',
                            'attributes' => [
                                'id' => 'critereradio2'
                            ]
                        ]
                    ]
                ]
            ]);

        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'document',
                'attributes' => [
                    'class' => 'sbm-radio'
                ],
                'options' => [
                    'label' => 'Que voulez-vous obtenir ?',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'dateReprise',
                'attributes' => [
                    'id' => 'dateReprise',
                    'class' => 'sbm-width-20c'
                ],
                'options' => [
                    'label' => 'Date de la reprise',
                    'label_attributes' => [],
                    'empty_option' => 'Quelle date ?',
                    'allow_empty' => true,
                    'disable_inarray_validator' => false,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'nouvelle',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Préparer une nouvelle édition',
                    'class' => 'button default submit left-95px'
                ]
            ]);
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Lancer l\'édition',
                    'class' => 'button default submit left-95px'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'autofocus' => 'autofocus',
                    'value' => 'Abandonner',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'dateReprise' => [
                'name' => 'dateReprise',
                'required' => false
            ]
        ];
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
                    $dateRepriseElement->setMessages(
                        [
                            'dateInvalid' => 'Aucune date. Reprise impossible.'
                        ]);
                }
            }
        }
        return $ok;
    }

    public function setDocumentValueOptions($aDocumentNames, $db_manager)
    {
        $document = $this->get('document');
        $tDocument = $db_manager->get('Sbm\Db\System\Documents');
        $value_options = [];
        foreach ($aDocumentNames as $array) {
            $name = $array['libelle'];
            $id = $tDocument->getDocumentId($name);
            $value_options[$name] = $tDocument->getRecord($id)->title;
        }
        $document->setValueOptions($value_options);
    }
}