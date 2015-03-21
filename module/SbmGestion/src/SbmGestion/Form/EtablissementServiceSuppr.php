<?php
/**
 * Formulaire de confirmation de la suppression d'une relation etablissement - service.
 * 
 * @project sbm
 * @package SbmGestion/Form
 * @filesource EtablissementServiceSuppr.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 mars 2015
 * @version 2015-1
 */
namespace SbmGestion\Form;

use Zend\Form\Form;
// use Zend\InputFilter\InputFilterProviderInterface;
class EtablissementServiceSuppr extends Form // implements InputFilterProviderInterface
{

    public function __construct($param = 'etablissement-service-suppr')
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
            'name' => 'etablissementId',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'serviceId',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'origine',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Confirmer',
                'id' => 'etablissement-service-suppr-submit',
                'autofocus' => 'autofocus',
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'etablissement-service-suppr-cancel',
                'class' => 'button default cancel'
            )
        ));
    }
}