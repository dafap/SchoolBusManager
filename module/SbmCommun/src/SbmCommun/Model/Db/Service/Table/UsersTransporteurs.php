<?php
/**
 * Gestion de la table `users-transporteurs`
 *
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource UsersTransporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 août 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Table;

class UsersTransporteurs extends AbstractSbmTable
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'users-transporteurs';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\UsersTransporteurs';
        $this->id_name = array(
            'userId',
            'transporteurId'
        );
    }

    /**
     * Renvoie un booléen qui indique si le user est associé à un transporteur
     *
     * @param int $userId            
     *
     * @return bool
     */
    public function hasTransporteur($userId)
    {
        $resultset = $this->fetchAll(array(
            'userId' => $userId
        ));
        return $resultset->count() == 1;
    }

    /**
     * Renvoie le transporteurId associé à un userId
     * 
     * @param int $userId
     * 
     * @return int
     * @throws \SbmCommun\Model\Db\Service\Table\Exception
     */
    public function getTransporteurId($userId)
    {
        $resultset = $this->fetchAll(array(
            'userId' => $userId
        ));
        if ($resultset->count() == 1) {
            return $resultset->current()->transporteurId;
        } else {
            throw new Exception(sprintf('L\utilisateur n° %d n\'est pas associé à un transporteur.', $userId));
        }
    }
}