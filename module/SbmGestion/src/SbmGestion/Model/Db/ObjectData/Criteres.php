<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
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
     * @see \SbmCommun\Model\Db\ObjectData\Criteres::getWhere()
     */
    public function getWhere($strict = array())
    {
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'));
        if (! empty($this->data['numero'])) {
            $where->equalTo('numero', $this->data['numero']);
        }
        if (! empty($this->data['nomSA'])) {
            $where->like('nomSA', $this->data['nomSA'] . '%');
        }
        if (! empty($this->data['responsable'])) {
            $where->nest()->like('responsable1NomPrenom', $this->data['responsable'] . '%')->OR->like('responsable2NomPrenom', $this->data['responsable'] . '%')->unnest();
        }
        if (! empty($this->data['etablissementId'])) {
            $where->equalTo('etablissementId', $this->data['etablissementId']);
        }
        if (! empty($this->data['classeId'])) {
            $where->equalTo('classeId', $this->data['classeId']);
        }
        if (! empty($this->data['etat'])) {
            switch ($this->data['etat']) {
                case 1:
                    $where->literal('inscrit = 1')->literal('paiement = 1');
                    break;
                case 2:
                    $where->literal('inscrit = 1')->literal('paiement = 0');
                    break;
                case 3:
                    $where->literal('inscrit = 0')->literal('paiement = 1');
                    break;
            }
        }
        if (! empty($this->data['demande'])) {
            $where->literal('inscrit = 1');
            switch ($this->data['demande']) {
                case 1:
                    // non traitée: on considère qu'il y a toujours une demandeR1
                    $where->literal('demandeR1 = 1')->literal('demandeR2 < 2');
                    break;
                case 2:
                    // partiellement traitée : l'une des demandes vaut 1 et l'autre vaut 2
                    $where->nest()->literal('demandeR1 = 1')->OR->literal('demandeR2 = 1')->unnest();
                    $where->nest()->literal('demandeR1 = 2')->OR->literal('demandeR2 = 2')->unnest();
                    break;
                case 3:
                    // traiée : on a répondu à la demandeR1 et la demandeR2 n'est pas en attente
                    $where->literal('demandeR1 = 2')->literal('demandeR2 <> 1');
                    break;
            }
        }
        if (! empty($this->data['decision'])) {
            $where->literal('inscrit = 1');
            switch ($this->data['decision']) {
                case 1:
                    // accord total
                    $where->literal('demandeR1 = 2')->literal('accordR1 = 1');
                    $where->nest()->literal('demandeR2 = 0')->OR->nest()->literal('demandeR2 = 2')->AND->literal('accordR2 = 1')
                        ->unnest()
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
                    $where->literal('demandeR1 = 2')
                        ->literal('accordR1 = 0')
                        ->literal('subventionR1 = 0')
                        ->nest()
                        ->literal('demandeR2 = 0')->OR->nest()->literal('demandeR2 = 2')->AND->literal('accordR2 = 0')
                        ->literal('subventionR2 = 0')
                        ->unnest()
                        ->unnest();
                    break;
            }
        }
        if (! empty($this->data['derogation'])) {
            $where->literal('derogation = 1');
        }
        if (! empty($this->data['selection'])) {
            $where->literal('selection = 1');
        }
        return $where;
    }
}