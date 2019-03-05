<?php
/**
 * Formulaire d'inscription pour un parent
 *
 * Ce formulaire ne présente pas de garde alternée.
 * Lorsqu'il s'agit d'un nouvel élève, il est forcément inscrit par son responsable1.
 *
 * Compatible ZF3
 *
 * @project sbm
 * @package SbmParent/Form
 * @filesource Enfant.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmParent\Form;

use SbmBase\Model\Session;
use SbmCommun\Filter\SansAccent;
use SbmCommun\Form\AbstractSbmForm;
use SbmCommun\Model\Strategy\Semaine;
use Zend\Db\Sql\Where;
use Zend\InputFilter\InputFilterProviderInterface;

class Enfant extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     * Db manager (nécessaire pour vérifier l'email)
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    public function __construct($db_manager)
    {
        $as = Session::get('as')['libelle'];
        $this->db_manager = $db_manager;
        parent::__construct('enfant');
        $this->setAttribute('method', 'post');
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
        $this->add([
            'type' => 'hidden',
            'name' => 'eleveId'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'responsable1Id'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'responsable2Id'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'derogation'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'motifDerogation'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'regimeId'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'organismeId'
        ]);
        $this->add(
            [
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'name' => 'nom',
                'attributes' => [
                    'id' => 'enfant_nom',
                    'autofocus' => 'autofocus',
                    'class' => 'sbmparent-enfant'
                ],
                'options' => [
                    'label' => 'Nom',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'SbmCommun\Form\Element\Prenom',
                'name' => 'prenom',
                'attributes' => [
                    'id' => 'enfant_prenom',
                    'class' => 'sbmparent-enfant'
                ],
                'options' => [
                    'label' => 'Prénom',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'name' => 'chez',
                'attributes' => [
                    'id' => 'enfant_chez',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Chez',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'adresseL1',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'enfant_adresseEleveL1',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Adresse',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'adresseL2',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'enfant_adresseEleveL2',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Complément d\'adresse',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'codePostal',
                'type' => 'SbmCommun\Form\Element\CodePostal',
                'attributes' => [
                    'id' => 'enfant_codePostalEleve',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Code postal',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'communeId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'enfant_communeEleveId',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Commune',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez une commune',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\DateSelect',
                'name' => 'dateN',
                'attributes' => [
                    'id' => 'enfant_dateN',
                    'class' => 'sbmparent-enfant'
                ],
                'options' => [
                    'label' => 'Date de naissance',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ],
                    // 'format' => 'Y-m-d'
                    'create_empty_option' => true,
                    'min_year' => date('Y') - 25,
                    'max_year' => date('Y') - 2
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'etablissementId',
                'attributes' => [
                    'id' => 'enfant_etablissementId',
                    'class' => 'sbmparent-enfant'
                ],
                'options' => [
                    'label' => 'Etablissement scolaire',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Etablissement fréquenté en ' . $as,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'classeId',
                'attributes' => [
                    'id' => 'enfant_classeId',
                    'class' => 'sbmparent-enfant'
                ],
                'options' => [
                    'label' => 'Classe suivie en ' . $as,
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez une classe',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'name' => 'joursTransport',
                'attributes' => [
                    'id' => 'enfant_joursTransport',
                    'class' => 'sbmparent-enfant'
                ],
                'options' => [
                    'label' => 'Demande de transport',
                    'label_attributes' => [
                        'class' => 'sbm-multi-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'ap',
                'attributes' => [
                    'id' => 'btnradioap',
                    'class' => 'sbmparent-enfant',
                    'value' => '0'
                ],
                'options' => [
                    'label' => 'Résidense personnelle',
                    'label_attributes' => [
                        'class' => 'sbm-radio-label'
                    ],
                    'value_options' => [
                        [
                            'value' => '1',
                            'label' => 'Oui',
                            'attributes' => [
                                'id' => 'btnradioap1'
                            ]
                        ],
                        [
                            'value' => '0',
                            'label' => 'Non',
                            'attributes' => [
                                'id' => 'btnradioap0'
                            ]
                        ]
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'ga',
                'attributes' => [
                    'id' => 'btnradioga',
                    'class' => 'sbmparent-enfant',
                    'value' => '0'
                ],
                'options' => [
                    'label' => 'Garde alternée',
                    'label_attributes' => [
                        'class' => 'sbm-radio-label'
                    ],
                    'value_options' => [
                        [
                            'value' => '1',
                            'label' => 'Oui',
                            'attributes' => [
                                'id' => 'btnradioga1'
                            ]
                        ],
                        [
                            'value' => '0',
                            'label' => 'Non',
                            'attributes' => [
                                'id' => 'btnradioga0'
                            ]
                        ]
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'fa',
                'attributes' => [
                    'id' => 'btnradiofa',
                    'class' => 'sbmparent-enfant',
                    'value' => '0'
                ],
                'options' => [
                    'label' => 'Famille d\'accueil',
                    'label_attributes' => [
                        'class' => 'sbm-radio-label'
                    ],
                    'value_options' => [
                        [
                            'value' => '1',
                            'label' => 'Oui',
                            'attributes' => [
                                'id' => 'btnradiofa1'
                            ]
                        ],
                        [
                            'value' => '0',
                            'label' => 'Non',
                            'attributes' => [
                                'id' => 'btnradiofa0'
                            ]
                        ]
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'demandeR2',
                'attributes' => [
                    'id' => 'demandeR2',
                    'class' => 'sbmparent-enfant',
                    'value' => 0
                ],
                'options' => [
                    'label' => 'Demande de transport pour cette adresse',
                    'label_attributes' => [
                        'class' => 'sbm-radio-label'
                    ],
                    'value_options' => [
                        '1' => 'Oui',
                        '0' => 'Non'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'commentaire',
                'attributes' => [
                    'id' => 'enfant_commentaire'
                ],
                'options' => [
                    'label' => 'Commentaires à transmettre au service transport',
                    'label_attributes' => [
                        'class' => 'sbm-commentaire',
                        'style' => 'width: inherit;'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'submit',
                'name' => 'submit',
                'attributes' => [
                    'value' => 'Enregistrer',
                    'id' => 'enfant_submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'type' => 'submit',
                'name' => 'cancel',
                'attributes' => [
                    'value' => 'Abandonner',
                    'id' => 'enfant_cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'ap' => [
                'name' => 'ap',
                'required' => false
            ],
            'chez' => [
                'name' => 'chez',
                'required' => false
            ],
            'adresseL1' => [
                'name' => 'adresseL1',
                'required' => false
            ],
            'adresseL2' => [
                'name' => 'adresseL2',
                'required' => false
            ],
            'codePostal' => [
                'name' => 'codePostal',
                'required' => false
            ],
            'communeId' => [
                'name' => 'communeId',
                'required' => false
            ],
            'joursTransport' => [
                'name' => 'joursTransport',
                'required' => true
            ],
            'demandeR2' => [
                'name' => 'demandeR2',
                'required' => false
            ]
        ];
    }

    public function isValid()
    {
        if (parent::isValid()) {
            $data = $this->getData();
            // vérifie que la classe est ouverte dans l'établissement
            $classe = $this->db_manager->get('Sbm\Db\Table\Classes')->getRecord(
                $data['classeId']);
            $etablissement = $this->db_manager->get('Sbm\Db\Table\Etablissements')->getRecord(
                $data['etablissementId']);
            $ok = false;
            foreach ($classe->niveau as $n) {
                $ok |= in_array($n, $etablissement->niveau);
            }
            if (! $ok) {
                $this->setMessages(
                    [
                        'classeId' => [
                            'incorrect' => 'Cette classe n\'est pas ouverte dans cet établissement.'
                        ]
                    ]);
            }
            // vérifie que l'élève n'est pas inscrit
            if (empty($data['eleveId'])) {
                // lorsqu'il s'agit d'un nouvel élève
                $sa = new SansAccent();
                $where = new Where();
                $where->equalTo('nomSA', $sa->filter($data['nom']))
                    ->equalTo('prenomSA', $sa->filter($data['prenom']))
                    ->equalTo('dateN', $data['dateN'])
                    ->nest()
                    ->equalTo('responsable1Id', $data['responsable1Id'])->OR->equalTo(
                    'responsable2Id', $data['responsable1Id'])->unnest();
                $result = $this->db_manager->get('Sbm\Db\Table\Eleves')->fetchAll($where);
                $ok = $result->count() == 0;
                if (! $ok) {
                    // reprise d'un enfant inscrit antérieurement (modif du 21/O5/2015)
                    $data['eleveId'] = current($result->toArray())['eleveId'];
                    $this->setData($data);
                    $ok = parent::isValid();
                    if (! $ok) {
                        $this->setMessages(
                            [
                                'prenom' => [
                                    'existe' => 'Cet enfant est déjà enregistré.'
                                ]
                            ]);
                    }
                }
            } else {
                // lorsqu'il s'agit d'une modification
                $sa = new SansAccent();
                $where = new Where();
                $where->equalTo('nomSA', $sa->filter($data['nom']))
                    ->equalTo('prenomSA', $sa->filter($data['prenom']))
                    ->equalTo('dateN', $data['dateN'])
                    ->notEqualTo('eleveId', $data['eleveId'])
                    ->nest()
                    ->equalTo('responsable1Id', $data['responsable1Id'])->OR->equalTo(
                    'responsable2Id', $data['responsable2Id'])->unnest();
                $ok = $this->db_manager->get('Sbm\Db\Table\Eleves')
                    ->fetchAll($where)
                    ->count() == 0;
                if (! $ok) {
                    $this->setMessages(
                        [
                            'prenom' => [
                                'existe' => 'Cet enfant est déjà enregistré.'
                            ]
                        ]);
                }
            }
            return $ok;
        } else {
            return false;
        }
    }

    /**
     * Traitement de l'élément 'joursTransport' dans les données reçues avant de charger le
     * formulaire
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Form\Form::setData()
     */
    public function setData($data)
    {
        if (is_array($data) && array_key_exists('joursTransport', $data) &&
            ! is_array($data['joursTransport'])) {
            $strategie = new Semaine();
            $data['joursTransport'] = $strategie->hydrate($data['joursTransport']);
        }
        return parent::setData($data);
    }
}