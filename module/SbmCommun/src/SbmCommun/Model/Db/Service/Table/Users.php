<?php
/**
 * Gestion de la table `users`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Users.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 févr. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\Db\Sql\Where;

class Users extends AbstractSbmTable
{

    /**
     * Initialisation du transporteur
     */
    protected function init()
    {
        $this->table_name = 'users';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Users';
        $this->id_name = 'userId';
    }

    /**
     * Cherche une fiche où le champ $field_name prend la valeur $search
     * Renvoie la fiche sous forme d'un objet
     * 
     * @param string $field_name
     * @param string $search
     * 
     * @throws Exception
     * @return \SbmCommun\Model\Db\Service\Table\objectData
     */
    public function getRecordOneBy($field_name, $search)
    {
        $where_mask = sprintf('%s = ?', $field_name);
        $array_where = array(
            $where_mask => $search
        );
        $condition_msg = "$field_name = '$search'";
        
        $rowset = $this->table_gateway->select($array_where);
        $row = $rowset->current();
        if (! $row) {
            throw new Exception(sprintf(_("Could not find row '%s' in table %s"), $condition_msg, $this->table_name));
        }
        return $row;
    }

    /**
     * Renvoie la fiche sous forme d'un objet
     * 
     * @param string $token
     * @return \SbmCommun\Model\Db\Service\Table\objectData
     */
    public function getRecordByToken($token)
    {
        return $this->getRecordOneBy('token', $token);
    }

    /**
     * Renvoie la fiche sous forme d'un objet
     * 
     * @param string $email
     * 
     * @return \SbmCommun\Model\Db\Service\Table\objectData
     */
    public function getRecordByEmail($email)
    {
        return $this->getRecordOneBy('email', $email);
    }

    /**
     * Cette fonction renvoie le mot de passe (codé dans la table) et le gds à condition que la fiche soit confirmée et active.
     * Redondance dans le retour de cette fonction pour pouvoir utiliser list($mdp, $gds) = ...
     * 
     * @param string $email
     * @return multitype:array |boolean
     */
    public function getMdpGdsByEmail($email)
    {
        $record = $this->getRecordOneBy('email', $email);
        if ($record->confirme && $record->active) {
            return array(
                $record->mdp,
                $record->gds,
                'mdp' => $record->mdp,
                'gds' => $record->gds
            );
        } else {
            return false;
        }
    }
    
    /**
     * Cette méthode supprime l'enregistrement dont le token est indiqué.
     * On doit avoir tokenalive=1 et confirme=0 et active=0.
     * 
     * @param string $token
     */
    public function deleteRecordByToken($token)
    {
        $where = new Where();
        $where->literal('tokenalive=1')->literal('confirme=0')->literal('active=0')->equalTo('token', $token);
        return $this->table_gateway->delete($where);
    }
    
    /**
     * Coche ou décoche la sélection
     * 
     * @param int $userId
     * @param bool $selection
     */
    public function setSelection($userId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(array(
            'userId' => $userId,
            'selection' => $selection
        ));
        parent::saveRecord($oData);
    }
}
