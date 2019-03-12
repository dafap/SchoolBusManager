<?php
/**
 * Calcul des effectifs des élèves transportés et des demandes par commune
 *
 * Calcul spécial qui n'est pas rattaché à un AbstractEffectifTypex mais dérive
 * directement de AbstractEffectif
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifCommunes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Having;
use Zend\Db\Sql\Where;

class EffectifCommunes extends AbstractEffectif implements EffectifInterface
{

    public function init(bool $sanspreinscrits = false)
    {
        $this->structure = [];
        // calcul du nombre d'élèves inscrits ou préinscrits ( = sauf rayés)
        // - pour la commune du R1 : inscrit = 1 AND sco.communeId IS NULL AND demandeR1 > 0
        $filtre = array_merge($this->getFiltreDemandes($sanspreinscrits),
            [
                'inscrit' => 1,
                '>' => [
                    'demandeR1',
                    0
                ],
                'is null' => [
                    's.communeId'
                ]
            ]);
        $rowset = $this->requete(1, $filtre, 'r.communeId');
        foreach ($rowset as $row) {
            $this->structure[$row['column']]['demandes']['r1'] = $row['effectif'];
        }
        $filtre = array_merge($filtre, [
            'a.trajet' => 1,
            'a.correspondance' => 1
        ]);
        $rowset = $this->requete(4, $filtre, 'r.communeId');
        foreach ($rowset as $row) {
            $this->structure[$row['column']]['transportes']['r1'] = $row['effectif'];
        }

        // - pour la commune du R2 : inscrit = 1 AND demandeR2 > 0
        $filtre = [
            'inscrit' => 1,
            '>' => [
                'demandeR2',
                0
            ]
        ];
        $rowset = $this->requete(2, $filtre, 'r.communeId');
        foreach ($rowset as $row) {
            $this->structure[$row['column']]['demandes']['r2'] = $row['effectif'];
        }
        $filtre = array_merge($filtre, [
            'a.trajet' => 2,
            'a.correspondance' => 1
        ]);
        $rowset = $this->requete(5, $filtre, 'r.communeId');
        foreach ($rowset as $row) {
            $this->structure[$row['column']]['transportes']['r2'] = $row['effectif'];
        }

        // - pour la commune de l'élève lorsqu'il a une adresse personnelle :
        // inscrit = 1 AND sco.communeId IS NOT NULL AND demandeR1 > 0
        $filtre = [
            'inscrit' => 1,
            '>' => [
                'demandeR1',
                0
            ],
            'is not null' => [
                's.communeId'
            ]
        ];
        $rowset = $this->requete(3, $filtre, 'communeId');
        foreach ($rowset as $row) {
            $this->structure[$row['column']]['demandes']['ele'] = $row['effectif'];
        }
        $filtre = array_merge($filtre, [
            'a.trajet' => 1,
            'a.correspondance' => 1
        ]);
        $rowset = $this->requete(6, $filtre, 'communeId');
        foreach ($rowset as $row) {
            $this->structure[$row['column']]['transportes']['ele'] = $row['effectif'];
        }

        // calcul du nombre d'élèves
        foreach ($this->structure as &$value) {
            if (isset($value['demandes'])) {
                $value['total']['demandes'] = array_sum($value['demandes']);
            } else {
                $value['total']['demandes'] = 0;
            }
            if (isset($value['transportes'])) {
                $value['total']['transportes'] = array_sum($value['transportes']);
            } else {
                $value['total']['transportes'] = 0;
            }
        }
        return $this->structure;
    }

    public function getEffectifColumns()
    {
        return [
            'transportes' => "nombre d'élèves transportés",
            'demandes' => "nombre de demandes"
        ];
    }

    public function getIdColumn()
    {
        return 'communeId';
    }

    private function requete($rang, $filtre, $group)
    {
        $where = new Where();
        $where->equalTo('s.millesime', $this->millesime);

        $select = $this->sql->select();
        switch ($rang) {
            case 1:
            case 2:
                $on = sprintf('e.responsable%sId=r.responsableId', $rang);
                $select->from([
                    'e' => $this->tableNames['eleves']
                ])
                    ->join([
                    's' => $this->tableNames['scolarites']
                ], 'e.eleveId=s.eleveId', [])
                    ->join([
                    'r' => $this->tableNames['responsables']
                ], $on, [
                    'column' => 'communeId'
                ])
                    ->columns([
                    'effectif' => new Expression('count(*)')
                ])
                    ->where($this->arrayToWhere($where, $filtre))
                    ->group($group);
                break;
            case 3:
                $select->from([
                    's' => $this->tableNames['scolarites']
                ])
                    ->columns(
                    [
                        'column' => 'communeId',
                        'effectif' => new Expression('count(*)')
                    ])
                    ->where($this->arrayToWhere($where, $filtre))
                    ->group($group)
                    ->having(function (Having $where) {
                    $where->isNotNull('communeId');
                });
                break;
            case 4:
            case 5:
                $select->from([
                    's' => $this->tableNames['scolarites']
                ])
                    ->join([
                    'a' => $this->tableNames['affectations']
                ], 'a.millesime = s.millesime AND a.eleveId=s.eleveId', [])
                    ->join([
                    'r' => $this->tableNames['responsables']
                ], 'r.responsableId = a.responsableId', [
                    'column' => 'communeId'
                ])
                    ->columns([
                    'effectif' => new Expression('count(*)')
                ])
                    ->where($this->arrayToWhere($where, $filtre))
                    ->group($group);
                break;
            case 6:
                $select->from([
                    's' => $this->tableNames['scolarites']
                ])
                    ->join([
                    'a' => $this->tableNames['affectations']
                ], 'a.millesime = s.millesime AND a.eleveId=s.eleveId', [])
                    ->columns(
                    [
                        'column' => 'communeId',
                        'effectif' => new Expression('count(*)')
                    ])
                    ->where($this->arrayToWhere($where, $filtre))
                    ->group($group);
                break;
            default:
                throw new \SbmGestion\Model\Db\Service\Exception(
                    __METHOD__ . ' - Mauvais argument `rang`.');
                break;
        }

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    public function demandes($communeId)
    {
        return StdLib::getParamR([
            $communeId,
            'total',
            'demandes'
        ], $this->structure, 0);
    }

    public function transportes($communeId)
    {
        return StdLib::getParamR([
            $communeId,
            'total',
            'transportes'
        ], $this->structure, 0);
    }
}