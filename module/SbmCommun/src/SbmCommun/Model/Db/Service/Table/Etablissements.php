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
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\Db\Sql\Where;
use SbmCommun\Model\Strategy\Semaine as SemaineStrategy;
use SbmCommun\Model\Strategy\Niveau as NiveauStrategy;
use Zend\Db\Sql\Zend\Db\Sql;

class Etablissements extends AbstractSbmTable
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
    }

    /**
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\Table\AbstractTable::setStrategies()
     */
    protected function setStrategies()
    {
        $this->hydrator->addStrategy('jOuverture', new SemaineStrategy());
        $this->hydrator->addStrategy('niveau', new NiveauStrategy());
    }

    public function getSemaine()
    {
        return array(
            SemaineStrategy::CODE_SEMAINE_LUNDI => 'lun',
            SemaineStrategy::CODE_SEMAINE_MARDI => 'mar',
            SemaineStrategy::CODE_SEMAINE_MERCREDI => 'mer',
            SemaineStrategy::CODE_SEMAINE_JEUDI => 'jeu',
            SemaineStrategy::CODE_SEMAINE_VENDREDI => 'ven',
            SemaineStrategy::CODE_SEMAINE_SAMEDI => 'sam',
            SemaineStrategy::CODE_SEMAINE_DIMANCHE => 'dim'
        );
    }

    public function getNiveau()
    {
        return array(
            '1' => 'Maternelle',
            '2' => 'Primaire',
            '4' => 'Collège',
            '8' => 'Lycée',
            '16' => 'Autre'
        );
    }

    /**
     * Renvoie les écoles publiques de la zone d'un niveau donné (maternelle ou primaire) ou uniquement celles de la commune si la commune est précisée.
     *
     * @param int $niveau
     *            enum {1, 2}
     * @param int|null $statut
     *            enum {0, 1} 0: privé ; 1: public
     * @param string|null $communeId            
     *
     * @return \SbmCommun\Model\Db\Service\Table\ResultSet
     */
    public function getEcoles($niveau, $statut = null, $communeId = null)
    {
        $where = new Where();
        if (is_null($communeId)) {
            if (is_null($statut)) {
                $where->equalTo('niveau', $niveau)->OR->equalTo('niveau', 3);
            } else {
                $where->equalTo('statut', 1)
                    ->nest()
                    ->equalTo('niveau', $niveau)->OR->equalTo('niveau', 3)->unnest();
            }
        } else {
            if (is_null($statut)) {
                $where->equalTo('communeId', $communeId)
                    ->nest()
                    ->equalTo('niveau', $niveau)->OR->equalTo('niveau', 3)->unnest();
            } else {
                $where->literal('statut = 1')
                    ->equalTo('communeId', $communeId)
                    ->nest()
                    ->equalTo('niveau', $niveau)->OR->equalTo('niveau', 3)->unnest();
            }
        }
        
        return $this->fetchAll($where);
    }

    /**
     * Renvoie toutes les écoles primaires publiques ou les écoles primaires publiques d'une commune donnée
     *
     * @param string|null $communeId            
     *
     * @return \SbmCommun\Model\Db\Service\Table\ResultSet
     */
    public function getEcolesPrimairesPubliques($communeId = null)
    {
        return $this->getEcoles(2, 1, $communeId);
    }

    /**
     * Renvoie toutes les écoles maternelles publiques ou les écoles maternelles publiques d'une commune donnée
     *
     * @param string|null $communeId            
     *
     * @return \SbmCommun\Model\Db\Service\Table\ResultSet
     */
    public function getEcolesMaternellesPubliques($communeId = null)
    {
        return $this->getEcoles(1, 1, $communeId);
    }

    /**
     * Renvoie les écoles primaires privées de la zone
     *
     * @return \SbmCommun\Model\Db\Service\Table\ResultSet
     */
    public function getEcolesPrimairesPrivees()
    {
        return $this->getEcoles(2, 0);
    }

    /**
     * Renvoie les écoles maternelles privées de la zone
     *
     * @return \SbmCommun\Model\Db\Service\Table\ResultSet
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
     * @return \SbmCommun\Model\Db\Service\Table\ResultSet
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
     * @return \SbmCommun\Model\Db\Service\Table\ResultSet
     */
    public function getCollegesPrives()
    {
        $where = new Where();
        $where->literal('statut = 0')->equalTo('niveau', 4);
        return $this->fetchAll($where);
    }
}

