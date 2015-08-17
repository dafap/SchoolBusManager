<?php
/**
 * Extension de la classe SbmCommun\Model\Db\ObjectData pour surcharger le méthode getWhere()
 *
 * @project sbm
 * @package SbmGestion/Model/Db/ObjectData
 * @filesource Criteres.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2015
 * @version 2015-1
 */
namespace SbmGestion\Model\Db\ObjectData;

use Zend\Db\Sql\Where;
use DafapSession\Model\Session;
use SbmCommun\Model\Db\ObjectData\Criteres as SbmCommunCriteres;

class Criteres extends SbmCommunCriteres
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
     * $strict et $alias sont inutiles et ne sont gardés que pour la compatibilité stricte des appels
     *
     * @see \SbmCommun\Model\Db\ObjectData\Criteres::getWhere()
     */
    public function getWhere($strict = array(), $alias = array())
    {
        $where = new Where();
        $where->equalTo('sco.millesime', Session::get('millesime'));
        if (! empty($this->data['numero'])) {
            $where->equalTo('numero', $this->data['numero']);
        }
        if (! empty($this->data['nomSA'])) {
            $where->like('ele.nomSA', $this->data['nomSA'] . '%');
        }
        if (! empty($this->data['prenomSA'])) {
            $where->like('ele.prenomSA', $this->data['prenomSA'] . '%');
        }
        if (! empty($this->data['responsable'])) {
            $where->nest()->like('r1.nomSA', $this->data['responsable'] . '%')->OR->like('r2.nomSA', $this->data['responsable'] . '%')->unnest();
        }
        if (! empty($this->data['etablissementId'])) {
            $where->equalTo('sco.etablissementId', $this->data['etablissementId']);
        }
        if (! empty($this->data['classeId'])) {
            $where->equalTo('sco.classeId', $this->data['classeId']);
        }
        if (! empty($this->data['etat'])) {
            switch ($this->data['etat']) {
                case 1:
                    $where->literal('inscrit = 1')
                        ->nest()
                        ->literal('paiement = 1')->OR->literal('fa=1')->unnest();
                    break;
                case 2:
                    $where->literal('inscrit = 1')
                        ->literal('paiement = 0')
                        ->literal('fa=0');
                    break;
                case 3:
                    $where->literal('inscrit = 0')
                        ->nest()
                        ->literal('paiement = 1')->OR->literal('fa=1')->unnest();
                    break;
                case 4:
                    $where->literal('inscrit = 1')->literal('fa=1');
                    break;
            }
        }
        if (! empty($this->data['demande'])) {
            $where->literal('inscrit = 1');
            switch ($this->data['demande']) {
                case 1:
                    // non traitée:
                    $where->nest()
                        ->nest()->literal('demandeR1 = 0')->AND->literal('demandeR2 = 1')->unnest()
                        ->OR
                        ->nest()->literal('demandeR1 = 1')->AND->literal('demandeR2 < 2')->unnest()
                        ->unnest();
                    break;
                case 2:
                    // partiellement traitée : l'une des demandes vaut 1 et l'autre vaut 2
                    $where->nest()->literal('demandeR1 = 1')->OR->literal('demandeR2 = 1')->unnest();
                    $where->nest()->literal('demandeR1 = 2')->OR->literal('demandeR2 = 2')->unnest();
                    break;
                case 3:
                    // traiée : on a répondu à la demandeR1 et la demandeR2 n'est pas en attente
                    $where->nest()
                        ->nest()->literal('demandeR1 = 0')->AND->literal('demandeR2 <> 1')->unnest()
                        ->OR
                        ->nest()->literal('demandeR1 = 2')->AND->literal('demandeR2 <> 1')->unnest()
                        ->unnest();
                    break;
            }
        }
        if (! empty($this->data['decision'])) {
            $where->literal('inscrit = 1');
            switch ($this->data['decision']) {
                case 1:
                    // accord total
                    // 3 cas : ((demandeR1 = 0 AND demandeR2 = 2 AND accordR2 = 1) OR (demandeR1 = 2 AND accordR1 = 1 AND demandeR2 = 0) OR (demandeR1 = 2 AND accordR1 = 1 AND demandeR2 = 2 AND accordR2 = 1)
                    $where->nest()
                        ->nest()->literal('demandeR1 = 0')->AND->literal('demandeR2 = 2')->AND->literal('accordR2 = 1')->unnest()
                        ->OR
                        ->nest()->literal('demandeR1 = 2')->AND->literal('demandeR1 = 2')->AND->literal('demandeR2 = 0')->unnest()
                    ->OR
                        ->nest()->literal('demandeR1 = 2')->AND->literal('accordR1 = 1')->AND->literal('demandeR2 = 2')->AND->literal('accordR2 = 1')->unnest()
                        ->unnest();
                    break;
                case 2:
                    // accord partiel
                    $where->literal('demandeR1 = 2')->literal('demandeR2 = 2');
                    $where->nest()->literal('accordR1 = 0')->OR->literal('accordR2 = 0')->unnest();
                    break;
                case 3:
                    // subvention
                    $where->nest()
                        ->nest()
                        ->literal('demandeR1 = 2')
                        ->literal('accordR1 = 0')
                        ->literal('subventionR1 = 1')
                        ->unnest()->OR->nest()
                        ->literal('demandeR2 = 2')
                        ->literal('accordR2 = 0')
                        ->literal('subventionR2 = 1')
                        ->unnest()
                        ->unnest();
                    break;
                case 4:
                    // refus total
                    $where->nest()
                        ->nest()->literal('demandeR1 = 0')->literal('demandeR2 = 2')->literal('accordR2 = 0')->literal('subventionR2 = 0')->unnest()
                        ->OR
                        ->nest()->literal('demandeR1 = 2')->literal('demandeR2 = 0')->literal('accordR1 = 0')->literal('subventionR1 = 0')->unnest()
                        ->OR
                        ->nest()->literal('demandeR1 = 2')->literal('accordR1 = 0')->literal('subventionR1 = 0')->literal('demandeR2 = 2')->literal('accordR2 = 0')->literal('subventionR2 = 0')->unnest()
                        ->unnest();
                    break;
            }
        }
        if (! empty($this->data['derogation'])) {
            $where->literal('derogation = 1');
        }
        if (! empty($this->data['selection'])) {
            $where->literal('ele.selection = 1');
        }
        if (! empty($this->data['nonaffecte'])) {
            $where->isNull('aff.eleveId');
        }
        return $where;
    }

    /**
     * Transforme l'objet en tableau de critéres en modifiant certaines propriétés
     *
     * @param array $criteres            
     */
    public function getCriteres()
    {
        $filtre = array(
            'expression' => array(),
            'criteres' => (array) $this->data,
            'strict' => array(
                'empty' => array(
                    'inscrit',
                    'fa',
                    'paiement',
                ),
                'not empty' => array(
                    'numero',
                    'etablissementId',
                    'classeId',
                    'derogation',
                    'demandeR1',
                    'demandeR2',
                    'selection'
                )
            )
        );
        if (! empty($this->data['etat'])) {
            switch ($this->data['etat']) {
                case 1:
                    // inscrits
                    $filtre['criteres']['inscrit'] = 1;
                    $filtre['expression'][] = '(paiement = 1 OR fa = 1)';
                    break;
                case 2:
                    // pré inscrits
                    $filtre['criteres']['inscrit'] = 1;
                    $filtre['criteres']['paiement'] = 0;
                    $filtre['criteres']['fa'] = 0;
                    break;
                case 3:
                    // rayés
                    $filtre['criteres']['inscrit'] = 0;
                    $filtre['expression'][] = '(paiement = 1 OR fa = 1)';
                    break;
                case 4:
                    // famille d'accueil
                    $filtre['criteres']['inscrit'] = 1;
                    $filtre['criteres']['fa'] = 1;
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
                    // accord partiel : pour avoir un accord partiel il faut 2 demandes sinon l'accord est total ou la demande est non traitée
                    $filtre['criteres']['demandeR1'] = 2;
                    $filtre['criteres']['demandeR2'] = 2;
                    $filtre['expression'][] = '(accordR1 = 0 OR accordR2 = 0)';
                    break;
                case 3:
                    // subvention
                    $filtre['expression'][] = '((demandeR1 = 2 AND accordR1 = 0 AND subventionR1 = 1) OR (demandeR2 = 2 AND accordR2 = 0 AND subventionR2 = 1))';
                    break;
                case 4:
                    // refus total
                    $filtre['expression'][] = '((demandeR1 = 0 AND demandeR2 = 2 AND accordR2 = 0 AND subventionR2 = 0) OR (demandeR1 = 2 AND accordR1 = 0 AND subventionR1 = 0 AND demandeR2 = 0) OR (demandeR1 = 2 and accordR1 = 0 AND subventionR1 = 0 AND demandeR2 = 2 AND accordR2 = 0 AND subventionR2 = 0))';
                    break;
            }
        }
        if (! empty($this->data['nonaffecte'])) {
            $filtre['expression'][] = 'eleveIdAffectation IS NULL';            
        }
        unset($filtre['criteres']['etat']);
        unset($filtre['criteres']['demande']);
        unset($filtre['criteres']['decision']);
        unset($filtre['criteres']['nonaffecte']);
        return $filtre;
    }
}