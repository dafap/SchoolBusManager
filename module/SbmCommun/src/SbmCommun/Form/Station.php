<?php
/**
 * Formulaire de saisie et modification d'une station
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Station.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

class Station extends AbstractSbmForm
{

    public function __construct($param = 'station')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'stationId',
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
            'name' => 'communeId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'station-communeId',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Commune',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'empty_option' => 'Choisissez une commune',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'nom',
            'type' => 'text',
            'attributes' => array(
                'id' => 'station-nom',
                'class' => 'sbm-text45'
            ),
            'options' => array(
                'label' => 'Nom de la station',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'id' => 'station-aliasCG',
                'class' => 'sbm-text45'
            ),
            'options' => array(
                'label' => 'Nom CG',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'codeCG',
            'type' => 'text',
            'attributes' => array(
                'id' => 'station-codeCG',
                'class' => 'sbm-text11'
            ),
            'options' => array(
                'label' => 'Code CG',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'latitude',
            'type' => 'text',
            'attributes' => array(
                'id' => 'station-latitude',
                'class' => 'sbm-text20'
            ),
            'options' => array(
                'label' => 'Latitude',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'longitude',
            'type' => 'text',
            'attributes' => array(
                'id' => 'station-longitude',
                'class' => 'sbm-text20'
            ),
            'options' => array(
                'label' => 'Longitude',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'visible',
            'attributes' => array(
                'id' => 'station-visible',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Visible',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'ouverte',
            'attributes' => array(
                'id' => 'station-ouverte',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Ouverte',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'id' => 'station-submit',
                'autofocus' => 'autofocus',
                'class' => 'button submit left135'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'station-cancel',
                'class' => 'button cancel'
            )
        ));
    }
}