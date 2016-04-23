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
 * @date 7 avr. 2016
 * @version 2016-2
 */
namespace SbmFront\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Where;
use SbmCommun\Filter\SansAccent;

class CreerCompte extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     * Db manager (nécessaire pour vérifier l'email)
     *
     * @var DbManager
     */
    private $db_manager;

    public function __construct($db_manager)
    {
        $this->db_manager = $db_manager;
        parent::__construct('compte');
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
                'autofocus' => 'autofocus',
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
        return array(
            'titre' => array(
                'name' => 'titre',
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
                            'table' => $this->db_manager->getCanonicName('users', 'table'),
                            'field' => 'email',
                            'adapter' => $this->db_manager->getDbAdapter()
                        )
                    )
                )
            )
        );
    }

    public function isValid()
    {
        $result = parent::isValid();
        if ($result) {
            // vérifier qu'un compte de même nom et prénom n'existe pas déjà parmi les responsables et les users
            $filterSA = new SansAccent();
            // d'abord dans la table user pour savoir s'il n'y a pas un compte avec un autre email
            $where = new Where();
            $where->equalTo('nom', $this->data['nom'])->equalTo('prenom', $this->data['prenom']);
            $tUsers = $this->db_manager->get('Sbm\Db\Table\Users');
            $resultset = $tUsers->fetchAll($where);
            if ($resultset->count()) {
                $u = $resultset->current();
                $msg = 'Vous avez déjà créé un compte avec l\'email ' . $u->email . ". Si vous ne vous connaissez pas le mot de passe, cliquez sur le lien `Mot de passe oublié` de la page d'accueil.\n";
                $msg .= 'Si vous n\'avez plus accès à cet email rapprochez vous des services de la Communauté de communes pour faire modifier votre compte.';
                $e = $this->get('prenom');
                $e->setMessages(array(
                    $msg
                ));
                $result = false;
            } else {
                // ensuite dans la table responsables pour savoir si entretemps une inscription papier n'a pas été enregistrée.
                //@todo: il faudrait vérifier qu'aucune inscription n'a eu lieu cette année. Sinon, il va y avoir des demandes 
                // nombreuses lors des renouvellements d'inscription.
                unset($where);
                $where = new Where();
                $nomSA = $filterSA->filter($this->data['nom']);
                $prenomSA = $filterSA->filter($this->data['prenom']);
                $where->equalTo('nomSA', $nomSA)->equalTo('prenomSA', $prenomSA);
                $tResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
                $resultset = $tResponsables->fetchAll($where);
                if ($resultset->count()) {
                    $msg = 'Il existe déjà une personne enregistrée avec ce nom et ce prénom. Rapprochez vous des services de la Communauté de communes pour vous faire créer un compte.';
                    $e = $this->get('prenom');
                    $e->setMessages(array(
                        $msg
                    ));
                    $result = false;
                }
            }
        } else {
            $e = $this->get('email');
            $messages = $e->getMessages();
            if (array_key_exists('recordFound', $messages)) {
                $msg = 'Un compte a déjà été créé avec cet email. Si vous ne vous souvenez plus du mot de passe, cliquez sur le lien `Mot de passe oublié` sur la page d\'accueil.';
                $e->setMessages(array(
                    $msg
                ));
            }
        }
        return $result;
    }
}