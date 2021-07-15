<?php
/**
 * Formulaire de saisie et modification d'un lien station-station
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource StationStation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Form;

use SbmBase\Model\StdLib;
use Zend\InputFilter\InputFilterProviderInterface;

class StationStation extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($name = 'stationstation', $param = [])
    {
        parent::__construct($name, $param);
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
        $this->add(
            [
                'name' => 'station1Id',
                'type' => 'hidden',
                'attributes' => [
                    'id' => 'station1IdElement'
                ]
            ]);
        $this->add(
            [
                'name' => 'station2Id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'station2IdElement',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Station',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez une station',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'temps',
                'type' => 'Zend\Form\Element\Time',
                'attributes' => [
                    'id' => 'temps',
                    'class' => 'sbm-width-10c',
                    'min' => '00:00:00',
                    'max' => '00:20:59',
                    'step' => '15'
                ],
                'options' => [
                    'label' => 'Temps de déplacement à pied (en h:m:s)',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'format' => 'H:i:s',
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

    public function getInputFilterSpecification()
    {
        return [
            'station2Id' => [
                'name' => 'station2Id',
                'required' => true
            ],
            'temps' => [
                'name' => 'temps',
                'required' => false,
                'allow_empty' => true
            ]
        ];
    }

    /**
     * Correction du format du temps renvoyé par certains navigateurs sans les secondes
     * lorsque la partie secondes est 00
     *
     * {@inheritdoc}
     * @see \Zend\Form\Form::setData()
     */
    public function setData($data)
    {
        if (is_array($data)) {
            $temps = StdLib::getParam('temps', $data);
            if (is_string($temps) &&
                ! (\DateTime::createFromFormat('H:i:s', $temps) instanceof \DateTime)) {
                $dt = \DateTime::createFromFormat('H:i', $temps);
                if ($dt instanceof \DateTime) {
                    $data['temps'] = $dt->format('H:i:s');
                }
            }
        }
        return parent::setData($data);
    }
}