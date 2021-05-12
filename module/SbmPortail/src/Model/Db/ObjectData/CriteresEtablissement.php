<?php
/**
 * Entité pour les critères élèves du portail des établissements
 *
 * @project sbm
 * @package SbmPortail/src/Model/Db/ObjectData
 * @filesource CriteresEtablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mai 2021
 * @version 2021-2.6.1
 */
namespace SbmPortail\Model\Db\ObjectData;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\ObjectData\Criteres as SbmCommunCriteres;
use Zend\Db\Sql\Where;

class CriteresEtablissement extends SbmCommunCriteres
{
    use \SbmCommun\Model\Traits\ServiceTrait;

    /**
     *
     * @var bool
     */
    private $sanspreinscrits;

    public function __construct($form_fields, bool $sanspreinscrits = true)
    {
        $this->sanspreinscrits = $sanspreinscrits;
        parent::__construct($form_fields);
    }

    /**
     * On filtre sur le millesime en cours.
     * La propriété `data` est un tableau de la forme
     * :<dl> <dt>array (size=10)</dt> <dd>'numero' => string '' (length=0)</dd>
     * <dd>'nomSA' => string '' (length=0)</dd> <dd>'responsableSA' => string ''
     * (length=0)</dd> <dd>'etablissementId' => string '' (length=0)</dd> <dd>'classeId'
     * => string '' (length=0)</dd> <dd>'etat' => string '' (length=0)</dd> <dd>'demande'
     * => string '' (length=0)</dd> <dd>'decision' => string '' (length=0)</dd>
     * <dd>'derogation' => string '0' (length=1)</dd> <dd>'selection' => string '0'
     * (length=1)</dd></dl> (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\ObjectData\Criteres::getWhere()
     */
    public function getWhere($strict = [], $alias = [])
    {
        if ($this->sanspreinscrits) {
            $elevesSansPreinscrits = new \SbmCommun\Model\Db\Sql\Predicate\ElevesSansPreinscrits(
                Session::get('millesime'), 'sco');
            $where = $elevesSansPreinscrits();
        } else {
            $elevesNonRayes = new \SbmCommun\Model\Db\Sql\Predicate\ElevesNonRayes(
                Session::get('millesime'), 'sco');
            $where = $elevesNonRayes();
        }
        if (! empty($this->data['numero'])) {
            $where->equalTo('numero', $this->data['numero']);
        }
        if (array_key_exists('regimeId', $this->data) && strlen($this->data['regimeId'])) {
            $where->equalTo('sco.regimeId', $this->data['regimeId']);
        }
        if (! empty($this->data['nomSA'])) {
            $where->like('ele.nomSA', $this->data['nomSA'] . '%');
        }
        if (! empty($this->data['prenomSA'])) {
            $where->like('ele.prenomSA', $this->data['prenomSA'] . '%');
        }
        if (! empty($this->data['responsable'])) {
            $where->nest()->like('r1.nomSA', $this->data['responsable'] . '%')->or->like(
                'r2.nomSA', $this->data['responsable'] . '%')->unnest();
        }
        if (! empty($this->data['communeId'])) {
            $where->nest()->equalTo('sco.communeId', $this->data['communeId'])->or->nest()
                ->isNull('sco.communeId')
                ->equalTo('r1.communeId', $this->data['communeId'])
                ->unnest()->or->equalTo('r2.communeId', $this->data['communeId'])->unnest();
        }
        if (! empty($this->data['etablissementId'])) {
            $where->equalTo('sco.etablissementId', $this->data['etablissementId']);
        }
        if (! empty($this->data['classeId'])) {
            $where->equalTo('sco.classeId', $this->data['classeId']);
        }
        if (! empty($this->data['serviceId'])) {
            list ('ligneId' => $ligneId, 'sens' => $sens, 'moment' => $moment, 'ordre' => $ordre) = $this->decodeServiceId(
                $this->data['serviceId']);
            $where->nest()
                ->nest()
                ->equalTo('affR1.ligne1Id', $ligneId)
                ->equalTo('affR1.sensligne1', $sens)
                ->equalTo('affR1.moment', $moment)
                ->equalTo('affR1.ordreligne1', $ordre)
                ->unnest()->or->nest()
                ->equalTo('affR2.ligne1Id', $ligneId)
                ->equalTo('affR2.sensligne1', $sens)
                ->equalTo('affR2.moment', $moment)
                ->equalTo('affR2.ordreligne1', $ordre)
                ->unnest()
                ->unnest();
        }
        if (! empty($this->data['stationId'])) {
            $where->nest()
                ->nest()
                ->nest()
                ->nest()
                ->literal('affR1.moment = 1')->or->literal('affR1.moment = 4')->or->literal(
                'affR1.moment = 5')
                ->unnest()
                ->equalTo('affR1.station1Id', $this->data['stationId'])
                ->unnest()->or->nest()
                ->nest()
                ->literal('affR1.moment = 2')->or->literal('affR1.moment = 3')
                ->unnest()
                ->equalTo('affR1.station2Id', $this->data['stationId'])
                ->unnest()
                ->unnest()->or->nest()
                ->nest()
                ->nest()
                ->literal('affR2.moment = 1')->or->literal('affR2.moment = 4')->or->literal(
                'affR2.moment = 5')
                ->unnest()
                ->equalTo('affR2.station1Id', $this->data['stationId'])
                ->unnest()->or->nest()
                ->nest()
                ->literal('affR2.moment = 2')->or->literal('affR2.moment = 3')
                ->unnest()
                ->equalTo('affR2.station2Id', $this->data['stationId'])
                ->unnest()
                ->unnest();
        }
        return $where;
    }

    /**
     * Prépare et renvoie un Where à partir des données de l'objet.
     * Le tableau
     * $descripteur est structuré de la façon suivante :<ul> <li>'strict' => [liste de
     * champs ...]</li> <li>'expressions' => [liste de champs]</li></ul> En fait, cette
     * méthode appelle la précédente mais il ne doit pas y avoir de champ préfixé dans le
     * tableau 'expressions'.
     *
     * @param array $descripteur
     *
     * @return \Zend\Db\Sql\Where
     */
    public function getWherePdf($descripteur = null)
    {
        $where = new Where();
        if ($this->sanspreinscrits) {
            $where->literal('inscrit = 1')
                ->nest()
                ->literal('paiementR1 = 1')->or->literal('gratuit > 0')->unnest();
        } else {
            $where->literal('inscrit = 1');
        }
        $where->equalTo('millesime', Session::get('millesime'));
        if (! empty($this->data['numero'])) {
            $where->equalTo('numero', $this->data['numero']);
        }
        if (! empty($this->data['regimeId'])) {
            $where->equalTo('sco.regimeId', $this->data['regimeId']);
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
        if (! empty($this->data['communeId'])) {
            $where->nest()->equalTo('sco.communeId', $this->data['communeId'])->or->nest()
                ->isNull('sco.communeId')
                ->equalTo('r1.communeId', $this->data['communeId'])
                ->unnest()->or->equalTo('r2.communeId', $this->data['communeId'])->unnest();
        }
        if (! empty($this->data['etablissementId'])) {
            $where->equalTo('etablissementId', $this->data['etablissementId']);
        }
        if (! empty($this->data['classeId'])) {
            $where->equalTo('classeId', $this->data['classeId']);
        }
        if (! empty($this->data['serviceId'])) {
            list ($ligneId, $sens, $moment, $ordre) = explode('|',
                $this->decodeServiceId($this->data['serviceId']));
            $where->nest()
                ->nest()
                ->equalTo('R1ligne1Id', $ligneId)
                ->equalTo('R1sensligne1', $sens)
                ->equalTo('R1moment', $moment)
                ->equalTo('R1ordreligne1', $ordre)
                ->unnest()->or->nest()
                ->equalTo('R2ligne1Id', $ligneId)
                ->equalTo('R2sens', $sens)
                ->equalTo('R2moment', $moment)
                ->equalTo('R2ordre', $ordre)
                ->unnest()
                ->unnest();
        }
        if (! empty($this->data['stationId'])) {
            $where->nest()
                ->nest()
                ->nest()
                ->nest()
                ->literal('R1moment = 1')->or->literal('R1moment = 4')->or->literal(
                'R1moment = 5')
                ->unnest()
                ->equalTo('R1station1Id', $this->data['stationId'])
                ->unnest()->or->nest()
                ->nest()
                ->literal('R1moment = 2')->or->literal('affR1.moment = 3')
                ->unnest()
                ->equalTo('R1station2Id', $this->data['stationId'])
                ->unnest()
                ->unnest()->or->nest()
                ->nest()
                ->nest()
                ->literal('R2moment = 1')->or->literal('R2moment = 4')->or->literal(
                'R2moment = 5')
                ->unnest()
                ->equalTo('R2station1Id', $this->data['stationId'])
                ->unnest()->or->nest()
                ->nest()
                ->literal('R2moment = 2')->or->literal('affR2.moment = 3')
                ->unnest()
                ->equalTo('R2station2Id', $this->data['stationId'])
                ->unnest()
                ->unnest();
        }
        return $where;
    }
}