<?php
/**
 * Formulaire d'affectation et de décision d'une demande de transport
 *
 * Le trajet 1 correspond à la première adresse (R1 ou adresse perso)
 * Le trajet 2 correspond au R2
 *
 * Pour TRANSDEV ALBERTVILLE, les circuits sont déterminés par :
 * millesime, ligneId, sens, moment, ordre
 * ServiceId est le codage de ces données (sauf millesime) par la méthode encodeServiceId du
 * trait SbmCommun\Model\Traits\ServiceTrait
 *
 * @project sbm
 * @package SbmGestion/Form
 * @filesource AffectationDecision.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Form;

use SbmCommun\Form\AbstractSbmForm as Form;
use SbmCommun\Model\Traits\ServiceTrait;
use Zend\Form\FormInterface;
use Zend\InputFilter\InputFilterProviderInterface;

class AffectationDecision extends Form implements InputFilterProviderInterface
{
    use ServiceTrait;

    /**
     * Correspond au n° de trajet. Prend la valeur 1 ou 2 selon qu'il s'agit du trajet 1
     * ou 2
     *
     * @var int
     */
    private $trajet;

    /**
     * Correspond au n° de la phase d'inscription
     *
     * @var int
     */
    private $phase;

    /**
     * Constructeur du formulaire
     *
     * @param int $trajet
     *            Correspond au n° de trajet. Prend la valeur 1 ou 2 selon qu'il s'agit du
     *            trajet 1 ou 2
     * @param int $phase
     *            Correspond au n° de phase. Prend la valeur 1 ou 2 selon qu'il s'agit de
     *            la phase 1 ou 2 de l'affectation
     */
    public function __construct($trajet, $phase)
    {
        $this->trajet = $trajet;
        $this->phase = $phase;
        parent::__construct($phase == 1 ? 'decision-form' : 'affectation-form');
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
        // les hiddens reçus en post et à transmettre à nouveau
        foreach ([
            'etablissementId',
            'eleveId',
            'millesime',
            'trajet',
            'jours',
            'moment',
            'correspondance',
            'responsableId',
            'demandeR' . $trajet,
            'op'
        ] as $name) {
            $this->add([
                'name' => $name,
                'type' => 'hidden'
            ]);
        }

        if ($phase == 1) {
            $this->preparePhase1();
        } else {
            $this->preparePhase2();
            $this->add(
                [
                    'name' => 'back',
                    'attributes' => [
                        'type' => 'submit',
                        'value' => 'Précédent',
                        'id' => 'affectation-back',
                        'autofocus' => 'autofocus',
                        'class' => 'button default cancel'
                    ]
                ]);
        }
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => $phase == 1 ? 'decision-cancel' : 'affectation-cancel',
                    'autofocus' => 'autofocus',
                    'class' => 'button default cancel'
                ]
            ]);
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Valider',
                    'id' => $phase == 1 ? 'decision-submit' : 'affectation-submit',
                    'class' => 'button default submit'
                ]
            ]);
    }

    /**
     * Crée les éléments du formulaire pour la phase 1
     */
    private function preparePhase1()
    {
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'fictif',
                'attributes' => [
                    'id' => 'decision-district',
                    'class' => 'sbm-checkbox',
                    'disabled' => 'disabled'
                ],
                'options' => [
                    'label' => 'Secteur scolaire',
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
                'name' => 'derogation',
                'attributes' => [
                    'id' => 'decision-derogation',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Dérogation',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'value_options' => [
                        '0' => 'Non',
                        '1' => 'Dérogation',
                        '2' => 'Non ayant-droit'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);

        $this->add(
            [
                'type' => 'text',
                'name' => 'motifDerogation' . $this->trajet,
                'attributes' => [
                    'id' => 'decision-motifDerogation',
                    'class' => 'sbm-width-35c'
                ],
                'options' => [
                    'label' => 'Motif de la dérogation',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'accordR' . $this->trajet,
                'attributes' => [
                    'id' => 'decision-accordR',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Transport accepté',
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
                'type' => 'text',
                'name' => 'motifRefusR' . $this->trajet,
                'attributes' => [
                    'id' => 'decision-motifRefusR',
                    'class' => 'sbm-width-35c'
                ],
                'options' => [
                    'label' => 'Motif du refus',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'subventionR' . $this->trajet,
                'attributes' => [
                    'id' => 'decision-subventionR',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Subvention attribuée',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    /**
     * Crée les éléments du formulaire pour la phase 2
     */
    private function preparePhase2()
    {
        $this->add(
            [
                'name' => 'service1Id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'affectation-service1Id',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Circuit',
                    'label_attributes' => [
                        'class' => 'sbm-form-auto'
                    ],
                    'empty_option' => 'Choisissez un circuit',
                    // 'allow_empty' => true,
                    // 'disable_inarray_validator' => false,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'station1Id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'affectation-station1Id',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Point de montée',
                    'label_attributes' => [
                        'class' => 'sbm-form-auto'
                    ],
                    'empty_option' => 'Choisissez une station',
                    // 'allow_empty' => true,
                    // 'disable_inarray_validator' => false,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'station2Id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'affectation-station2Id',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Point de descente',
                    'label_attributes' => [
                        'class' => 'sbm-form-auto'
                    ],
                    'empty_option' => 'Choisissez une station',
                    'allow_empty' => true,
                    'disable_inarray_validator' => false,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'service2Id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'affectation-service2Id',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Correspondance',
                    'label_attributes' => [
                        'class' => 'sbm-form-auto'
                    ],
                    'empty_option' => 'Choisissez un circuit',
                    'allow_empty' => true,
                    'disable_inarray_validator' => false,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    /**
     * En phase 1, pour afficher un checkbox disabled avec la valeur de district, car
     * l'élément district ne fait pas partie du formulaire Dans tous les cas, la sortie du
     * formulaire doit se faire avec un demandeR1 ou un demandeR2 (selon trajet) égal à 2
     * (demandé et traité) (non-PHPdoc)
     *
     * @see \Zend\Form\Form::setData()
     */
    public function setData($data)
    {
        if ($this->phase == 1 && array_key_exists('district', $data)) {
            $fictif = $this->get('fictif');
            $fictif->setValue($data['district']);
        }
        parent::setData($data);
        $demande = $this->get('demandeR' . $this->trajet);
        $demande->setValue(2);
        return $this;
    }

    public function getData($flag = FormInterface::VALUES_NORMALIZED)
    {
        $data = parent::getData($flag);
        $arrayService1Id = $this->getServiceId(1, $data['service1Id']);
        $arrayService2Id = $this->getServiceId(2, $data['service2Id']);
        return array_merge($data, $arrayService1Id, $arrayService2Id);
    }

    private function getServiceId(int $n, string $serviceId)
    {
        $array = $this->decodeServiceId($serviceId);
        if ($array) {
            return [
                sprintf('ligne%dId', $n) => $array['ligneId'],
                sprintf('sensligne%d', $n) => $array['sens'],
                'moment' => $array['moment'],
                sprintf('ordreligne%d', $n) => $array['ordre']
            ];
        } else {
            return [];
        }
    }

    public function getInputFilterSpecification()
    {
        if ($this->phase == 1) {
            return [
                'fictif' => [
                    'name' => 'fictif',
                    'required' => false
                ],
                'derogation' => [
                    'name' => 'derogation',
                    'required' => true
                ],
                'accordR' . $this->trajet => [
                    'name' => 'accordR' . $this->trajet,
                    'required' => true
                ],
                'subventionR' . $this->trajet => [
                    'name' => 'subventionR' . $this->trajet,
                    'required' => true
                ],
                'motifDerogation' . $this->trajet => [
                    'name' => 'motifDerogation' . $this->trajet,
                    'required' => false,
                    'filters' => [
                        [
                            'name' => 'StripTags'
                        ],
                        [
                            'name' => 'StringTrim'
                        ]
                    ]
                ],
                'motifRefusR' . $this->trajet => [
                    'name' => 'motifRefusR' . $this->trajet,
                    'required' => false,
                    'filters' => [
                        [
                            'name' => 'StripTags'
                        ],
                        [
                            'name' => 'StringTrim'
                        ]
                    ]
                ]
            ];
        } else {
            return [
                'station2Id' => [
                    'name' => 'station2Id',
                    'required' => false
                ],
                'service2Id' => [
                    'name' => 'service2Id',
                    'required' => false
                ]
            ];
        }
    }
}