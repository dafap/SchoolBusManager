<?php
/**
 * Formulaire présentant un textarea pour éditer le contenu d'un fichier et les
 * boutons Envoyer et Abandonner
 *
 * @project sbm
 * @package SbmInstallation/src/Form
 * @filesource FileContent.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmInstallation\Form;

use Zend\Form\Form;

class FileContent extends Form
{

    public function __construct($name = 'file-content', $options = [])
    {
        parent::__construct($name, $options);
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
            'type' => 'hidden',
            'name' => 'filename'
        ]);
        $this->add(
            [
                'type' => 'textarea',
                'name' => 'content',
                'attributes' => [
                    'class' => 'file-content',
                    'id' => 'file-content'
                ],
                'options' => [
                    'label' => 'Contenu du fichier',
                    'label_attributes' => [
                        'class' => 'bloc'
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
                    'value' => 'Enregistrer',
                    'id' => 'station-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'station-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }
}