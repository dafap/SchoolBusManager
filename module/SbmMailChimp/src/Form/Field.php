<?php
/**
 * Formulaire d'édition d'un champ d'une liste de diffusion
 *
 * 
 * 
 * @project sbm
 * @package SbmMailChimp/Form
 * @filesource Field.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmMailChimp\Form;

use SbmBase\Model\StdLib;
use SbmCommun\Form\AbstractSbmForm;
use Zend\Form\FormInterface;
use Zend\InputFilter\InputFilterProviderInterface;

class Field extends AbstractSbmForm implements InputFilterProviderInterface
{

    private $with_merge_id;

    /**
     *
     * @param boolean $with_merge_id
     *            si false, l'élément 'merge_id' (Identifiant) n'est pas dans le
     *            formulaire
     */
    public function __construct($with_merge_id = true)
    {
        $this->with_merge_id = $with_merge_id;
        parent::__construct('sbm-mailchimp-liste');
        $this->setAttribute('method', 'post');
        $this->add([
            'type' => 'hidden',
            'name' => 'id_liste'
        ]);
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
        if ($with_merge_id) {
            $this->add(
                [
                    'name' => 'merge_id',
                    'type' => 'text',
                    'attributes' => [
                        'id' => 'field-merge_id',
                        'class' => 'sbm-width-5c'
                    ],
                    'options' => [
                        'label' => 'Identifiant',
                        'label_attributes' => [
                            'class' => 'sbm-label'
                        ],
                        'error_attributes' => [
                            'class' => 'sbm-error'
                        ]
                    ]
                ]);
        }
        $this->add(
            [
                'name' => 'tag',
                'type' => 'text',
                'attributes' => [
                    'id' => 'field-tag',
                    'class' => 'sbm-width-35c'
                ],
                'options' => [
                    'label' => 'Tag',
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
                'name' => 'name',
                'type' => 'text',
                'attributes' => [
                    'id' => 'field-name',
                    'class' => 'sbm-width-35c'
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
                'name' => 'type',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'field-type',
                    'class' => 'sbm-select'
                ],
                'options' => [
                    'label' => 'Type',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'value_options' => [
                        'text' => 'text',
                        'number' => 'number',
                        'radio buttons' => 'radio buttons',
                        'check boxes' => 'check boxes',
                        'drop down' => 'drop down',
                        'date' => 'date',
                        'birthday' => 'birthday',
                        'address' => 'address',
                        'zip code' => 'zip code',
                        'phone' => 'phone',
                        'website' => 'website',
                        'image' => 'image'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'required',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
                    'id' => 'field-required',
                    'class' => 'sbm-radio'
                ],
                'options' => [
                    'label' => 'Obligatoire',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        '0' => 'Non',
                        '1' => 'Oui'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'default_value',
                'type' => 'text',
                'attributes' => [
                    'id' => 'field-default_value',
                    'class' => 'sbm-width-35c'
                ],
                'options' => [
                    'label' => 'Valeur par défaut',
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
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Enregistrer',
                    'id' => 'sbm-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'sbm-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        if ($this->with_merge_id) {
            $result = [
                'merge_id' => [
                    'name' => 'merge_id',
                    'required' => false,
                    'filters' => [
                        [
                            'name' => 'Digits'
                        ]
                    ]
                ]
            ];
        } else {
            $result = [];
        }
        return array_merge($result,
            [
                'tag' => [
                    'name' => 'tag',
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
                'name' => [
                    'name' => 'name',
                    'required' => true,
                    'filters' => [
                        [
                            'name' => 'StripTags'
                        ],
                        [
                            'name' => 'StringTrim'
                        ]
                    ]
                ],
                'type' => [
                    'name' => 'type',
                    'required' => true
                ],
                'required' => [
                    'name' => 'required',
                    'required' => false
                ],
                'default_value' => [
                    'name' => 'default_value',
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
            ]);
    }

    /**
     * Fait un Form::getData() puis structure le résultat pour l'API v3
     *
     * @param int $flag
     *            constante définié dans FormInterface
     * @return array tableau associatif structuré comme l'indique le résultat d'un appel
     *         par l'API v3
     */
    public function getDataForApi3($flag = FormInterface::VALUES_NORMALIZED)
    {
        $data = $this->getData($flag);
        if (array_key_exists('merge_id', $data)) {
            $data['merge_id'] = (int) $data['merge_id'];
        }
        if (array_key_exists('required', $data)) {
            $data['required'] = (bool) $data['required'];
        }
        return $data;
    }

    /**
     * Fait un Form::setData($data) à partir d'un tableau structuré selon l'API v3
     *
     * @param array $data
     *            tableau associatif structuré comme l'indique le résultat d'un appel par
     *            l'API v3
     * @param boolean $with_id
     *            si vrai alors le merge_id du champ est mis dans les data, sinon il est
     *            laissé vide
     * @return \SbmMailChimp\Form\Field
     */
    public function setDataFromApi3($data, $with_id = true)
    {
        $this->setData(
            [
                'id_liste' => StdLib::getParam('list_id', $data),
                'merge_id' => $with_id ? StdLib::getParam('merge_id', $data) : null,
                'tag' => StdLib::getParam('tag', $data),
                'name' => StdLib::getParam('name', $data),
                'type' => StdLib::getParam('type', $data),
                'required' => StdLib::getParam('required', $data),
                'default_value' => StdLib::getParam('default_value', $data)
            ]);
        return $this;
    }
}
 