<?php
/**
 * Formulaire de création d'un compte
 *
 * Seuls l'email et l'identité sont nécessaires. Le reste sera demandé lors de la première connexion (en particulier le mot de passe).
 * A noter que les éléments SbmCommun\Form\Element\NomPropre et SbmCommun\Form\Element\Prenom ont leur propre méthode getInputSpecification()
 * 
 * @project sbm
 * @package SbmFront/Form
 * @filesource CreerCompte.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 févr. 2015
 * @version 2015-1
 */
namespace SbmFront\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CreerCompte extends AbstractSbmForm implements InputFilterProviderInterface
{
    /**
     * Service manager (nécessaire pour vérifier l'email)
     * 
     * @var ServiceLocatorInterface
     */
    private $sm;
    
    public function __construct(ServiceLocatorInterface $sm, $param = 'compte')
    {
        $this->sm = $sm;
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'userId',
            'type' => 'hidden',
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
                'class' => 'sbm-select1'
            ),
            'options' => array(
                'label' => 'Votre identité',
                'label_attributes' => array(
                    'class' => 'sbm-label-page1'
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
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom',
                'label_attributes' => array(
                    'class' => 'sbm-label-page1 align-right'
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
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Prénom',
                'label_attributes' => array(
                    'class' => 'sbm-label-page1 align-right'
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
                'class' => 'sbm-text50'
            ),
            'options' => array(
                'label' => 'Email',
                'label_attributes' => array(
                    'class' => 'sbm-label-page1'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Demander la création du compte',
                'id' => 'responsable-submit',
                'autofocus' => 'autofocus',
                'class' => 'button submit left-95px'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'responsable-cancel',
                'class' => 'button cancel left-10px'
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
            'email' => array(
                'name' => 'email',
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim')
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
                            'adapter' => $this->sm->get('Zend\Db\Adapter\Adapter')
                        )
                    )
                )
            )
        );
    }
}