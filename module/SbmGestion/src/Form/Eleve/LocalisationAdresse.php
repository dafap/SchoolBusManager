<?php
/**
 * Formulaire permettant de saisir l'adresse particulière d'un élève et de la géolocaliser
 *
 * Les champs du formulaire sont :
 * 
 * @project sbm
 * @package SbmGestion/Fomr/Eleve
 * @filesource LocalisationAdresse.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmGestion\Form\Eleve;

use SbmCommun\Form\AbstractSbmForm;
use SbmCommun\Form\Exception;
use Zend\InputFilter\InputFilterProviderInterface;

class LocalisationAdresse extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     * Dans un tableau simple, minimum et maximum autorisés pour la latitude
     *
     * @var array
     */
    private $latRange;

    /**
     * Dans un tableau simple, minimum et maximum autorisés pour la longitude
     *
     * @var array
     */
    private $lngRange;

    public function __construct(array $valide, $name = 'chez', $options = [])
    {
        $ok = array_key_exists('lat', $valide);
        $ok &= array_key_exists('lng', $valide);
        $ok &= count($valide['lat']) == 2;
        $ok &= count($valide['lng']) == 2;
        foreach ($valide['lat'] as $item) {
            $ok &= is_numeric($item);
        }
        foreach ($valide['lng'] as $item) {
            $ok &= is_numeric($item);
        }
        if (! $ok) {
            throw new Exception(__METHOD__ . ' - Le paramètre "valide" est incorrect.');
        }
        $this->latRange = $valide['lat'];
        $this->lngRange = $valide['lng'];

        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'eleveId',
                'attributes' => [
                    'id' => 'eleveId'
                ]
            ]);
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'lat',
                'attributes' => [
                    'id' => 'lat'
                ]
            ]);
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'lng',
                'attributes' => [
                    'id' => 'lng'
                ]
            ]);
        $this->add(
            [
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'name' => 'chez',
                'attributes' => [
                    'id' => 'eleve-chez',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Chez',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'SbmCommun\Form\Element\Adresse',
                'name' => 'adresseL1',
                'attributes' => [
                    'id' => 'eleve-adresseL1',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Adresse',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'SbmCommun\Form\Element\Adresse',
                'name' => 'adresseL2',
                'attributes' => [
                    'id' => 'eleve-addresseL2',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Complément d\'adresse',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'SbmCommun\Form\Element\CodePostal',
                'name' => 'codePostal',
                'attributes' => [
                    'id' => 'eleve-codePostal',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Code postal',
                    'label_attributes' => [
                        'class' => 'sbm-label new-line'
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'communeId',
                'attributes' => [
                    'id' => 'eleve-communeId',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Commune',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez une commune',
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'submit',
                'name' => 'submit',
                'attributes' => [
                    'class' => 'button default submit left-95px',
                    'value' => 'Enregistrer'
                ]
            ]);
        $this->add(
            [
                'type' => 'submit',
                'name' => 'remove',
                'attributes' => [
                    'class' => 'button default submit left-10px',
                    'value' => 'Supprimer'
                ]
            ]);
        $this->add(
            [
                'type' => 'submit',
                'name' => 'cancel',
                'attributes' => [
                    'class' => 'button default cancel left-10px',
                    'value' => 'Abandonner'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'eleveId' => [
                'name' => 'eleveId',
                'required' => true
            ],
            'lat' => [
                'name' => 'lat',
                'required' => true,
                'validators' => [
                    new \Zend\Validator\Between(
                        [
                            'min' => $this->latRange[0],
                            'max' => $this->latRange[1]
                        ])
                ]
            ],
            'lng' => [
                'name' => 'lng',
                'required' => true,
                'validators' => [
                    new \Zend\Validator\Between(
                        [
                            'min' => $this->lngRange[0],
                            'max' => $this->lngRange[1]
                        ])
                ]
            ],
            'adresseL1' => [
                'name' => 'adresseL1',
                'required' => true
            ],
            'adresseL2' => [
                'name' => 'adresseL2',
                'required' => false
            ]
        ];
    }
}