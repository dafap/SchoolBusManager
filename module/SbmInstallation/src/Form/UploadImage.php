<?php
/**
 * Forumulaire d'upload d'une image
 * 
 * @project sbm
 * @package SbmInstallation/Form
 * @filesource UploadImage.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmInstallation\Form;

use SbmBase\Model\StdLib;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class UploadImage extends Form implements InputFilterProviderInterface
{

    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $this->add([
            'type' => 'hidden',
            'name' => 'fname'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'label'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'width'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'height'
        ]);

        $this->add([
            'type' => 'hidden',
            'name' => 'type'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'mime'
        ]);
        $file_element = new Element\File('image-file');
        $file_element->setLabel('Choisissez le fichier image')
            ->setAttribute('id', 'image-file')
            ->setOption('error_attributes', [
            'class' => 'sbm-error'
        ]);
        $this->add($file_element);
        $this->add(
            [
                'type' => 'submit',
                'name' => 'cancel',
                'attributes' => [
                    'value' => 'Abandonner',
                    'id' => 'classe-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
        $this->add(
            [
                'type' => 'submit',
                'name' => 'submit',
                'attributes' => [
                    'value' => 'Envoyer le fichier',
                    'id' => 'classe-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button default submit'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        $tmpuploads = StdLib::concatPath($this->getOption('tmpuploads'), 'avatar');
        return [
            'image-file' => [
                'type' => 'Zend\InputFilter\FileInput',
                // 'name' => 'image-file',
                'filters' => [
                    [
                        'name' => 'filerenameupload',
                        'options' => [
                            'target' => $tmpuploads,
                            'randomize' => true
                        ]
                    ]
                ]
            ]
        ];
    }
}