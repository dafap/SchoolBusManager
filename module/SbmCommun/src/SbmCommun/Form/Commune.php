<?php
/**
 * Formulaire de saisie et modification d'une commune
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Commune.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

class Commune extends AbstractSbmForm
{

    public function __construct($param = 'commune')
    {
        parent::__construct($param);
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
            'name' => 'communeId',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-codeid',
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Code INSEE de la commune',
                'label_attributes' => array(
                    'class' => 'sbm-label150'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'nom',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-nom',
                'class' => 'sbm-text45'
            ),
            'options' => array(
                'label' => 'Nom de la commune en majuscules',
                'label_attributes' => array(
                    'class' => 'sbm-label150'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'nom_min',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-nom-min',
                'class' => 'sbm-text45'
            ),
            'options' => array(
                'label' => 'Nom de la commune en minuscules',
                'label_attributes' => array(
                    'class' => 'sbm-label150'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'alias',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-alias',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Autre nom (en majuscules)',
                'label_attributes' => array(
                    'class' => 'sbm-label150'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'alias_min',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-alias-min',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Autre nom (en minuscules)',
                'label_attributes' => array(
                    'class' => 'sbm-label150'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'aliasCG',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-aliascg',
                'class' => 'sbm-text45'
            ),
            'options' => array(
                'label' => 'Nom CG',
                'label_attributes' => array(
                    'class' => 'sbm-label150'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'codePostal',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-codepostal',
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Code postal',
                'label_attributes' => array(
                    'class' => 'sbm-label150'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'departement',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-departement',
                'class' => 'sbm-text3'
            ),
            'options' => array(
                'label' => 'Code du département',
                'label_attributes' => array(
                    'class' => 'sbm-label150'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'canton',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-canton',
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Code du canton',
                'label_attributes' => array(
                    'class' => 'sbm-label150'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'population',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-population',
                'class' => 'sbm-text8'
            ),
            'options' => array(
                'label' => 'Population',
                'label_attributes' => array(
                    'class' => 'sbm-label150'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'membre',
            'attributes' => array(
                'id' => 'commune-membre',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Commune membre',
                'label_attributes' => array(
                    'class' => 'sbm-label150'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'desservie',
            'attributes' => array(
                'id' => 'commune-desservie',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Commune desservie',
                'label_attributes' => array(
                    'class' => 'sbm-label150'
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
                'id' => 'commune-submit',
                'autofocus' => 'autofocus',
                'class' => 'button submit left135'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'commune-cancel',
                'class' => 'button cancel'
            )
        ));
    }

    public function modifFormForEdit()
    {
        $e = $this->remove('communeId');
        $this->add(array(
            'name' => 'communeId',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'communeInsee',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-codeid',
                'disabled' => 'disabled',
                'class' => 'form commune codeid'
            ),
            'options' => array(
                'label' => 'Code INSEE de la commune',
                'label_attributes' => array(
                    'class' => 'form commune label label-codeid'
                ),
                'error_attributes' => array(
                    'class' => 'form commune error error-codeid'
                )
            )
        ));
    }

    public function setData($data)
    {
        parent::setData($data);
        if ($this->has('communeInsee')) {
            $e = $this->get('communeInsee');
            $e->setValue($this->get('communeId')
                ->getValue());
        }
    }
}