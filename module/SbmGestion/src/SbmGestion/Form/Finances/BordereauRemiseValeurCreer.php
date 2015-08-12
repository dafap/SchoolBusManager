<?php
/**
 * Formulaire permettant de paramétrer la préparation d'un bordereau de remise de valeurs
 *
 * @project sbm
 * @package SbmGestion/Form/Finances
 * @filesource BordereauRemiseValeurCreer.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 août 2015
 * @version 2015-1
 */
namespace SbmGestion\Form\Finances;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class BordereauRemiseValeurCreer extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('bordereau');
        $this->setAttribute('method', 'post');
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
            'type' => 'text',
            'name' => 'exercice',
            'attributes' => array(
                'id' => 'exercice'
            ),
            'options' => array(
                'label' => 'Exercice budgétaire',
                'label_attributes' => array(),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'anneeScolaire',
            'attributes' => array(
                'id' => 'anneeScolaire'
            ),
            'options' => array(
                'label' => 'Année scolaire',
                'label_attributes' => array(),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'codeModeDePaiement',
            'attributes' => array(
                'id' => 'codeModeDePaiement'
            ),
            'options' => array(
                'label' => 'Quel mode de paiement ?',
                'label_attributes' => array(),
                'empty_option' => 'Choisissez dans la liste',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'codeCaisse',
            'attributes' => array(
                'id' => 'codeCaisse'
            ),
            'options' => array(
                'label' => 'Dans quelle caisse ?',
                'label_attributes' => array(),
                'empty_option' => 'Choisissez dans la liste',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'preparer-cancel',
                'autofocus' => 'autofocus',
                'class' => 'button default cancel'
            )
        ));
        $this->add(array(
            'name' => 'preparer',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Préparer un bordereau',
                'id' => 'preparer-submit',
                'class' => 'button default submit'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'exercice' => array(
                'name' => 'exercice',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\Digits'
                    )
                )
            ),
            'anneeScolaire' => array(
                'name' => 'anneeScolaire',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'SbmCommun\Filter\DigitSeparator'
                    )
                )
            )
        );
    }
}