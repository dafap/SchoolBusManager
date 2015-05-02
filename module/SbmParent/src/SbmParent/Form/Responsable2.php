<?php
/**
 * Partie du formulaire d'inscription d'un enfant concernant le second responsable en cas de garde alternée
 *
 * Cette classe est utilisée en tant que collection
 * Afin qu'il n'y ait pas de conflit, tous les nom d'éléments commmencent par r2.
 * Les methodes setData et getData sont adaptées en conséquence pour que ça fonctionne aussi bien si les datas
 * proviennent de la table (pas de r2 en préfixe du nom des colonnes) ou du post (r2 en préfixe).
 * 
 * @project sbm
 * @package SbmParent/Form
 * @filesource Responsable2.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 avr. 2015
 * @version 2015-1
 */
namespace SbmParent\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\FormInterface;

class Responsable2 extends AbstractSbmForm implements InputFilterProviderInterface
{

    private $complet;

    public function __construct($complet)
    {
        $this->complet = $complet;
        parent::__construct('responsable2');
        $this->add(array(
            'type' => 'hidden',
            'name' => 'r2responsable2Id'
        ));
        $this->add(array(
            'type' => 'hidden',
            'name' => 'r2userId'
        ));
        if ($this->complet) {
            $this->add(array(
                'type' => 'Zend\Form\Element\Select',
                'name' => 'r2titre',
                'attributes' => array(
                    'id' => 'titre',
                    'class' => 'sbm-width-15c',
                    'autofocus' => 'autofocus'
                ),
                'options' => array(
                    'label' => 'Identité du responsable',
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
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'name' => 'r2nom',
                'attributes' => array(
                    'id' => 'nom',
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
                'type' => 'SbmCommun\Form\Element\Prenom',
                'name' => 'r2prenom',
                'attributes' => array(
                    'id' => 'prenom',
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
                'name' => 'r2adresseL1',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => array(
                    'id' => 'adresseL1',
                    'class' => 'sbm-width-40c'
                ),
                'options' => array(
                    'label' => 'Adresse',
                    'label_attributes' => array(
                        'class' => 'sbm-label'
                    ),
                    'error_attributes' => array(
                        'class' => 'sbm-error'
                    )
                )
            ));
            $this->add(array(
                'name' => 'r2adresseL2',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => array(
                    'id' => 'adresseL2',
                    'class' => 'sbm-width-40c'
                ),
                'options' => array(
                    'label' => 'Adresse',
                    'label_attributes' => array(
                        'class' => 'sbm-label'
                    ),
                    'error_attributes' => array(
                        'class' => 'sbm-error'
                    )
                )
            ));
            $this->add(array(
                'name' => 'r2codePostal',
                'type' => 'SbmCommun\Form\Element\CodePostal',
                'attributes' => array(
                    'id' => 'codePostal',
                    'class' => 'sbm-width-5c'
                ),
                'options' => array(
                    'label' => 'Code postal',
                    'label_attributes' => array(
                        'class' => 'sbm-label'
                    ),
                    'error_attributes' => array(
                        'class' => 'sbm-error'
                    )
                )
            ));
            $this->add(array(
                'name' => 'r2communeId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => array(
                    'id' => 'communeId',
                    'class' => 'sbm-width-45c'
                ),
                'options' => array(
                    'label' => 'Commune',
                    'label_attributes' => array(
                        'class' => 'sbm-label'
                    ),
                    'empty_option' => 'Choisissez une commune',
                    'disable_inarray_validator' => true,
                    'allow_empty' => false,
                    'error_attributes' => array(
                        'class' => 'sbm-error'
                    )
                )
            ));
            $this->add(array(
                'name' => 'r2telephoneF',
                'type' => 'SbmCommun\Form\Element\Telephone',
                'attributes' => array(
                    'id' => 'telephoneF',
                    'class' => 'sbm-width-15c'
                ),
                'options' => array(
                    'label' => 'Téléphone',
                    'label_attributes' => array(
                        'class' => 'sbm-label'
                    ),
                    'error_attributes' => array(
                        'class' => 'sbm-error'
                    )
                )
            ));
            $this->add(array(
                'name' => 'r2email',
                'type' => 'Zend\Form\Element\Email',
                'attributes' => array(
                    'id' => 'email',
                    'class' => 'sbm-width-50c'
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
        }
    }

    public function getInputFilterSpecification()
    {
        if ($this->complet) {
            return array(
                
                'r2telephoneF' => array(
                    'required' => false
                ),
                'r2email' => array(
                    'required' => false
                )
            );
        } else {
            return array(
                'r2titre' => array(
                    'required' => false
                ),
                'r2nom' => array(
                    'required' => false
                ),
                'r2prenom' => array(
                    'required' => false
                ),
                'r2adresseL1' => array(
                    'required' => false
                ),
                'r2adresseL2' => array(
                    'required' => false
                ),
                'r2codePostal' => array(
                    'required' => false
                ),
                'r2communeId' => array(
                    'required' => false
                ),
                'r2telephoneF' => array(
                    'required' => false
                ),
                'r2email' => array(
                    'required' => false
                )
            );
        }
    }

    /**
     * Ajoute le préfixe r2 aux clés qui ne l'ont pas
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Form\Form::setData()
     */
    public function setData($data)
    {
        $d = array_combine(preg_replace('/^/', 'r2', preg_replace('/^(r2)/', '', array_keys($data))), array_values($data));
        parent::setData($d);
        return $this;
    }

    /**
     * Supprime le préfice r2 aux clés qui l'ont et renvoie un responsableId au lieu d'un responsable2Id
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Form\Form::getData()
     */
    public function getData($flag = FormInterface::VALUES_NORMALIZED)
    {
        $a = parent::getData($flag);
        return array_combine(preg_replace('/(2Id)$/', 'Id', preg_replace('/^(r2)/', '', array_keys($a))), array_values($a));
    }
}