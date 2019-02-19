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
 * @date 4 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\ObjectData;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\ObjectData\Criteres as SbmCommunCriteres;
use Zend\Db\Sql\Where;

class CriteresEleves extends SbmCommunCriteres
{

    /**
     * On filtre sur le millesime en cours.
     * La propriété `data` est un tableau de la forme :
     * array (size=10)
     * 'numero' => string '' (length=0)
     * 'nomSA' => string '' (length=0)
     * 'responsableSA' => string '' (length=0)
     * 'etablissementId' => string '' (length=0)
     * 'classeId' => string '' (length=0)
     * 'etat' => string '' (length=0)
     * 'demande' => string '' (length=0)
     * 'decision' => string '' (length=0)
     * 'derogation' => string '0' (length=1)
     * 'selection' => string '0' (length=1)
     *
     * (non-PHPdoc)
     *
     * $strict et $alias sont inutiles et ne sont gardés que pour la compatibilité stricte des
     * appels
     *
     * @see \SbmCommun\Model\Db\ObjectData\Criteres::getWhere()
     */
    public function getWhere($strict = [], $alias = [])
    {
        $where = new Where();
        $where->equalTo('sco.millesime', Session::get('millesime'));
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
                    // traitée : on a répondu à la demandeR1 et la demandeR2 n'est pas en attente
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
                    ;
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
                    // traiée : on a répondu à la demandeR1 et la demandeR2 n'est pas en attente
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
                    // 3 cas : ((demandeR1 = 0 AND demandeR2 = 2 AND accordR2 = 1) OR (demandeR1 =
                    // 2 AND accordR1 = 1 AND demandeR2 = 0) OR (demandeR1 = 2 AND accordR1 = 1 AND
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
     *
     * $strict et $alias sont inutiles et ne sont gardés que pour la compatibilité stricte des
     * appels
     *
     * @param array $criteres
     */
    public function getCriteres($strict = [], $alias = [])
    {
        $filtre = [
            'expression' => [],
            'criteres' => (array) $this->data,
            'strict' => [
                'empty' => [
                    'inscrit',
                    'fa',
                    'paiement'
                ],
                'not empty' => [
                    'numero',
                    'etablissementId',
                    'classeId',
                    'derogation',
                    'demandeR1',
                    'demandeR2',
                    'selection'
                ]
            ]
        ];
        if (! empty($this->data['etat'])) {
            switch ($this->data['etat']) {
                case 1:
                    // inscrits
                    $filtre['criteres']['inscrit'] = 1;
                    $filtre['expression'][] = '(paiement = 1 OR fa = 1 OR gratuit > 0)';
                    break;
                case 2:
                    // pré inscrits
                    $filtre['criteres']['inscrit'] = 1;
                    $filtre['criteres']['paiement'] = 0;
                    $filtre['criteres']['fa'] = 0;
                    $filtre['criteres']['gratuit'] = 0;
                    break;
                case 3:
                    // rayés
                    $filtre['criteres']['inscrit'] = 0;
                    break;
                case 4:
                    // non rayé
                    $filtre['criteres']['inscrit'] = 1;
                    break;
                case 5:
                    // avec photo
                    break;
                case 6:
                    // sans photo
                    break;
            }
        }
        if (! empty($this->data['demande'])) {
            $filtre['criteres']['inscrit'] = 1;
            switch ($this->data['demande']) {
                case 1:
                    // non traitée:
                    $filtre['expression'][] = '((demandeR1 = 0 AND demandeR2 = 1) OR (demandeR1 = 1 AND demandeR2 < 2))';
                    break;
                case 2:
                    // partiellement traitée : l'une des demandes vaut 1 et l'autre vaut 2
                    $filtre['expression'][] = '((demandeR1 = 2 AND demandeR2 = 1) OR (demandeR1 = 1 AND demandeR2 = 2))';
                    break;
                case 3:
                    // traiée : on a répondu à la demandeR1 et la demandeR2 n'est pas en attente
                    $filtre['expression'][] = '((demandeR1 = 0 AND demandeR2 <> 1) OR (demandeR1 = 2 AND demandeR2 <> 1))';
                    break;
            }
        }
        if (! empty($this->data['decision'])) {
            $filtre['criteres']['inscrit'] = 1;
            switch ($this->data['decision']) {
                case 1:
                    // accord total
                    $filtre['expression'][] = '((demandeR1 = 0 AND demandeR2 = 2 AND accordR2 = 1) OR (demandeR1 = 2 AND accordR1 = 1 AND demandeR2 = 0) OR (demandeR1 = 2 AND accordR1 = 1 AND demandeR2 = 2 AND accordR2 = 1)';
                    break;
                case 2:
                    // accord partiel : pour avoir un accord partiel il faut 2 demandes sinon
                    // l'accord est total ou la demande est non traitée
                    $filtre['criteres']['demandeR1'] = 2;
                    $filtre['criteres']['demandeR2'] = 2;
                    $filtre['expression'][] = '(accordR1 = 0 OR accordR2 = 0)';
                    break;
                case 3:
                    // subvention
                    // $filtre['expression'][] = '((demandeR1 = 2 AND accordR1 = 0 AND subventionR1
                    // = 1) OR (demandeR2 = 2 AND accordR2 = 0 AND subventionR2 = 1))';
                    $filtre['expression'][] = '((demandeR1 = 2 AND AND subventionR1 = 1) OR (demandeR2 = 2 AND subventionR2 = 1))';
                    break;
                case 4:
                    // refus total
                    $filtre['expression'][] = '((demandeR1 = 0 AND demandeR2 = 2 AND accordR2 = 0 AND subventionR2 = 0) OR (demandeR1 = 2 AND accordR1 = 0 AND subventionR1 = 0 AND demandeR2 = 0) OR (demandeR1 = 2 and accordR1 = 0 AND subventionR1 = 0 AND demandeR2 = 2 AND accordR2 = 0 AND subventionR2 = 0))';
                    break;
            }
        }
        if (! empty($this->data['incomplet'])) {
            switch ($this->data['incomplet']) {
                case 1:
                    // distance à calculer
                    break;
                case 2:
                    // sans affectation
                    $filtre['expression'][] = 'eleveIdAffectation IS NULL';
                    break;
                case 3:
                    // sans photo
                    break;
            }
        }
        if (! empty($this->data['particularite'])) {
            switch ($this->data['particularite']) {
                case 1:
                    // garde alternée
                    $filtre['expression'][] = 'responsable2Id IS NOT NULL';
                    break;
                case 2:
                    // famille d'accueil
                    $filtre['criteres']['inscrit'] = 1;
                    $filtre['criteres']['fa'] = 1;
                    break;
                case 3:
                    // dérogation
                    break;
            }
        }
        unset($filtre['criteres']['etat']);
        unset($filtre['criteres']['demande']);
        unset($filtre['criteres']['decision']);
        unset($filtre['criteres']['nonaffecte']);
        return $filtre;
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseNumero(Where $where, $pdf = false)
    {
        return $where->equalTo('numero', $this->data['numero']);
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseNomSA(Where $where, $pdf = false)
    {
        if ($pdf) {
            return $where->like('nomSA', $this->data['nomSA'] . '%');
        } else {
            return $where->like('ele.nomSA', $this->data['nomSA'] . '%');
        }
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clausePrenomSA(Where $where, $pdf = false)
    {
        if ($pdf) {
            return $where->like('prenomSA', $this->data['prenomSA'] . '%');
        } else {
            return $where->like('ele.prenomSA', $this->data['prenomSA'] . '%');
        }
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseResponsable(Where $where, $pdf = false)
    {
        if ($pdf) {
            return $where->like('responsable', $this->data['responsable'] . '%');
        } else {
            return $where->nest()->like('r1.nomSA', $this->data['responsable'] . '%')->OR->like(
                'r2.nomSA', $this->data['responsable'] . '%')->unnest();
        }
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseEtablissement(Where $where, $pdf = false)
    {
        if ($pdf) {
            return $where->equalTo('etablissementId', $this->data['etablissementId']);
        } else {
            return $where->equalTo('sco.etablissementId', $this->data['etablissementId']);
        }
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseClasse(Where $where, $pdf = false)
    {
        if ($pdf) {
            return $where->equalTo('classeId', $this->data['classeId']);
        } else {
            return $where->equalTo('sco.classeId', $this->data['classeId']);
        }
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseInscrits(Where $where, $pdf = false)
    {
        return $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->OR->literal('fa = 1')->OR->literal('gratuit > 0')->unnest();
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clausePreinscrits(Where $where, $pdf = false)
    {
        return $where->literal('inscrit = 1')
            ->literal('paiement = 0')
            ->literal('fa=0')
            ->literal('gratuit = 0');
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseRayes(Where $where, $pdf = false)
    {
        return $where->literal('inscrit = 0');
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseNonRayes(Where $where, $pdf = false)
    {
        return $where->literal('inscrit = 1');
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseAvecPhoto(Where $where, $pdf = false)
    {
        if ($pdf) {
            return $where->literal('sansphoto = FALSE');
        } else {
            return $where->isNotNull('photos.eleveId');
        }
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseSansPhoto(Where $where, $pdf = false)
    {
        if ($pdf) {
            return $where->literal('sansphoto = TRUE');
        } else {
            return $where->isNull('photos.eleveId');
        }
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseDemandesNonTraitees(Where $where, $pdf = false)
    {
        return $where->nest()
            ->nest()
            ->literal('demandeR1 = 0')->AND->literal('demandeR2 = 1')->unnest()->OR->nest()->literal(
            'demandeR1 = 1')->AND->literal('demandeR2 < 2')
            ->unnest()
            ->unnest();
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseDemandesPartiellementTraitees(Where $where, $pdf = false)
    {
        $where->nest()->literal('demandeR1 = 1')->OR->literal('demandeR2 = 1')->unnest();
        $where->nest()->literal('demandeR1 = 2')->OR->literal('demandeR2 = 2')->unnest();
        return $where;
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseDemandesTraitees(Where $where, $pdf = false)
    {
        return $where->nest()
            ->nest()
            ->literal('demandeR1 = 0')->AND->literal('demandeR2 <> 1')->unnest()->OR->nest()->literal(
            'demandeR1 = 2')->AND->literal('demandeR2 <> 1')
            ->unnest()
            ->unnest();
    }

    /**
     * Accord total - 3 cas :
     * (demandeR1 = 0 AND demandeR2 = 2 AND accordR2 = 1)
     * OR (demandeR1 = 2 AND accordR1 = 1 AND demandeR2 = 0)
     * OR (demandeR1 = 2 AND accordR1 = 1 AND demandeR2 = 2 AND accordR2 = 1)
     *
     * @param Where $where
     */
    private function clauseAccordTotal(Where $where, $pdf = false)
    {
        return $where->nest()
            ->nest()
            ->literal('demandeR1 = 0')->AND->literal('demandeR2 = 2')->AND->literal(
            'accordR2 = 1')->unnest()->OR->nest()->literal('demandeR1 = 2')->AND->literal(
            'demandeR1 = 2')->AND->literal('demandeR2 = 0')->unnest()->OR->nest()->literal(
            'demandeR1 = 2')->AND->literal('accordR1 = 1')->AND->literal('demandeR2 = 2')->AND->literal(
            'accordR2 = 1')
            ->unnest()
            ->unnest();
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseAccordPartiel(Where $where, $pdf = false)
    {
        $where->literal('demandeR1 = 2')->literal('demandeR2 = 2');
        $where->nest()->literal('accordR1 = 0')->OR->literal('accordR2 = 0')->unnest();
        return $where;
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseSubvention(Where $where, $pdf = false)
    {
        return $where->nest()
            ->nest()
            ->literal('demandeR1 = 2')
            ->literal('subventionR1 = 1')
            ->unnest()->OR->nest()
            ->literal('demandeR2 = 2')
            ->literal('subventionR2 = 1')
            ->unnest()
            ->unnest();
        ;
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseRefusTotal(Where $where, $pdf = false)
    {
        return $where->nest()
            ->nest()
            ->literal('demandeR1 = 0')
            ->literal('demandeR2 = 2')
            ->literal('accordR2 = 0')
            ->literal('subventionR2 = 0')
            ->unnest()->OR->nest()
            ->literal('demandeR1 = 2')
            ->literal('demandeR2 = 0')
            ->literal('accordR1 = 0')
            ->literal('subventionR1 = 0')
            ->unnest()->OR->nest()
            ->literal('demandeR1 = 2')
            ->literal('accordR1 = 0')
            ->literal('subventionR1 = 0')
            ->literal('demandeR2 = 2')
            ->literal('accordR2 = 0')
            ->literal('subventionR2 = 0')
            ->unnest()
            ->unnest();
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseDistancesACalculer(Where $where, $pdf = false)
    {
        return $where->nest()
            ->nest()
            ->literal('demandeR1 > 0')
            ->literal('distanceR1 = 0')
            ->unnest()->or->nest()
            ->literal('demandeR2 > 0')
            ->literal('distanceR2 = 0')
            ->unnest()
            ->unnest();
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseSansAffectation(Where $where, $pdf = false)
    {
        if ($pdf) {
            return $where->isNull('eleveIdAffectation');
        } else {
            return $where->isNull('aff.eleveId');
        }
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseGardeAlternee(Where $where, $pdf = false)
    {
        if ($pdf) {
            return $where->isNotNull('responsable2Id');
        } else {
            return $where->isNotNull('ele.responsable2Id');
        }
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseFamilleDAccueil(Where $where, $pdf = false)
    {
        return $where->literal('inscrit = 1')->literal('fa=1');
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseDerogation(Where $where, $pdf = false)
    {
        return $where->literal('derogation = 1');
    }

    /**
     *
     * @param Where $where
     * @param bool $pdf
     *
     * @return \Zend\Db\Sql\Where
     */
    private function clauseSelection(Where $where, $pdf = false)
    {
        if ($pdf) {
            return $where->literal('selection = 1');
            ;
        } else {
            return $where->literal('ele.selection = 1');
        }
    }
}