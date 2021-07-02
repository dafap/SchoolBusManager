<?php
/**
 * Requêtes permettant de calculer les flux financiers
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Finances
 * @filesource Flux.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 juil. 2021
 * @version 2021-2.6.2
 */
namespace SbmGestion\Model\Db\Service\Finances;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Predicate;

class Flux extends AbstractQuery
{

    private $tableName;

    protected function init()
    {
        $this->tableName = $this->db_manager->getCanonicName('paiements', 'table');
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getFlux(array $criteres)
    {
        $result = $this->renderResult($this->selectFlux($criteres));
        return $result;
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorFlux(array $criteres)
    {
        return $this->paginator($this->selectFlux($criteres));
    }

    /**
     * Rapprochement des flux du CA et de Tresorerie
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\Sql\Select
     */
    private function selectFlux(array $criteres): Select
    {
        return $this->sql->select()
            ->columns([
            'date',
            'caisseJournee' => 'montant',
            'caisseCumul' => 'cumul'
        ])
            ->from(
            [
                't1' => $this->selectTresorerie($this->getWhereTresorerie($criteres))
            ])
            ->join([
            't2' => $this->selectCA($this->getWhereCA($criteres))
        ], 't1.date = t2.date', [
            'caJournee' => 'montant',
            'caCumul' => 'cumul'
        ], Select::JOIN_LEFT)
            ->order('date');
    }

    private function getWhereTresorerie(array $criteres): Where
    {
        $where = new Where();
        if (array_key_exists('anneeScolaire', $criteres)) {
            $where->equalTo('anneeScolaire', $criteres['anneeScolaire']);
            if (array_key_exists('du', $criteres)) {
                $where->greaterThanOrEqualTo('dateValeur', $criteres['du']);
            }
            if (array_key_exists('au', $criteres)) {
                $where->lessThanOrEqualTo('dateValeur', $criteres['au']);
            }
        } else {
            $where->between('dateValeur', $criteres['du'], $criteres['au']);
        }
        return $where;
    }

    private function getWhereCA(array $criteres): Where
    {
        $platform = $this->db_manager->getDbAdapter()->getPlatform();
        $where = new Where();
        if (array_key_exists('anneeScolaire', $criteres)) {
            $where->equalTo('anneeScolaire', $criteres['anneeScolaire']);
            if (array_key_exists('du', $criteres)) {
                $literal = sprintf('%s >= %s', $this->xDatePaiement(),
                    $platform->quoteValue($criteres['du']));
                $where->literal($literal);
            }
            if (array_key_exists('au', $criteres)) {
                $literal = sprintf('%s <= %s', $this->xDatePaiement(),
                    $platform->quoteValue($criteres['au']));
                $where->literal($literal);
            }
        } else {
            $literal = sprintf('%s BETWEEN %s AND %s', $this->xDatePaiement(),
                $platform->quoteValue($criteres['du']),
                $platform->quoteValue($criteres['au']));
            $where->literal($literal);
        }
        return $where;
    }

    /**
     * Renvoie le flux de la trésorerie par jour
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getFluxTresorerie(Where $where)
    {
        return $this->renderResult($this->selectTresorerie($where));
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorFluxTresorerie(Where $where)
    {
        return $this->paginator($this->selectTresorerie($where));
    }

    /**
     * La colonne dateValeur de la table est du type DATE
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\Sql\Select
     */
    private function selectTresorerie(Where $where): Select
    {
        return $this->sql->select($this->tableName)
            ->columns(
            [
                'date' => 'dateValeur',
                'montant' => new Literal('sum(montant)'),
                'cumul' => new Literal($this->xCumulTresorerie($where))
            ])
            ->where($where)
            ->group([
            'dateValeur'
        ]);
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @return string
     */
    private function xCumulTresorerie(Where $where): string
    {
        $where1 = new Where([
            $where
        ]);
        $where1->lessThanOrEqualTo('dateValeur', 'date', Predicate::TYPE_IDENTIFIER,
            Predicate::TYPE_IDENTIFIER);
        $select = $this->sql->select($this->tableName)
            ->columns([
            'cumul' => new Literal('sum(montant)')
        ])
            ->where($where1);
        return sprintf('(%s)', $this->getSqlString($select));
    }

    /**
     * Renvoie le flux des CA par jour
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getFluxCA(Where $where)
    {
        return $this->renderResult($this->selectCA($where));
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorFluxCA(Where $where)
    {
        return $this->paginator($this->selectCA($where));
    }

    /**
     * La colonne datePaiement de la table est du type DATETIME
     *
     * SELECT DATE_FORMAT(p.`datePaiement`,'%Y-%m-%d') AS date, sum(p.`montant`) AS
     * montant, (SELECT sum(`montant`) FROM `sbm_t_paiements` WHERE
     * DATE_FORMAT(`datePaiement`,'%Y-%m-%d') %lt%= date AND `anneeScolaire` Like
     * '%millesime%%') AS cumul
     * FROM `sbm_t_paiements` AS p
     * WHERE `anneeScolaire` Like '%millesime%%'
     * GROUP BY DATE_FORMAT(`datePaiement`,'%Y-%m-%d')
     */
    private function selectCA(Where $where): Select
    {
        return $this->sql->select($this->tableName)
            ->columns(
            [
                'date' => new Literal($this->xDatePaiement()),
                'montant' => new Literal('sum(montant)'),
                'cumul' => new Literal($this->xCumulCA($where))
            ])
            ->where($where)
            ->group([
            new Literal($this->xDatePaiement())
        ]);
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @return string
     */
    private function xCumulCA(Where $where): string
    {
        $where1 = new Where([
            $where
        ]);
        $platform = $this->db_manager->getDbAdapter()->getPlatform();
        $where1->literal(
            sprintf('%s <= %s', $this->xDatePaiement(), $platform->quoteIdentifier('date')));
        $select = $this->sql->select($this->tableName)
            ->columns([
            'cumul' => new Literal('sum(montant)')
        ])
            ->where($where1);
        return sprintf('(%s)', $this->getSqlString($select));
    }

    /**
     * Renvoie la date AAAA-MM-JJ alors que le champ datePaiement est un DateTime
     *
     * @return string
     */
    private function xDatePaiement(): string
    {
        $platform = $this->db_manager->getDbAdapter()->getPlatform();
        return sprintf('DATE_FORMAT(%s,"%s")', $platform->quoteIdentifier('datePaiement'),
            '%Y-%m-%d');
    }
}