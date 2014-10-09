<?php
/**
 * Gestion de la table `etablissements`
 *
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

use SbmCommun\Model\Strategy\Semaine as SemaineStrategy;

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
     * @see \SbmCommun\Model\Db\Table\AbstractTable::setStrategies()
     */
    protected function setStrategies()
    {
        $this->hydrator->addStrategy('jOuverture', new SemaineStrategy());
    }
    
    public function getSemaine()
    {
        return  array(
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
                    '3' => 'Collège',
                    '4' => 'Lycée',
                    '5' => 'Autre'
                );
    }
}

