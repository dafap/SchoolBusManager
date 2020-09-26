<?php
/**
 * Entité pour les critères élèves du portail
 *
 * @project sbm
 * @package SbmPortail\Model\Db\ObjectData
 * @filesource CriteresTransporteur.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 sept. 2020
 * @version 2020-2.6.0
 */
namespace SbmPortail\Model\Db\ObjectData;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\ObjectData\Criteres as SbmCommunCriteres;
use Zend\Db\Sql\Where;

class CriteresTransporteur extends SbmCommunCriteres
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
     * Les paramètres ne sont présents que par compatibilité avec la classe parent
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
        if (! empty($this->data['nomSA'])) {
            $where->like('ele.nomSA', $this->data['nomSA'] . '%');
        }
        if (! empty($this->data['prenomSA'])) {
            $where->like('ele.prenomSA', $this->data['prenomSA'] . '%');
        }
        if (! empty($this->data['responsable'])) {
            $where->like('responsable', $this->data['responsable'] . '%');
        }
        if (! empty($this->data['etablissementId'])) {
            $where->equalTo('sco.etablissementId', $this->data['etablissementId']);
        }
        if (! empty($this->data['classeId'])) {
            $where->equalTo('sco.classeId', $this->data['classeId']);
        }
        if (! empty($this->data['serviceId'])) {
            list ($ligneId, $sens, $moment, $ordre) = $this->decodeServiceId(
                $this->data['serviceId']);
            $where->equalTo('sub.ligne1Id', $ligneId);
            $where->equalTo('sub.sensligne1', $sens);
            $where->equalTo('sub.moment', $moment);
            $where->equalTo('sub.ordreligne1', $ordre);
        }
        if (! empty($this->data['stationId'])) {
            $where->nest()->equalTo('sub.station1Id', $this->data['stationId'])->or->equalTo(
                'sub.station2Id', $this->data['stationId'])->unnest();
        }
        return $where;
    }

    /**
     * Cette méthode est semblable à la précédente mais il ne doit pas y avoir de champ
     * préfixé.
     *
     * @param array $descripteur
     *            Ce paramètre n'est présent que pour la compatibilité avec sa classe
     *            parent
     * @return \Zend\Db\Sql\Where
     */
    public function getWherePdf($descripteur = null)
    {
        $where = new Where();
        if ($this->sanspreinscrits) {
            $where->literal('inscrit = 1')
                ->nest()
                ->literal('paiementR1 = 1')->or->literal('gratuit = 1')->unnest();
        } else {
            $where->literal('inscrit = 1');
        }
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
        return $where;
    }

    /**
     * Idem
     *
     * @see \SbmPortail\Model\Db\ObjectData\CriteresTransporteur::getWhere()
     */
    public function getWhereForEleves($strict = [], $alias = [])
    {
        return $this->getWhere($strict, $alias);
    }

    /**
     * Idem
     *
     * @see \SbmPortail\Model\Db\ObjectData\CriteresTransporteur::getWherePdf()
     */
    public function getWherePdfForEleves($descripteur = null)
    {
        return $this->getWherePdf($descripteur);
    }
}