<?php
/**
 * Gestion de la table du plugin
 * (déclarée dans /config/autoload/sbm.global.php)
 *
 * @project sbm
 * @package SbmPaiement/Plugin/SystemPay/Db/Table
 * @filesource TablePlugin.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 sept.2018
 * @version 2018-2.4.5
 */
namespace SbmPaiement\Plugin\SystemPay\Db\Table;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use SbmPaiement\Plugin\TablePluginInterface;
use Zend\Db\Sql\Where;

class TablePlugin extends AbstractSbmTable implements TablePluginInterface
{

    /**
     * Initialisation du transporteur
     */
    protected function init()
    {
        $this->table_name = 'systempay';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'SbmPaiement\Plugin\TableGateway';
        $this->id_name = 'systempayId';
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

    public function criteres()
    {
        return [
            [
                'name' => 'vads_cust_name',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-vads_cust_name',
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
                'type' => 'Zend\Form\Element\Date',
                'name' => 'vads_trans_date',
                'attributes' => [
                    'id' => 'critere-vads_trans_date',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Date du paiement',
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
                    $item->getIdentifier() == 'vads_trans_date') {
                    $item->setLike(str_replace('-', '', $item->getLike()));
                }
            }
        }
    }
}