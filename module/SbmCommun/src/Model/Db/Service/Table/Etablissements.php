<?php
/**
 * Gestion de la table `etablissements`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Etablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Strategy\Niveau as NiveauStrategy;
use SbmCommun\Model\Strategy\Semaine as SemaineStrategy;
use Zend\Db\Sql\Where;

class Etablissements extends AbstractSbmTable implements EffectifInterface
{

    /**
     * Initialisation de l'etablissement
     */
    protected function init()
    {
        $this->table_name = 'etablissements';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Etablissements';
        $this->id_name = 'etablissementId';
        $this->strategies['jOuverture'] = new SemaineStrategy();
        $this->strategies['niveau'] = new NiveauStrategy();
    }

    public function getSemaine()
    {
        return [
            SemaineStrategy::CODE_SEMAINE_LUNDI => 'lun',
            SemaineStrategy::CODE_SEMAINE_MARDI => 'mar',
            SemaineStrategy::CODE_SEMAINE_MERCREDI => 'mer',
            SemaineStrategy::CODE_SEMAINE_JEUDI => 'jeu',
            SemaineStrategy::CODE_SEMAINE_VENDREDI => 'ven',
            SemaineStrategy::CODE_SEMAINE_SAMEDI => 'sam',
            SemaineStrategy::CODE_SEMAINE_DIMANCHE => 'dim'
        ];
    }

    public function getNiveau()
    {
        return [
            '1' => 'Maternelle',
            '2' => 'Primaire',
            '4' => 'Collège',
            '8' => 'Lycée',
            '16' => 'Autre'
        ];
    }

    /**
     * Renvoie les écoles publiques de la zone d'un niveau donné (maternelle ou primaire)
     * ou uniquement celles des ou de la commune si $communeId n'est pas null.
     *
     * @param int $niveau
     *            enum {1, 2}
     * @param int|null $statut
     *            enum {0, 1} 0: privé ; 1: public
     * @param array|string|null $communeId
     *            null ou $communeId ou tableau de $communeId
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function getEcoles($niveau, $statut = null, $communeId = null)
    {
        $where = new Where();
        if (is_null($communeId)) {
            if (is_null($statut)) {
                $where->equalTo('niveau', $niveau)->OR->equalTo('niveau', 3);
            } else {
                $where->equalTo('statut', $statut)
                    ->nest()
                    ->equalTo('niveau', $niveau)->OR->equalTo('niveau', 3)->unnest();
            }
        } elseif (is_string($communeId)) {
            if (is_null($statut)) {
                $where->equalTo('communeId', $communeId)
                    ->nest()
                    ->equalTo('niveau', $niveau)->OR->equalTo('niveau', 3)->unnest();
            } else {
                $where->equalTo('statut', $statut)
                    ->equalTo('communeId', $communeId)
                    ->nest()
                    ->equalTo('niveau', $niveau)->OR->equalTo('niveau', 3)->unnest();
            }
        } else {
            if (is_null($statut)) {
                $where->in('communeId', $communeId)
                    ->nest()
                    ->equalTo('niveau', $niveau)->OR->equalTo('niveau', 3)->unnest();
            } else {
                $where->equalTo('statut', $statut)
                    ->in('communeId', $communeId)
                    ->nest()
                    ->equalTo('niveau', $niveau)->OR->equalTo('niveau', 3)->unnest();
            }
        }

        return $this->fetchAll($where);
    }

    /**
     * Renvoie toutes les écoles primaires publiques ou les écoles primaires publiques d'une
     * commune donnée
     *
     * @param string|null $communeId
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function getEcolesPrimairesPubliques($communeId = null)
    {
        return $this->getEcoles(2, 1, $communeId);
    }

    /**
     * Renvoie toutes les écoles maternelles publiques ou les écoles maternelles publiques d'une
     * commune donnée
     *
     * @param string|null $communeId
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function getEcolesMaternellesPubliques($communeId = null)
    {
        return $this->getEcoles(1, 1, $communeId);
    }

    /**
     * Renvoie les écoles primaires privées de la zone
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function getEcolesPrimairesPrivees()
    {
        return $this->getEcoles(2, 0);
    }

    /**
     * Renvoie les écoles maternelles privées de la zone
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function getEcolesMaternellesPrivees()
    {
        return $this->getEcoles(1, 0);
    }

    /**
     * Renvoie les collèges publics ou les collèges publics du secteur scolaire de la commune
     *
     * @param string|null $communeId
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function getCollegesPublics($communeId = null)
    {
        $where = new Where();
        $where->literal('statut = 1')->equalTo('niveau', 4);
        // rechercher les collèges de la zone
        return $this->fetchAll($where);
    }

    /**
     * Renvoie les collèges privés de la zone
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function getCollegesPrives()
    {
        $where = new Where();
        $where->literal('statut = 0')->equalTo('niveau', 4);
        return $this->fetchAll($where);
    }

    /**
     *
     * @param string $etablissementId
     * @param bool $selection
     *            0 ou 1
     */
    public function setSelection($etablissementId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(
            [
                'etablissementId' => $etablissementId,
                'selection' => $selection
            ]);
        parent::saveRecord($oData);
    }

    /**
     *
     * @param string $etablissementId
     * @param bool $desservie
     *            0 ou 1
     */
    public function setDesservie($etablissementId, $desservie)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(
            [
                'etablissementId' => $etablissementId,
                'desservie' => $desservie
            ]);
        parent::saveRecord($oData);
    }

    /**
     *
     * @param string $etablissementId
     * @param bool $visible
     *            0 ou 1
     */
    public function setVisible($etablissementId, $visible)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(
            [
                'etablissementId' => $etablissementId,
                'visible' => $visible
            ]);
        parent::saveRecord($oData);
    }
}

