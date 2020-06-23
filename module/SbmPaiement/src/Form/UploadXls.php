<?php
/**
 * Formulaire d'upload d'un fichier de contrôle au format xls
 *
 * Le fichier export_transactions est un fichier d'exportation fourni par le backoffice
 * de la plateforme.
 * Les caractéristiques du fichier sont précisées dans Plugin/[nom de la plateforme]/config
 *
 * @project sbm
 * @package SbmPaiement/Form
 * @filesource UploadXls.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmPaiement\Form;

use SbmBase\Model\StdLib;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class UploadXls extends Form implements InputFilterProviderInterface
{

    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post')
        ->setAttribute('target', '__blank');
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
        $file_element = new Element\File('xlsfile');
        $file_element->setLabel('Choisissez le fichier xls')
        ->setLabelAttributes([
            'class' => 'sbm-label-140dem'
        ])
        ->setAttribute('id', 'xlsfile')
        ->setOption('error_attributes',
            [
                'class' => 'sbm-error'
            ]);
        $this->add($file_element);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'firstline',
                'attributes' => [
                    'id' => 'xlsfirstline',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Nom des colonnes en première ligne dans le tableau',
                    'label_attributes' => [
                        'class' => 'sbm-label-245dem'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
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
        $tmpuploads = StdLib::concatPath($this->getOption('tmpuploads'),
            'pluginpaiements.xls');
        return [
            'xlsfile' => [
                'type' => 'Zend\InputFilter\FileInput',
                // 'name' => 'xlsfile',
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