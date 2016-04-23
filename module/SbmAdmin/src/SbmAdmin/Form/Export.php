<?php
/**
 * Choix des données à exporter
 *
 * Donnée avec coordonnées géographiques
 * 
 * @project sbm
 * @package SbmAdmin/Form
 * @filesource Export.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 avr. 2016
 * @version 2016-2
 */
namespace SbmAdmin\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\InputFilter\InputFilterProviderInterface;

class Export extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     * Nom de la source des données à extraire
     *
     * @var string
     */
    private $source;

    /**
     * ServiceManager
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $sm;

    /**
     * Constructeur d'un formulaire de critères d'exportation
     *
     * @param string $source
     *            nom de la source ('eleve', 'etablissement', 'responsable', 'station')
     */
    public function __construct($source, ServiceLocatorInterface $sm)
    {
        $this->source = $source;
        $this->sm = $sm;
        parent::__construct();
        $this->setAttribute('method', 'post');
        $method = 'form' . ucwords(strtolower($source));
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            throw new Exception("Les sources de données sont 'eleve', 'etablissement', 'responsable' ou 'station'. On a reçu $source.");
        }
        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'id' => 'export-cancel',
                'class' => 'button default submit left-95px',
                'value' => 'Extraire les données'
            ]
        ]);
        $this->add([
            'type' => 'submit',
            'name' => 'cancel',
            'attributes' => [
                'id' => 'export-cancel',
                'class' => 'button default cancel',
                'value' => 'Abandonner'
            ]
        ]);
    }

    public function getInputFilterSpecification()
    {
        $method = 'form' . ucwords(strtolower($this->source)) . 'Specification';
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            return [];
        }
    }

    private function formEleve()
    {
        $this->add([
            'type' => 'text',
            'name' => 'id_ccda',
            'attributes' => [
                'id' => 'id_ccda'
            ],
            'options' => [
                'label' => 'Code CCDA'
            ]
        ]);
        $this->add([
            'type' => 'text',
            'name' => 'numero',
            'attributes' => [
                'id' => 'numero'
            ],
            'options' => [
                'label' => 'Numéro'
            ]
        ]);
        $this->add([
            'type' => 'text',
            'name' => 'nom_eleve',
            'attributes' => [
                'id' => 'nom_eleve'
            ],
            'options' => [
                'label' => 'Nom'
            ]
        ]);
        $this->add([
            'type' => 'text',
            'name' => 'prenom_eleve',
            'attributes' => [
                'id' => 'prenom_eleve'
            ],
            'options' => [
                'label' => 'Prénom'
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Select',
            'name' => 'etablissementId',
            'attributes' => [
                'id' => 'etablissementId'
            ],
            'options' => [
                'label' => 'Etablissement',
                'empty_option' => 'Tous',
                'allow_empty' => true,
                'disable_inarray_validator' => false,
                'value_options' => $this->sm->get('Sbm\Db\Select\Etablissements')
                    ->desservis()
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Select',
            'name' => 'classeId',
            'attributes' => [
                'id' => 'classeId'
            ],
            'options' => [
                'label' => 'Classe',
                'empty_option' => 'Toutes',
                'allow_empty' => true,
                'disable_inarray_validator' => false,
                'value_options' => $this->sm->get('Sbm\Db\Select\Classes')
            ]
        ]);
        $this->add([
            'type' => 'text',
            'name' => 'responsable1',
            'attributes' => [
                'id' => 'responsable1'
            ],
            'options' => [
                'label' => 'Responsable 1'
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Select',
            'name' => 'communeId_responsable1',
            'attributes' => [
                'id' => 'communeId_responsable1'
            ],
            'options' => [
                'label' => 'Commune R1',
                'empty_option' => 'Toutes',
                'allow_empty' => true,
                // 'disable_inarray_validator' => false,
                'value_options' => $this->sm->get('Sbm\Db\Select\Communes')
                    ->desservies()
            ]
        ]);
        $this->add([
            'type' => 'text',
            'name' => 'responsable2',
            'attributes' => [
                'id' => 'responsable2'
            ],
            'options' => [
                'label' => 'Responsable 2'
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Select',
            'name' => 'communeId_responsable2',
            'attributes' => [
                'id' => 'communeId_responsable2'
            ],
            'options' => [
                'label' => 'Commune R2',
                'empty_option' => 'Toutes',
                'allow_empty' => true,
                // 'disable_inarray_validator' => false,
                'value_options' => $this->sm->get('Sbm\Db\Select\Communes')
                    ->visibles()
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'ga',
            'attributes' => [
                'value' => '2'
            ],
            'options' => [
                'label' => 'Garde alternée',
                'value_options' => [
                    '0' => 'Non',
                    '1' => 'Oui',
                    '2' => 'Tous'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'chez',
            'attributes' => [
                'value' => '2'
            ],
            'options' => [
                'label' => 'Domicile personnel',
                'value_options' => [
                    '0' => 'Non',
                    '1' => 'Oui',
                    '2' => 'Tous'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'lot',
            'attributes' => [
                'value' => '1'
            ],
            'options' => [
                'label' => 'Quels élèves exporter ?',
                'value_options' => [
                    '0' => 'Tous, sans l\'affectation',
                    '1' => 'Uniquement les élèves ayant une affectation'
                ]
            ]
        ]);
    }

    private function formEleveSpecification()
    {
        return [
            'id_ccda' => [
                'name' => 'id_ccda',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\StripTags'
                    ],
                    [
                        'name' => 'Zend\Filter\StringTrim'
                    ]
                ]
            ],
            'numero' => [
                'name' => 'numero',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\StripTags'
                    ],
                    [
                        'name' => 'Zend\Filter\StringTrim'
                    ]
                ]
            ],
            'nom_eleve' => [
                'name' => 'nom_eleve',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\StripTags'
                    ],
                    [
                        'name' => 'Zend\Filter\StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\SansAccent'
                    ]
                ]
            ],
            'prenom_eleve' => [
                'name' => 'prenom_eleve',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\StripTags'
                    ],
                    [
                        'name' => 'Zend\Filter\StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\SansAccent'
                    ]
                ]
            ],
            'etablissementId' => [
                'name' => 'etablissementId',
                'required' => false
            ],
            'classeId' => [
                'name' => 'classeId',
                'required' => false
            ],
            'responsable1' => [
                'name' => 'responsable1',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\StripTags'
                    ],
                    [
                        'name' => 'Zend\Filter\StringTrim'
                    ]
                ]
            ],
            'communeId_responsable1' => [
                'name' => 'communeId_responsable1',
                'required' => false
            ],
            'responsable2' => [
                'name' => 'responsable2',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\StripTags'
                    ],
                    [
                        'name' => 'Zend\Filter\StringTrim'
                    ]
                ]
            ],
            'communeId_responsable2' => [
                'name' => 'communeId_responsable2',
                'required' => false
            ],
            'ga' => [
                'name' => 'ga',
                'required' => false
            ],
            'chez' => [
                'name' => 'chez',
                'required' => false
            ]
        ];
    }

    /**
     * Renvoie un objet Where bâti à partir des données valides du formulaire
     *
     * @return \Zend\Db\Sql\Where
     */
    public function whereEleve()
    {
        $where = new Where();
        $where->literal('sco.inscrit=1')
            ->nest()
            ->literal('accordR1 = 1')->or->literal('accordR2 = 1')->unnest();
        $data = $this->getData();
        
        if (! empty($data['id_ccda'])) {
            $where->equalTo('ele.id_ccda', $data['id_ccda']);
        }
        if (! empty($data['numero'])) {
            $where->equalTo('numero', $data['numero']);
        }
        if (! empty($data['nom_eleve'])) {
            $where->like('ele.nom', $data['nom_eleve'] . '%');
        }
        if (! empty($data['prenom_eleve'])) {
            $where->like('ele.prenom', $data['prenom_eleve'] . '%');
        }
        if (! empty($data['etablissementId'])) {
            $where->equalTo('sco.etablissementId', $data['etablissementId']);
        }
        if (! empty($data['classeId'])) {
            $where->equalTo('sco.classeId', $data['classeId']);
        }
        if ($data['lot']) {
            if (! empty($data['responsable1'])) {
                $where->literal('trajet = 1')->like('res.nom', $data['responsable1'] . '%');
            }
            if (! empty($data['communeId_responsable1'])) {
                $where->literal('trajet = 1')->equalTo('res.communeId', $data['communeId_responsable1']);
            }
            if (! empty($data['responsable2'])) {
                $where->literal('trajet = 2')->like('res.nom', $data['responsable2'] . '%');
            }
            if (! empty($data['communeId_responsable2'])) {
                $where->literal('trajet = 2')->equalTo('res.communeId', $data['communeId_responsable2']);
            }
        } else {
            if (! empty($data['responsable1'])) {
                $where->like('r1.nom', $data['responsable1'] . '%');
            }
            if (! empty($data['communeId_responsable1'])) {
                $where->equalTo('r1.communeId', $data['communeId_responsable1']);
            }
            if (! empty($data['responsable2'])) {
                $where->like('r2.nom', $data['responsable2'] . '%');
            }
            if (! empty($data['communeId_responsable2'])) {
                $where->equalTo('r2.communeId', $data['communeId_responsable2']);
            }
        }
        if (isset($data['ga'])) {
            if ($data['ga'] == '0') {
                $where->equalTo('sco.demandeR2', '0');
            } elseif ($data['ga'] == '1') {
                $where->greaterThan('sco.demandeR2', '0');
            }
        }
        if (isset($data['chez'])) {
            if ($data['chez'] == '0') {
                $where->isNull('sco.chez');
            } elseif ($data['chez'] == '1') {
                $where->isNotNull('sco.chez');
            }
        }
        return $where;
    }
    
    // ================= Etablissements ================================================================
    private function formEtablissement()
    {
        $this->add([
            'type' => 'Zend\Form\Element\Select',
            'name' => 'etablissementId',
            'attributes' => [
                'id' => 'etablissementId'
            ],
            'options' => [
                'label' => 'Etablissement',
                'empty_option' => 'Tous',
                'allow_empty' => true,
                'value_options' => $this->sm->get('Sbm\Db\Select\Etablissements')
                    ->desservis()
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Select',
            'name' => 'communeId',
            'attributes' => [
                'id' => 'communeId'
            ],
            'options' => [
                'label' => 'Commune',
                'empty_option' => 'Toutes',
                'allow_empty' => true,
                'value_options' => $this->sm->get('Sbm\Db\Select\Communes')
                    ->desservies()
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'visible',
            'attributes' => [
                'value' => '2'
            ],
            'options' => [
                'label' => 'Visible',
                'value_options' => [
                    '0' => 'Non',
                    '1' => 'Oui',
                    '2' => 'Tous'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'desservie',
            'attributes' => [
                'value' => '2'
            ],
            'options' => [
                'label' => 'Desservi',
                'value_options' => [
                    '0' => 'Non',
                    '1' => 'Oui',
                    '2' => 'Tous'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'statut',
            'attributes' => [
                'value' => '2'
            ],
            'options' => [
                'label' => 'Statut',
                'value_options' => [
                    '0' => 'Privé',
                    '1' => 'Public',
                    '2' => 'Tous'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'name' => 'niveau',
            'attributes' => [
                'value' => [
                    \SbmCommun\Model\Strategy\Niveau::CODE_NIVEAU_MATERNELLE,
                    \SbmCommun\Model\Strategy\Niveau::CODE_NIVEAU_ELEMENTAIRE,
                    \SbmCommun\Model\Strategy\Niveau::CODE_NIVEAU_PREMIER_CYCLE,
                    \SbmCommun\Model\Strategy\Niveau::CODE_NIVEAU_SECOND_CYCLE
                ]
            ],
            'options' => [
                'label' => 'Niveau',
                'value_options' => \SbmCommun\Model\Strategy\Niveau::getNiveaux()
            ]
        ]);
    }

    private function formEtablissementSpecification()
    {
        return [
            'etablissementId' => [
                'name' => 'etablissementId',
                'required' => false
            ],
            'communeId' => [
                'name' => 'communeId',
                'required' => false
            ]
        ];
    }

    /**
     * Renvoie un objet Where bâti à partir des données valides du formulaire
     *
     * @return \Zend\Db\Sql\Where
     */
    public function whereEtablissement()
    {
        $where = new Where();
        $data = $this->getData();
        if (! empty($data['nom'])) {
            $where->equalTo('eta.nom', $data['nom']);
        }
        if (! empty($data['commune'])) {
            $where->equalTo('com.nom', $data['commune']);
        }
        if (! empty($data['visible']) && $data['visible'] != '2') {
            $where->equalTo('eta.visible', $data['visible']);
        }
        if (! empty($data['desservie']) && $data['desservie'] != '2') {
            $where->equalTo('eta.desservie', $data['desservie']);
        }
        if (! empty($data['statut']) && $data['statut'] != '2') {
            $where->equalTo('eta.statut', $data['statut']);
        }
        if (! empty($data['niveau'])) {
            if (count($data['niveau']) == 1) {
                $cond = 'niveau &' . current($data['niveau']);
                $where->literal($cond);
            } else {
                $predicate = new Predicate();
                $or = false;
                foreach ($data['niveau'] as $codeNiveau) {
                    if ($or) {
                        $predicate->or;
                    }
                    $cond = 'niveau &' . $codeNiveau;
                    $predicate->literal($cond);
                    $or = true;
                }
                $where->nest()
                    ->predicate($predicate)
                    ->unnest();
            }
        }
        return $where;
    }
    
    // ====================== Responsables ==========================================================
    private function formResponsable()
    {
        $this->add([
            'type' => 'text',
            'name' => 'nomSA',
            'attributes' => [
                'id' => 'nomSA'
            ],
            'options' => [
                'label' => 'Nom',
                'label_attributes' => [
                    'class' => 'sbm-first'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Select',
            'name' => 'communeId',
            'attributes' => [
                'id' => 'communeId'
            ],
            'options' => [
                'label' => 'Commune',
                'empty_option' => 'Toutes',
                'allow_empty' => true,
                'value_options' => $this->sm->get('Sbm\Db\Select\Communes')
                    ->desservies(),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'selection',
            'attributes' => [
                'id' => 'selection',
                'useHiddenElement' => false,
                'options' => [
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ],
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Sélectionnés',
                'label_attributes' => [
                    'class' => 'sbm-new-line'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'demenagement',
            'attributes' => [
                'id' => 'demenagement',
                'useHiddenElement' => true,
                'options' => [
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ],
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Déménagement',
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'text',
            'name' => 'nbEleves',
            'attributes' => [
                'id' => 'nbEleves'
            ],
            'options' => [
                'label' => 'Nb d\'élèves',
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
    }

    private function formResponsableSpecification()
    {
        return [
            'nomSA' => [
                'name' => 'nomSA',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\StripTags'
                    ],
                    [
                        'name' => 'Zend\Filter\StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\SansAccent'
                    ]
                ]
            ],
            'communeId' => [
                'name' => 'communeId',
                'required' => false
            ],
            'selection' => [
                'name' => 'selection',
                'required' => false
            ],
            'demenagement' => [
                'name' => 'demenagement',
                'required' => false
            ],
            'nbEleves' => [
                'name' => 'nbEleves',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\StripTags'
                    ],
                    [
                        'name' => 'Zend\Filter\StringTrim'
                    ]
                ]
            ]
        ];
    }

    /**
     * Renvoie un objet Where bâti à partir des données valides du formulaire
     *
     * @return \Zend\Db\Sql\Where
     */
    public function whereResponsable()
    {
        $where = new Where();
        $data = $this->getData();
        if (! empty($data['nomSA'])) {
            $where->like('nomSA', $data['nomSA'] . '%');
        }
        if (! empty($data['communeId'])) {
            $where->equalTo('communeId', $data['communeId']);
        }
        if (! empty($data['selection'])) {
            $where->equalTo('selection', $data['selection']);
        }
        if (! empty($data['demenagement'])) {
            $where->equalTo('demenagement', $data['demenagement']);
        }
        if (! empty($data['nbEleves'])) {
            $where->equalTo('nbEleves', $data['nbEleves']);
        }
        return $where;
    }
    
    // ==================== Stations ==============================================================
    private function formStation()
    {
        $this->add([
            'type' => 'Zend\Form\Element\Select',
            'name' => 'stationId',
            'attributes' => [
                'id' => 'stationId'
            ],
            'options' => [
                'label' => 'Station',
                'empty_option' => 'Toutes',
                'allow_empty' => true,
                'value_options' => $this->sm->get('Sbm\Db\Select\Stations')
                    ->toutes()
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Select',
            'name' => 'communeId',
            'attributes' => [
                'id' => 'communeId'
            ],
            'options' => [
                'label' => 'Commune',
                'empty_option' => 'Toutes',
                'allow_empty' => true,
                'value_options' => $this->sm->get('Sbm\Db\Select\Communes')
                    ->desservies()
            ]
        ]);
        
        $this->add([
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'visible',
            'attributes' => [
                'value' => '2'
            ],
            'options' => [
                'label' => 'Visible',
                'value_options' => [
                    '0' => 'Non',
                    '1' => 'Oui',
                    '2' => 'Tous'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'ouverte',
            'attributes' => [
                'value' => '2'
            ],
            'options' => [
                'label' => 'Ouverte',
                'value_options' => [
                    '0' => 'Non',
                    '1' => 'Oui',
                    '2' => 'Tous'
                ]
            ]
        ]);
    }

    private function formStationSpecification()
    {
        return [
            'stationId' => [
                'name' => 'stationId',
                'required' => false
            ],
            'communeId' => [
                'name' => 'communeId',
                'required' => false
            ]
        ];
    }

    /**
     * Renvoie un objet Where bâti à partir des données valides du formulaire
     *
     * @return \Zend\Db\Sql\Where
     */
    public function whereStation()
    {
        $where = new Where();
        $data = $this->getData();
        if (! empty($data['stationId'])) {
            $where->equalTo('sta.stationId', $data['stationId']);
        }
        if (! empty($data['communeId'])) {
            $where->equalTo('sta.communeId', $data['communeId']);
        }
        if (isset($data['visible']) && $data['visible'] != '2') {
            $where->equalTo('sta.visible', $data['visible']);
        }
        if (isset($data['ouverte']) && $data['ouverte'] != '2') {
            $where->equalTo('sta.ouverte', $data['ouverte']);
        }
        return $where;
    }
}