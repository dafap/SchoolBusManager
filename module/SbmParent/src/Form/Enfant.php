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
 * @date 19 mai 2021
 * @version 2021-2.6.2
 */
namespace SbmParent\Form;

use SbmBase\Model\Session;
use SbmCommun\Filter\SansAccent;
use SbmCommun\Form\AbstractSbmForm;
use SbmCommun\Model\Db\ObjectData\Scolarite;
use SbmCommun\Model\Strategy\Semaine;
use Zend\Db\Sql\Where;
use Zend\InputFilter\InputFilterProviderInterface;
use SbmBase\Model\StdLib;

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
            'name' => 'organismeId'
        ]);
        $this->add(
            [
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'name' => 'nom',
                'attributes' => [
                    'id' => 'enfant_nom',
                    'autofocus' => 'autofocus',
                    'tabindex' => 1,
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
                    'tabindex' => 11,
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
                'type' => 'Zend\Form\Element\Select',
                'name' => 'sexe',
                'attributes' => [
                    'id' => 'eleve-sexe',
                    'tabindex' => 31,
                    'class' => 'sbmparent-enfant'
                ],
                'options' => [
                    'label' => 'Sexe',
                    'label_options' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Quel sexe ?',
                    'value_options' => [
                        1 => 'masculin',
                        2 => 'féminin'
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
                    'tabindex' => 41,
                    'class' => 'sbm-width-30c'
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
                    'tabindex' => 42,
                    'class' => 'sbm-width-30c'
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
                    'tabindex' => 43,
                    'class' => 'sbm-width-30c'
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
                    'tabindex' => 44,
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
                    'tabindex' => 45,
                    'class' => 'sbm-width-30c'
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
                    'day_attributes' => [
                        'tabindex' => 21
                    ],
                    'month_attributes' => [
                        'tabindex' => 22
                    ],
                    'year_attributes' => [
                        'tabindex' => 23
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
                    'tabindex' => 61,
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
                    'tabindex' => 71,
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
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'regimeId',
                'attributes' => [
                    'tabindex' => 81
                ],
                'options' => [
                    'label' => 'Régime',
                    'label_attributes' => [
                        'class' => 'sbm-radio regime'
                    ],
                    'value_options' => [
                        [
                            'value' => '0',
                            'attributes' => [
                                'id' => 'regimeidradio0dp'
                            ],
                            'label' => 'Externe ou Demi-pensionnaire',
                            'label_attributes' => [
                                'class' => 'sbm-radio-label dp'
                            ]
                        ],
                        [
                            'value' => '1',
                            'attributes' => [
                                'id' => 'regimeidradio1in'
                            ],
                            'label' => 'interne',
                            'label_attributes' => [
                                'class' => 'sbm-radio-label interne'
                            ]
                        ]
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'name' => 'joursTransportR1',
                'attributes' => [
                    'id' => 'enfant_joursTransportR1',
                    'tabindex' => 51,
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
                    'tabindex' => 54,
                    'class' => 'sbmparent-enfant',
                    'value' => '0'
                ],
                'options' => [
                    'label' => 'Résidence de l\'enfant',
                    'label_attributes' => [
                        'class' => 'sbm-radio-label',
                        'title' => 'lorsqu\'elle est différente de celle du responsable'
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
                    'tabindex' => 84,
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
        if (false) {
            // pas géré dans cette version
            $this->add(
                [
                    'type' => 'Zend\Form\Element\Radio',
                    'name' => 'fa',
                    'attributes' => [
                        'id' => 'btnradiofa',
                        'class' => 'sbmparent-enfant',
                        'tabindex' => 58,
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
        }
        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'demandeR2',
                'attributes' => [
                    'id' => 'demandeR2',
                    'tabindex' => 280,
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
                'name' => 'stationIdR1',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'enfant_stationIdR1',
                    'tabindex' => 52,
                    'class' => 'sbmparent-enfant'
                ],
                'options' => [
                    'label' => 'Point de montée',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un point d\'arrêt',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'stationIdR2',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'enfant_stationIdR2',
                    'tabindex' => 290,
                    'class' => 'sbmparent-enfant'
                ],
                'options' => [
                    'label' => 'Point de montée',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un point d\'arrêt',
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
                    'id' => 'enfant_commentaire',
                    'tabindex' => 300
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
                    'tabindex' => 310,
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
                    'tabindex' => 320,
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
            'joursTransportR1' => [
                'name' => 'joursTransportR1',
                'required' => true
            ],
            'demandeR2' => [
                'name' => 'demandeR2',
                'required' => false
            ],
            'stationIdR2' => [
                'name' => 'stationIdR2',
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
            if ($ok) {
                if ($data['chez'] || $data['adresseL1'] || $data['adresseL2'] ||
                    $data['codePostal'] || $data['communeId']) {
                    $obj = new Scolarite();
                    $ok = $obj->exchangeArray($data)->hasAdressePerso();
                    if (! $ok) {
                        $this->setMessages(
                            [
                                'communeId' => [
                                    'ap' => 'Une adresse personnelle doit nécessairement avoir au moins une adresse, un codePostal et une commune'
                                ]
                            ]);
                    }
                }
            }
            // vérifie que le point d'origine n'est pas le point d'arrivée à
            // l'établissement
            try {
                $this->db_manager->get('Sbm\Db\Table\Etablissements-Stations')->getRecord(
                    [
                        'etablissementId' => $data['etablissementId'],
                        'stationId' => $data['stationIdR1']
                    ]);
                $ok = false;
                $this->setMessages(
                    [
                        'stationIdR1' => [
                            'Le point de montée est un point d\'arrêt proche du  domicile où votre enfant monte dans le bus le matin. Vous avez indiqué un point d\'arrivée à l\'établissement.'
                        ]
                    ]);
            } catch (\Exception $e) {
                try {
                    if (StdLib::getParam('stationIdR1', $data, 0)) {
                        $this->db_manager->get('Sbm\Db\Table\Etablissements-Stations')->getRecord(
                            [
                                'etablissementId' => $data['etablissementId'],
                                'stationId' => $data['stationIdR2']
                            ]);
                        $ok = false;
                        $this->setMessages(
                            [
                                'stationIdR2' => [
                                    'Le point de montée est un point d\'arrêt proche du domicile où votre enfant monte dans le bus le matin. Vous avez indiqué un point d\'arrivée à l\'établissement.'
                                ]
                            ]);
                    }
                } catch (\Exception $e) {
                    // tout va bien
                }
            }
            return $ok;
        } else {
            return false;
        }
    }

    /**
     * Traitement de l'élément 'joursTransportR1' dans les données reçues avant de charger
     * le formulaire (non-PHPdoc)
     *
     * @see \Zend\Form\Form::setData()
     */
    public function setData($data)
    {
        if ((is_array($data) || $data instanceof \ArrayObject) &&
            array_key_exists('joursTransportR1', $data) &&
            ! is_array($data['joursTransportR1'])) {
            $strategie = new Semaine();
            $data['joursTransportR1'] = $strategie->hydrate($data['joursTransportR1']);
        }
        return parent::setData($data);
    }
}