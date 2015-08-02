<?php
/**
 * Gestion de la table `users-etablissements`
 *
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource UsersEtablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 août 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Table;

class UsersEtablissements extends AbstractSbmTable
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'users-etablissements';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\UsersEtablissements';
        $this->id_name = array(
            'userId',
            'etablissementId'
        );
    }

    /**
     * Renvoie un booléen qui indique si le user est associé à un établissement
     *
     * @param int $userId            
     *
     * @return bool
     */
    public function hasEtablissement($userId)
    {
        $resultset = $this->fetchAll(array(
            'userId' => $userId
        ));
        return $resultset->count() == 1;
    }

    /**
     * Renvoie le etablissementId associé à un userId
     * 
     * @param int $userId
     * 
     * @return int
     * @throws \SbmCommun\Model\Db\Service\Table\Exception
     */
    public function getEtablissementId($userId)
    {
        $resultset = $this->fetchAll(array(
            'userId' => $userId
        ));
        if ($resultset->count() == 1) {
            return $resultset->current()->etablissementId;
        } else {
            throw new Exception(sprintf('L\utilisateur n° %d n\'est pas associé à un établissement.', $userId));
        }
    }
}