<?php
/**
 * Formulaire de saisie et modification d'un lien etablissement-service
 *
 * Les méthodes setData() et getData() gère le transcodage des données en serviceId et réciproquement.
 * Le Select serviceId doit être initialisé en conséquence.
 *
 * Attention, pas de bind sur la table si on passe un 'serviceId' (car il disparaitrait dans le getData).
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource EtablissementService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Form;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use Zend\Form\FormInterface;
use Zend\InputFilter\InputFilterProviderInterface;

class EtablissementService extends AbstractSbmForm implements
    InputFilterProviderInterface
{
    use \SbmCommun\Model\Traits\ServiceTrait;

    /**
     * Désigne la colonne qui sera dans un Select. L'autre sera dans un hidden.
     *
     * @var string
     */
    private $select;

    public function __construct($select = 'service', $param = 'etablissement')
    {
        $this->select = $select;
        parent::__construct($param);
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
            'name' => 'origine',
            'type' => 'hidden'
        ]);
        if ($select == 'service') {
            $this->selectService();
        } else {
            $this->selectEtablissement();
        }
        $this->add(
            [
                'name' => 'stationId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'stationIdElement',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Station desservant l\'établissement',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez une station',
                    'disable_inarray_validator' => true,
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
                    'id' => 'station-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'station-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    private function selectEtablissement()
    {
        $this->add(
            [
                'name' => 'ligneId',
                'type' => 'hidden',
                'attributes' => [
                    'id' => 'ligneIdElement'
                ]
            ]);
        $this->add(
            [
                'name' => 'sens',
                'type' => 'hidden',
                'attributes' => [
                    'id' => 'sensElement'
                ]
            ]);
        $this->add(
            [
                'name' => 'moment',
                'type' => 'hidden',
                'attributes' => [
                    'id' => 'momentElement'
                ]
            ]);
        $this->add(
            [
                'name' => 'ordre',
                'type' => 'hidden',
                'attributes' => [
                    'id' => 'ordreElement'
                ]
            ]);
        $this->add(
            [
                'name' => 'etablissementId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'etablissementIdElement',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Etablissement',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un établissement',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    /**
     * L'id 'serviceIdElement' de l'élément 'serviceId' est utilisé en ajax. Ne pas le
     * modifier.
     */
    private function selectService()
    {
        $this->add(
            [
                'name' => 'etablissementId',
                'type' => 'hidden',
                'attributes' => [
                    'id' => 'etablissementIdElement'
                ]
            ]);
        $this->add(
            [
                'name' => 'serviceId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'serviceIdElement',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Service',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un service',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    /**
     * Rajoute le millesime et encode un serviceId s'il ne sont pas présents dans data
     *
     * {@inheritdoc}
     * @see \Zend\Form\Form::setData()
     */
    public function setData($data)
    {
        if (! array_key_exists('millesime', $data)) {
            $data['millesime'] = Session::get('millesime');
        }
        if (! array_key_exists('serviceId', $data) && $this->validServiceKeys($data)) {
            $data['serviceId'] = $this->encodeServiceId($data);
        }
        return parent::setData($data);
    }

    /**
     * Renvoie les datas validés après avoir décodé serviceId en 'ligneId', 'sens',
     * 'moment', 'ordre'.
     *
     * {@inheritdoc}
     * @see \Zend\Form\Form::getData()
     * @return array|\SbmCommun\Model\Db\ObjectData\ObjectDataInterface
     */
    public function getData($flag = FormInterface::VALUES_NORMALIZED)
    {
        $data = parent::getData($flag);
        if ($data instanceof ObjectDataInterface) {
            return $data;
        }
        if (array_key_exists('serviceId', $data)) {
            return array_merge($data, $this->decodeServiceId($data['serviceId']));
        }
        return $data;
    }

    public function getInputFilterSpecification()
    {
        if ($this->select == 'service') {
            return [
                'serviceId' => [
                    'name' => 'serviceId',
                    'required' => true
                ],
                'stationId' => [
                    'name' => 'stationId',
                    'required' => true
                ]
            ];
        } else {
            return [
                'etablissementId' => [
                    'name' => 'etablissementId',
                    'required' => true
                ],
                'stationId' => [
                    'name' => 'stationId',
                    'required' => true
                ]
            ];
        }
    }
}