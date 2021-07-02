<?php
/**
 * Formulaire de critères sur les flux financiers.
 *
 * @project sbm
 * @package SbmGestion/src/Form/Finances
 * @filesource CriteresForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 juil. 2021
 * @version 2021-2.6.2
 */
namespace SbmGestion\Form\Finances;

use SbmBase\Model\StdLib;
use SbmBase\Model\Session;
use SbmBase\Model\DateLib;
use SbmCommun\Form\CriteresForm as SbmCommunCriteresForm;
use Zend\InputFilter\InputFilterProviderInterface;

class CriteresForm extends SbmCommunCriteresForm implements InputFilterProviderInterface
{

    private $as;

    public function __construct()
    {
        $this->as = Session::get('as');
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

    public function getCriteres(): array
    {
        $default = [
            'anneeScolaire' => $this->as['libelle']
        ];
        try {
            $criteres = $this->getData();
            if (StdLib::getParam('raz', $criteres)) {
                $criteres = [];
            } else {
                $criteres = array_filter(
                    array_intersect_key($criteres, [
                        'du' => null,
                        'au' => null
                    ]));
            }
            if (count($criteres) < 2) {
                $criteres = array_merge($criteres, $default);
            }
            return $criteres;
        } catch (\Exception $e) {
            return $default;
        }
    }

    public function getTitre(): string
    {
        $default = sprintf("Flux financiers de l'année scolaire %s", $this->as['libelle']);
        try {
            $criteres = $this->getData();
            if (StdLib::getParam('raz', $criteres)) {
                $criteres = [];
            }
            if (! StdLib::getParam('du', $criteres, false)) {
                if (! StdLib::getParam('au', $criteres, false)) {
                    $title = $default;
                } else {
                    $title = sprintf("%s jusqu'au %s", $default,
                        DateLib::formatDateFromMysql($criteres['au']));
                }
            } else {
                if (! StdLib::getParam('au', $criteres, false)) {
                    $title = sprintf("%s depuis le %s", $default,
                        DateLib::formatDateFromMysql($criteres['du']));
                } else {
                    $title = sprintf("Flux financiers du %s jusqu'au %s",
                        DateLib::formatDateFromMysql($criteres['du']),
                        DateLib::formatDateFromMysql($criteres['au']));
                }
            }
            return $title;
        } catch (\Exception $e) {
            return $default;
        }
    }
}