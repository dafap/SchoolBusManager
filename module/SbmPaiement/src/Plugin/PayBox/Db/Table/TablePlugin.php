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
 * @date 28 déc. 2019
 * @version 2019-2.5.4
 */
namespace SbmPaiement\Plugin\PayBox\Db\Table;

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
     * @TODO : à mettre au point pour PayBox. Actuellement c'est le code utilisé dans PayFiP
     *
     * {@inheritDoc}
     * @see \SbmPaiement\Plugin\TablePluginInterface::criteres()
     */
    public function criteres()
    {
        return [
            [
                'name' => 'Titulaire',
                'attributes' => [
                    'type' => 'text',
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
            ],[
                'type'=> 'text',
                'name' => 'exer',
                'attributes'=>[
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
                'type' => 'Zend\Form\Element\Date',
                'name' => 'dattrans',
                'attributes' => [
                    'id' => 'critere-dattrans',
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
                'name' => 'saisie',
                'options' => [
                    'label' => 'Mode',
                    'empty_option' => 'Tous',
                    'value_options' => [
                        'T' => 'Test',
                        'X' => 'Activation',
                        'W' => 'Production'
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
     * Nécessaire pour pouvoir modifier le format de la date dans $where si nécessaire. Le
     * format créé est de la forme Y-m-d. Ici il faut Ymd.
     *
     * @TODO à vérifier pour Paybox.
     *
     * @param Where $where
     */
    public function adapteWhere(Where &$where)
    {
        $predicates = $where->getPredicates();
        foreach ($predicates as &$predicate) {
            foreach ($predicate as &$item) {
                if ($item instanceof \Zend\Db\Sql\Predicate\Like &&
                    $item->getIdentifier() == 'dattrans') {
                        $datetmp = \DateTime::createFromFormat('Y-m-d|%', $item->getLike());
                        if ($datetmp) {
                            $item->setLike($datetmp->format('dmY'));
                            // le % terminal est supprimé - comparaison stricte
                        }
                    }
                    if ($item instanceof \Zend\Db\Sql\Predicate\Like &&
                        $item->getIdentifier() == 'titulaire') {
                            $item->setLike('%' . $item->getLike());
                        }
            }
        }
    }
}
