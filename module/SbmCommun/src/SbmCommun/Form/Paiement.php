<?php
/**
 * Formulaire de saisie et modification d'un paiement
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Paiement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 jan. 2015
 * @version 2015-1
 */
namespace SbmCommun\Form;

use Zend\Filter\StringToUpper;
use Zend\Filter\StripTags;
use Zend\Filter\StringTrim;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\FormInterface;

class Paiement extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     * Permet de passer les arguments à la méthode getInputFilter()
     *
     * @var array
     */
    private $args = array();

    public function __construct($args = array('responsableId' => true, 'note' => false), $param = 'paiement')
    {
        parent::__construct($param);
        $this->args = $args;
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
            'name' => 'paiementId',
            'type' => 'hidden'
        ));
        
        $this->adapte($args);
        
        $this->add(array(
            'name' => 'dateDepot',
            'type' => 'SbmCommun\Form\Element\DateTimeSelect',
            'attributes' => array(
                'id' => 'paiement-dateDepot'
            ),
            'options' => array(
                'label' => 'Date du dépot',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'create_empty_option' => true,
                'min_year' => date('Y') - 10,
                'max_year' => date('Y') + 1,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'datePaiement',
            'type' => 'Zend\Form\Element\DateTimeSelect',
            'attributes' => array(
                'id' => 'paiement-datePaiement'
            ),
            'options' => array(
                'label' => 'Date du paiement',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'create_empty_option' => true,
                'min_year' => date('Y') - 10,
                'max_year' => date('Y') + 1,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'dateValeur',
            'type' => 'Zend\Form\Element\DateSelect',
            'attributes' => array(
                'id' => 'paiement-dateValeur'
            ),
            'options' => array(
                'label' => 'Date de valeur',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'create_empty_option' => true,
                'min_year' => date('Y') - 10,
                'max_year' => date('Y') + 1,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'anneeScolaire',
            'type' => 'text',
            'attributes' => array(
                'id' => 'paiement-annee-scolaire',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Année scolaire',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'exercice',
            'type' => 'text',
            'attributes' => array(
                'id' => 'paiement-exercice',
                'class' => 'sbm-width-5c'
            ),
            'options' => array(
                'label' => 'Exercice budgétaire',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'montant',
            'type' => 'text',
            'attributes' => array(
                'id' => 'paiement-montant',
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'Montant',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'codeModeDePaiement',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'paiement-mode-de-paiement',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Mode de paiemant',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'value_options' => array(),
                'empty_option' => 'Choisissez le mode de paiement',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'codeCaisse',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'paiement-mode-caisse',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Caisse',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'value_options' => array(),
                'empty_option' => 'Choisissez la caisse',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'banque',
            'type' => 'text',
            'attributes' => array(
                'id' => 'paiement-banque',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Banque',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'titulaire',
            'type' => 'text',
            'attributes' => array(
                'id' => 'paiement-titulaire',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Titulaire',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'reference',
            'type' => 'text',
            'attributes' => array(
                'id' => 'paiement-reference',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Référence du paiement',
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
                'value' => 'Enregistrer',
                'id' => 'paiement-submit',
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'paiement-cancel',
                'class' => 'button default cancel'
            )
        ));
    }

    /**
     * Attention, cette adaptation du formulaire doit être appelée dans le constructeur afin qu'on retrouve la valeur donnée
     * par la méthode $this->getData()
     *
     * @param bool $hidden            
     */
    private function adapte($config)
    {
        if ($config['responsableId']) {
            $this->add(array(
                'name' => 'responsableId',
                'type' => 'hidden',
                'attributes' => array(
                    'id' => 'responsableId'
                )
            ));
        } else {
            $this->add(array(
                'name' => 'responsableId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => array(
                    'id' => 'responsableId',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-30c'
                ),
                'options' => array(
                    'label' => 'Responsable',
                    'label_attributes' => array(
                        'class' => 'sbm-label'
                    ),
                    'value_options' => array(),
                    'empty_option' => 'Choisissez le responsable concerné',
                    'error_attributes' => array(
                        'class' => 'sbm-error'
                    )
                )
            ));
        }
        if ($config['note']) {
            $this->add(array(
                'type' => 'textarea',
                'name' => 'note',
                'attributes' => array(
                    'id' => 'note',
                    'class' => 'sbm-width-55c'
                ),
                'options' => array(
                    'label' => 'Motif de la modification',
                    'label_attributes' => array(
                        'class' => 'sbm-label-top'
                    ),
                    'error_attributes' => array(
                        'class' => 'sbm-error'
                    )
                )
            ));
        }
    }

    public function getInputFilterSpecification()
    {
        $result = array(
            'banque' => array(
                'name' => 'banque',
                'required' => false
            ),
            'titulaire' => array(
                'name' => 'titulaire',
                'required' => false
            ),
            'reference' => array(
                'name' => 'reference',
                'required' => false
            ),
            'montant' => array(
                'name' => 'montant',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => array(
                            'separateur' => '.',
                            'car2sep' => ','
                        )
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'SbmCommun\Model\Validator\Decimal'
                    ),
                    array(
                        'name' => 'Zend\Validator\GreaterThan',
                        'options' => array(
                            'min' => 0,
                            'inclusive' => false
                        )
                    )
                )
            )
        );
        if (\array_key_exists('note', $this->args) && $this->args['note']) {
            $result['note'] = array(
                'name' => 'note',
                'required' => true
            );
        }
        return $result;
    }

    public function isValid()
    {
        $data = $this->data;
        if (empty($data['dateValeur']['year']) && empty($data['dateValeur']['month']) && empty($data['dateValeur']['day'])) {
            $data['dateValeur']['year'] = $data['datePaiement']['year'];
            $data['dateValeur']['month'] = $data['datePaiement']['month'];
            $data['dateValeur']['day'] = $data['datePaiement']['day'];
        }
        $this->setData($data);
        return parent::isValid();
    }
}