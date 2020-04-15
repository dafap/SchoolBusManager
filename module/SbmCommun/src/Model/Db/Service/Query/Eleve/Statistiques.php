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
 * @date 15 avr. 2020
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
     * @param string $communeId
     *            Si communeId est donné, on ne compte que les élèves de la commune
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbEnregistresByMillesime($millesime = null, $communeId = null)
    {
        $where = new Where();
        $where->literal('sco.selection = 0'); // on supprime les élèves en attente
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
                'effectif' => new Expression('count(sco.eleveId)')
            ]);
        if ($communeId) {
            $select->join([
                'f' => $this->selectFiltreCommune($communeId)
            ], 'f.eleveId = sco.eleveId', []);
        }
        $select->where($where)->group([
            'millesime',
            'regimeId'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie un tableau statistiques des élèves inscrits par millesime. ATTENTION !
     * L'élève est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit l'élève en
     * payant. Le R2 ne compte pas pour ça.
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbInscritsByMillesime($millesime = null, $communeId = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('sco.selection = 0')
            ->nest()
            ->literal('paiementR1 = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')->unnest();
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
                'effectif' => new Expression('count(sco.eleveId)')
            ]);
        if ($communeId) {
            $select->join([
                'f' => $this->selectFiltreCommune($communeId)
            ], 'f.eleveId = sco.eleveId', []);
        }
        $select->where($where)->group([
            'millesime',
            'regimeId'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie un tableau statistiques des élèves préinscrits par millesime. ATTENTION !
     * L'élève est inscrit si paiementR1 == 0 car c'est le R1 qui inscrit l'élève en
     * payant. Le R2 ne compte pas pour ça.
     *
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbPreinscritsByMillesime($millesime = null, $communeId = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('sco.selection = 0')
            ->literal('paiementR1 = 0')
            ->literal('fa = 0')
            ->literal('gratuit = 0');
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
                'effectif' => new Expression('count(sco.eleveId)')
            ]);
        if ($communeId) {
            $select->join([
                'f' => $this->selectFiltreCommune($communeId)
            ], 'f.eleveId = sco.eleveId', []);
        }
        $select->where($where)->group([
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
    public function getNbFamilleAccueilByMillesime($millesime = null, $communeId = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('sco.selection = 0')
            ->literal('fa = 1');
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
                'effectif' => new Expression('count(sco.eleveId)')
            ]);
        if ($communeId) {
            $select->join([
                'f' => $this->selectFiltreCommune($communeId)
            ], 'f.eleveId = sco.eleveId', []);
        }
        $select->where($where)->group([
            'millesime',
            'regimeId'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie un tableau statistiques des élèves rayés par millesime. ATTENTION ! L'élève
     * est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit l'élève en payant. Le R2
     * ne compte pas pour ça.
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
    public function getNbRayesByMillesime($millesime = null, $inscrits = true,
        $communeId = null)
    {
        $where = new Where();
        $where->literal('inscrit = 0')->literal('sco.selection = 0');
        if ($inscrits) {
            $where->nest()->literal('paiementR1 = 1')->or->literal('fa = 1')->or->literal(
                'gratuit > 0')->unnest();
        } else {
            $where1 = new Where();
            $where1->literal('paiementR1 = 1')->or->literal('fa = 1')->or->literal(
                'gratuit > 0');
            $where->addPredicate(new Not($where1));
        }
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
                'effectif' => new Expression('count(sco.eleveId)')
            ]);
        if ($communeId) {
            $select->join([
                'f' => $this->selectFiltreCommune($communeId)
            ], 'f.eleveId = sco.eleveId', []);
        }
        $select->where($where)->group([
            'millesime',
            'regimeId'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie un tableau statistiques des élèves en garde alternée par millesime.
     * ATTENTION ! L'élève est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit
     * l'élève en payant. Le R2 ne compte pas pour ça.
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbGardeAlterneeByMillesime($millesime = null, $communeId = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('sco.selection = 0')
            ->nest()
            ->literal('paiementR1 = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')
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
        ], 'ele.eleveId = sco.eleveId', []);
        if ($communeId) {
            $select->join([
                'f' => $this->selectFiltreCommune($communeId)
            ], 'f.eleveId = sco.eleveId', []);
        }
        $select->where($where)->group([
            'millesime',
            'regimeId'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie un tableau statistiques des élèves inscrits par millesime et etablissement.
     * ATTENTION ! L'élève est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit
     * l'élève en payant. Le R2 ne compte pas pour ça.
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbByMillesimeEtablissement($millesime = null, $communeId = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('sco.selection = 0')
            ->nest()
            ->literal('paiementR1 = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')->unnest();
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
                'effectif' => new Expression('count(sco.eleveId)')
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
            ]);
        if ($communeId) {
            $select->join([
                'f' => $this->selectFiltreCommune($communeId)
            ], 'f.eleveId = sco.eleveId', []);
        }
        $select->where($where)->group([
            'millesime',
            'regimeId',
            'com.nom',
            'eta.nom'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie un tableau statistiques des élèves inscrits par millesime et classe.
     * ATTENTION ! L'élève est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit
     * l'élève en payant. Le R2 ne compte pas pour ça.
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbByMillesimeClasse($millesime = null, $communeId = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('sco.selection = 0')
            ->nest()
            ->literal('paiementR1 = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')->unnest();
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
                'effectif' => new Expression('count(sco.eleveId)')
            ])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'sco.classeId = cla.classeId', [
            'classe' => 'nom'
        ]);
        if ($communeId) {
            $select->join([
                'f' => $this->selectFiltreCommune($communeId)
            ], 'f.eleveId = sco.eleveId', []);
        }
        $select->where($where)->group([
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
    public function getNbMoins1KmByMillesime($millesime = null, $communeId = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('sco.selection = 0')
            ->lessThan('distanceR1', 1)
            ->lessThan('distanceR2', 1);
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
                'effectif' => new Expression('count(sco.eleveId)')
            ]);
        if ($communeId) {
            $select->join([
                'f' => $this->selectFiltreCommune($communeId)
            ], 'f.eleveId = sco.eleveId', []);
        }
        $select->where($where)->group([
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
    public function getNbDe1A3KmByMillesime($millesime = null, $communeId = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('sco.selection = 0')
            ->lessThan('distanceR1', 3)
            ->lessThan('distanceR2', 3)
            ->nest()
            ->greaterThanOrEqualTo('distanceR1', 1)->or->greaterThanOrEqualTo(
            'distanceR2', 1)->unnest();
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
                'effectif' => new Expression('count(sco.eleveId)')
            ]);
        if ($communeId) {
            $select->join([
                'f' => $this->selectFiltreCommune($communeId)
            ], 'f.eleveId = sco.eleveId', []);
        }
        $select->where($where)->group([
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
    public function getNb3kmEtPlusByMillesime($millesime = null, $communeId = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('sco.selection = 0')
            ->nest()
            ->greaterThanOrEqualTo('distanceR1', 3)->or->greaterThanOrEqualTo(
            'distanceR2', 3)->unnest();
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
                'effectif' => new Expression('count(sco.eleveId)')
            ]);
        if ($communeId) {
            $select->join([
                'f' => $this->selectFiltreCommune($communeId)
            ], 'f.eleveId = sco.eleveId', []);
        }
        $select->where($where)->group([
            'millesime',
            'regimeId'
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    private function selectFiltreCommune($communeId)
    {
        return $this->sql->select()
            ->columns([
            'eleveId'
        ])
            ->from([
            'e' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->join([
            'r' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'r.responsableId = e.responsable1Id OR r.responsableId=e.responsable2Id')
            ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
            ->where((new Where())->equalTo('communeId', $communeId));
    }
}