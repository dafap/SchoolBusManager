<?php
/**
 * Formulaire de demande d'un mot de passe
 *
 * Un nouveau mot de passe sera renvoyé à l'adresse indiquée si elle correspond au compte d'un utilisateur.
 * 
 * @project sbm
 * @package SbmFront/Form
 * @filesource MdpDemande.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmFront\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class MdpDemande extends AbstractSbmForm implements InputFilterProviderInterface
{

    private $canonic_name;

    private $db_adapter;

    public function __construct($canonic_name, $db_adapter)
    {
        $this->canonic_name = $canonic_name;
        $this->db_adapter = $db_adapter;
        parent::__construct('mdp-demande');
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
        $this->add(
            [
                'type' => 'Zend\Form\Element\Email',
                'name' => 'email',
                'attributes' => [
                    'id' => 'user-email',
                    'class' => 'sbm-page1',
                    'autofocus' => 'autofocus'
                ],
                'options' => [
                    'label' => 'Email',
                    'label_attributes' => [
                        'class' => 'sbm-label-page1'
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
                    'value' => 'Envoyer la demande',
                    'id' => 'responsable-submit',
                    'class' => 'default left-95px'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'responsable-cancel',
                    'class' => 'default left-10px'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'email' => [
                'name' => 'email',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'Zend\Validator\EmailAddress'
                    ],
                    [
                        'name' => 'Zend\Validator\Db\RecordExists',
                        'options' => [
                            'table' => $this->canonic_name,
                            'field' => 'email',
                            'adapter' => $this->db_adapter
                        ]
                    ]
                ]
            ]
        ];
    }

    public function isValid()
    {
        $result = parent::isValid();
        if (! $result) {
            $e = $this->get('email');
            if (array_key_exists('noRecordFound', $e->getMessages())) {
                $e->setMessages(
                    [
                        'Cet email est inconnu.'
                    ]);
            }
        }
        return $result;
    }
}