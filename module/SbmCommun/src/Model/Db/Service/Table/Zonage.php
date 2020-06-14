<?php
/**
 * Gestion de la table `zonage`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Zonage.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 juin 2020
 * @version 2020-2.5.7
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmBase\Model\StdLib;
use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Zonage extends AbstractSbmTable
{

    /**
     * Structuré
     * ['communeId'][md5(adresse)]['existe'=>...,'inscriptionenligne'=>...,'paiementenligne'=>...]
     *
     * @var array
     */
    private $attributes;

    /**
     * Initialisation du transporteur
     */
    protected function init()
    {
        $this->table_name = 'zonage';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Zonage';
        $this->id_name = 'zonageId';
        $this->attributes = [];
    }

    /**
     * Enregistre les données en complétant s'il le faut le champ 'nomSA' et renvoie le
     * zonageId
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::saveRecord()
     */
    public function saveRecord(ObjectDataInterface $obj_data)
    {
        try {
            $old_data = $this->getRecord($obj_data->getId());
            $is_new = false;
        } catch (\Exception $e) {
            $is_new = true;
        }
        if ($is_new) {
            $obj_data->addCalculateField('nomSA');
        } else {
            if (! $obj_data->isUnchanged($old_data)) {
                if ($old_data->nom != $obj_data->nom) {
                    $obj_data->addCalculateField('nomSA');
                }
            }
        }
        parent::saveRecord($obj_data);
        if ($is_new) {
            return $this->getTableGateway()->getLastInsertValue();
        } else {
            return $obj_data->getId();
        }
    }

    /**
     *
     * @param int $zonageId
     * @param bool $value
     */
    public function setSelection($zonageId, $value)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'zonageId' => $zonageId,
            'selection' => $value
        ]);
        parent::updateRecord($oData);
    }

    /**
     *
     * @param int $zonageId
     * @param bool $value
     */
    public function setInscriptionEnLigne($zonageId, $value)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'zonageId' => $zonageId,
            'inscriptionenligne' => $value
        ]);
        parent::updateRecord($oData);
    }

    /**
     *
     * @param int $zonageId
     * @param bool $value
     */
    public function setPaiementEnLigne($zonageId, $value)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'zonageId' => $zonageId,
            'paiementenligne' => $value
        ]);
        parent::updateRecord($oData);
    }

    /**
     * Renvoie un tableau d'identifiants des communes zonées
     */
    public function getCommunesZonees(): array
    {
        $result = [];
        foreach ($this->getTableGateway()->selectWith(
            $this->getTableGateway()
                ->getSql()
                ->select()
                ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
                ->columns([
                'communeId'
            ])) as $row) {
            $result[] = $row->communeId;
        }
        return $result;
    }

    public function getJoinWith(Select $subselect)
    {
        $t = $this->getTableGateway()->getTable();
        return $this->getTableGateway()->selectWith(
            $this->getTableGateway()
                ->getSql()
                ->select()
                ->columns([
                'nom'
            ])
                ->join([
                's' => $subselect
            ], "$t.zonageId = s.zonageId", [])
                ->order('s.nb DESC'));
    }

    private function getAttributs(string $communeId, string $key, string $adresse): array
    {
        if (! StdLib::array_keys_exists([
            $communeId,
            $key
        ], $this->attributes)) {
            $result = [];
            $where = new Where();
            $where->equalTo('communeId', $communeId)->like('nomSA', $adresse);
            $rowset = $this->fetchAll($where);
            if ($rowset->count()) {
                $inscriptionenligne = false;
                $paiementenligne = false;
                foreach ($rowset as $ozonage) {
                    $inscriptionenligne |= $ozonage->inscriptionenligne;
                    $paiementenligne |= $ozonage->paiementenligne;
                }
                $result = [
                    'existe' => true,
                    'inscriptionenligne' => $inscriptionenligne,
                    'paiementenligne' => $paiementenligne
                ];
            } else {
                $result = [
                    'existe' => false,
                    'inscriptionenligne' => false,
                    'paiementenligne' => false
                ];
            }
            $this->attributes[$communeId][$key] = $result;
        }
        return $this->attributes;
    }

    /**
     * Indique le l'adresse est dans la table 'zonage'
     *
     * @param string $communeId
     * @param string $adresse
     * @return bool
     */
    public function isAdresseConnue(string $communeId, string $adresse): bool
    {
        $key = md5($adresse);
        return StdLib::getParamR([
            $communeId,
            $key,
            'existe'
        ], $this->getAttributs($communeId, $key, $adresse), false);
    }

    public function isInscriptionEnLigne(string $communeId, string $adresse): bool
    {
        $key = md5($adresse);
        return StdLib::getParamR([
            $communeId,
            $key,
            'inscriptionenligne'
        ], $this->getAttributs($communeId, $key, $adresse), false);
    }

    public function isPaiementEnLigne(string $communeId, string $adresse): bool
    {
        $key = md5($adresse);
        return StdLib::getParamR([
            $communeId,
            $key,
            'paiementenligne'
        ], $this->getAttributs($communeId, $key, $adresse), false);
    }
}

