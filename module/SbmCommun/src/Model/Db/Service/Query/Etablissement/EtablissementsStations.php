<?php
/**
 * Requêtes concernant la table `etablissements-stations`
 * (déclarée dans module.config.php sous l'alias 'Sbm\Db\Query\EtablissementsStations')
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Query/Etablissement
 * @filesource EtablissementsStations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Etablissement;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Db\Sql\Predicate\Not;
use Zend\Db\Sql\Where;

class EtablissementsStations extends AbstractQuery
{

    protected function init()
    {
        $this->select = $this->sql->select(
            [
                'rel' => $this->db_manager->getCanonicName('etablissements-stations',
                    'table')
            ])
            ->columns([
            'etablissementId',
            'stationId',
            'rang'
        ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'rel.etablissementId = eta.etablissementId',
            [
                'etab_nom' => 'nom',
                'etab_alias' => 'alias',
                'etab_aliasCG' => 'aliasCG',
                'etab_adresse1' => 'adresse1',
                'etab_adresse2' => 'adresse2',
                'etab_codePostal' => 'codePostal',
                'etab_communeId' => 'communeId',
                'etab_niveau' => 'niveau',
                'etab_statut' => 'statut',
                'etab_visible' => 'visible',
                'etab_desservie' => 'desservie',
                'etab_regrPeda' => 'regrPeda',
                'etab_rattacheA' => 'rattacheA',
                'etab_telephone' => 'telephone',
                'etab_fax' => 'fax',
                'etab_email' => 'email',
                'etab_directeur' => 'directeur',
                'etab_jOuverture' => 'jOuverture',
                'etab_hMatin' => 'hMatin',
                'etab_hMidi' => 'hMidi',
                'etab_hAMidi' => 'hAMidi',
                'etab_hSoir' => 'hSoir',
                'etab_hGarderieOMatin' => 'hGarderieOMatin',
                'etab_hGarderieFMidi' => 'hGarderieFMidi',
                'etab_hGarderieFSoir' => 'hGarderieFSoir',
                'etab_x' => 'x',
                'etab_y' => 'y'
            ])
            ->join([
            'com1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'com1.communeId = eta.communeId',
            [
                'etab_commune' => 'nom',
                'etab_lacommune' => 'alias',
                'etab_laposte' => 'alias_laposte'
            ])
            ->join([
            'sta' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'rel.stationId = sta.stationId',
            [
                'sta_nom' => 'nom',
                'sta_ouverte' => 'ouverte',
                'sta_visible' => 'visible',
                'sta_selection' => 'selection',
                'sta_x' => 'x',
                'sta_y' => 'y'
            ])
            ->join([
            'comsta' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'comsta.communeId = sta.communeId',
            [
                'sta_commune' => 'nom',
                'sta_lacommune' => 'alias',
                'sta_laposte' => 'alias_laposte'
            ]);
    }

    /**
     * Renvoie un paginator
     *
     * @param \Zend\Db\Sql\Where|array $where
     * @param string|array $order
     *
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorES($where, $order = [])
    {
        return $this->paginator($this->selectES($where, $order));
    }

    protected function selectES($where, $order = [])
    {
        if (! $where instanceof Where) {
            $where = new Where($where);
        }
        $select = clone $this->select;
        if ($order) {
            $select->order($order);
        }
        return $this->paginator($select->where($where));
    }

    public function fetchAll($where, $order = [])
    {
        if (! $where instanceof Where) {
            $where = $this->arrayToWhere($where);
        }
        $select = clone $this->select;
        if ($order) {
            $select->order($order);
        }
        return $this->renderResult($select->where($where));
    }

    private function arrayToWhere($filtre = [])
    {
        $where = new Where();
        foreach ($filtre as $key => $value) {
            if (is_array($value)) {
                $key = (string) $key;
                switch ($key) {
                    case 'not':
                    case 'sauf':
                    case 'pas':
                        $where->addPredicate(new Not($this->arrayToWhere(null, $value)));
                        break;
                    case '<':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->lessThan($value[0], $value[1]);
                                break;
                            case 4:
                                $where->lessThan($value[0], $value[1], $value[2],
                                    $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException(
                                    'Nombre incorrect de paramètres dans lessThan.');
                        }
                        break;
                    case '<=':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->lessThanOrEqualTo($value[0], $value[1]);
                                break;
                            case 4:
                                $where->lessThanOrEqualTo($value[0], $value[1], $value[2],
                                    $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException(
                                    'Nombre incorrect de paramètres dans lessThanOrEqualTo.');
                        }
                        break;
                    case '>':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->greaterThan($value[0], $value[1]);
                                break;
                            case 4:
                                $where->greaterThan($value[0], $value[1], $value[2],
                                    $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException(
                                    'Nombre incorrect de paramètres dans greaterThan.');
                        }
                        break;
                    case '>=':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->greaterThanOrEqualTo($value[0], $value[1]);
                                break;
                            case 4:
                                $where->greaterThanOrEqualTo($value[0], $value[1],
                                    $value[2], $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException(
                                    'Nombre incorrect de paramètres dans greaterThanOrEqualTo.');
                        }
                        break;
                    case '=':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->equalTo($value[0], $value[1]);
                                break;
                            case 4:
                                $where->equalTo($value[0], $value[1], $value[2], $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException(
                                    'Nombre incorrect de paramètres dans notEqualTo.');
                        }
                        break;
                    case '<>':
                    case '!=':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->notEqualTo($value[0], $value[1]);
                                break;
                            case 4:
                                $where->notEqualTo($value[0], $value[1], $value[2],
                                    $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException(
                                    'Nombre incorrect de paramètres dans notEqualTo.');
                        }
                        break;
                    case 'isNull':
                    case 'IsNull':
                    case 'is null':
                        $where->isNull($value[0]);
                        break;
                    case 'isNotNull':
                    case 'IsNotNull':
                    case 'is not null':
                        $where->isNotNull($value[0]);
                        break;
                    default:
                        $where->nest()
                            ->addPredicate($this->arrayToWhere(null, $value))
                            ->unnest();
                        break;
                }
            } else {
                switch ($value) {
                    case 'or':
                        $where->or;
                        break;
                    case 'and':
                        $where->and;
                        break;
                    default:
                        $where->equalTo($key, $value);
                        break;
                }
            }
        }
        return $where;
    }
}