<?php
/**
 * Gestion de la table `users-organismes`
 *
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Db/Service/Table
 * @filesource UsersOrganismes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Table;

class UsersOrganismes extends AbstractSbmTable
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'users-organismes';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\UsersOrganismes';
        $this->id_name = [
            'userId',
            'organismeId'
        ];
    }

    /**
     * Renvoie un booléen qui indique si le user est associé à un organisme
     *
     * @param int $userId
     *
     * @return bool
     */
    public function hasOrganisme($userId)
    {
        $resultset = $this->fetchAll([
            'userId' => $userId
        ]);
        return $resultset->count() == 1;
    }

    /**
     * Renvoie le organismeId associé à un userId
     *
     * @param int $userId
     *
     * @return int
     * @throws \SbmCommun\Model\Db\Service\Table\Exception\RuntimeException
     */
    public function getOrganismeId($userId)
    {
        $resultset = $this->fetchAll([
            'userId' => $userId
        ]);
        if ($resultset->count() == 1) {
            return $resultset->current()->organismeId;
        } else {
            throw new Exception\RuntimeException(
                sprintf('L\utilisateur n° %d n\'est pas associé à un organisme.',
                    $userId));
        }
    }
}