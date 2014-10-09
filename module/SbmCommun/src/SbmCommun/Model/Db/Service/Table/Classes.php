<?php
/**
 * Gestion de la table `classes`
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Classes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Strategy\ClasseNiveau as NiveauStrategy;


class Classes extends AbstractSbmTable
{
    /**
     * Initialisation de la classe
     */
    protected function init()
    {
        $this->table_name = 'classes';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Classes';
        $this->id_name = 'classeId';
    }
    
    /**
     * (non-PHPdoc)
     * @see \SbmCommun\Model\Db\Table\AbstractTable::setStrategies()
     */
    protected function setStrategies()
    {
        $this->hydrator->addStrategy('niveau', new NiveauStrategy());
    }
    
    public function getNiveaux()
    {
        return  array(
            NiveauStrategy::CODE_NIVEAU_MATERNELLE => 'maternelle',
            NiveauStrategy::CODE_NIVEAU_ELEMENTAIRE => 'élémentaire',
            NiveauStrategy::CODE_NIVEAU_PREMIER_CYCLE => 'premier cycle',
            NiveauStrategy::CODE_NIVEAU_SECOND_CYCLE => 'second cycle',
            NiveauStrategy::CODE_NIVEAU_POST_BAC => 'post bac',
            NiveauStrategy::CODE_NIVEAU_SUPERIEUR => 'ens. supérieur',
            NiveauStrategy::CODE_NIVEAU_AUTRE => 'autres'
        );
    }
}

