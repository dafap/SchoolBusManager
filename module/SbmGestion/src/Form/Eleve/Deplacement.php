<?php
/**
 * Formulaire permettant de sélectionner les élèves à déplacer et les services impactés
 *
 * Attention ! Si ce formulaire est appelé depuis un service, les éléments moment et serviceinitial
 * seront initialisés et verrouillés ou cachés (hiddens).
 *
 * @project sbm
 * @package SbmGestion/Form/Eleve
 * @filesource Deplacement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 juil. 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Form\Eleve;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class Deplacement extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($name = null, $options = [])
    {
        parent::__construct($name ?: 'deplacement', $options);
        $this->add([
            'name' => 'moment',
            'type' => 'hidden'
        ])
            ->add([
            'name' => 'serviceinitial',
            'type' => 'hidden'
        ])
            ->add(
            [
                'name' => 'servicefinal',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'servicefinal',
                    'class' => 'service',
                    'autofocus' => 'autofocus',
                    'title' => 'Service sur lesquel les élèves devraient être affectés après le déplacement.'
                ],
                'options' => [
                    'label' => 'Vers le service',
                    'label_attributes' => [
                        'class' => 'label_class service'
                    ],
                    'empty_option' => 'Choisir le service final',
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'etablissementcommune',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'etablissementcommune',
                    'multiple' => 'multiple',
                    'class' => 'commune',
                    'title' => 'Laisser vide si vous voulez toutes les communes.'
                ],
                'options' => [
                    'label' => 'Restreindre les communes des établissements',
                    'label_attributes' => [
                        'class' => 'label_class commune',
                        'title' => 'Vous pouvez sélectionner plusieurs communes dans la liste déroulante.'
                    ],
                    'empty_option' => 'Filtrer les commmunes concernées',
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'etablissementniveau',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'etablissementniveau',
                    'multiple' => 'multiple',
                    'class' => 'niveau',
                    'title' => 'Laisser vide si vous voulez tous les niveaux.'
                ],
                'options' => [
                    'label' => 'Restreindre les niveaux des établissements',
                    'label_attributes' => [
                        'class' => 'label_class niveau',
                        'title' => 'Vous pouvez sélectionner plusieurs niveaux dans la liste déroulante.'
                    ],
                    'empty_option' => 'Quels niveaux ?',
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'etablissementId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'etablissementId',
                    'multiple' => 'multiple',
                    'class' => 'etablissementId',
                    'title' => 'Laisser vide si vous souhaitez tous les établissements.'
                ],
                'options' => [
                    'label' => 'Restreindre les établissements concernés',
                    'label_attributes' => [
                        'class' => 'label_class etablissementId',
                        'title' => 'Vous pouvez sélectionner plusieurs établissements dans la liste déroulante.'
                    ],
                    'empty_option' => 'Quels établissements ?',
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'regimeId',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
                    'id' => 'regimeId',
                    'class' => 'regimeId',
                    'value' => 2
                ],
                'options' => [
                    'label' => 'Restreindre les régimes des élèves concernés',
                    'label_attributes' => [
                        'class' => 'label_class regimeId'
                    ],
                    'value_options' => [
                        [
                            'value' => 0,
                            'label' => 'DP',
                            'label_attributes' => [
                                'class' => 'first'
                            ]
                        ],
                        1 => 'Internes',
                        2 => 'Tous'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'classeId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'classeId',
                    'multiple' => 'multiple',
                    'class' => 'classeId',
                    'title' => 'Laisser vide si vous souhaitez toutes les classes.'
                ],
                'options' => [
                    'label' => 'Restreindre les classes concernées',
                    'label_attributes' => [
                        'class' => 'label_class classeId',
                        'title' => 'Vous pouvez sélectionner plusieurs classes dans la liste déroulante.'
                    ],
                    'empty_option' => 'Quelles classes ?',
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'classeniveau',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'classeniveau',
                    'multiple' => 'multiple',
                    'class' => 'niveau',
                    'title' => 'Laisser vide si vous voulez tous les niveaux.'
                ],
                'options' => [
                    'label' => 'Restreindre les niveaux d\'étude des élèves',
                    'label_attributes' => [
                        'class' => 'label_class niveau',
                        'title' => 'Vous pouvez sélectionner plusieurs niveaux dans la liste déroulante.'
                    ],
                    'empty_option' => 'Quels niveaux ?',
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'stationId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'stationId',
                    'multiple' => 'multiple',
                    'class' => 'stationId',
                    'title' => 'Laisser vide si vous voulez agir sur toutes les stations communes aux deux services.'
                ],
                'options' => [
                    'disable_inarray_validator' => true,
                    'label' => 'Restreindre les stations d\'origine',
                    'label_attributes' => [
                        'class' => 'label_class stationId',
                        'title' => 'La liste déroulante ne présente que les stations communes aux deux services.'
                    ],
                    'empty_option' => 'Quelles stations d\'origine ?',
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'paiement',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
                    'id' => 'paiement',
                    'class' => 'paiement',
                    'value' => 2
                ],
                'options' => [
                    'label' => 'Restreindre le paiement de l\'abonnement',
                    'label_attributes' => [
                        'class' => 'label_class paiement'
                    ],
                    'value_options' => [
                        [
                            'value' => 0,
                            'label' => 'Impayés',
                            'label_attributes' => [
                                'class' => 'first'
                            ]
                        ],
                        1 => 'Payés',
                        2 => 'Tous'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'carte',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
                    'id' => 'carte',
                    'class' => 'carte',
                    'value' => 2
                ],
                'options' => [
                    'label' => 'Restreindre l\'état de la carte de transport',
                    'label_attributes' => [
                        'class' => 'label_class carte'
                    ],
                    'value_options' => [
                        [
                            'value' => 0,
                            'attributes' => [
                                'id' => 'carteoption0'
                            ],
                            'label' => 'Non tirée',
                            'label_attributes' => [
                                'title' => 'Ces fiches sont dans aucun lot de cartes',
                                'class' => 'carteoption first'
                            ]
                        ],
                        [
                            'value' => 1,
                            'attributes' => [
                                'id' => 'carteoption1'
                            ],
                            'label' => 'Dans un lot de carte',
                            'label_attributes' => [
                                'title' => 'Permet aussi de sélectionner le lot',
                                'class' => 'carteoption'
                            ]
                        ],
                        2 => 'Tout'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'cartelot',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'cartelot',
                    'multiple' => 'multiple',
                    'class' => 'cartelot',
                    'title' => 'Laisser vide pour travailler sur tous les lots'
                ],
                'options' => [
                    'label' => 'Restreindre les lots de cartes de transport',
                    'label_attributes' => [
                        'class' => 'label_class cartelot',
                        'title' => 'Vous pouvez sélectionner plusieurs lots dans la liste déroulante.'
                    ],
                    'empty_option' => 'Quel lot ?',
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'dateinscription',
                'type' => 'Zend\Form\Element\Date',
                'attributes' => [
                    'id' => 'dateinscription',
                    'class' => 'dateinscription',
                    'title' => 'Laisser vide pour ne pas tenir compte de la date d\'inscription.'
                ],
                'options' => [
                    'label' => 'Date d\'inscription à partir de ...',
                    'label_attributes' => [
                        'class' => 'label_class dateinscription'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'type' => 'submit',
                'name' => 'submit',
                'attributes' => [
                    'class' => 'button default submit left-95px',
                    'value' => 'Enregistrer'
                ]
            ])
            ->add(
            [
                'type' => 'submit',
                'name' => 'cancel',
                'attributes' => [
                    'class' => 'button default cancel left-10px',
                    'value' => 'Abandonner'
                ]
            ]);
    }

    /**
     * Remplace les hiddens par des Radio et Select pour choix du service initial
     *
     * @return \SbmGestion\Form\Eleve\Deplacement
     */
    public function choixServiceInitial()
    {
        $momentValue = $this->get('moment')->getValue();
        $serviceinitialValue = $this->get('serviceinitial')->getValue();
        $this->get('servicefinal')->removeAttribute('autofocus');
        return $this->remove('moment')
            ->remove('serviceinitial')
            ->add(
            [
                'name' => 'moment',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
                    'class' => 'momentradio',
                    'value' => $momentValue,
                    'autofocus' => 'autofocus'
                ],
                'options' => [
                    'label' => 'À quel moment',
                    'label_attributes' => [
                        'class' => 'momentlabel'
                    ],
                    'value_options' => [
                        [
                            'value' => 1,
                            'attributes' => [
                                'id' => 'momentoption1'
                            ],
                            'label' => 'Matin',
                            'label_attributes' => [
                                'class' => 'momentoption'
                            ]
                        ],
                        [
                            'value' => 2,
                            'attributes' => [
                                'id' => 'momentoption2'
                            ],
                            'label' => 'Midi',
                            'label_attributes' => [
                                'class' => 'momentoption'
                            ]
                        ],
                        3 => [
                            'value' => 3,
                            'attributes' => [
                                'id' => 'momentoption3'
                            ],
                            'label' => 'Soir',
                            'label_attributes' => [
                                'class' => 'momentoption'
                            ]
                        ]
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'serviceinitial',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'serviceinitial',
                    'class' => 'service',
                    'value' => $serviceinitialValue,
                    'title' => 'Service sur lesquels les élèves sont actuellement affectés.'
                ],
                'options' => [
                    'label' => 'Déplacer les élèves du service',
                    'label_attributes' => [
                        'class' => 'label_class service'
                    ],
                    'empty_opton' => 'Choisir le service initial',
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'moment' => [
                'name' => 'moment',
                'required' => true
            ],
            'serviceinitial' => [
                'name' => 'serviceinitial',
                'required' => true
            ],
            'servicefinal' => [
                'name' => 'servicefinal',
                'required' => true
            ],
            'etablissementcommune' => [
                'name' => 'etablissementcommune',
                'required' => false
            ],
            'etablissementniveau' => [
                'name' => 'etablissementniveau',
                'required' => false
            ],
            'etablissementId' => [
                'name' => 'etablissementId',
                'required' => false
            ],
            'regimeId' => [
                'name' => 'regimeId',
                'required' => false
            ],
            'classeniveau' => [
                'name' => 'classeniveau',
                'required' => false
            ],
            'classeId' => [
                'name' => 'classeId',
                'required' => false
            ],
            'stationId' => [
                'name' => 'stationId',
                'required' => false,
            ],
            'paiement' => [
                'name' => 'paiement',
                'required' => false
            ],
            'carte' => [
                'name' => 'carte',
                'required' => false
            ],
            'cartelot' => [
                'name' => 'cartelot',
                'required' => false
            ],
            'dateinscription' => [
                'name' => 'dateinscription',
                'required' => false
            ]
        ];
    }
}