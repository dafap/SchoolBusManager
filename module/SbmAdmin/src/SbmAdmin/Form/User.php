<?php
/**
 * Formulaire de saisie d'un user
 * 
 * @project sbm
 * @package SbmAdmin/Form
 * @filesource User.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 mai 2015
 * @version 2015-1
 */
namespace SbmAdmin\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class User extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     * Service manager (nécessaire pour vérifier l'email)
     *
     * @var ServiceLocatorInterface
     */
    private $sm;
    
    private $userId;

    public function __construct(ServiceLocatorInterface $sm, $param = 'compte')
    {
        $this->sm = $sm;
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'userId',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 180
                )
            )
        ));
        $this->add(array(
            'name' => 'titre',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'user-titre',
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'Votre identité',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'value_options' => array(
                    'M.' => 'Monsieur',
                    'Mme' => 'Madame',
                    'Mlle' => 'Mademoiselle',
                    'Dr' => 'Docteur',
                    'Me' => 'Maître',
                    'Pr' => 'Professeur'
                ),
                'empty_option' => 'Choisissez la civilité',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'nom',
            'type' => 'SbmCommun\Form\Element\NomPropre',
            'attributes' => array(
                'id' => 'user-nom',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Nom',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'prenom',
            'type' => 'SbmCommun\Form\Element\Prenom',
            'attributes' => array(
                'id' => 'user-prenom',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Prénom',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'email',
            'type' => 'Zend\Form\Element\Email',
            'attributes' => array(
                'id' => 'user-email',
                'class' => 'sbm-width-55c'
            ),
            'options' => array(
                'label' => 'Email',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'categorieId',
            'attributes' => array(
                'id' => 'user-categorieId'
            ),
            'options' => array(
                'label' => 'Catégorie',
                'label-attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Quelle catégorie ?',
                'value_options' => array(
                    '1' => 'Parent',
                    '2' => 'Transporteur',
                    '3' => 'Etablissement scolaire',
                    '253' => 'Gestionnaire',
                    '254' => 'Administrateur'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'tokenalive',
            'attributes' => array(
                'id' => 'user-tokenalive'
            ),
            'options' => array(
                'label' => 'Mot de passe bloqué',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'confirme',
            'attributes' => array(
                'id' => 'user-confirme'
            ),
            'options' => array(
                'label' => 'Confirmé',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'active',
            'attributes' => array(
                'id' => 'user-active'
            ),
            'options' => array(
                'label' => 'Activé',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'selection',
            'attributes' => array(
                'id' => 'user-selection'
            ),
            'options' => array(
                'label' => 'Sélectionné',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'note',
            'attributes' => array(
                'id' => 'user-note',
                'class' => 'sbm-note'
            ),
            'options' => array(
                'label' => 'Commentaires',
                'label_attributes' => array(),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'id' => 'responsable-submit',
                'autofocus' => 'autofocus',
                'class' => 'button default submit left-95px'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'responsable-cancel',
                'class' => 'button default cancel left-10px'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        $db = $this->sm->get('Sbm\Db\DbLib');
        return array(
            'titre' => array(
                'name' => 'titre',
                'required' => true
            ),
            'nom' => array(
                'name' => 'nom',
                'required' => true
            ),
            'prenom' => array(
                'name' => 'prenom',
                'required' => true
            ),
            'email' => array(
                'name' => 'email',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'Zend\Validator\EmailAddress'
                    ),
                    array(
                        'name' => 'Zend\Validator\Db\NoRecordExists',
                        'options' => array(
                            'table' => $db->getCanonicName('users', 'table'),
                            'field' => 'email',
                            'adapter' => $this->sm->get('Zend\Db\Adapter\Adapter'),
                            'exclude' => array(
                                'field' => 'userId',
                                'value' => $this->userId
                            )
                        )
                    )
                )
            ),
            'categorieId' => array(
                'name' => 'categorieId',
                'required' => true
            )
        );
    }
    
    /**
     * Initialise la propriété userId pour le validateur avant d'appeler la méthode standard
     * 
     * (non-PHPdoc)
     * @see \Zend\Form\Form::setData()
     */
    public function setData($data)
    {
        $this->userId = -1;
        if (is_array($data)) {
            if (array_key_exists('userId', $data)) {
                $this->userId = $data['userId'];
            } 
        } elseif ($data instanceof \ArrayAccess) {
             if ($data->offsetExists('userId')) {
                 $this->userId = $data->offsetGet('userId');
             }
        } elseif ($data instanceof \Traversable) {
            foreach ($data as $key => $value) {
                if ($key == 'userId') {
                    $this->userId = $value;
                }
            }
        }
        return parent::setData($data);
    }
}