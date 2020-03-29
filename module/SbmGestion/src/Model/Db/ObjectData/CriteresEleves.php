<?php
/**
 * Extension de la classe SbmCommun\Model\Db\ObjectData pour surcharger le méthode getWhere()
 *
 * Les methodes clause...() ont toutes le même modèle à 2 paramètres Where $where et bool $pdf.
 * Elles reçoivent le $where en construction et le renvoie après modification.
 * Le paramètre $pdf doit être à TRUE lorsqu'on appelle la méthode depuis getWhereSql() et
 * il est omis lorsque l'appel se fait depuis getWhere(). En effet, la requête du document
 * PDF est de la forme SELECT * FROM (SELECT ... FROM ... JOIN ...) WHERE $where. Aussi les
 * noms des champs ne sont pas préfixés par les noms de tables. C'est tout à fait le contraire
 * dans la requête donnée au paginateur pour l'écran.
 *
 * @project sbm
 * @package SbmGestion/Model/Db/ObjectData
 * @filesource CriteresEleves.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Db\ObjectData;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\ObjectData\Criteres as SbmCommunCriteres;
use Zend\Db\Sql\Where;

class CriteresEleves extends SbmCommunCriteres
{

    /**
     * On filtre sur le millesime en cours. La propriété `data` est un tableau de la forme
     * : array (size=10) 'numero' => string '' (length=0) 'nomSA' => string '' (length=0)
     * 'responsableSA' => string '' (length=0) 'etablissementId' => string '' (length=0)
     * 'classeId' => string '' (length=0) 'etat' => string '' (length=0) 'demande' =>
     * string '' (length=0) 'decision' => string '' (length=0) 'derogation' => string '0'
     * (length=1) 'selection' => string '0' (length=1) (non-PHPdoc) $strict et $alias sont
     * inutiles et ne sont gardés que pour la compatibilité stricte des appels
     *
     * @see \SbmCommun\Model\Db\ObjectData\Criteres::getWhere()
     */
    public function getWhere($strict = [], $alias = [])
    {
        $where = new Where();
        $where->equalTo('sco.millesime', Session::get('millesime'))->literal(
            'sco.selection = 0');
        if (! empty($this->data['numero'])) {
            $where = $this->clauseNumero($where);
        }
        if (! empty($this->data['nomSA'])) {
            $where = $this->clauseNomSA($where);
        }
        if (! empty($this->data['prenomSA'])) {
            $where = $this->clausePrenomSA($where);
        }
        if (! empty($this->data['responsable'])) {
            $where = $this->clauseResponsable($where);
        }
        if (! empty($this->data['etablissementId'])) {
            $where = $this->clauseEtablissement($where);
        }
        if (! empty($this->data['classeId'])) {
            $where = $this->clauseClasse($where);
        }
        if (! empty($this->data['etat'])) {
            switch ($this->data['etat']) {
                case 1:
                    // inscrits
                    $where = $this->clauseInscrits($where);
                    break;
                case 2:
                    // pré-inscrits
                    $where = $this->clausePreinscrits($where);
                    break;
                case 3:
                    // rayé
                    $where = $this->clauseRayes($where);
                    break;
                case 4:
                    // non rayé
                    $where = $this->clauseNonRayes($where);
                    break;
                case 5:
                    // avec photo
                    $where = $this->clauseAvecPhoto($where);
                    break;
                case 6:
                    // sans photo
                    $where = $this->clauseSansPhoto($where);
                    break;
            }
        }
        if (! empty($this->data['demande'])) {
            $where->literal('inscrit = 1');
            switch ($this->data['demande']) {
                case 1:
                    // non traitée:
                    $where = $this->clauseDemandesNonTraitees($where);
                    break;
                case 2:
                    // partiellement traitée : l'une des demandes vaut 1 et l'autre vaut 2
                    $where = $this->clauseDemandesPartiellementTraitees($where);
                    break;
                case 3:
                    // traitée : on a répondu à la demandeR1 et la demandeR2 n'est pas en
                    // attente
                    $where = $this->clauseDemandesTraitees($where);
                    break;
            }
        }
        if (! empty($this->data['decision'])) {
            $where->literal('inscrit = 1');
            switch ($this->data['decision']) {
                case 1:
                    // accord total
                    $where = $this->clauseAccordTotal($where);
                    break;
                case 2:
                    // accord partiel
                    $where = $this->clauseAccordPartiel($where);
                    break;
                case 3:
                    // subvention
                    $where = $this->clauseSubvention($where);
                    break;
                case 4:
                    // refus total
                    $where = $this->clauseRefusTotal($where);
                    break;
            }
        }
        if (! empty($this->data['incomplet'])) {
            $where->literal('inscrit = 1');
            switch ($this->data['incomplet']) {
                case 1:
                    // Distance à calculer
                    $where = $this->clauseDistancesACalculer($where);
                    break;
                case 2:
                    // Sans affectation
                    $where = $this->clauseSansAffectation($where);
                    break;
                case 3:
                    // Sans photo
                    $where = $this->clauseSansPhoto($where);
                    break;
            }
        }
        if (! empty($this->data['particularite'])) {
            $where->literal('inscrit = 1');
            switch ($this->data['particularite']) {
                case 1:
                    // Garde alternée
                    $where = $this->clauseGardeAlternee($where);
                    break;
                case 2:
                    // Famille d'accueil
                    $where = $this->clauseFamilleDAccueil($where);
                    break;
                case 3:
                    // Dérogation accordée
                    $where = $this->clauseDerogation($where);
                    break;
                case 4:
                    // Non ayants droit acceptés
                    $where = $this->clauseNonAyantDroit($where);
                    break;
            }
        }
        if (! empty($this->data['selection'])) {
            $where = $this->clauseSelection($where);
        }
        return $where;
    }

    public function getWherePdf($descripteur = null)
    {
        $pageheader_string = [];
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'));
        if (! empty($this->data['numero'])) {
            $where = $this->clauseNumero($where, true);
            $pageheader_string[] = sprintf('élève dont la carte porte le numéro %d',
                $this->data['numero']);
        }
        if (! empty($this->data['nomSA'])) {
            $where = $this->clauseNomSA($where, true);
            $pageheader_string[] = sprintf('élèves dont le nom commence par %s',
                $this->data['nomSA']);
        }
        if (! empty($this->data['prenomSA'])) {
            $where = $this->clausePrenomSA($where, true);
            $pageheader_string[] = sprintf('élèves dont le prénom commence par %s',
                $this->data['prenomSA']);
        }
        if (! empty($this->data['responsable'])) {
            $where = $this->clauseResponsable($where, true);
            $pageheader_string[] = sprintf(
                'élèves dont le nom du responsable commence par %s',
                $this->data['responsable']);
        }
        if (! empty($this->data['etablissementId'])) {
            $where = $this->clauseEtablissement($where, true);
            $pageheader_string[] = sprintf('élèves de l\'établissement %s',
                $this->data['etablissementId']);
        }
        if (! empty($this->data['classeId'])) {
            $where = $this->clauseClasse($where, true);
            $pageheader_string[] = sprintf('élèves de la classe %s',
                $this->data['classeId']);
        }
        if (! empty($this->data['etat'])) {
            switch ($this->data['etat']) {
                case 1:
                    // inscrit
                    $where = $this->clauseInscrits($where, true);
                    $pageheader_string[] = 'élèves inscrits';
                    break;
                case 2:
                    // pré-inscrit
                    $where = $this->clausePreinscrits($where, true);
                    $pageheader_string[] = 'élèves préinscrits';
                    break;
                case 3:
                    // rayé
                    $where = $this->clauseRayes($where, true);
                    $pageheader_string[] = 'élèves rayés';
                    break;
                case 4:
                    // non rayé
                    $where = $this->clauseNonRayes($where, true);
                    $pageheader_string[] = 'élèves non rayés';
                    break;
                case 5:
                    // avec photo
                    $where = $this->clauseAvecPhoto($where, true);
                    $pageheader_string[] = 'élèves avec photo';
                    break;
                case 6:
                    // sans photo
                    $where = $this->clauseSansPhoto($where, true);
                    $pageheader_string[] = 'élèves sans photo';
                    break;
            }
        }
        if (! empty($this->data['demande'])) {
            $where->literal('inscrit = 1');
            switch ($this->data['demande']) {
                case 1:
                    // demandes non traitée:
                    $where = $this->clauseDemandesNonTraitees($where, true);
                    $pageheader_string[] = 'élèves dont la demande n\'est pas traitée';
                    break;
                case 2:
                    // partiellement traitée : l'une des demandes vaut 1 et l'autre vaut 2
                    $where = $this->clauseDemandesPartiellementTraitees($where, true);
                    $pageheader_string[] = 'élèves dont les demandes sont partiellement traitées';
                    break;
                case 3:
                    // traiée : on a répondu à la demandeR1 et la demandeR2 n'est pas en
                    // attente
                    $where = $this->clauseDemandesTraitees($where, true);
                    $pageheader_string[] = 'élèves dont les demandes sont traitées';
                    break;
            }
        }
        if (! empty($this->data['decision'])) {
            $where->literal('inscrit = 1');
            switch ($this->data['decision']) {
                case 1:
                    // accord total
                    // 3 cas : ((demandeR1 = 0 AND demandeR2 = 2 AND accordR2 = 1) OR
                    // (demandeR1 =
                    // 2 AND accordR1 = 1 AND demandeR2 = 0) OR (demandeR1 = 2 AND
                    // accordR1 = 1 AND
                    // demandeR2 = 2 AND accordR2 = 1)
                    $where = $this->clauseAccordTotal($where, true);
                    $pageheader_string[] = 'accord total';
                    break;
                case 2:
                    // accord partiel
                    $where = $this->clauseAccordPartiel($where, true);
                    $pageheader_string[] = 'accord partiel';
                    break;
                case 3:
                    // subvention
                    $where = $this->clauseSubvention($where, true);
                    $pageheader_string[] = 'élèves pour lesquels une subvention est accordée';
                    break;
                case 4:
                    // refus total
                    $where = $this->clauseRefusTotal($where, true);
                    $pageheader_string[] = 'élèves refusés';
                    break;
            }
        }
        if (! empty($this->data['incomplet'])) {
            $where->literal('inscrit = 1');
            switch ($this->data['incomplet']) {
                case 1:
                    // distance à calculer
                    $where = $this->clauseDistancesACalculer($where, true);
                    $pageheader_string[] = 'distance à calculer';
                    break;
                case 2:
                    // sans affectation
                    $where = $this->clauseSansAffectation($where, true);
                    $pageheader_string[] = 'élèves sans affectation';
                    break;
                case 3:
                    // sans photo
                    $where = $this->clauseSansPhoto($where, true);
                    $pageheader_string[] = 'élèves sans photo';
                    break;
            }
        }
        if (! empty($this->data['particularite'])) {
            $where->literal('inscrit = 1');
            switch ($this->data['particularite']) {
                case 1:
                    // garde alternée
                    $where = $this->clauseGardeAlternee($where, true);
                    $pageheader_string[] = 'élèves en garde alternée';
                    break;
                case 2:
                    // famille d'accueil
                    $where = $this->clauseFamilleDAccueil($where, true);
                    $pageheader_string[] = 'élèves en famille d\'accueil';
                    break;
                case 3:
                    // dérogation
                    $where = $this->clauseDerogation($where, true);
                    $pageheader_string[] = 'élèves ayant une dérogation';
                    break;
            }
        }
        if (! empty($this->data['selection'])) {
            $where = $this->clauseSelection($where, true);
            $pageheader_string[] = 'élèves sélectionnés';
        }
        if (! empty($pageheader_string)) {
            $this->pageheader_params['pageheader_string'] = implode(' ; ',
                $pageheader_string);
        }

        return $where;
    }

    /**
     * Transforme l'objet en tableau de critéres en modifiant certaines propriétés
     * Nécessaire pour les étiquettes et les cartes à sélectionner à partir de la liste
     * des élèves $strict et $alias sont inutiles et ne sont gardés que pour la
     * compatibilité stricte des appels
     *
     * @param array $criteres
     */
    public function getCriteres($strict = [], $alias = [])
    {
        $pageheader_string = [];
        $where = [
            'expression' => [],
            'criteres' => [],
            'strict' => [
                'empty' => [],
                'not empty' => []
            ]
        ];
        // le filtre sur le millesime doit être dans la requête sous la forme :
        // millesime=%millesime%
        if (! empty($this->data['numero'])) {
            $where = $this->clauseNumero($where, true);
            $pageheader_string[] = sprintf('élève dont la carte porte le numéro %d',
                $this->data['numero']);
        }
        if (! empty($this->data['nomSA'])) {
            $where = $this->clauseNomSA($where, true);
            $pageheader_string[] = sprintf('élèves dont le nom commence par %s',
                $this->data['nomSA']);
        }
        if (! empty($this->data['prenomSA'])) {
            $where = $this->clausePrenomSA($where, true);
            $pageheader_string[] = sprintf('élèves dont le prénom commence par %s',
                $this->data['prenomSA']);
        }
        if (! empty($this->data['responsable'])) {
            $where = $this->clauseResponsable($where, true);
            $pageheader_string[] = sprintf(
                'élèves dont le nom du responsable commence par %s',
                $this->data['responsable']);
        }
        if (! empty($this->data['etablissementId'])) {
            $where = $this->clauseEtablissement($where, true);
            $pageheader_string[] = sprintf('élèves de l\'établissement %s',
                $this->data['etablissementId']);
        }
        if (! empty($this->data['classeId'])) {
            $where = $this->clauseClasse($where, true);
            $pageheader_string[] = sprintf('élèves de la classe %s',
                $this->data['classeId']);
        }
        if (! empty($this->data['etat'])) {
            switch ($this->data['etat']) {
                case 1:
                    // inscrits
                    $where = $this->clauseInscrits($where, true);
                    $pageheader_string[] = 'élèves inscrits';
                    break;
                case 2:
                    // pré inscrits
                    $where = $this->clausePreinscrits($where, true);
                    $pageheader_string[] = 'élèves préinscrits';
                    break;
                case 3:
                    // rayés
                    $where = $this->clauseRayes($where, true);
                    $pageheader_string[] = 'élèves rayés';
                    break;
                case 4:
                    // non rayé
                    $where = $this->clauseNonRayes($where, true);
                    $pageheader_string[] = 'élèves non rayés';
                    break;
                case 5:
                    // avec photo
                    $where = $this->clauseAvecPhoto($where, true);
                    $pageheader_string[] = 'élèves avec photo';
                    break;
                case 6:
                    // sans photo
                    $where = $this->clauseSansPhoto($where, true);
                    $pageheader_string[] = 'élèves sans photo';
                    break;
            }
        }
        if (! empty($this->data['demande'])) {
            switch ($this->data['demande']) {
                case 1:
                    // non traitée:
                    $where = $this->clauseDemandesNonTraitees($where, true);
                    $pageheader_string[] = 'élèves dont la demande n\'est pas traitée';
                    break;
                case 2:
                    // partiellement traitée : l'une des demandes vaut 1 et l'autre vaut 2
                    $where = $this->clauseDemandesPartiellementTraitees($where, true);
                    $pageheader_string[] = 'élèves dont les demandes sont partiellement traitées';
                    break;
                case 3:
                    // traiée : on a répondu à la demandeR1 et la demandeR2 n'est pas en
                    // attente
                    $where = $this->clauseDemandesTraitees($where, true);
                    $pageheader_string[] = 'élèves dont les demandes sont traitées';
                    break;
            }
        }
        if (! empty($this->data['decision'])) {
            switch ($this->data['decision']) {
                case 1:
                    // accord total
                    // 3 cas : ((demandeR1 = 0 AND demandeR2 = 2 AND accordR2 = 1) OR
                    // (demandeR1 =
                    // 2 AND accordR1 = 1 AND demandeR2 = 0) OR (demandeR1 = 2 AND
                    // accordR1 = 1 AND
                    // demandeR2 = 2 AND accordR2 = 1)
                    $where = $this->clauseAccordTotal($where, true);
                    $pageheader_string[] = 'accord total';
                    break;
                case 2:
                    // accord partiel
                    $where = $this->clauseAccordPartiel($where, true);
                    $pageheader_string[] = 'accord partiel';
                    break;
                case 3:
                    // subvention
                    $where = $this->clauseSubvention($where, true);
                    $pageheader_string[] = 'élèves pour lesquels une subvention est accordée';
                    break;
                case 4:
                    // refus total
                    $where = $this->clauseRefusTotal($where, true);
                    $pageheader_string[] = 'élèves refusés';
                    break;
            }
        }
        if (! empty($this->data['incomplet'])) {
            switch ($this->data['incomplet']) {
                case 1:
                    // distance à calculer
                    $where = $this->clauseDistancesACalculer($where, true);
                    $pageheader_string[] = 'distance à calculer';
                    break;
                case 2:
                    // sans affectation
                    $where = $this->clauseSansAffectation($where, true);
                    $pageheader_string[] = 'élèves sans affectation';
                    break;
                case 3:
                    // sans photo
                    $where = $this->clauseSansPhoto($where, true);
                    $pageheader_string[] = 'élèves sans photo';
                    break;
            }
        }
        if (! empty($this->data['particularite'])) {
            switch ($this->data['particularite']) {
                case 1:
                    // garde alternée
                    $where = $this->clauseGardeAlternee($where, true);
                    $pageheader_string[] = 'élèves en garde alternée';
                    break;
                case 2:
                    // famille d'accueil
                    $where = $this->clauseFamilleDAccueil($where, true);
                    $pageheader_string[] = 'élèves en famille d\'accueil';
                    break;
                case 3:
                    // dérogation
                    $where = $this->clauseDerogation($where, true);
                    $pageheader_string[] = 'élèves ayant une dérogation';
                    break;
                case 4:
                    // non ayants droit acceptés
                    $where = $this->clauseNonAyantDroit($where, true);
                    $pageheader_string[] = 'élèves non ayants droit acceptés';
                    break;
            }
        }
        if (! empty($this->data['selection'])) {
            $where = $this->clauseSelection($where, true);
            $pageheader_string[] = 'élèves sélectionnés';
        }
        if (! empty($pageheader_string)) {
            $this->pageheader_params['pageheader_string'] = implode(' ; ',
                $pageheader_string);
        }

        return $where;
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseNumero($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->equalTo('numero', $this->data['numero']);
        } else {
            $where['strict']['not empty'][] = 'numero';
            $where['criteres']['numero'] = $this->data['numero'];
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseNomSA($where, $pdf = false)
    {
        if ($where instanceof Where) {
            if ($pdf) {
                return $where->like('nomSA', $this->data['nomSA'] . '%');
            } else {
                return $where->like('ele.nomSA', $this->data['nomSA'] . '%');
            }
        } else {
            $where['criteres']['nomSA'] = $this->data['nomSA'] . '%';
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clausePrenomSA($where, $pdf = false)
    {
        if ($where instanceof Where) {
            if ($pdf) {
                return $where->like('prenomSA', $this->data['prenomSA'] . '%');
            } else {
                return $where->like('ele.prenomSA', $this->data['prenomSA'] . '%');
            }
        } else {
            $where['criteres']['prenomSA'] = $this->data['prenomSA'] . '%';
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseResponsable($where, $pdf = false)
    {
        if ($where instanceof Where) {
            if ($pdf) {
                return $where->nest()->like('responsable1NomSA',
                    $this->data['responsable'] . '%')->OR->like('responsable1Nom2SA',
                    $this->data['responsable'] . '%')->OR->like('responsable2NomSA',
                    $this->data['responsable'] . '%')->OR->like('responsable2Nom2SA',
                    $this->data['responsable'] . '%')->unnest();
            } else {
                return $where->nest()->like('r1.nomSA', $this->data['responsable'] . '%')->OR->like(
                    'r1.nom2SA', $this->data['responsable'] . '%')->OR->like('r2.nomSA',
                    $this->data['responsable'] . '%')->OR->like('r2.nom2SA',
                    $this->data['responsable'] . '%')->unnest();
            }
        } else {
            // $where['criteres']['responsable'] = true;
            $where['criteres']['responsable'] = [
                'operator' => 'OR',
                'parts' => [
                    [
                        'operator' => 'LIKE',
                        'parts' => [
                            'responsable1NomSA',
                            '"' . $this->data['responsable'] . '%"'
                        ]
                    ],
                    [
                        'operator' => 'LIKE',
                        'parts' => [
                            'responsable1Nom2SA',
                            '"' . $this->data['responsable'] . '%"'
                        ]
                    ],
                    [
                        'operator' => 'LIKE',
                        'parts' => [
                            'responsable2NomSA',
                            '"' . $this->data['responsable'] . '%"'
                        ]
                    ],
                    [
                        'operator' => 'LIKE',
                        'parts' => [
                            'responsable2Nom2SA',
                            '"' . $this->data['responsable'] . '%"'
                        ]
                    ]
                ]
            ];
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseEtablissement($where, $pdf = false)
    {
        if ($where instanceof Where) {
            if ($pdf) {
                return $where->equalTo('etablissementId', $this->data['etablissementId']);
            } else {
                return $where->equalTo('sco.etablissementId',
                    $this->data['etablissementId']);
            }
        } else {
            $where['strict']['not empty'][] = 'etablissementId';
            $where['criteres']['etablissementId'] = $this->data['etablissementId'];
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseClasse($where, $pdf = false)
    {
        if ($where instanceof Where) {
            if ($pdf) {
                return $where->equalTo('classeId', $this->data['classeId']);
            } else {
                return $where->equalTo('sco.classeId', $this->data['classeId']);
            }
        } else {
            $where['strict']['not empty'][] = 'classeId';
            $where['criteres']['classeId'] = $this->data['classeId'];
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseInscrits($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->literal('inscrit = 1')
                ->nest()
                ->literal('paiementR1 = 1')->OR->literal(
                'gratuit > 0')->unnest();
        } else {
            $where['criteres']['etat'] = 1;
            $where['expression']['etat'] = 'inscrit = 1 AND (paiementR1 = 1 OR gratuit > 0)';
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clausePreinscrits($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->literal('inscrit = 1')
                ->literal('paiementR1 = 0')
                ->literal('gratuit = 0');
        } else {
            $where['criteres']['etat'] = 2;
            $where['expression']['etat'] = 'inscrit = 1 AND paiementR1 = 0 AND gratuit = 0';
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseRayes($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->literal('inscrit = 0');
        } else {
            $where['criteres']['etat'] = 3;
            $where['expression']['etat'] = 'inscrit = 0';
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseNonRayes($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->literal('inscrit = 1');
        } else {
            $where['criteres']['etat'] = 4;
            $where['expression']['etat'] = 'inscrit = 1';
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseAvecPhoto($where, $pdf = false)
    {
        if ($where instanceof Where) {
            if ($pdf) {
                return $where->literal('sansphoto = FALSE');
            } else {
                return $where->isNotNull('photos.eleveId');
            }
        } else {
            $where['criteres']['etat'] = 5;
            $where['expression']['etat'] = 'sansphoto = FALSE';
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseSansPhoto($where, $pdf = false)
    {
        if ($where instanceof Where) {
            if ($pdf) {
                return $where->literal('sansphoto = TRUE');
            } else {
                return $where->isNull('photos.eleveId');
            }
        } else {
            $where['criteres']['etat'] = 6;
            $where['expression']['etat'] = 'sansphoto = TRUE';
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseDemandesNonTraitees($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->nest()
                ->nest()
                ->literal('demandeR1 < 2')->AND->literal('demandeR2 = 1')->unnest()->OR->nest()->literal(
                'demandeR1 = 1')->AND->literal('demandeR2 < 2')
                ->unnest()
                ->unnest();
        } else {
            $where['criteres']['demande'] = 1;
            $literal1 = 'demandeR1 < 2 AND demandeR2 = 1';
            $literal2 = 'demandeR1 = 1 AND demandeR2 < 2';
            $literal = "inscrit = 1 AND (($literal1) OR ($literal2))";
            $where['expression']['demande'] = $literal;
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseDemandesPartiellementTraitees($where, $pdf = false)
    {
        if ($where instanceof Where) {
            $where->nest()->literal('demandeR1 = 1')->OR->literal('demandeR2 = 1')->unnest();
            $where->nest()->literal('demandeR1 = 2')->OR->literal('demandeR2 = 2')->unnest();
            return $where;
        } else {
            $where['criteres']['demande'] = 2;
            $literal1 = 'demandeR1 = 1 OR demandeR2 = 1';
            $literal2 = 'demandeR1 = 2 OR demandeR2 = 2';
            $literal = "inscrit = 1 AND ($literal1) AND ($literal2)";
            $where['expression']['demande'] = $literal;
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseDemandesTraitees($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->literal('demandeR1 <> 1')->AND->literal('demandeR2 <> 1');
        } else {
            $where['criteres']['demande'] = 3;
            $literal = 'inscrit = 1 AND demandeR1 <> 1 AND demandeR2 <> 1';
            $where['expression']['demande'] = $literal;
            return $where;
        }
    }

    /**
     * Accord total - 3 cas : (demandeR1 = 0 AND demandeR2 = 2 AND accordR2 = 1) OR
     * (demandeR1 = 2 AND accordR1 = 1 AND demandeR2 = 0) OR (demandeR1 = 2 AND accordR1 =
     * 1 AND demandeR2 = 2 AND accordR2 = 1)
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseAccordTotal($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->nest()
                ->nest()
                ->literal('demandeR1 = 0')->AND->literal('demandeR2 = 2')->AND->literal(
                'accordR2 = 1')->unnest()->OR->nest()->literal('demandeR1 = 2')->AND->literal(
                'demandeR1 = 2')->AND->literal('demandeR2 = 0')->unnest()->OR->nest()->literal(
                'demandeR1 = 2')->AND->literal('accordR1 = 1')->AND->literal(
                'demandeR2 = 2')->AND->literal('accordR2 = 1')
                ->unnest()
                ->unnest();
        } else {
            $where['criteres']['decision'] = 1;
            // pas de demande R1 et accord sur la demande R2
            $literal1 = 'demandeR1 = 0 AND demandeR2 = 2 AND accordR2 = 1';
            // pas de demande R2 et accord sur la demande R1
            $literal2 = 'demandeR2 = 0 AND demandeR1 = 2 AND accordR1 = 1';
            // accord sur les deux demandes
            $literal3 = 'demandeR1 = 2 AND accordR1 = 1 AND demandeR2 = 2 AND accordR2 = 1';
            $literal = "inscrit = 1 AND (($literal1) OR ($literal2) OR ($literal3))";
            $where['expression']['decision'] = $literal;
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseAccordPartiel($where, $pdf = false)
    {
        if ($where instanceof Where) {
            $where->literal('demandeR1 = 2')->literal('demandeR2 = 2');
            $where->nest()->literal('accordR1 = 0')->OR->literal('accordR2 = 0')->unnest();
            return $where;
        } else {
            $where['criteres']['decision'] = 2;
            $literal1 = 'inscrit =1 AND demandeR1 = 2 AND demandeR2 = 2';
            $literal2 = 'accordR1 = 0 OR accordR2 = 0';
            $literal = "$literal1 AND ($literal2)";
            $where['expression']['decision'] = $literal;
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseSubvention($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->nest()
                ->nest()
                ->literal('demandeR1 = 2')
                ->literal('subventionR1 = 1')
                ->unnest()->OR->nest()
                ->literal('demandeR2 = 2')
                ->literal('subventionR2 = 1')
                ->unnest()
                ->unnest();
        } else {
            $where['criteres']['decision'] = 3;
            $literal1 = 'demandeR1 = 2 AND subventionR1 = 1';
            $literal2 = 'demandeR2 = 2 AND subventionR2 = 1';
            $literal = "inscrit = 1 AND (($literal1) OR ($literal2))";
            $where['expression']['decision'] = $literal;
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseRefusTotal($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->literal('subventionR1 = 0')
                ->literal('subventionR2 = 0')
                ->nest()
                ->literal('demandeR1 = 0')->OR->nest()
                ->literal('demandeR1 = 2')
                ->literal('accordR1 = 0')
                ->unnest()
                ->unnest()
                ->nest()
                ->literal('demandeR2 = 0')->OR->nest()
                ->literal('demandeR2 = 2')
                ->literal('accordR2 = 0')
                ->unnest()
                ->unnest();
        } else {
            $where['criteres']['decision'] = 4;
            // inscrit et pas de subvention
            $literal1 = 'inscrit = 1 AND subventionR1 = 0 AND subventionR2 = 0';
            // pas de demande R1 ou demande R1 refusée
            $literal2 = 'demandeR1 = 0 OR (demandeR1 = 2 AND accordR1 = 0)';
            // pas de demande R2 ou demande R2 refusée
            $literal3 = 'demandeR2 = 0 OR (demandeR2 = 2 AND accordR2 = 0)';
            $where['expression']['decision'] = "$literal1 AND ($literal2) AND ($literal3)";
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseDistancesACalculer($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->nest()
                ->nest()
                ->literal('demandeR1 > 0')
                ->literal('distanceR1 * (distanceR1 - 99) = 0')
                ->unnest()->or->nest()
                ->literal('demandeR2 > 0')
                ->literal('distanceR2 * (distanceR2 - 99) = 0')
                ->unnest()
                ->unnest();
        } else {
            $where['criteres']['incomplet'] = 1;
            $literalR1 = 'demandeR1 > 0 AND distanceR1 * (distanceR1 - 99) = 0';
            $literalR2 = 'demandeR2 > 0 AND distanceR2 * (distanceR2 - 99) = 0';
            $literal = "inscrit = 1 AND (($literalR1) OR ($literalR2))";
            $where['expression']['incomplet'] = $literal;
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseSansAffectation($where, $pdf = false)
    {
        if ($where instanceof Where) {
            if ($pdf) {
                return $where->isNull('eleveIdAffectation');
            } else {
                return $where->isNull('aff.eleveId');
            }
        } else {
            $where['criteres']['incomplet'] = 2;
            $literal = 'inscrit = 1 AND affecte IS FALSE';
            $where['expression']['incomplet'] = $literal;
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseGardeAlternee($where, $pdf = false)
    {
        if ($where instanceof Where) {
            if ($pdf) {
                return $where->isNotNull('responsable2Id');
            } else {
                return $where->isNotNull('ele.responsable2Id');
            }
        } else {
            $where['criteres']['particularite'] = 1;
            $literal = 'inscrit = 1 AND responsable2Id IS NOT NULL';
            $where['expression']['particularite'] = $literal;
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseFamilleDAccueil($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->literal('fa=1');
        } else {
            $where['criteres']['particularite'] = 2;
            $literal = 'inscrit = 1 AND fa = 1';
            $where['expression']['particularite'] = $literal;
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseDerogation($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->literal('derogation = 1');
        } else {
            $where['criteres']['particularite'] = 3;
            $literal = 'inscrit = 1 AND derogation = 1';
            $where['expression']['particularite'] = $literal;
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseNonAyantDroit($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->literal('derogation = 2');
        } else {
            $where['criteres']['particularite'] = 4;
            $literal = 'inscrit = 1 AND derogation = 2';
            $where['expression']['particularite'] = $literal;
            return $where;
        }
    }

    /**
     *
     * @param Where|array $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where|array
     */
    private function clauseSelection($where, $pdf = false)
    {
        if ($where instanceof Where) {
            if ($pdf) {
                return $where->literal('selection = 1');
                ;
            } else {
                return $where->literal('ele.selection = 1');
            }
        } else {
            $where['criteres']['selection'] = 1;
            $where['expression']['selection'] = 'selection = 1';
            return $where;
        }
    }
}