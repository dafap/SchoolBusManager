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
 * @date 2 avr. 2015
 * @version 2015-1
 */
namespace SbmPaiement\Plugin\SystemPay\Db\Table;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use Zend\Db\Sql\Where;
use SbmPaiement\Plugin\TablePluginInterface;

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
        $oData->exchangeArray(array(
            $this->getIdName() => $id,
            'selection' => $selection
        ));
        parent::saveRecord($oData);
    }
    
    public function criteres()
    {
        return array(
            array(
                'name' => 'vads_cust_last_name',
                'attributes' => array(
                    'type' => 'text',
                    'id' => 'critere-vads_cust_last_name',
                    'class' => 'sbm-width-30c',
                    'maxlegth' => '30'
                ),
                'options' => array(
                    'label' => 'Responsable',
                    'label_attributes' => array(
                        'class' => 'sbm-first'
                    ),
                    'error_attributes' => array(
                        'class' => 'sbm-error'
                    )
                )
            ),
            array(
                'type' => 'Zend\Form\Element\Date',
                'name' => 'vads_trans_date',
                'attributes' => array(
                    'id' => 'critere-vads_trans_date',
                    'class' => ''
                ),
                'options' => array(
                    'label' => 'Date du paiement',
                    'label_attributes' => array(
                        'class' => ''
                    ),
                    'error_attributes' => array(
                        'class' => 'sbm-error'
                    )
                )
            ),
            array(
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'selection',
                'attributes' => array(
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => array(
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ),
                    'class' => 'sbm-checkbox'
                ),
                'options' => array(
                    'label' => 'Sélectionnés',
                    'label_attributes' => array(
                        'class' => ''
                    ),
                    'error_attributes' => array(
                        'class' => 'sbm-error'
                    )
                )
            )
        );
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
                if ($item instanceof \Zend\Db\Sql\Predicate\Like && $item->getIdentifier() == 'vads_trans_date') {
                    $item->setLike(str_replace('-', '', $item->getLike()));
                }
            }
        }
    }
}