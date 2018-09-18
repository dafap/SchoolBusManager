<?php
/**
 * Formulaire de confirmation de la suppression d'une relation etablissement - service.
 * 
 * @project sbm
 * @package SbmGestion/Form
 * @filesource EtablissementServiceSuppr.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmGestion\Form;

use SbmCommun\Form\AbstractSbmForm as Form;

class EtablissementServiceSuppr extends Form
{

    public function __construct($param = 'etablissement-service-suppr')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');

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
        $this->add([
            'name' => 'etablissementId',
            'type' => 'hidden'
        ]);
        $this->add([
            'name' => 'serviceId',
            'type' => 'hidden'
        ]);
        $this->add([
            'name' => 'origine',
            'type' => 'hidden'
        ]);
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Confirmer',
                    'id' => 'etablissement-service-suppr-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'etablissement-service-suppr-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }
}