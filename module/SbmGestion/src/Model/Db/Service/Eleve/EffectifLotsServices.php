<?php
/**
 * Calcul des effectifs des élèves transportés par Service pour un lot de marché donné.
 *
 * L'initialisation doit nécessairement se faire par :
 *   $objet->setCaractereConditionnel($lotId)->init($sanspreinscrits);
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifLotsServices.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmGestion\Model\Db\Service\EffectifInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class EffectifLotsServices extends AbstractEffectifType3 implements EffectifInterface
{

    public function init(bool $sanspreinscrits = false)
    {
        $this->structure = [];
        $conditions = $this->getConditions($sanspreinscrits);

        $rowset = $this->requete('serviceId', $conditions,
            [
                'ligne1Id',
                'sens',
                'moment',
                'ordre'
            ]);
        foreach ($rowset as $row) {
            $this->structure[$row['serviceId']][1] = $row['effectif'];
        }

        $rowset = $this->requetePourCorrespondance('serviceId', $conditions,
            'a.service2Id');
        foreach ($rowset as $row) {
            $this->structure[$row['serviceId']][2] = $row['effectif'];
        }
        // total
        foreach ($this->structure as &$value) {
            $value = array_sum($value);
        }
        return $this->structure;
    }

    public function getIdColumn()
    {
        return 'serviceId';
    }

    protected function requete($column, $conditions, $group)
    {
        $where = new Where();
        $where->equalTo('a.millesime', $this->millesime);

        $select = $this->sql->select();
        $select->from([
            'a' => $this->tableNames['affectations']
        ])
            ->columns([])
            ->join([
            's' => $this->tableNames['scolarites']
        ], 's.millesime=a.millesime AND s.eleveId=a.eleveId', [])
            ->join([
            'ser' => $this->tableNames['services']
        ], implode(' AND ', [
            'a.ligne1Id = ser.ligneId',
            'a.sensligne1 = ser.sens',
            'a.moment = ser.moment',
            'a.ordreligne1 = ser.ordre'
        ]), [
            $column,
            'effectif' => new Expression('count(*)')
        ])
            ->where($this->arrayToWhere($where, $conditions))
            ->group($group);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    protected function requetePourCorrespondance($column, $conditions, $group)
    {
        $select1 = $this->sql->select()
            ->from($this->tableNames['affectations'])
            ->where([
            'millesime' => $this->millesime,
            'correspondance' => 2
        ]);

        $jointure = [
            'a.millesime = correspondances.millesime',
            'a.eleveId = correspondances.eleveId',
            'a.trajet = correspondances.trajet',
            'a.jours = correspondances.jours',
            'a.sens = correspondances.sens',
            'a.ligne2Id = correspondances.ligne1Id',
            'a.sensligne2 = correspondances.sensligne1',
            'a.moment = correspondances.moment',
            'a.ordreligne2 = correspondances.ordreligne1'
        ];
        $where = new Where();
        $where->equalTo('a.millesime', $this->millesime)->isNull(
            'correspondances.millesime');

        $select = $this->sql->select()
            ->from([
            'a' => $this->tableNames['affectations']
        ])
            ->columns([])
            ->join([
            's' => $this->tableNames['scolarites']
        ], 's.millesime=a.millesime AND s.eleveId=a.eleveId', [])
            ->join([
            'ser' => $this->tableNames['services']
        ], 'a.service2Id=ser.serviceId',
            [
                $column,
                'effectif' => new Expression('count(*)')
            ])
            ->join([
            'correspondances' => $select1
        ], implode(' AND ', $jointure), [], Select::JOIN_LEFT)
            ->where($this->arrayToWhere($where, $conditions))
            ->group($group);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
}