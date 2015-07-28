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
 * @date 7 juin 2015
 * @version 2015-1
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
        $this->setAttribute('methos', 'post');
        $method = 'form' . ucwords(strtolower($source));
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            throw new Exception("Les sources de données sont 'eleve', 'etablissement', 'responsable' ou 'station'. On a reçu $source.");
        }
        $this->add(array(
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => array(
                'id' => 'export-cancel',
                'class' => 'button default submit left-95px',
                'value' => 'Extraire les données'
            )
        ));
        $this->add(array(
            'type' => 'submit',
            'name' => 'cancel',
            'attributes' => array(
                'id' => 'export-cancel',
                'class' => 'button default cancel',
                'value' => 'Abandonner'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        $method = 'form' . ucwords(strtolower($this->source)) . 'Specification';
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            return array();
        }
    }

    private function formEleve()
    {
        $this->add(array(
            'type' => 'text',
            'name' => 'id_ccda',
            'attributes' => array(
                'id' => 'id_ccda'
            ),
            'options' => array(
                'label' => 'Code CCDA'
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'numero',
            'attributes' => array(
                'id' => 'numero'
            ),
            'options' => array(
                'label' => 'Numéro'
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'nom_eleve',
            'attributes' => array(
                'id' => 'nom_eleve'
            ),
            'options' => array(
                'label' => 'Nom'
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'prenom_eleve',
            'attributes' => array(
                'id' => 'prenom_eleve'
            ),
            'options' => array(
                'label' => 'Prénom'
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'etablissementId',
            'attributes' => array(
                'id' => 'etablissementId'
            ),
            'options' => array(
                'label' => 'Etablissement',
                'empty_option' => 'Tous',
                'allow_empty' => true,
                'disable_inarray_validator' => false,
                'value_options' => $this->sm->get('Sbm\Db\Select\EtablissementsDesservis')
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'classeId',
            'attributes' => array(
                'id' => 'classeId'
            ),
            'options' => array(
                'label' => 'Classe',
                'empty_option' => 'Toutes',
                'allow_empty' => true,
                'disable_inarray_validator' => false,
                'value_options' => $this->sm->get('Sbm\Db\Select\Classes')
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'responsable1',
            'attributes' => array(
                'id' => 'responsable1'
            ),
            'options' => array(
                'label' => 'Responsable 1'
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'communeId_responsable1',
            'attributes' => array(
                'id' => 'communeId_responsable1'
            ),
            'options' => array(
                'label' => 'Commune R1',
                'empty_option' => 'Toutes',
                'allow_empty' => true,
                // 'disable_inarray_validator' => false,
                'value_options' => $this->sm->get('Sbm\Db\Select\Communes')
                    ->desservies()
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'responsable2',
            'attributes' => array(
                'id' => 'responsable2'
            ),
            'options' => array(
                'label' => 'Responsable 2'
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'communeId_responsable2',
            'attributes' => array(
                'id' => 'communeId_responsable2'
            ),
            'options' => array(
                'label' => 'Commune R2',
                'empty_option' => 'Toutes',
                'allow_empty' => true,
                // 'disable_inarray_validator' => false,
                'value_options' => $this->sm->get('Sbm\Db\Select\Communes')
                    ->visibles()
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'ga',
            'attributes' => array(
                'value' => '2'
            ),
            'options' => array(
                'label' => 'Garde alternée',
                'value_options' => array(
                    '0' => 'Non',
                    '1' => 'Oui',
                    '2' => 'Tous'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'chez',
            'attributes' => array(
                'value' => '2'
            ),
            'options' => array(
                'label' => 'Domicile personnel',
                'value_options' => array(
                    '0' => 'Non',
                    '1' => 'Oui',
                    '2' => 'Tous'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'lot',
            'attributes' => array(
                'value' => '1'
            ),
            'options' => array(
                'label' => 'Quels élèves exporter ?',
                'value_options' => array(
                    '0' => 'Tous, sans l\'affectation',
                    '1' => 'Uniquement les élèves ayant une affectation'
                )
            )
        ));
    }

    private function formEleveSpecification()
    {
        return array(
            'id_ccda' => array(
                'name' => 'id_ccda',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\StripTags'
                    ),
                    array(
                        'name' => 'Zend\Filter\StringTrim'
                    )
                )
            ),
            'numero' => array(
                'name' => 'numero',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\StripTags'
                    ),
                    array(
                        'name' => 'Zend\Filter\StringTrim'
                    )
                )
            ),
            'nom_eleve' => array(
                'name' => 'nom_eleve',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\StripTags'
                    ),
                    array(
                        'name' => 'Zend\Filter\StringTrim'
                    ),
                    array(
                        'name' => 'SbmCommun\Filter\SansAccent'
                    )
                )
            ),
            'prenom_eleve' => array(
                'name' => 'prenom_eleve',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\StripTags'
                    ),
                    array(
                        'name' => 'Zend\Filter\StringTrim'
                    ),
                    array(
                        'name' => 'SbmCommun\Filter\SansAccent'
                    )
                )
            ),
            'etablissementId' => array(
                'name' => 'etablissementId',
                'required' => false
            ),
            'classeId' => array(
                'name' => 'classeId',
                'required' => false
            ),
            'responsable1' => array(
                'name' => 'responsable1',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\StripTags'
                    ),
                    array(
                        'name' => 'Zend\Filter\StringTrim'
                    )
                )
            ),
            'communeId_responsable1' => array(
                'name' => 'communeId_responsable1',
                'required' => false
            ),
            'responsable2' => array(
                'name' => 'responsable2',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\StripTags'
                    ),
                    array(
                        'name' => 'Zend\Filter\StringTrim'
                    )
                )
            ),
            'communeId_responsable2' => array(
                'name' => 'communeId_responsable2',
                'required' => false
            ),
            'ga' => array(
                'name' => 'ga',
                'required' => false
            ),
            'chez' => array(
                'name' => 'chez',
                'required' => false
            )
        );
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
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'etablissementId',
            'attributes' => array('id' => 'etablissementId'),
            'options' => array(
                'label' => 'Etablissement',
                'empty_option' => 'Tous',
                'allow_empty' => true,
                'value_options' => $this->sm->get('Sbm\Db\Select\EtablissementsDesservis')
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'communeId',
            'attributes' => array('id' => 'communeId'),
            'options' => array(
                'label' => 'Commune',
                'empty_option' => 'Toutes',
                'allow_empty' => true,
                'value_options' => $this->sm->get('Sbm\Db\Select\Communes')
                    ->desservies()
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'visible',
            'attributes' => array(
                'value' => '2'
            ),
            'options' => array(
                'label' => 'Visible',
                'value_options' => array(
                    '0' => 'Non',
                    '1' => 'Oui',
                    '2' => 'Tous'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'desservie',
            'attributes' => array(
                'value' => '2'
            ),
            'options' => array(
                'label' => 'Desservi',
                'value_options' => array(
                    '0' => 'Non',
                    '1' => 'Oui',
                    '2' => 'Tous'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'statut',
            'attributes' => array(
                'value' => '2'
            ),
            'options' => array(
                'label' => 'Statut',
                'value_options' => array(
                    '0' => 'Privé',
                    '1' => 'Public',
                    '2' => 'Tous'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'name' => 'niveau',
            'attributes' => array(
                'value' => array(
                    \SbmCommun\Model\Strategy\Niveau::CODE_NIVEAU_MATERNELLE,
                    \SbmCommun\Model\Strategy\Niveau::CODE_NIVEAU_ELEMENTAIRE,
                    \SbmCommun\Model\Strategy\Niveau::CODE_NIVEAU_PREMIER_CYCLE,
                    \SbmCommun\Model\Strategy\Niveau::CODE_NIVEAU_SECOND_CYCLE
                )
            ),
            'options' => array(
                'label' => 'Niveau',
                'value_options' => \SbmCommun\Model\Strategy\Niveau::getNiveaux()
            )
        ));
    }

    private function formEtablissementSpecification()
    {
        return array(
            'etablissementId' => array(
                'name' => 'etablissementId',
                'required' => false
            ),
            'communeId' => array(
                'name' => 'communeId',
                'required' => false
            )
        );
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
        $this->add(array(
            'type' => 'text',
            'name' => 'nomSA',
            'attributes' => array('id' => 'nomSA'),
            'options' => array(
                'label' => 'Nom',
                'label_attributes' => array(
                    'class' => 'sbm-first'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'communeId',
            'attributes' => array('id' => 'communeId'),
            'options' => array(
                'label' => 'Commune',
                'empty_option' => 'Toutes',
                'allow_empty' => true,
                'value_options' => $this->sm->get('Sbm\Db\Select\Communes')
                    ->desservies(),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'selection',
            'attributes' => array(
                'id' => 'selection',
                'useHiddenElement' => false,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Sélectionnés',
                'label_attributes' => array(
                    'class' => 'sbm-new-line'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'demenagement',
            'attributes' => array(
                'id' => 'demenagement',
                'useHiddenElement' => true,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Déménagement',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'nbEleves',
            'attributes' => array('id' => 'nbEleves'),
            'options' => array(
                'label' => 'Nb d\'élèves',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formResponsableSpecification()
    {
        return array(
            'nomSA' => array(
                'name' => 'nomSA',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\StripTags'
                    ),
                    array(
                        'name' => 'Zend\Filter\StringTrim'
                    ),
                    array(
                        'name' => 'SbmCommun\Filter\SansAccent'
                    )
                )
            ),
            'communeId' => array(
                'name' => 'communeId',
                'required' => false
            ),
            'selection' => array(
                'name' => 'selection',
                'required' => false
            ),
            'demenagement' => array(
                'name' => 'demenagement',
                'required' => false
            ),
            'nbEleves' => array(
                'name' => 'nbEleves',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\StripTags'
                    ),
                    array(
                        'name' => 'Zend\Filter\StringTrim'
                    )
                )
            )
        );
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
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'stationId',
            'attributes' => array('id' => 'stationId'),
            'options' => array(
                'label' => 'Station',
                'empty_option' => 'Toutes',
                'allow_empty' => true,
                'value_options' => $this->sm->get('Sbm\Db\Select\Stations')
                    ->toutes()
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'communeId',
            'attributes' => array('id' => 'communeId'),
            'options' => array(
                'label' => 'Commune',
                'empty_option' => 'Toutes',
                'allow_empty' => true,
                'value_options' => $this->sm->get('Sbm\Db\Select\Communes')
                    ->desservies()
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'visible',
            'attributes' => array(
                'value' => '2'
            ),
            'options' => array(
                'label' => 'Visible',
                'value_options' => array(
                    '0' => 'Non',
                    '1' => 'Oui',
                    '2' => 'Tous'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'ouverte',
            'attributes' => array(
                'value' => '2'
            ),
            'options' => array(
                'label' => 'Ouverte',
                'value_options' => array(
                    '0' => 'Non',
                    '1' => 'Oui',
                    '2' => 'Tous'
                )
            )
        ));
    }

    private function formStationSpecification()
    {
        return array(
            'stationId' => array(
                'name' => 'stationId',
                'required' => false
            ),
            'communeId' => array(
                'name' => 'communeId',
                'required' => false
            )
        );
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