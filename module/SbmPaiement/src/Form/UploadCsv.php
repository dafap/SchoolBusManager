<?php
/**
 * Formulaire d'upload d'un fichier de contrôle au format csv
 *
 * Les caractéristiques du fichier sont précisées dans Plugin/[nom de la plateforme]/config
 * 
 * @project sbm
 * @package SbmPaiement/Form
 * @filesource UploadCsv.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPaiement\Form;

use SbmBase\Model\StdLib;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class UploadCsv extends Form implements InputFilterProviderInterface
{

    public function __construct($name = null, $options = [])
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
        $file_element = new Element\File('csvfile');
        $file_element->setLabel('Choisissez le fichier csv')
            ->setLabelAttributes([
            'class' => 'sbm-label-140dem'
        ])
            ->setAttribute('id', 'csvfile')
            ->setOption('error_attributes', [
            'class' => 'sbm-error'
        ]);
        $this->add($file_element);
        $this->add(
            [
                'type' => 'text',
                'name' => 'separator',
                'attributes' => [
                    'id' => 'csvseparator',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Séparateur de colonnes',
                    'label_attributes' => [
                        'class' => 'sbm-label-140dem'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'enclosure',
                'attributes' => [
                    'id' => 'csvenclosure',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Encadrement de texte',
                    'label_attributes' => [
                        'class' => 'sbm-label-140dem'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);

        $this->add(
            [
                'type' => 'text',
                'name' => 'escape',
                'attributes' => [
                    'id' => 'csvescape',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Caractère d\'échappement',
                    'label_attributes' => [
                        'class' => 'sbm-label-140dem'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'firstline',
                'attributes' => [
                    'id' => 'csvfirstline',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Nom des colonnes en première ligne dans le fichier',
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
            'pluginpaiements.csv');
        return [
            'csvfile' => [
                'type' => 'Zend\InputFilter\FileInput',
                // 'name' => 'csvfile',
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