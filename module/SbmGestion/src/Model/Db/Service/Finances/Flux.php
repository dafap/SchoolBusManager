<?php
/**
 * Description du fichier
 *
 * @project sbm
 * @package
 * @filesource Flux.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 juin 2021
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
        $where->lessThanOrEqualTo('dateValeur', 'date', Predicate::TYPE_IDENTIFIER,
            Predicate::TYPE_IDENTIFIER);
        $select = $this->sql->select($this->tableName)
            ->columns([
            'cumul' => new Literal('sum(montant)')
        ])
            ->where($where);
        return $this->getSqlString($select);
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
                'date' => $this->xDatePaiement(),
                'montant' => new Literal('sum(montant)'),
                'cumul' => new Literal($this->xCumulCA($where))
            ])
            ->where($where)
            ->group([
            $this->xDatePaiement()
        ]);
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @return string
     */
    private function xCumulCA(Where $where): string
    {
        $where->lessThanOrEqualTo($this->xDatePaiement(), 'date',
            Predicate::TYPE_IDENTIFIER, Predicate::TYPE_IDENTIFIER);
        $select = $this->sql->select($this->tableName)
            ->columns([
            'cumul' => new Literal('sum(montant)')
        ])
            ->where($where);
        return $this->getSqlString($select);
    }

    /**
     * Renvoie la date AAAA-MM-JJ alors que le champ datePaiement est un DateTime
     *
     * @return string
     */
    private function xDatePaiement(): string
    {
        return "DATE_FORMAT(datePaiement,'%Y-%m-%d')";
    }
}