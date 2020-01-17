<?php
/**
 * Requêtes pour les statistiques concernant les élèves
 * (classe déclarée dans mocule.config.php sous l'alias 'Sbm\Statistiques\Eleve')
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Eleve
 * @filesource Statistiques.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 05 jan. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Db\Sql\Predicate\Not;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class Statistiques extends AbstractQuery
{

    protected function init()
    {
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés par millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbEnregistresByMillesime($millesime = null)
    {
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns(
            [
                'millesime',
                'regimeId',
                'effectif' => new Expression('count(eleveId)')
            ])
            ->group([
            'millesime',
            'regimeId'
        ]);
        $where = new Where();
        $where->literal('selection = 0'); // on supprime les élèves en attente
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        return iterator_to_array($this->renderResult($select->where($where)));
    }

    /**
     * Renvoie un tableau statistiques des élèves inscrits par millesime
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbInscritsByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('selection = 0')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns(
            [
                'millesime',
                'regimeId',
                'effectif' => new Expression('count(eleveId)')
            ])
            ->where($where)
            ->group([
            'millesime',
            'regimeId'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie un tableau statistiques des élèves préinscrits par millesime
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbPreinscritsByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('selection = 0')
            ->literal('paiement = 0')
            ->literal('fa = 0')
            ->literal('gratuit = 0');
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns(
            [
                'millesime',
                'regimeId',
                'effectif' => new Expression('count(eleveId)')
            ])
            ->where($where)
            ->group([
            'millesime',
            'regimeId'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie un tableau statistiques des élèves en famille d'accueil par millesime
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbFamilleAccueilByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('selection = 0')
            ->literal('fa = 1');
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns(
            [
                'millesime',
                'regimeId',
                'effectif' => new Expression('count(eleveId)')
            ])
            ->where($where)
            ->group([
            'millesime',
            'regimeId'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie un tableau statistiques des élèves rayés par millesime
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @param string $inscrits
     *            si true alors on ne compte que les inscrits rayés, sinon on ne compte
     *            que les préinscrits
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbRayesByMillesime($millesime = null, $inscrits = true)
    {
        $where = new Where();
        $where->literal('inscrit = 0')->literal('selection = 0');
        if ($inscrits) {
            $where->nest()->literal('paiement = 1')->or->literal('fa = 1')->or->literal(
                'gratuit > 0')->unnest();
        } else {
            $where1 = new Where();
            $where1->literal('paiement = 1')->or->literal('fa = 1')->or->literal(
                'gratuit > 0');
            $where->addPredicate(new Not($where1));
        }
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns(
            [
                'millesime',
                'regimeId',
                'effectif' => new Expression('count(eleveId)')
            ])
            ->where($where)
            ->group([
            'millesime',
            'regimeId'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie un tableau statistiques des élèves en garde alternée par millesime
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbGardeAlterneeByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('sco.selection = 0')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')
            ->unnest()
            ->isNotNull('responsable2Id');
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns(
            [
                'millesime',
                'regimeId',
                'effectif' => new Expression('count(ele.eleveId)')
            ])
            ->join([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ], 'ele.eleveId = sco.eleveId', [])
            ->where($where)
            ->group([
            'millesime',
            'regimeId'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie un tableau statistiques des élèves inscrits par millesime et etablissement
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbByMillesimeEtablissement($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('sco.selection = 0')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns(
            [
                'millesime',
                'regimeId',
                'effectif' => new Expression('count(eleveId)')
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'sco.etablissementId = eta.etablissementId', [])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'com.communeId = eta.communeId',
            [
                'etablissement' => new Expression('concat(com.alias, " - ", eta.nom)')
            ])
            ->where($where)
            ->group([
            'millesime',
            'regimeId',
            'com.nom',
            'eta.nom'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie un tableau statistiques des élèves inscrits par millesime et classe
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbByMillesimeClasse($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('sco.selection = 0')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns(
            [
                'millesime',
                'regimeId',
                'effectif' => new Expression('count(eleveId)')
            ])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'sco.classeId = cla.classeId', [
            'classe' => 'nom'
        ])
            ->where($where)
            ->group([
            'millesime',
            'regimeId',
            'cla.nom'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés à une distance inférieure à
     * 1 km par millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbMoins1KmByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('selection = 0')
            ->lessThan('distanceR1', 1)
            ->lessThan('distanceR2', 1);
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns(
            [
                'millesime',
                'regimeId',
                'effectif' => new Expression('count(eleveId)')
            ])
            ->where($where)
            ->group([
            'millesime',
            'regimeId'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés à une distance de 1 km à
     * moins de 3km par millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbDe1A3KmByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('selection = 0')
            ->lessThan('distanceR1', 3)
            ->lessThan('distanceR2', 3)
            ->nest()
            ->greaterThanOrEqualTo('distanceR1', 1)->or->greaterThanOrEqualTo(
            'distanceR2', 1)->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns(
            [
                'millesime',
                'regimeId',
                'effectif' => new Expression('count(eleveId)')
            ])
            ->where($where)
            ->group([
            'millesime',
            'regimeId'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés à une distance d'au moins 3
     * km par millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNb3kmEtPlusByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('selection = 0')
            ->nest()
            ->greaterThanOrEqualTo('distanceR1', 3)->or->greaterThanOrEqualTo(
            'distanceR2', 3)->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns(
            [
                'millesime',
                'regimeId',
                'effectif' => new Expression('count(eleveId)')
            ])
            ->where($where)
            ->group([
            'millesime',
            'regimeId'
        ]);
        return iterator_to_array($this->renderResult($select));
    }
}