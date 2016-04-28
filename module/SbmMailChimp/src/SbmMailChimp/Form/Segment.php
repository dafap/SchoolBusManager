<?php
/**
 * Formulaire d'édition d'un segment d'une liste de diffusion
 *
 * Les méthodes setDataFromApi() et getDataForApi() changent le nom de l'identifiant de la liste et du segment :
 * - list_id et id dans l'API
 * - id_liste et segment_id dans le formulaire
 * 
 * @project sbm
 * @package SbmMailChimp/Form
 * @filesource Segment.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 avr. 2016
 * @version 2016-2.1
 */
namespace SbmMailChimp\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\FormInterface;
use SbmCommun\Model\StdLib;

class Segment extends AbstractSbmForm implements InputFilterProviderInterface
{

    private $conditions;

    /**
     *
     * @param null|array $conditions
     *            les conditions sont passées lors d'une duplication de segment
     */
    public function __construct($conditions = [])
    {
        $this->conditions = $conditions;
        parent::__construct('sbm-mailchimp-liste');
        $this->setAttribute('method', 'post');
        $this->add([
            'type' => 'hidden',
            'name' => 'id_liste'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'segment_id'
        ]);
        $this->add([
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 180
                ]
            ]
        ]);
        $this->add([
            'name' => 'name',
            'type' => 'text',
            'attributes' => [
                'id' => 'segment-name',
                'class' => 'sbm-width-35c'
            ],
            'options' => [
                'label' => 'Nom du segment',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'match',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => [
                'id' => 'segment-match',
                'class' => 'sbm-radio',
                'value' => 'all'
            ],
            'options' => [
                'label' => 'Opérateur logique',
                'label_attributes' => [
                    'class' => 'sbm-label-radio'
                ],
                'value_options' => [
                    'any' => 'OU',
                    'all' => 'ET'
                ]
            ]
        ]);
        $this->add([
            'name' => 'submit',
            'attributes' => [
                'type' => 'submit',
                'value' => 'Enregistrer',
                'id' => 'sbm-submit',
                'class' => 'button default submit'
            ]
        ]);
        $this->add([
            'name' => 'cancel',
            'attributes' => [
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'sbm-cancel',
                'class' => 'button default cancel'
            ]
        ]);
    }

    public function setConditions($conditions)
    {
        if (!is_array($conditions)) {
            throw new \Exception(sprintf('Un tableau est attendu. On a reçu %s.',gettype($conditions)));
        }
        $this->conditions = $conditions;
    }
    
    public function getInputFilterSpecification()
    {
        return [
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
            ]
        ];
    }

    public function setDataFromApi3($data)
    {
        $this->setData([
            'id_liste' => $data['list_id'],
            'segment_id' => StdLib::getParam('id', $data),
            'name' => StdLib::getParam('name', $data),
            'match' => StdLib::getParamR([
                'options',
                'match'
            ], $data)
        ]);
        return $this;
    }

    public function getDataForApi3($with_condition = false, $flag = FormInterface::VALUES_NORMALIZED)
    {
        $data = $this->getData($flag);
        $result = [
            'name' => $data['name'],
            'options' => [
                'match' => $data['match']
            ]
        ];
        if ($with_condition) {
            $result['options']['conditions'] = $this->conditions;
        }
        return $result;
    }
} 