<?php
/**
 * Formulaire de saisie pour un correspondant
 *
 * Un invité est une personne qui reçoit un PASS JUNIOR PROVISOIRE et qui n'est pas un
 * élève déjà enregistré.
 * Les champs millesime et gratuit sont cachés ; gratuit est initialisé au chargement du
 * formulaire et n'est
 * présent que pour une éventuelle évolution ultérieure si la délivrance du pass devenait
 * payante.
 * Les champs modifiables du formulaire sont :
 * nom, prenom, sexe, nationalite, dateDebut, dateFin, eleveId, responsableId,
 * organismeId, chez, adresseL1, adresseL2, adresseL3, codePostal, communeId,
 * etablissementId, stationId, servicesMatin, servicesMidi, servicesSoir, servicesMerSoir,
 * demande, motifDemande, motifRefus et commentaire.
 *
 * Les champs suivants sont des Select dont le tableau de value_options devra être
 * initialisé : eleveId, responsableId, organismeId, communeId, etablissementId,
 * stationId
 * Le champ nationalite (Select) est initialisé par la méthode
 * SbmCommun\Model\Db\ObjectData\Invite::getNationalites()
 *
 * 5 cas seront envisagés par la suite :
 * 1/ Si eleveId est renseigné, il s'agit
 * 1.a/ Soit d'un élève qui a besoin d'un itinéraire spécifique sur une durée limitée. Il
 * faut alors saisir les champs : chez, adresseL1, adresseL2, adresseL3, codePostal,
 * communeId, etablissementId, stationId, servicesMatin, servicesMidi, servicesSoir,
 * servicesMerSoir
 * 1.b/ Soit d'un correspondant résidant au même domicile que l'élève. Il n'y a pas lieu
 * de saisir la suite car l'adresse est celle de l'élève, le responsable est celui de
 * l'élève, l'établissement est celui de l'élève, la station origine est celle de l'élève
 * et les services de transport sont ceux de l'élève.
 * 2/ Si responsabeId est renseigné, l'adresse est celle du responsable. Il reste à saisir
 * l'établissement, la station d'origine et les services de transport
 *
 * 3/ Si organismeId est resnseigné, l'adresse est celle de l'organisme. Il reste à saisir
 * l'établissement, la station d'origine et les services de transport
 *
 * 4/ Dans les autres cas, saisir les champs : chez, adresseL1, adresseL2, adresseL3,
 * codePostal, communeId, etablissementId, stationId, servicesMatin, servicesMidi,
 * servicesSoir, servicesMerSoir,
 *
 * Le champ sexe (Radio) prend les valeurs :
 * - 1 pour masculin
 * - 2 pour féminin
 * Le champ demande (Radio) prend les valeurs suivantes :
 * - 0 pour refus de la demande
 * - 1 pour demande non traitée
 * - 2 pour demande traitée
 *
 * @project sbm
 * @package SbmCommun/src/Form
 * @filesource Invite.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 juin 2021
 * @version 2021-2.6.2
 */
namespace SbmCommun\Form;

use SbmBase\Model\StdLib;
use SbmCommun\Model\Db\ObjectData\Invite as ObjectDataInvite;
use Zend\InputFilter\InputFilterProviderInterface;

class Invite extends AbstractSbmForm implements InputFilterProviderInterface
{

    const MSG_ELV = ' ou un élève.';

    const MSG_ELVRESORG = ' ou un élève ou un responsable ou un organisme.';

    private $dataTmp;

    private $millesime;

    /**
     * Correspond au cas adapté à un onglet
     *
     * @var integer
     */
    private $etat;

    /**
     *
     * @param mixed $millesime
     * @return self
     */
    public function setMillesime($millesime): self
    {
        $this->millesime = $millesime;
        return $this;
    }

    public function __construct(string $name = 'invite', $options = [])
    {
        $this->dataTmp = null;
        $this->resetEtat();
        parent::__construct($name, $options);
        $this->add([
            'name' => 'millesime',
            'type' => 'hidden'
        ])
            ->add(
            [
                'name' => 'gratuit',
                'type' => 'hidden',
                'attributes' => [
                    'value' => 1
                ]
            ])
            ->add([
            'name' => 'inviteId',
            'type' => 'hidden'
        ])
            ->add(
            [
                'name' => 'csrf',
                'type' => 'Zend\Form\Element\Csrf',
                'options' => [
                    'csrf_options' => [
                        'timeout' => 180
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'nom',
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'attributes' => [
                    'id' => 'invite-nom',
                    'class' => 'sbm-width-30c',
                    'autofocus' => 'autofocus'
                ],
                'options' => [
                    'label' => 'Nom du bénéficiaire',
                    'label_attributes' => [
                        'class' => 'sbm-label nom'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'prenom',
                'type' => 'SbmCommun\Form\Element\Prenom',
                'attributes' => [
                    'id' => 'invite-prenom',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Prénom',
                    'label_attributes' => [
                        'class' => 'sbm-label prenom'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'sexe',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
                    'id' => 'invite-sexe',
                    'class' => 'sexe'
                ],
                'options' => [
                    'label' => 'Sexe',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        1 => 'Masculin',
                        2 => 'Féminin'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'nationalite',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'invite-nationalite',
                    'class' => 'sbm-width-20c'
                ],
                'options' => [
                    'label' => 'Nationalité',
                    'label_attributes' => [
                        'class' => 'sbm-label nationalite'
                    ],
                    'empty_option' => 'Choisir dans la liste',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'eleveId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'invite-eleveId',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Accompagnant l\'élève',
                    'label_attributes' => [
                        'class' => 'sbm-label eleveId'
                    ],
                    'empty_option' => 'Choisir un élève',
                    'disable_inarray_validator' => true,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'responsableId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'invite-responsableId',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Domicilié chez',
                    'label_attributes' => [
                        'class' => 'sbm-label responsableId'
                    ],
                    'empty_option' => 'Choisir un responsable',
                    'disable_inarray_validator' => true,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'organismeId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'invite-organismeId',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Organisme accueillant',
                    'label_attributes' => [
                        'class' => 'sbm-label organismeId'
                    ],
                    'empty_option' => 'Choisir un organisme',
                    'disable_inarray_validator' => true,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'chez',
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'attributes' => [
                    'id' => 'invite-chez',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Domicilié chez',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'adresseL1',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'invite-adresseL1',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Adresse',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'adresseL2',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'invite-adresseL2',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Adresse-Complément 1',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'adresseL3',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'invite-adresseL3',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Adresse-Complément 2',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'codePostal',
                'type' => 'SbmCommun\Form\Element\CodePostal',
                'attributes' => [
                    'id' => 'invite-codePostal',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Code postal',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'communeId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'invite-communeId',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Commune',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisir une commune',
                    'disable_inarray_validator' => true,
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
                    'id' => 'invite-etablissementId',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Établissement scolaire',
                    'label_attributes' => [
                        'class' => 'label_class'
                    ],
                    'empty_option' => 'Choisir dans la liste',
                    'disable_inarray_validator' => true,
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
                    'id' => 'invite-stationId',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Point de départ depuis ce domicile',
                    'label_attributes' => [
                        'class' => 'label_class'
                    ],
                    'empty_option' => 'Choisir dans la liste',
                    'disable_inarray_validator' => true,
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'servicesMatin',
                'type' => 'text',
                'attributes' => [
                    'id' => 'invite-servicesMatin',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Services du matin',
                    'label_attributes' => [
                        'class' => 'label_class'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'servicesMidi',
                'type' => 'text',
                'attributes' => [
                    'id' => 'invite-servicesMidi',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Services du retour midi',
                    'label_attributes' => [
                        'class' => 'label_class'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'servicesSoir',
                'type' => 'text',
                'attributes' => [
                    'id' => 'invite-servicesSoir',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Services du soir',
                    'label_attributes' => [
                        'class' => 'label_class'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'servicesMerSoir',
                'type' => 'text',
                'attributes' => [
                    'id' => 'invite-servicesMerSoir',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Services du mercredi soir',
                    'label_attributes' => [
                        'class' => 'label_class'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'dateDebut',
                'type' => 'Zend\Form\Element\Date',
                'attributes' => [
                    'id' => 'invite-dateDebut',
                    'class' => 'date'
                ],
                'options' => [
                    'label' => 'Validité du',
                    'label_attributes' => [
                        'class' => 'sbm-label date'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'dateFin',
                'type' => 'Zend\Form\Element\Date',
                'attributes' => [
                    'id' => 'invite-dateFin',
                    'class' => 'date'
                ],
                'options' => [
                    'label' => 'au',
                    'label_attributes' => [
                        'class' => 'sbm-label date'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'demande',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
                    'class' => 'sbm-radio'
                ],
                'options' => [
                    'label' => 'Demande',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        [
                            'value' => '0',
                            'attributes' => [
                                'id' => 'demanderadio0'
                            ],
                            'label' => 'Refusée'
                        ],
                        [
                            'value' => '1',
                            'attributes' => [
                                'id' => 'demanderadio1'
                            ],
                            'label' => 'A traiter'
                        ],
                        [
                            'value' => '2',
                            'attributes' => [
                                'id' => 'demanderadio2'
                            ],
                            'label' => 'Acceptée'
                        ]
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'motifDemande',
                'type' => 'Zend\Form\Element\Textarea',
                'attributes' => [
                    'id' => 'invite-motifDemande',
                    'class' => 'note motifDemande'
                ],
                'options' => [
                    'label' => 'Motif de la demande',
                    'label_attributes' => [
                        'class' => 'sbm-label note motifDemande'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'motifRefus',
                'type' => 'Zend\Form\Element\Textarea',
                'attributes' => [
                    'id' => 'invite-motifRefus',
                    'class' => 'note motifRefus'
                ],
                'options' => [
                    'label' => 'Motif du refus',
                    'label_attributes' => [
                        'class' => 'sbm-label note motifRefus'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'commentaire',
                'type' => 'Zend\Form\Element\Textarea',
                'attributes' => [
                    'id' => 'invite-commentaire',
                    'class' => 'note commentaire'
                ],
                'options' => [
                    'label' => 'Commentaire',
                    'label_attributes' => [
                        'class' => 'sbm-label note commentaire'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->setValueOptions('nationalite', ObjectDataInvite::getNationalites());

        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Enregistrer',
                    'id' => 'invite-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button default submit'
                ]
            ])->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'invite-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'inviteId' => [
                'name' => 'inviteId',
                'required' => false
            ],
            'millesime' => [
                'name' => 'millesime',
                'required' => true
            ],
            'nom' => [
                'name' => 'nom',
                'required' => false
            ],
            'prenom' => [
                'name' => 'prenom',
                'required' => false
            ],
            'sexe' => [
                'name' => 'sexe',
                'required' => false
            ],
            'nationalite' => [
                'name' => 'nationalite',
                'required' => false
            ],
            'eleveId' => [
                'name' => 'eleveId',
                'required' => false
            ],
            'responsableId' => [
                'name' => 'responsableId',
                'required' => false
            ],
            'organismeId' => [
                'name' => 'organismeId',
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
            'adresseL3' => [
                'name' => 'adresseL3',
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
            'etablissementId' => [
                'name' => 'etablissementId',
                'required' => false
            ],
            'stationId' => [
                'name' => 'stationId',
                'required' => false
            ],
            'servicesMatin' => [
                'name' => 'servicesMatin',
                'required' => false
            ],
            'servicesMidi' => [
                'name' => 'servicesMidi',
                'required' => false
            ],
            'servicesSoir' => [
                'name' => 'servicesSoir',
                'required' => false
            ],
            'servicesMerSoir' => [
                'name' => 'servicesMerSoir',
                'required' => false
            ],
            'dateDebut' => [
                'name' => 'dateDebut',
                'required' => true
            ],
            'dateFin' => [
                'name' => 'dateFin',
                'required' => true
            ],
            'demande' => [
                'name' => 'demande',
                'required' => true
            ],
            'motifDemande' => [
                'name' => 'motifDemande',
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
            'motifRefus' => [
                'name' => 'motifRefus',
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
            'commentaire' => [
                'name' => 'commentaire',
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
    }

    public function isValid()
    {
        $valid = parent::isValid();
        $this->resetEtat();
        $this->dataTmp = $this->data;
        switch ($this->getEtat()) {
            case 1:
                $valid &= $this->validIfNotEmpty('chez');
                $valid &= $this->validIfNotEmpty('adresseL1');
                $valid &= $this->validIfNotEmpty('codePostal');
                $valid &= $this->validIfNotEmpty('communeId');
                $valid &= $this->validIfNotEmpty('stationId');
                $valid &= $this->validIfNotEmpty('etablissementId');
                $valid &= $this->validIfNotEmpty('servicesMatin');
                $valid &= $this->validIfNotEmpty('servicesSoir');
                break;
            case 2:
                $valid &= $this->validIfNotEmpty('nom');
                $valid &= $this->validIfNotEmpty('prenom');
                $valid &= $this->validIfNotEmpty('eleveId');
                $valid &= $this->validIfExists('sexe');
                $valid &= $this->validIfExists('nationalite');
                break;
            case 3:
                $valid &= $this->validIfNotEmpty('nom');
                $valid &= $this->validIfNotEmpty('prenom');
                $valid &= $this->validIfExists('sexe');
                $valid &= $this->validIfExists('nationalite');
                $valid &= $this->validIfNotEmpty('responsableId');
                $valid &= $this->validIfNotEmpty('stationId');
                $valid &= $this->validIfNotEmpty('etablissementId');
                $valid &= $this->validIfNotEmpty('servicesMatin');
                $valid &= $this->validIfNotEmpty('servicesSoir');
                break;
            case 4:
                $valid &= $this->validIfNotEmpty('nom');
                $valid &= $this->validIfNotEmpty('prenom');
                $valid &= $this->validIfExists('sexe');
                $valid &= $this->validIfExists('nationalite');
                $valid &= $this->validIfNotEmpty('organismeId');
                $valid &= $this->validIfNotEmpty('stationId');
                $valid &= $this->validIfNotEmpty('etablissementId');
                $valid &= $this->validIfNotEmpty('servicesMatin');
                $valid &= $this->validIfNotEmpty('servicesSoir');
                break;
            case 5:
                $valid &= $this->validIfNotEmpty('nom');
                $valid &= $this->validIfNotEmpty('prenom');
                $valid &= $this->validIfExists('sexe');
                $valid &= $this->validIfExists('nationalite');
                $valid &= $this->validIfNotEmpty('chez');
                $valid &= $this->validIfNotEmpty('adresseL1');
                $valid &= $this->validIfNotEmpty('codePostal');
                $valid &= $this->validIfNotEmpty('communeId');
                $valid &= $this->validIfNotEmpty('stationId');
                $valid &= $this->validIfNotEmpty('etablissementId');
                $valid &= $this->validIfNotEmpty('servicesMatin');
                $valid &= $this->validIfNotEmpty('servicesSoir');
                break;
            default:
                break;
        }
        return $valid;
    }

    private function validIfNotEmpty($key): bool
    {
        $chez = StdLib::getParam($key, $this->data, '');
        if (empty($chez)) {
            $this->get($key)->setMessages(
                [
                    'Cette donnée est nécessaire et ne peut être vide.'
                ]);
            return false;
        }
        return true;
    }

    private function validIfExists($key): bool
    {
        $valid = array_key_exists($key, $this->data);
        if (! $valid) {
            $this->get($key)->setMessages([
                'Ce renseignement est nécessaire.'
            ]);
        }
        return $valid;
    }

    public function getEtat()
    {
        if ($this->etat == - 1) {
            if (empty($this->dataTmp)) {
                $this->etat = 1;
            } else {
                if (StdLib::getParam('eleveId', $this->dataTmp, 0) > 0) {
                    if (StdLib::getParam('stationId', $this->dataTmp, 0) > 0) {
                        $this->etat = 1;
                    } else {
                        $this->etat = 2;
                    }
                } elseif (StdLib::getParam('responsableId', $this->dataTmp, 0) > 0) {
                    $this->etat = 3;
                } elseif (StdLib::getParam('organismeId', $this->dataTmp, 0) > 0) {
                    $this->etat = 4;
                } else {
                    $this->etat = 5;
                }
            }
        }
        return $this->etat;
    }

    public function resetEtat(): self
    {
        $this->etat = - 1;
        return $this;
    }

    public function setEtat($etat): self
    {
        $this->etat = $etat;
        return $this;
    }

    public function setData($data)
    {
        //var_dump($this->etat, $data);
        $this->dataTmp = $data;
        switch ($this->etat) {
            case 1:
                unset($this->dataTmp['nom'], $this->dataTmp['prenom'],
                    $this->dataTmp['sexe'], $this->dataTmp['nationalite'],
                    $this->dataTmp['responsableId'], $this->dataTmp['organismeId']);
                break;
            case 2:
                unset($this->dataTmp['chez'], $this->dataTmp['adresseL1'],
                    $this->dataTmp['adresseL2'], $this->dataTmp['adresseL3'],
                    $this->dataTmp['codePostal'], $this->dataTmp['communeId'],
                    $this->dataTmp['stationId'], $this->dataTmp['etablissementId'],
                    $this->dataTmp['responsableId'], $this->dataTmp['organismeId']);
                break;
            case 3:
                unset($this->dataTmp['chez'], $this->dataTmp['adresseL1'],
                    $this->dataTmp['adresseL2'], $this->dataTmp['adresseL3'],
                    $this->dataTmp['codePostal'], $this->dataTmp['communeId'],
                    $this->dataTmp['eleveId'], $this->dataTmp['organismeId']);
                break;
                break;
            case 4:
                unset($this->dataTmp['chez'], $this->dataTmp['adresseL1'],
                    $this->dataTmp['adresseL2'], $this->dataTmp['adresseL3'],
                    $this->dataTmp['codePostal'], $this->dataTmp['communeId'],
                    $this->dataTmp['eleveId'], $this->dataTmp['responsableId']);
                break;
                break;
            case 5:
                unset($this->dataTmp['eleveId'], $this->dataTmp['responsableId'],
                    $this->dataTmp['organismeId']);
                break;
            default:
                $this->etat = - 1;
                break;
        }
        //die(var_dump($this->dataTmp));
        return parent::setData($this->dataTmp);
    }

    public function getData($flag = self::VALUES_NORMALIZED)
    {
        $objData = parent::getData($flag);
        if (is_object($objData)) {
            $this->dataTmp = $objData->getArrayCopy();
        } else {
            $this->dataTmp = $objData;
        }
        // die(var_dump($this->dataTmp));
        $this->resetEtat()->getEtat();
        if (is_object($objData)) {
            return $objData->exchangeArray($this->dataTmp);
        } else {
            return $this->dataTmp;
        }
    }
}