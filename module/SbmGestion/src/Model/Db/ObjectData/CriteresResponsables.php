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
 * @filesource CriteresResponsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Db\ObjectData;

use SbmCommun\Model\Db\ObjectData\Criteres as SbmCommunCriteres;
use Zend\Db\Sql\Where;

class CriteresResponsables extends SbmCommunCriteres
{

    private $sansLocalisationCondition;

    public function setSansLocalisationCondition($literal)
    {
        $this->sansLocalisationCondition = $literal;
    }

    /**
     * Pour les comptes, on filtre sur le millesime en cours. La propriété `data` est un
     * tableau de la forme : array (size=10) 'numero' => string '' (length=0) 'nomSA' =>
     * string '' (length=0) 'responsableSA' => string '' (length=0) 'etablissementId' =>
     * string '' (length=0) 'classeId' => string '' (length=0) 'etat' => string ''
     * (length=0) 'demande' => string '' (length=0) 'decision' => string '' (length=0)
     * 'derogation' => string '0' (length=1) 'selection' => string '0' (length=1)
     * (non-PHPdoc)
     * $strict et $alias sont inutiles et ne sont gardés que pour la compatibilité stricte
     * des appels
     *
     * @see \SbmCommun\Model\Db\ObjectData\Criteres::getWhere()
     */
    public function getWhere($strict = [], $alias = [])
    {
        $where = new Where();
        if (! empty($this->data['nomSA'])) {
            $where = $this->clauseNomSA($where);
        }
        if (! empty($this->data['prenomSA'])) {
            $where = $this->clausePrenomSA($where);
        }
        if (! empty($this->data['commune'])) {
            $where = $this->clauseCommune($where);
        }
        if (! empty($this->data['nbEnfants']) || $this->data['nbEnfants'] == '0') {
            $where = $this->clauseNbEnfants($where);
        }
        if (! empty($this->data['nbInscrits']) || $this->data['nbInscrits'] == '0') {
            $where = $this->clauseNbInscrits($where);
        }
        if (! empty($this->data['nbPreinscrits']) || $this->data['nbPreinscrits'] == '0') {
            $where = $this->clauseNbPreinscrits($where);
        }
        if (! empty($this->data['demenagement'])) {
            $where = $this->clauseDemenagement($where);
        }
        if (! empty($this->data['inscrits'])) {
            $where = $this->clauseTousLesInscrits($where);
        }
        if (! empty($this->data['preinscrits'])) {
            $where = $this->clauseTousLesPreinscrits($where);
        }
        if (! empty($this->data['localisation'])) {
            $where = $this->clauseSansLocalisation($where);
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
        if (! empty($this->data['nomSA'])) {
            $where = $this->clauseNomSA($where, true);
            $pageheader_string[] = sprintf('responsables dont le nom commence par %s',
                $this->data['nomSA']);
        }
        if (! empty($this->data['prenomSA'])) {
            $where = $this->clausePrenomSA($where, true);
            $pageheader_string[] = sprintf('responsables dont le prénom commence par %s',
                $this->data['prenomSA']);
        }
        if (! empty($this->data['commune'])) {
            $where = $this->clauseCommune($where, true);
            $pageheader_string[] = sprintf('responsables résidant à %s',
                $this->data['commune']);
        }
        if (! empty($this->data['nbEnfants']) || $this->data['nbEnfants'] == '0') {
            $where = $this->clauseNbEnfants($where, true);
            $pageheader_string[] = sprintf('responsables ayant %d enfants',
                $this->data['nbEnfants']);
        }
        if (! empty($this->data['nbInscrits']) || $this->data['nbInscrits'] == '0') {
            $where = $this->clauseNbInscrits($where, true);
            $pageheader_string[] = sprintf('responsables ayant %d enfants inscrits',
                $this->data['nbInscrits']);
        }
        if (! empty($this->data['nbPreinscrits']) || $this->data['nbPreinscrits'] == '0') {
            $where = $this->clauseNbPreinscrits($where, true);
            $pageheader_string[] = sprintf('responsables ayant %d enfants préinscrits',
                $this->data['nbPreinscrits']);
        }
        if (! empty($this->data['demenagement'])) {
            $where = $this->clauseDemenagement($where, true);
            $pageheader_string[] = 'responsables ayant déménagé';
        }
        if (! empty($this->data['inscrits'])) {
            $where = $this->clauseTousLesInscrits($where, true);
            $pageheader_string[] = 'responsables ayant des enfants inscrits';
        }
        if (! empty($this->data['preinscrits'])) {
            $where = $this->clauseTousLesPreinscrits($where, true);
            $pageheader_string[] = 'responsables ayant des enfants préinscrits';
        }
        if (! empty($this->data['localisation'])) {
            $where = $this->clauseSansLocalisation($where);
            $pageheader_string[] = 'responsables sans localisation de leur domicile';
        }
        if (! empty($this->data['selection'])) {
            $where = $this->clauseSelection($where, true);
            $pageheader_string[] = 'responsables sélectionnés';
        }
        if (! empty($pageheader_string)) {
            $this->pageheader_params['pageheader_string'] = implode(' ; ',
                $pageheader_string);
        }

        return $where;
    }

    /**
     * Transforme l'objet en tableau de critéres en modifiant certaines propriétés
     * Nécessaire pour les étiquettes à partir de la liste des responsables
     * $strict et $alias sont inutiles et ne sont gardés que pour la compatibilité stricte
     * des appels
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
        if (! empty($this->data['nomSA'])) {
            $where = $this->clauseNomSA($where, true);
            $pageheader_string[] = sprintf('responsables dont le nom commence par %s',
                $this->data['nomSA']);
        }
        if (! empty($this->data['prenomSA'])) {
            $where = $this->clausePrenomSA($where, true);
            $pageheader_string[] = sprintf('responsables dont le prénom commence par %s',
                $this->data['prenomSA']);
        }
        if (! empty($this->data['commune'])) {
            $where = $this->clauseCommune($where, true);
            $pageheader_string[] = sprintf('responsables résidant à %s',
                $this->data['commune']);
        }
        if (! empty($this->data['nbEnfants'])) {
            $where = $this->clauseNbEnfants($where, true);
            $pageheader_string[] = sprintf('responsables ayant %d enfants',
                $this->data['nbEnfants']);
        }
        if (! empty($this->data['nbInscrits'])) {
            $where = $this->clauseNbInscrits($where, true);
            $pageheader_string[] = sprintf('responsables ayant %d enfants inscrits',
                $this->data['nbInscrits']);
        }
        if (! empty($this->data['nbPreinscrits'])) {
            $where = $this->clauseNbPreinscrits($where, true);
            $pageheader_string[] = sprintf('responsables ayant %d enfants préinscrits',
                $this->data['nbPreinscrits']);
        }
        if (! empty($this->data['demenagement'])) {
            $where = $this->clauseDemenagement($where, true);
            $pageheader_string[] = 'responsables ayant déménagé';
        }
        if (! empty($this->data['inscrits'])) {
            $where = $this->clauseTousLesInscrits($where, true);
            $pageheader_string[] = 'responsables ayant des enfants inscrits';
        }
        if (! empty($this->data['preinscrits'])) {
            $where = $this->clauseTousLesPreinscrits($where, true);
            $pageheader_string[] = 'responsables ayant des enfants préinscrits';
        }
        if (! empty($this->data['localisation'])) {
            $where = $this->clauseSansLocalisation($where);
            $pageheader_string[] = 'responsables sans localisation de leur domicile';
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
    private function clauseNomSA($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->nest()->like('nomSA', $this->data['nomSA'] . '%')->OR->like(
                'nom2SA', $this->data['nomSA'] . '%')->unnest();
        } else {
            $where['criteres']['nomSA'] = [
                'operator' => 'OR',
                'parts' => [
                    [
                        'operator' => 'LIKE',
                        'parts' => [
                            'nomSA',
                            '"' . $this->data['nomSA'] . '%"'
                        ]
                    ],
                    [
                        'operator' => 'LIKE',
                        'parts' => [
                            'nom2SA',
                            '"' . $this->data['nomSA'] . '%"'
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
    private function clausePrenomSA($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->nest()->like('prenomSA', $this->data['prenomSA'] . '%')->OR->like(
                'prenom2SA', $this->data['prenomSA'] . '%')->unnest();
        } else {
            $where['criteres']['prenomSA'] = [
                'operator' => 'OR',
                'parts' => [
                    [
                        'operator' => 'LIKE',
                        'parts' => [
                            'prenomSA',
                            '"' . $this->data['prenomSA'] . '%"'
                        ]
                    ],
                    [
                        'operator' => 'LIKE',
                        'parts' => [
                            'prenom2SA',
                            '"' . $this->data['prenomSA'] . '%"'
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
    private function clauseCommune($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->like('commune', $this->data['commune'] . '%');
        } else {
            // $where['criteres']['responsable'] = true;
            $where['criteres']['commune'] = [
                $this->data['commune'] . '%'
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
    private function clauseNbEnfants($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->equalTo('nbEnfants', $this->data['nbEnfants']);
        } else {
            $where['strict']['empty'][] = 'nbEnfants';
            $where['criteres']['nbEnfants'] = $this->data['nbEnfants'];
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
    private function clauseNbInscrits($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->equalTo('nbInscrits', $this->data['nbInscrits']);
        } else {
            $where['strict']['empty'][] = 'nbInscrits';
            $where['criteres']['nbInscrits'] = $this->data['nbInscrits'];
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
    private function clauseNbPreinscrits($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->equalTo('nbPreinscrits', $this->data['nbPreinscrits']);
        } else {
            $where['strict']['empty'][] = 'nbPreinscrits';
            $where['criteres']['nbPreinscrits'] = $this->data['nbPreinscrits'];
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
    private function clauseDemenagement($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->literal('demenagement = 1');
        } else {
            $where['criteres']['demenagement'] = 1;
            $where['expression']['demenagement'] = 'demenagement = 1';
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
    private function clauseTousLesInscrits($where, $pdf = false)
    {
        if ($where instanceof Where) {
            if ($pdf) {
                return $where->literal('nbInscrits > 0');
            } else {
                return $where->literal('count(inscritId) > 0');
            }
        } else {
            $where['criteres']['inscrits'] = 1;
            $where['expression']['inscrits'] = 'nbInscrits > 0';
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
    private function clauseTousLesPreinscrits($where, $pdf = false)
    {
        if ($where instanceof Where) {
            if ($pdf) {
                return $where->literal('nbPreinscrits > 0');
            } else {
                return $where->literal('count(preinscritId) > 0');
            }
        } else {
            $where['criteres']['preinscrits'] = 1;
            $where['expression']['preinscrits'] = 'nbPreinscrits > 0';
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
    private function clauseSansLocalisation($where, $pdf = false)
    {
        if ($where instanceof Where) {
            return $where->literal($this->sansLocalisationCondition);
        } else {
            $where['criteres']['localisation'] = 1;
            $where['expression']['localisation'] = $this->sansLocalisationCondition;
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
            return $where->literal('selection = 1');
        } else {
            $where['criteres']['selection'] = 1;
            $where['expression']['selection'] = 'selection = 1';
            return $where;
        }
    }
}