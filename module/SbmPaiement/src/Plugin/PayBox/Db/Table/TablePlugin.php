<?php
/**
 * Gestion de la table du plugin
 * (déclarée dans /config/autoload/sbm.global.php)
 *
 * @project sbm
 * @package SbmPaiement/Plugin/PayBox/Db/Table
 * @filesource TablePlugin.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 juil. 2021
 * @version 2021-2.6.3
 */
namespace SbmPaiement\Plugin\PayBox\Db\Table;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use SbmPaiement\Plugin\TablePluginInterface;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class TablePlugin extends AbstractSbmTable implements TablePluginInterface
{

    /**
     * Initialisation du transporteur
     */
    protected function init()
    {
        $this->table_name = 'paybox';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'SbmPaiement\Plugin\TableGateway';
        $this->id_name = 'payboxId';
    }

    public function getIdName()
    {
        return $this->id_name;
    }

    public function setSelection($id, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            $this->getIdName() => $id,
            'selection' => $selection
        ]);
        parent::saveRecord($oData);
    }

    /**
     * Surcharge la méthode pour que le paginator renvoie une structure contenant le nom
     * du responsable et le mode
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::select()
     */
    public function join()
    {
        $t1 = $this->db_manager->getCanonicName($this->table_name, $this->table_type);
        $t2 = $this->db_manager->getCanonicName('responsables', 'table');
        $on = "$t1.responsableId = $t2.responsableId";
        $this->obj_select->join($t2, $on,
            [
                'nom',
                'responsable' => new Expression("concat($t2.nom,' ',$t2.prenom)")
            ]);
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\TablePluginInterface::criteres()
     */
    public function criteres()
    {
        return [
            [
                'name' => 'nom',
                'type' => 'text',
                'attributes' => [
                    'id' => 'critere-titulaire',
                    'class' => 'sbm-width-30c',
                    'maxlegth' => '30'
                ],
                'options' => [
                    'label' => 'Responsable',
                    'label_attributes' => [
                        'class' => 'sbm-first'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ],
            [
                'name' => 'exercice',
                'type' => 'text',
                'attributes' => [
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Exercice',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ],
            [
                'name' => 'numero',
                'type' => 'text',
                'attributes' => [
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'N° de facture',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ],
            [
                'type' => 'Zend\Form\Element\Date',
                'name' => 'datetrans',
                'attributes' => [
                    'id' => 'critere-dattrans'
                ],
                'options' => [
                    'label' => 'Date du paiement',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ],
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'mode',
                'options' => [
                    'label' => 'Mode',
                    'label_attributes' => [
                        'class' => 'sbm-new-line'
                    ],
                    'empty_option' => 'Tous',
                    'value_options' => [
                        '%' => 'Production',
                        'XXXXXX' => 'Test'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ],
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'resiliation',
                'attributes' => [
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Résiliations',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ],
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'selection',
                'attributes' => [
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sélectionnés',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]
        ];
    }

    public function getExpressions()
    {
        return [
            'mode' => "expression:IF(?='XXXXXX',auto='XXXXXX',auto<>'XXXXXX')",
            'resiliation' => "literal:auto LIKE 'erreur %'"
        ];
    }

    /**
     * Nécessaire pour pouvoir modifier le format de la date dans $where si nécessaire.
     * Le format créé est de la forme Y-m-d. Ici il faut Ymd.
     *
     * @param Where $where
     */
    public function adapteWhere(Where &$where)
    {
        $predicates = $where->getPredicates();

        foreach ($predicates as &$predicate) {
            foreach ($predicate as &$item) {
                if ($item instanceof \Zend\Db\Sql\Predicate\Like &&
                    $item->getIdentifier() == 'datetrans') {
                    $datetmp = \DateTime::createFromFormat('Y-m-d|%', $item->getLike());
                    if ($datetmp) {
                        $item->setLike($datetmp->format('dmY'));
                        // le % terminal est supprimé - comparaison stricte
                    }
                } elseif ($item instanceof \Zend\Db\Sql\Predicate\Operator &&
                    $item->getLeft() == 'selection') {
                    $item->setLeft(
                        sprintf('%s.%s',
                            $this->db_manager->getCanonicName($this->table_name, 'table'),
                            $item->getLeft()));
                }
            }
        }
    }
}
