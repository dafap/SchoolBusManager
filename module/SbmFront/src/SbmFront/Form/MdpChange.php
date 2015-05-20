<?php
/**
 * Formulaire de changement de mot de passe
 *
 * L'utilisateur donne l'ancien mot de passe et deux fois le nouveau.
 * 
 * @project sbm
 * @package SbmFront/Form
 * @filesource MdpChange.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 févr. 2015
 * @version 2015-1
 */
namespace SbmFront\Form;

use Zend\InputFilter\InputFilterProviderInterface;
use SbmCommun\Form\AbstractSbmForm;

class MdpChange extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($param = 'mdp')
    {
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
            'name' => 'mdp_old',
            'type' => 'password',
            'attributes' => array(
                'id' => 'mdp-old',
                'class' => 'sbm-mdp'
            ),
            'options' => array(
                'label' => 'Donnez votre mot de passe',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'mdp_new',
            'type' => 'password',
            'attributes' => array(
                'id' => 'mdp-new',
                'class' => 'sbm-mdp'
            ),
            'options' => array(
                'label' => 'Donnez un nouveau mot de passe',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'mdp_ctrl',
            'type' => 'password',
            'attributes' => array(
                'id' => 'mdp-ctrl',
                'class' => 'sbm-mdp'
            ),
            'options' => array(
                'label' => 'Confirmez ce mot de passe',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'value' => 'Envoyer la demande',
                'id' => 'responsable-submit',
                'autofocus' => 'autofocus',
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'responsable-cancel',
                'class' => 'button default cancel'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'mdp_new' => array(
                'name' => 'mdp_new',
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
                        'name' => 'SbmFront\Model\Validator\Mdp',
                        'options' => array(
                            'len' => 6,
                            'min' => 1,
                            'maj' => 1,
                            'num' => 1
                        )
                    )
                )
            ),
            'mdp_ctrl' => array(
                'name' => 'mdp_ctrl',
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
                        'name' => 'identical',
                        'options' => array(
                            'token' => 'mdp_new'
                        )
                    )
                )
            )
        );
    }
}