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
 * @date 27 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Db\Sql\Predicate\Not;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;
use Zend\Stdlib\ArrayObject;

class Statistiques extends AbstractQuery
{

    /**
     *
     * @var bool
     */
    private $sansimpayes = false;

    protected function init()
    {
    }

    /**
     *
     * @param bool $sansimpayes
     * @return self
     */
    public function setSansImpayes(bool $sansimpayes): self
    {
        $this->sansimpayes = $sansimpayes;
        return $this;
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés par millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0 /** Renvoie le tableau statistiques des élèves enregistrés à
     *            une distance de 1 km à moins de 3km par millesime
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @param string $nature
     *            prend pour valeur 'commune', 'etablissement', 'transporteur'. Les autres
     *            valeurs sont assimilées à 'secretariat'
     * @param string $id
     *            idenntifiant correspondant à la nature indiquée
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbEnregistresByMillesime(int $millesime = null, string $nature = '',
        $id = null)
    {
        $resultset = $this->renderResult(
            $this->selectNbEnregistresByMillesime($millesime, $nature, $id));
        if ($resultset->count()) {
            return iterator_to_array($resultset);
        } else {
            return [
                new ArrayObject([
                    'regimeId' => 0,
                    'effectif' => 0
                ]),
                new ArrayObject([
                    'regimeId' => 1,
                    'effectif' => 0
                ])
            ];
        }
    }

    public function selectNbEnregistresByMillesime(int $millesime = null,
        string $nature = '', $id = null)
    {
        $where = new Where();
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
        if ($nature) {
            switch ($nature) {
                case 'commune':
                    $select->join([
                        'f' => $this->selectFiltreCommune($id)
                    ], 'f.eleveId = sco.eleveId', []);
                    break;
                case 'etablissement':
                    $where->equalTo('sco.etablissementId', $id);
                    break;
                case 'transporteur':
                    $select->join(
                        [
                            'f' => $this->selectFiltreTransporteur($id, $millesime)
                        ], 'f.eleveId = sco.eleveId', []);
                    break;
                default: // secretariat (pas de filtre)
                    break;
            }
        }
        $select->where($where)->group([
            'millesime',
            'regimeId'
        ]);
        return $select;
    }

    /**
     * Renvoie un tableau statistiques des élèves inscrits par millesime.
     * ATTENTION !
     * L'élève est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit l'élève en
     * payant. Le R2 ne compte pas pour ça.
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @param string $nature
     *            prend pour valeur 'commune', 'etablissement', 'transporteur'. Les autres
     *            valeurs sont assimilées à 'secretariat'
     * @param string $id
     *            idenntifiant correspondant à la nature indiquée
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbInscritsByMillesime(int $millesime = null, string $nature = '',
        $id = null)
    {
        $resultset = $this->renderResult(
            $this->selectNbInscritsByMillesime($millesime, $nature, $id));
        if ($resultset->count()) {
            return iterator_to_array($resultset);
        } else {
            return new ArrayObject([
                'effectif' => 0
            ]);
        }
    }

    protected function selectNbInscritsByMillesime($millesime, $nature, $id)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiementR1 = 1')->or->literal('gratuit = 1')->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns([
            'millesime',
            'effectif' => new Expression('count(sco.eleveId)')
        ]);
        if ($nature) {
            switch ($nature) {
                case 'commune':
                    $select->join([
                        'f' => $this->selectFiltreCommune($id)
                    ], 'f.eleveId = sco.eleveId', []);
                    break;
                case 'etablissement':
                    $where->equalTo('sco.etablissementId', $id);
                    break;
                case 'transporteur':
                    $select->join(
                        [
                            'f' => $this->selectFiltreTransporteur($id, $millesime)
                        ], 'f.eleveId = sco.eleveId', []);
                    break;
                default: // secretariat (pas de filtre)
                    break;
            }
        }
        $select->where($where)->group([
            'millesime'
        ]);
        return $select;
    }

    /**
     * Renvoie un tableau statistiques des élèves préinscrits par millesime.
     * ATTENTION !
     * L'élève est inscrit si paiementR1 == 0 car c'est le R1 qui inscrit l'élève en
     * payant. Le R2 ne compte pas pour ça.
     *
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @param string $nature
     *            prend pour valeur 'commune', 'etablissement', 'transporteur'. Les autres
     *            valeurs sont assimilées à 'secretariat'
     * @param string $id
     *            idenntifiant correspondant à la nature indiquée
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbPreinscritsByMillesime(int $millesime = null, string $nature = '',
        $id = null)
    {
        $resultset = $this->renderResult(
            $this->selectNbPreinscritsByMillesime($millesime, $nature, $id));
        if ($resultset->count()) {
            return iterator_to_array($resultset);
        } else {
            return new ArrayObject([
                'effectif' => 0
            ]);
        }
    }

    protected function selectNbPreinscritsByMillesime($millesime, $nature, $id)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('paiementR1 = 0')
            ->literal('gratuit != 1');
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns([
            'millesime',
            'effectif' => new Expression('count(sco.eleveId)')
        ]);
        if ($nature) {
            switch ($nature) {
                case 'commune':
                    $select->join([
                        'f' => $this->selectFiltreCommune($id)
                    ], 'f.eleveId = sco.eleveId', []);
                    break;
                case 'etablissement':
                    $where->equalTo('sco.etablissementId', $id);
                    break;
                case 'transporteur':
                    $select->join(
                        [
                            'f' => $this->selectFiltreTransporteur($id, $millesime)
                        ], 'f.eleveId = sco.eleveId', []);
                    break;
                default: // secretariat (pas de filtre)
                    break;
            }
        }
        $select->where($where)->group([
            'millesime'
        ]);
        return $select;
    }

    /**
     * Renvoie un tableau statistiques des élèves en famille d'accueil par millesime
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @param string $nature
     *            prend pour valeur 'commune', 'etablissement', 'transporteur'. Les autres
     *            valeurs sont assimilées à 'secretariat'
     * @param string $id
     *            idenntifiant correspondant à la nature indiquée
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbFamilleAccueilByMillesime(int $millesime = null,
        string $nature = '', $id = null)
    {
        $resultset = $this->renderResult(
            $this->selectNbFamilleAccueilByMillesime($millesime, $nature, $id));
        if ($resultset->count()) {
            return iterator_to_array($resultset);
        } else {
            return new ArrayObject([
                'effectif' => 0
            ]);
        }
    }

    protected function selectNbFamilleAccueilByMillesime($millesime, $nature, $id)
    {
        $where = new Where();
        $where->literal('inscrit = 1')->literal('fa = 1');
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
        if ($nature) {
            switch ($nature) {
                case 'commune':
                    $select->join([
                        'f' => $this->selectFiltreCommune($id)
                    ], 'f.eleveId = sco.eleveId', []);
                    break;
                case 'etablissement':
                    $where->equalTo('sco.etablissementId', $id);
                    break;
                case 'transporteur':
                    $select->join(
                        [
                            'f' => $this->selectFiltreTransporteur($id, $millesime)
                        ], 'f.eleveId = sco.eleveId', []);
                    break;
                default: // secretariat (pas de filtre)
                    break;
            }
        }
        $select->where($where)->group([
            'millesime',
            'regimeId'
        ]);
        return $select;
    }

    /**
     * Renvoie un tableau statistiques des élèves rayés par millesime.
     * ATTENTION ! L'élève
     * est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit l'élève en payant. Le R2
     * ne compte pas pour ça.
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @param string $nature
     *            prend pour valeur 'commune', 'etablissement', 'transporteur'. Les autres
     *            valeurs sont assimilées à 'secretariat'
     * @param string $id
     *            idenntifiant correspondant à la nature indiquée
     * @param string $inscrits
     *            si true alors on ne compte que les inscrits rayés, sinon on ne compte
     *            que les préinscrits
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbRayesByMillesime($millesime = null, string $nature = '',
        $id = null, $inscrits = true)
    {
        $resultset = $this->renderResult(
            $this->selectNbRayesByMillesime($millesime, $inscrits, $nature, $id));
        if ($resultset->count()) {
            return iterator_to_array($resultset);
        } else {
            return new ArrayObject([
                'effectif' => 0
            ]);
        }
    }

    protected function selectNbRayesByMillesime($millesime, $inscrits, $nature, $id)
    {
        $where = new Where();
        $where->literal('inscrit = 0');
        if ($inscrits) {
            $where->nest()->literal('paiementR1 = 1')->or->literal('gratuit = 1')->unnest();
        } else {
            $where1 = new Where();
            $where1->literal('paiementR1 = 1')->or->literal('gratuit = 1');
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
            ->columns([
            'millesime',
            'effectif' => new Expression('count(sco.eleveId)')
        ]);
        if ($nature) {
            switch ($nature) {
                case 'commune':
                    $select->join([
                        'f' => $this->selectFiltreCommune($id)
                    ], 'f.eleveId = sco.eleveId', []);
                    break;
                case 'etablissement':
                    $where->equalTo('sco.etablissementId', $id);
                    break;
                case 'transporteur':
                    $select->join(
                        [
                            'f' => $this->selectFiltreTransporteur($id, $millesime)
                        ], 'f.eleveId = sco.eleveId', []);
                    break;
                default: // secretariat (pas de filtre)
                    break;
            }
        }
        $select->where($where)->group([
            'millesime'
        ]);
        return $select;
    }

    /**
     * Renvoie un tableau statistiques des élèves en garde alternée par millesime.
     * ATTENTION ! L'élève est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit
     * l'élève en payant. Le R2 ne compte pas pour ça.
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @param string $nature
     *            prend pour valeur 'commune', 'etablissement', 'transporteur'. Les autres
     *            valeurs sont assimilées à 'secretariat'
     * @param string $id
     *            idenntifiant correspondant à la nature indiquée
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbGardeAlterneeByMillesime(int $millesime = null,
        string $nature = '', $id = null)
    {
        $resultset = $this->renderResult(
            $this->selectNbGardeAlterneeByMillesime($millesime, $nature, $id));
        if ($resultset->count()) {
            return iterator_to_array($resultset);
        } else {
            return new ArrayObject([
                'effectif' => 0
            ]);
        }
    }

    protected function selectNbGardeAlterneeByMillesime($millesime, $nature, $id = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiementR1 = 1')->or->literal('gratuit = 1')
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
        if ($nature) {
            switch ($nature) {
                case 'commune':
                    $select->join([
                        'f' => $this->selectFiltreCommune($id)
                    ], 'f.eleveId = sco.eleveId', []);
                    break;
                case 'etablissement':
                    $where->equalTo('sco.etablissementId', $id);
                    break;
                case 'transporteur':
                    $select->join(
                        [
                            'f' => $this->selectFiltreTransporteur($id, $millesime)
                        ], 'f.eleveId = sco.eleveId', []);
                    break;
                default: // secretariat (pas de filtre)
                    break;
            }
        }
        $select->where($where)->group([
            'millesime',
            'regimeId'
        ]);
        return $select;
    }

    /**
     * Renvoie un tableau statistiques des élèves inscrits par millesime et etablissement.
     * ATTENTION ! L'élève est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit
     * l'élève en payant. Le R2 ne compte pas pour ça.
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @param string $nature
     *            prend pour valeur 'commune', 'etablissement', 'transporteur'. Les autres
     *            valeurs sont assimilées à 'secretariat'
     * @param string $id
     *            idenntifiant correspondant à la nature indiquée
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbByMillesimeEtablissement(int $millesime = null,
        string $nature = '', $id = null)
    {
        $resultset = $this->renderResult(
            $this->selectNbByMillesimeEtablissement($millesime, $nature, $id));
        if ($resultset->count()) {
            return iterator_to_array($resultset);
        } else {
            return new ArrayObject([
                'effectif' => 0
            ]);
        }
    }

    protected function selectNbByMillesimeEtablissement($millesime, $nature, $id)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiementR1 = 1')->or->literal('gratuit = 1')->unnest();
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
        if ($nature) {
            switch ($nature) {
                case 'commune':
                    $select->join([
                        'f' => $this->selectFiltreCommune($id)
                    ], 'f.eleveId = sco.eleveId', []);
                    break;
                case 'etablissement':
                    $where->equalTo('sco.etablissementId', $id);
                    break;
                case 'transporteur':
                    $select->join(
                        [
                            'f' => $this->selectFiltreTransporteur($id, $millesime)
                        ], 'f.eleveId = sco.eleveId', []);
                    break;
                default: // secretariat (pas de filtre)
                    break;
            }
        }
        $select->where($where)->group([
            'millesime',
            'regimeId',
            'com.nom',
            'eta.nom'
        ]);
        return $select;
    }

    /**
     * Renvoie un tableau statistiques des élèves inscrits par millesime et classe.
     * ATTENTION ! L'élève est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit
     * l'élève en payant. Le R2 ne compte pas pour ça.
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @param string $nature
     *            prend pour valeur 'commune', 'etablissement', 'transporteur'. Les autres
     *            valeurs sont assimilées à 'secretariat'
     * @param string $id
     *            idenntifiant correspondant à la nature indiquée
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbByMillesimeClasse(int $millesime = null, string $nature = '',
        $id = null)
    {
        $resultset = $this->renderResult(
            $this->selectNbByMillesimeClasse($millesime, $nature, $id));
        if ($resultset->count()) {
            return iterator_to_array($resultset);
        } else {
            return new ArrayObject([
                'effectif' => 0
            ]);
        }
    }

    protected function selectNbByMillesimeClasse($millesime, $nature, $id)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiementR1 = 1')->or->literal('gratuit = 1')->unnest();
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
        if ($nature) {
            switch ($nature) {
                case 'commune':
                    $select->join([
                        'f' => $this->selectFiltreCommune($id)
                    ], 'f.eleveId = sco.eleveId', []);
                    break;
                case 'etablissement':
                    $where->equalTo('sco.etablissementId', $id);
                    break;
                case 'transporteur':
                    $select->join(
                        [
                            'f' => $this->selectFiltreTransporteur($id, $millesime)
                        ], 'f.eleveId = sco.eleveId', []);
                    break;
                default: // secretariat (pas de filtre)
                    break;
            }
        }
        $select->where($where)->group([
            'millesime',
            'regimeId',
            'cla.nom'
        ]);
        return $select;
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés à une distance inférieure à
     * 1 km par millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @param string $nature
     *            prend pour valeur 'commune', 'etablissement', 'transporteur'. Les autres
     *            valeurs sont assimilées à 'secretariat'
     * @param string $id
     *            idenntifiant correspondant à la nature indiquée
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbMoins1KmByMillesime($millesime = null, string $nature = '',
        $id = null)
    {
        $resultset = $this->renderResult(
            $this->selectNbMoins1KmByMillesime($millesime, $nature, $id));
        if ($resultset->count()) {
            return iterator_to_array($resultset);
        } else {
            return new ArrayObject([
                'effectif' => 0
            ]);
        }
    }

    protected function selectNbMoins1KmByMillesime($millesime, $nature, $id)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->lessThan('distanceR1', 1)
            ->lessThan('distanceR2', 1)
            ->nest()
            ->greaterThan('distanceR1', 0)->or->greaterThan('distanceR2', 0)->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns([
            'millesime',
            'effectif' => new Expression('count(sco.eleveId)')
        ]);
        if ($nature) {
            switch ($nature) {
                case 'commune':
                    $select->join([
                        'f' => $this->selectFiltreCommune($id)
                    ], 'f.eleveId = sco.eleveId', []);
                    break;
                case 'etablissement':
                    $where->equalTo('sco.etablissementId', $id);
                    break;
                case 'transporteur':
                    $select->join(
                        [
                            'f' => $this->selectFiltreTransporteur($id, $millesime)
                        ], 'f.eleveId = sco.eleveId', []);
                    break;
                default: // secretariat (pas de filtre)
                    break;
            }
        }
        $select->where($where)->group([
            'millesime'
        ]);
        return $select;
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés à une distance de 1 km à
     * moins de 3km par millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @param string $nature
     *            prend pour valeur 'commune', 'etablissement', 'transporteur'. Les autres
     *            valeurs sont assimilées à 'secretariat'
     * @param string $id
     *            idenntifiant correspondant à la nature indiquée
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbDe1A3KmByMillesime(int $millesime = null, string $nature = '',
        $id = null)
    {
        $resultset = $this->renderResult(
            $this->selectNbDe1A3KmByMillesime($millesime, $nature, $id));
        if ($resultset->count()) {
            return iterator_to_array($resultset);
        } else {
            return new ArrayObject([
                'effectif' => 0
            ]);
        }
    }

    protected function selectNbDe1A3KmByMillesime($millesime, $nature, $id)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
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
            ->columns([
            'millesime',
            'effectif' => new Expression('count(sco.eleveId)')
        ]);
        if ($nature) {
            switch ($nature) {
                case 'commune':
                    $select->join([
                        'f' => $this->selectFiltreCommune($id)
                    ], 'f.eleveId = sco.eleveId', []);
                    break;
                case 'etablissement':
                    $where->equalTo('sco.etablissementId', $id);
                    break;
                case 'transporteur':
                    $select->join(
                        [
                            'f' => $this->selectFiltreTransporteur($id, $millesime)
                        ], 'f.eleveId = sco.eleveId', []);
                    break;
                default: // secretariat (pas de filtre)
                    break;
            }
        }
        $select->where($where)->group([
            'millesime'
        ]);
        return $select;
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés à une distance d'au moins 3
     * km par millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @param string $nature
     *            prend pour valeur 'commune', 'etablissement', 'transporteur'. Les autres
     *            valeurs sont assimilées à 'secretariat'
     * @param string $id
     *            idenntifiant correspondant à la nature indiquée
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNb3kmEtPlusByMillesime(int $millesime = null, string $nature = '',
        $id = null)
    {
        $resultset = $this->renderResult(
            $this->selectNb3kmEtPlusByMillesime($millesime, $nature, $id));
        if ($resultset->count()) {
            return iterator_to_array($resultset);
        } else {
            return new ArrayObject([
                'effectif' => 0
            ]);
        }
    }

    protected function selectNb3kmEtPlusByMillesime($millesime, $nature, $id)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->nest()
            ->greaterThanOrEqualTo('distanceR1', 3)
            ->notEqualTo('distanceR1', 99)
            ->unnest()->or->nest()
            ->greaterThanOrEqualTo('distanceR2', 3)
            ->notEqualTo('distanceR2', 99)
            ->unnest()
            ->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns([
            'millesime',
            'effectif' => new Expression('count(sco.eleveId)')
        ]);
        if ($nature) {
            switch ($nature) {
                case 'commune':
                    $select->join([
                        'f' => $this->selectFiltreCommune($id)
                    ], 'f.eleveId = sco.eleveId', []);
                    break;
                case 'etablissement':
                    $where->equalTo('sco.etablissementId', $id);
                    break;
                case 'transporteur':
                    $select->join(
                        [
                            'f' => $this->selectFiltreTransporteur($id, $millesime)
                        ], 'f.eleveId = sco.eleveId', []);
                    break;
                default: // secretariat (pas de filtre)
                    break;
            }
        }
        $select->where($where)->group([
            'millesime'
        ]);
        return $select;
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés dont on n'a pas pu calculer
     * la distance du domicile à l'établissement
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément
     *            d'index 0
     * @param string $nature
     *            prend pour valeur 'commune', 'etablissement', 'transporteur'. Les autres
     *            valeurs sont assimilées à 'secretariat'
     * @param string $id
     *            idenntifiant correspondant à la nature indiquée
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des
     *         tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbDistanceInconnue(int $millesime = null, string $nature = '',
        $id = null)
    {
        $resultset = $this->renderResult(
            $this->selectNbDistanceInconnue($millesime, $nature, $id));
        if ($resultset->count()) {
            return iterator_to_array($resultset);
        } else {
            return new ArrayObject([
                'effectif' => 0
            ]);
        }
    }

    protected function selectNbDistanceInconnue($millesime, $nature, $id)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('distanceR1 = 0')->or->literal('distanceR1 = 99')
            ->unnest()
            ->nest()
            ->literal('distanceR2 = 0')->or->literal('distanceR2 = 99')->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns([
            'millesime',
            'effectif' => new Expression('count(sco.eleveId)')
        ]);
        if ($nature) {
            switch ($nature) {
                case 'commune':
                    $select->join([
                        'f' => $this->selectFiltreCommune($id)
                    ], 'f.eleveId = sco.eleveId', []);
                    break;
                case 'etablissement':
                    $where->equalTo('sco.etablissementId', $id);
                    break;
                case 'transporteur':
                    $select->join(
                        [
                            'f' => $this->selectFiltreTransporteur($id, $millesime)
                        ], 'f.eleveId = sco.eleveId', []);
                    break;
                default: // secretariat (pas de filtre)
                    break;
            }
        }
        $select->where($where)->group([
            'millesime'
        ]);
        return $select;
    }

    private function selectFiltreCommune(string $communeId)
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

    private function selectFiltreTransporteur(int $transporteurId, int $millesime)
    {
        return $this->sql->select()
            ->columns([
            'eleveId'
        ])
            ->from([
            'e' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->join([
            'a' => $this->db_manager->getCanonicName('affectations', 'table')
        ], 'e.eleveId = a.eleveId', [])
            ->join([
            's' => $this->db_manager->getCanonicName('services', 'table')
        ],
            implode(' AND ',
                [
                    's.millesime = a.millesime',
                    's.ligneId = a.ligne1Id',
                    's.sens = a.sensligne1',
                    's.moment = a.moment',
                    's.ordre = a.ordreligne1'
                ]), [])
            ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
            ->where(
            (new Where())->equalTo('a.millesime', $millesime)
                ->equalTo('s.transporteurId', $transporteurId));
    }
}