<?php
/**
 * Description du fichier
 *
 * @project sbm
 * @package
 * @filesource CriteresForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 juin 2021
 * @version 2021-2.6.2
 */
namespace SbmGestion\Form\Finances;

use SbmCommun\Form\CriteresForm as SbmCommunCriteresForm;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Db\Sql\Where;

class CriteresForm extends SbmCommunCriteresForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        $descriptor = [
            [
                'name' => 'du',
                'type' => 'date',
                'attributes' => [
                    'class' => 'critere-date'
                ],
                'options' => [
                    'label' => 'Date à partir du',
                    'label_attributes' => [
                        'class' => 'critere-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ],
            [
                'name' => 'au',
                'type' => 'date',
                'attributes' => [
                    'class' => 'critere-date'
                ],
                'options' => [
                    'label' => 'jusqu\'au',
                    'label_attributes' => [
                        'class' => 'critere-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ],
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'raz',
                'attributes' => [
                    'class' => 'critere-checkbox'
                ],
                'options' => [
                    'use_hidden_element' => false,
                    'label' => 'Supprimer les critères',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]
        ];
        parent::__construct($descriptor);
    }

    public function getWhere(): Where
    {
        $where = new Where();

        return $where;
    }

    public function getTitle(): string
    {
        $title = 'à écrire';
        return $title;
    }
}