<?php
/**
 * Calcul des effectifs des élèves transportés par Service pour un transporteur donné.
 *
 * L'initialisation doit nécessairement se faire par :
 *   $objet->setCaractereConditionnel($transporteurId)->init($sanspreinscrits);
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifTransporteursServices.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;
use SbmGestion\Model\Db\Service\EffectifInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class EffectifTransporteursServices extends AbstractEffectifType3 implements
    EffectifInterface
{

    public function init(bool $sanspreinscrits = false)
    {
        $serviceKeys = [
            'ligneId',
            'sens',
            'moment',
            'ordre'
        ];
        $this->structure = [];
        $conditions = $this->getConditions($sanspreinscrits);
        $conditions['transporteurId'] = $this->caractere;

        $rowset = $this->requete($serviceKeys, $conditions, $serviceKeys);

        foreach ($rowset as $row) {
            if (array_key_exists('column1', $row) && array_key_exists('column2', $row) &&
                array_key_exists('column3', $row) && array_key_exists('column4', $row)) {
                $this->structure[$row['column1']][$row['column2']][$row['column3']][$row['column4']][1] = $row['effectif'];
            }
        }

        $rowset = $this->requetePourCorrespondance($serviceKeys, $conditions, $serviceKeys);
        foreach ($rowset as $row) {
            if (array_key_exists('column1', $row) && array_key_exists('column2', $row) &&
                array_key_exists('column3', $row) && array_key_exists('column4', $row)) {
                $this->structure[$row['column1']][$row['column2']][$row['column3']][$row['column4']][2] = $row['effectif'];
            }
        }
        // remplace les colonnes 1 et 2 de niveau 5 par leur total
        foreach ($this->structure as &$niveau1) {
            foreach ($niveau1 as &$niveau2) {
                foreach ($niveau2 as &$niveau3) {
                    foreach ($niveau3 as &$niveau4) {
                        $total = 0;
                        foreach ($niveau4 as &$value) {
                            $total += $value;
                        }
                        $niveau4 = $total;
                    }
                }
            }
        }
        return $this->structure;
    }

    /**
     * Surcharge pour gestion de la jointure
     *
     * {@inheritdoc}
     * @see \SbmGestion\Model\Db\Service\Eleve\AbstractEffectifType2::getJointureAffectationsCorrespondances()
     */
    protected function getJointureAffectationsCorrespondances($index)
    {
        return [
            'a.millesime = correspondances.millesime',
            'a.eleveId = correspondances.eleveId',
            'a.trajet = correspondances.trajet',
            'a.jours = correspondances.jours',
            'a.moment = correspondances.moment',
            'a.ligne2Id = correspondances.ligne1Id',
            'a.sensligne2 = correspondances.sensligne1',
            'a.ordreligne2 = correspondances.ordreligne1'
        ];
    }

    /**
     * Ici, on groupe sur les colonnes de la table service
     *
     * {@inheritDoc}
     * @see \SbmGestion\Model\Db\Service\Eleve\AbstractEffectifType3::getGroup()
     */
    protected function getGroup($group)
    {
        if (is_array($group)) {
            foreach ($group as &$k) {
                $k = "ser.$k";
            }
            return $group;
        } else {
            $k = "ser.$group";
            return $k;
        }
    }

    /**
     *
     * @param string $ligneId
     * @param int $sens
     * @param int $moment
     * @param int $ordre
     * @return mixed|array
     */
    public function transportes(string $ligneId, int $sens, int $moment, int $ordre)
    {
        return StdLib::getParamR([
            $ligneId,
            $sens,
            $moment,
            $ordre
        ], $this->structure, 0);
    }

    public function getIdColumn()
    {
        return 'transporteurId';
    }

    protected function requete($columns, $conditions, $group)
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
        ], $this->getJointureAffectationsServices(1), $this->getColumns($columns))
            ->where($this->arrayToWhere($where, $conditions))
            ->group($this->getGroup($group));

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * Surcharge la méthode pour joindre la table service qui contient la colonne
     * transporteurId (le caractere conditionnel)
     *
     * @param string $columns
     * @param array $conditions
     * @param string|array $group
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function requetePourCorrespondance($columns, $conditions, $group)
    {
        $select1 = $this->sql->select()
            ->from($this->tableNames['affectations'])
            ->where([
            'millesime' => $this->millesime,
            'correspondance' => 2
        ]);

        $where = new Where();
        $where->equalTo('s.millesime', $this->millesime)->isNull(
            'correspondances.millesime');

        $select = $this->sql->select()
            ->from([
            's' => $this->tableNames['scolarites']
        ])
            ->columns([])
            ->join([
            'a' => $this->tableNames['affectations']
        ], 'a.millesime=s.millesime AND a.eleveId=s.eleveId', [])
            ->join([
            'ser' => $this->tableNames['services']
        ], $this->getJointureAffectationsServices(2), $this->getColumns($columns))
            ->join([
            'correspondances' => $select1
        ], implode(' AND ', $this->getJointureAffectationsCorrespondances('')), [],
            Select::JOIN_LEFT)
            ->where($this->arrayToWhere($where, $conditions))
            ->group($this->getGroup($group));

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
}