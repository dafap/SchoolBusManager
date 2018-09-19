<?php
/**
 * Entité pour les critères élèves du portail
 * 
 * @project sbm
 * @package SbmPortail\Model\Db\ObjectData
 * @filesource Criteres.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 sept. 2018
 * @version 2016-2.4.5
 */
namespace SbmPortail\Model\Db\ObjectData;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\ObjectData\Criteres as SbmCommunCriteres;
use Zend\Db\Sql\Where;

class Criteres extends SbmCommunCriteres
{

    /**
     * On filtre sur le millesime en cours.
     * La propriété `data` est un tableau de la forme :<dl>
     * <dt>array (size=10)</dt>
     * <dd>'numero' => string '' (length=0)</dd>
     * <dd>'nomSA' => string '' (length=0)</dd>
     * <dd>'responsableSA' => string '' (length=0)</dd>
     * <dd>'etablissementId' => string '' (length=0)</dd>
     * <dd>'classeId' => string '' (length=0)</dd>
     * <dd>'etat' => string '' (length=0)</dd>
     * <dd>'demande' => string '' (length=0)</dd>
     * <dd>'decision' => string '' (length=0)</dd>
     * <dd>'derogation' => string '0' (length=1)</dd>
     * <dd>'selection' => string '0' (length=1)</dd></dl>
     *
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\ObjectData\Criteres::getWhere()
     */
    public function getWhere($strict = [], $alias = [])
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')->unnest();
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
            $where->nest()->equalTo('aff.service1Id', $this->data['serviceId'])->or->equalTo(
                'aff.service2Id', $this->data['serviceId'])->unnest();
        }
        if (! empty($this->data['stationId'])) {
            $where->nest()->equalTo('aff.station1Id', $this->data['stationId'])->or->equalTo(
                'aff.station2Id', $this->data['stationId'])->unnest();
        }
        return $where;
    }

    /**
     * Prépare et renvoie un Where à partir des données de l'objet.
     * Le tableau $descripteur est structuré de la façon suivante :<ul>
     * <li>'strict' => [liste de champs ...]</li>
     * <li>'expressions' => [liste de champs]</li></ul>
     *
     * En fait, cette méthode appelle la précédente mais il ne doit pas y avoir de champ préfixé
     * dans le tableau 'expressions'.
     *
     * @param array $descripteur
     *
     * @return \Zend\Db\Sql\Where
     */
    public function getWherePdf($descripteur = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')->unnest();
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
            $where->nest()->equalTo('service1Id', $this->data['serviceId'])->or->equalTo(
                'service2Id', $this->data['serviceId'])->unnest();
        }
        if (! empty($this->data['stationId'])) {
            $where->nest()->equalTo('station1Id', $this->data['stationId'])->or->equalTo(
                'station2Id', $this->data['stationId'])->unnest();
        }
        return $where;
    }

    /**
     * On filtre sur le millesime en cours.
     * La propriété `data` est un tableau de la forme :<dl>
     * <dt>array (size=10)</dt>
     * <dd>'numero' => string '' (length=0)</dd>
     * <dd>'nomSA' => string '' (length=0)</dd>
     * <dd>'responsableSA' => string '' (length=0)</dd>
     * <dd>'etablissementId' => string '' (length=0)</dd>
     * <dd>'classeId' => string '' (length=0)</dd>
     * <dd>'etat' => string '' (length=0)</dd>
     * <dd>'demande' => string '' (length=0)</dd>
     * <dd>'decision' => string '' (length=0)</dd>
     * <dd>'derogation' => string '0' (length=1)</dd>
     * <dd>'selection' => string '0' (length=1)</dd>
     * </dl>
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\ObjectData\Criteres::getWhere()
     */
    public function getWhereForEleves($strict = [], $alias = [])
    {
        $where = new Where();
        $where->literal('inscrit = 1');
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
            $where->nest()->like('res1.nomSA', $this->data['responsable'] . '%')->or->like(
                'res2.nomSA', $this->data['responsable'] . '%')->unnest();
        }
        if (! empty($this->data['etablissementId'])) {
            $where->equalTo('sco.etablissementId', $this->data['etablissementId']);
        }
        if (! empty($this->data['classeId'])) {
            $where->equalTo('sco.classeId', $this->data['classeId']);
        }
        if (! empty($this->data['serviceId'])) {
            $where->nest()->equalTo('affr1.service1Id', $this->data['serviceId'])->or->equalTo(
                'affr1.service2Id', $this->data['serviceId'])->or->equalTo(
                'affr2.service1Id', $this->data['serviceId'])->or->equalTo(
                'affr2.service2Id', $this->data['serviceId'])->unnest();
        }
        if (! empty($this->data['stationId'])) {
            $where->nest()->equalTo('affr1.station1Id', $this->data['stationId'])->or->equalTo(
                'affr1.station2Id', $this->data['stationId'])->or->equalTo(
                'affr2.station1Id', $this->data['stationId'])->or->equalTo(
                'affr2.station2Id', $this->data['stationId'])->unnest();
        }
        return $where;
    }

    /**
     * Prépare et renvoie un Where à partir des données de l'objet.
     * Le tableau $descripteur est structuré de la façon suivante :<ul>
     * <li>'strict' => [liste de champs ...]</li>
     * <li>'expressions' => [liste de champs]</li></ul>
     *
     * En fait, cette méthode appelle la précédente mais il ne doit pas y avoir de champ préfixé
     * dans le tableau 'expressions'.
     *
     * @param array $descripteur
     *
     * @return \Zend\Db\Sql\Where
     */
    public function getWherePdfForEleves($descripteur = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1');
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
            $where->nest()->like('responsable1', $this->data['responsable'] . '%')->or->like(
                'responsable2', $this->data['responsable'] . '%')->unnest();
        }
        if (! empty($this->data['etablissementId'])) {
            $where->equalTo('etablissementId', $this->data['etablissementId']);
        }
        if (! empty($this->data['classeId'])) {
            $where->equalTo('classeId', $this->data['classeId']);
        }
        if (! empty($this->data['serviceId'])) {
            $where->nest()->equalTo('service1IdR1', $this->data['serviceId'])->or->equalTo(
                'service2IdR1', $this->data['serviceId'])->or->equalTo('service1IdR2',
                $this->data['serviceId'])->or->equalTo('service2IdR2',
                $this->data['serviceId'])->unnest();
        }
        if (! empty($this->data['stationId'])) {
            $where->nest()->equalTo('station1IdR1', $this->data['stationId'])->or->equalTo(
                'station2IdR1', $this->data['stationId'])->or->equalTo('station1IdR2',
                $this->data['stationId'])->or->equalTo('station2IdR2',
                $this->data['stationId'])->unnest();
        }
        return $where;
    }
}