<?php
/**
 * Entité pour les critères élèves du portail
 * 
 * @project sbm
 * @package SbmPortail\Model\Db\ObjectData
 * @filesource Criteres.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 juillet 2015
 * @version 2015-1
 */
namespace SbmPortail\Model\Db\ObjectData;

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
    public function getWhere($strict = array(), $alias = array())
    {
        $where = new Where();
        $where->literal('inscrit = 1')->literal('paiement = 1');
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
            $where->like('res.nomSA', $this->data['responsable'] . '%');
        }
        if (! empty($this->data['etablissementId'])) {
            $where->equalTo('sco.etablissementId', $this->data['etablissementId']);
        }
        if (! empty($this->data['classeId'])) {
            $where->equalTo('sco.classeId', $this->data['classeId']);
        }
        if (! empty($this->data['serviceId'])) {
            $where->nest()->equalTo('aff.service1Id', $this->data['serviceId'])->or->equalTo('aff.service2Id', $this->data['serviceId'])->unnest();
        }
        if (! empty($this->data['stationId'])) {
            $where->nest()->equalTo('aff.station1Id', $this->data['stationId'])->or->equalTo('aff.station2Id', $this->data['stationId'])->unnest();
        }
        return $where;
    }

    /**
     * Prépare et renvoie un Where à partir des données de l'objet.
     * Le tableau $descripteur est structuré de la façon suivante :
     * 'strict' => array(liste de champs ...)
     * 'expressions' => array(liste de champs)
     *
     * En fait, cette méthode appelle la précédente mais il ne doit pas y avoir de champ préfixé dans le tableau 'expressions'.
     *
     * @param array $descripteur
     *
     * @return \Zend\Db\Sql\Where
     */
    public function getWherePdf($descripteur = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')->literal('paiement = 1');
        $where->equalTo('millesime', Session::get('millesime'));
        if (! empty($this->data['numero'])) {
            $where->equalTo('numero', $this->data['numero']);
        }
        if (! empty($this->data['nomSA'])) {
            $where->like('nomSA', $this->data['nomSA'] . '%');
        }
        if (! empty($this->data['prenomSA'])) {
            $where->like('prenomSA', $this->data['prenomSA'] . '%');
        }
        if (! empty($this->data['responsable'])) {
            $where->like('responsable', $this->data['responsable'] . '%');
        }
        if (! empty($this->data['etablissementId'])) {
            $where->equalTo('etablissementId', $this->data['etablissementId']);
        }
        if (! empty($this->data['classeId'])) {
            $where->equalTo('classeId', $this->data['classeId']);
        }
        if (! empty($this->data['serviceId'])) {
            $where->nest()->equalTo('service1Id', $this->data['serviceId'])->or->equalTo('service2Id', $this->data['serviceId'])->unnest();
        }
        if (! empty($this->data['stationId'])) {
            $where->nest()->equalTo('station1Id', $this->data['stationId'])->or->equalTo('station2Id', $this->data['stationId'])->unnest();
        }
        return $where;
    }
    /**
     * Renvoie un tableau pour filtrer les requêtes lorsque le recordSource est une requete Sql (pour Tcpdf)
     * On peut passer certains critères pour filtrer autoritairement le contenu de la requete
     * (par exemple pour limiter les résultats à l'établissement connecté - marche pas pour le transporteur connecté à cause d'un OR)
     *
     * @param array $filtre
     *            par exemple : array('ser.transporteurId' => 2)
     * @param array $strict_empty
     *            les champs pour lesquels il faut une égalité (pas un like) et où on peut chercher l'égalité à une valeur vide
     *            par exemple, si on cherche les établissements privés il faut 'statut = 0' or 0 est assimilé à empty
     * @param array $strict_not_empty
     *            les champs pour lesquels il faut une égalité (pas un like) et où on ne cherche jamais l'égalité à une valeur vide ou à 0
     *            par exemple, ici, on ne cherchera jamais le critère 'ser.transporteurId = 0' puisque 'ser.transporteurId' est un auto-increment qui commence à 1
     *                    
     * @return array avec les clés 'criteres' et 'strict' à utiliser dans l'appel de la liste à l'écran (paginateur) ou du pdf
     */
    /*public function getCriteres(array $filtre = array(), array $strict_empty = array(), array $strict_not_empty = array())
    {
        $result = array(
            'criteres' => $filtre,
            'strict' => array(
                'empty' => $strict_empty,
                'not empty' => $strict_not_empty
            )
        );
        $result['criteres']['inscrit'] = 1;
        $result['strict']['not empty'][] = 'inscrit';
        
        $result['criteres']['millesime'] = Session::get('millesime');
        $result['strict']['not empty'][] = 'millesime';
        
        if (! empty($this->data['numero'])) {
            $result['criteres']['numero'] = $this->data['numero'];
            $result['strict']['not empty'][] = 'numero';
        }
        if (! empty($this->data['nomSA'])) {
            $result['criteres']['nomSA'] = $this->data['nomSA'] . '%';
        }
        if (! empty($this->data['prenomSA'])) {
            $result['criteres']['prenomSA'] = $this->data['prenomSA'] . '%';
        }
        if (! empty($this->data['responsable'])) {
            $result['criteres']['responsable'] = $this->data['responsable'] . '%';
        }
        if (! empty($this->data['etablissementId'])) {
            $result['criteres']['etablissementId'] = $this->data['etablissementId'];
            $result['strict']['not empty'][] = 'etablissementId';
        }
        if (! empty($this->data['classeId'])) {
            $result['criteres']['classeId'] = $this->data['classeId'];
            $result['strict']['not empty'][] = 'classeId';
        }
        if (! empty($this->data['serviceId'])) {
            $result['criteres']['bloc_OR1'] = array('service1Id' => $this->data['serviceId'], 'service2Id' => $this->data['serviceId']);
            $result['strict']['not empty'][] = 'service1Id';
            $result['strict']['not empty'][] = 'service2Id';
        }
        if (! empty($this->data['stationId'])) {
            $result['criteres']['bloc_OR2'] = array('station1Id' => $this->data['stationId'],'station2Id' => $this->data['stationId']);
            $result['strict']['not empty'][] = 'station1Id';
            $result['strict']['not empty'][] = 'station2Id';
        }
        return $result;
    }*/
}