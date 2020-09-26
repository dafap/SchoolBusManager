<?php
/**
 * Entité pour les critères élèves du portail
 *
 * @project sbm
 * @package SbmPortail\Model\Db\ObjectData
 * @filesource CriteresCommune.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 août 2020
 * @version 2020-2.6.0
 */
namespace SbmPortail\Model\Db\ObjectData;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\ObjectData\Criteres as SbmCommunCriteres;
use Zend\Db\Sql\Where;

class CriteresCommune extends SbmCommunCriteres
{

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
     * On filtre sur le millesime en cours. La propriété `data` est un tableau de la forme
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
            $where->nest()->equalTo('r1.communeId', $this->data['communeId'])->or->equalTo(
                'r2.communeId', $this->data['communeId'])->unnest();
        }
        if (! empty($this->data['etablissementId'])) {
            $where->equalTo('sco.etablissementId', $this->data['etablissementId']);
        }
        if (! empty($this->data['classeId'])) {
            $where->equalTo('sco.classeId', $this->data['classeId']);
        }
        return $where;
    }

    public function getPredicates()
    {
        $where = $this->getWhere();
        echo '<pre>';
        var_dump($where->getPredicates(), $where->getExpressionData());
        die(__METHOD__);
    }

    /**
     * Prépare et renvoie un Where à partir des données de l'objet. Le tableau
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
            $where->nest()->equalTo('communeIdR1', $this->data['communeId'])->or->equalTo(
                'communeIdR2', $this->data['communeId'])->unnest();
        } else {
            $or = false;
            foreach (array_keys($descripteur) as $communeId) {
                if ($or) {
                    $where->or;
                } else {
                    $where = $where->nest();
                    $or = true;
                }
                $where->equalTo('communeIdR1', $communeId)->or->equalTo('communeIdR2',
                    $communeId);
            }
            $where = $where->unnest();
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
     * On filtre sur le millesime en cours. La propriété `data` est un tableau de la forme
     * :<dl> <dt>array (size=10)</dt> <dd>'numero' => string '' (length=0)</dd>
     * <dd>'nomSA' => string '' (length=0)</dd> <dd>'responsableSA' => string ''
     * (length=0)</dd> <dd>'etablissementId' => string '' (length=0)</dd> <dd>'classeId'
     * => string '' (length=0)</dd> <dd>'etat' => string '' (length=0)</dd> <dd>'demande'
     * => string '' (length=0)</dd> <dd>'decision' => string '' (length=0)</dd>
     * <dd>'derogation' => string '0' (length=1)</dd> <dd>'selection' => string '0'
     * (length=1)</dd> </dl> (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\ObjectData\Criteres::getWhere()
     */
    public function getWhereForEleves($strict = [], $alias = [])
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
            $where->nest()->like('r1.nomSA', $this->data['responsable'] . '%')->or->like(
                'r2.nomSA', $this->data['responsable'] . '%')->unnest();
        }
        if (! empty($this->data['communeId'])) {
            $where->nest()->equalTo('r1.communeId', $this->data['communeId'])->or->equalTo(
                'r2.communeId', $this->data['communeId'])->unnest();
        }
        if (! empty($this->data['etablissementId'])) {
            $where->equalTo('sco.etablissementId', $this->data['etablissementId']);
        }
        if (! empty($this->data['classeId'])) {
            $where->equalTo('sco.classeId', $this->data['classeId']);
        }
        return $where;
    }

    /**
     * Prépare et renvoie un Where à partir des données de l'objet. Le tableau
     * $descripteur est structuré de la façon suivante :<ul> <li>'strict' => [liste de
     * champs ...]</li> <li>'expressions' => [liste de champs]</li></ul> En fait, cette
     * méthode appelle la précédente mais il ne doit pas y avoir de champ préfixé dans le
     * tableau 'expressions'.
     *
     * @param array $descripteur
     *
     * @return \Zend\Db\Sql\Where
     */
    public function getWherePdfForEleves($descripteur = null)
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
        if (! empty($this->data['communeId'])) {
            $where->nest()->equalTo('communeIdR1', $this->data['communeId'])->or->equalTo(
                'communeIdR2', $this->data['communeId'])->unnest();
        } else {
            $or = false;
            foreach (array_keys($descripteur) as $communeId) {
                if ($or) {
                    $where->or;
                } else {
                    $where = $where->nest();
                    $or = true;
                }
                $where->equalTo('communeIdR1', $communeId)->or->equalTo('communeIdR2',
                    $communeId);
            }
            $where = $where->unnest();
        }
        if (! empty($this->data['etablissementId'])) {
            $where->equalTo('etablissementId', $this->data['etablissementId']);
        }
        if (! empty($this->data['classeId'])) {
            $where->equalTo('classeId', $this->data['classeId']);
        }
        return $where;
    }
}