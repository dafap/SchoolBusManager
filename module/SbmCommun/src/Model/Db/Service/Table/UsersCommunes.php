<?php
/**
 * Gestion de la table `users-communes`
 *
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Db/Table
 * @filesource UsersCommunes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Table;

class UsersCommunes extends AbstractSbmTable
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'users-communes';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\UsersCommunes';
        $this->id_name = [
            'userId',
            'communeId'
        ];
    }

    /**
     * Renvoie un booléen qui indique si le user est associé à un commune
     *
     * @param int $userId
     *
     * @return bool
     */
    public function hasCommune($userId)
    {
        $resultset = $this->fetchAll([
            'userId' => $userId
        ]);
        return $resultset->count() == 1;
    }

    /**
     * Renvoie le communeId associé à un userId
     *
     * @param int $userId
     *
     * @return int
     * @throws \SbmCommun\Model\Db\Service\Table\Exception\RuntimeException
     */
    public function getCommuneId($userId)
    {
        $resultset = $this->fetchAll([
            'userId' => $userId
        ]);
        if ($resultset->count() == 1) {
            return $resultset->current()->communeId;
        } else {
            throw new Exception\RuntimeException(
                sprintf('L\utilisateur n° %d n\'est pas associé à une commune.',
                    $userId));
        }
    }
}