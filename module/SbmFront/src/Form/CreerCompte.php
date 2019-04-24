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
 * @date 16 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmFront\Form;

use SbmCommun\Filter\SansAccent;
use SbmCommun\Form\AbstractSbmForm;
use Zend\Db\Sql\Where;
use Zend\InputFilter\InputFilterProviderInterface;

class CreerCompte extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     * Db manager (nécessaire pour vérifier l'email)
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    public function __construct($db_manager)
    {
        $this->db_manager = $db_manager;
        parent::__construct('compte');
        $this->setAttribute('method', 'post');
        $this->add([
            'name' => 'userId',
            'type' => 'hidden'
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
        $this->add(
            [
                'name' => 'titre',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'user-titre',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-select1'
                ],
                'options' => [
                    'label' => 'Votre identité',
                    'label_attributes' => [
                        'class' => 'sbm-label-page1'
                    ],
                    'value_options' => [
                        'M.' => 'Monsieur',
                        'Mme' => 'Madame',
                        'Mlle' => 'Mademoiselle',
                        'Dr' => 'Docteur',
                        'Me' => 'Maître',
                        'Pr' => 'Professeur'
                    ],
                    'empty_option' => 'Choisissez la civilité',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'nom',
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'attributes' => [
                    'id' => 'user-nom',
                    'class' => 'sbm-text30'
                ],
                'options' => [
                    'label' => 'Nom',
                    'label_attributes' => [
                        'class' => 'sbm-label-page1 align-right'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'prenom',
                'type' => 'SbmCommun\Form\Element\Prenom',
                'attributes' => [
                    'id' => 'user-prenom',
                    'class' => 'sbm-text30'
                ],
                'options' => [
                    'label' => 'Prénom',
                    'label_attributes' => [
                        'class' => 'sbm-label-page1 align-right'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'email',
                'type' => 'Zend\Form\Element\Email',
                'attributes' => [
                    'id' => 'user-email',
                    'class' => 'sbm-text50'
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
                    'value' => 'Demander la création du compte',
                    'id' => 'responsable-submit',
                    'class' => 'button submit left-95px'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'responsable-cancel',
                    'class' => 'button cancel left-10px'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'titre' => [
                'name' => 'titre',
                'required' => true
            ],
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
                        'name' => 'Zend\Validator\Db\NoRecordExists',
                        'options' => [
                            'table' => $this->db_manager->getCanonicName('users', 'table'),
                            'field' => 'email',
                            'adapter' => $this->db_manager->getDbAdapter()
                        ]
                    ]
                ]
            ]
        ];
    }

    public function isValid()
    {
        $result = parent::isValid();
        if ($result) {
            // vérifier qu'un compte de même nom et prénom n'existe pas déjà parmi les
            // responsables
            // et les users
            $filterSA = new SansAccent();
            // d'abord dans la table user pour savoir s'il n'y a pas un compte avec un
            // autre email
            $where = new Where();
            $where->equalTo('nom', $this->data['nom'])->equalTo('prenom',
                $this->data['prenom']);
            $tUsers = $this->db_manager->get('Sbm\Db\Table\Users');
            $resultset = $tUsers->fetchAll($where);
            if ($resultset->count()) {
                $u = $resultset->current();
                $msg = 'Vous avez déjà créé un compte avec l\'email ' . $u->email .
                    ". Si vous ne connaissez pas le mot de passe, cliquez sur le lien `Mot de passe oublié` de la page d'accueil.\n";
                $msg .= 'Si vous n\'avez plus accès à cet email rapprochez vous des services de la Communauté de communes pour faire modifier votre compte.';
                $e = $this->get('prenom');
                $e->setMessages([
                    $msg
                ]);
                $result = false;
            } else {
                // ensuite dans la table responsables pour savoir si entretemps une
                // inscription papier n'a pas été enregistrée cette année.
                unset($where);
                $nomSA = $filterSA->filter($this->data['nom']);
                $prenomSA = $filterSA->filter($this->data['prenom']);
                $qResponsables = $this->db_manager->get('Sbm\Db\Query\Responsables');
                if ($qResponsables->estDejaInscritCetteAnnee($nomSA, $prenomSA)) {
                    $msg = 'Il existe déjà une personne enregistrée avec ce nom et ce prénom. Rapprochez vous des services de la Communauté de communes pour vous faire créer un compte.';
                    $e = $this->get('prenom');
                    $e->setMessages([
                        $msg
                    ]);
                    $result = false;
                }
            }
        } else {
            $e = $this->get('email');
            $messages = $e->getMessages();
            if (array_key_exists('recordFound', $messages)) {
                $msg = 'Un compte a déjà été créé avec cet email. Si vous ne connaissez pas le mot de passe, cliquez sur le lien `Mot de passe oublié` sur la page d\'accueil.';
                $e->setMessages([
                    $msg
                ]);
            }
        }
        return $result;
    }
}