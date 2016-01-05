<?php
/**
 * Gestion de la table `responsables`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Responsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 05 janv. 2016
 * @version 2016-1.7.1
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use SbmCommun\Model\Db\ObjectData\Responsable as ObjectDataResponsable;
use SbmCommun\Model\Db\ObjectData\Exception as ExceptionObjectData;

class Responsables extends AbstractSbmTable
{

    private $lastResponsableId;

    /**
     * Initialisation du responsable
     */
    protected function init()
    {
        $this->table_name = 'responsables';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Responsables';
        $this->id_name = 'responsableId';
        $this->lastResponsableId = 0;
    }

    /**
     * Reçoit un objectData à enregistrer.
     *
     * Si c'est un nouveau, regarde si l'email est déjà connu dans la table. Si c'est le cas,
     * identifie l'objectData à cet enregistrement et sort sans enregistrer en metttant à jour
     * la propriété lastResponsableId.
     *
     * Renvoie true si la commune a changée ou si c'est un nouveau.
     * Mets à jour le propriété lastResponsableId quelque soit le cas : insert, update ou inchangé
     *
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::saveRecord()
     */
    public function saveRecord(ObjectDataInterface $obj_data)
    {
        try {
            $old_data = $this->getRecord($obj_data->getId());
            $is_new = false;
        } catch (Exception $e) {
            try {
                $nom = $obj_data->nom;
                $prenom = $obj_data->prenom;
                $adresseL1 = $obj_data->adresseL1;
                $adresseL2 = $obj_data->adresseL2;
                $communeId = $obj_data->communeId;
                $telephone = $obj_data->telephoneF;
                $email = $obj_data->email;
                $old_data = $this->getRecordByEmail($email);
                // $old_data est false si pas trouvé                
                if (! $old_data) {
                    $old_data = $this->getRecordByNomPrenomAdresse($nom, $prenom, $adresseL1, $communeId);                    
                    if (! $old_data) {
                        $old_data = $this->getRecordByNomPrenomAdresse($nom, $prenom, $adresseL2, $communeId);                        
                        if (! $old_data) {
                            $old_data = $this->getRecordByNomPrenomTelephone($nom, $prenom, $telephone);
                        }
                    }
                }
                $is_new = ! $old_data;
                if (! $is_new) {
                    // dans ce cas, c'est le responsable. On ne le change pas.
                    $obj_data = $old_data;
                }
            } catch (ExceptionObjectData $e) {
                //die($e->getMessage());
                $is_new = true;
            }
        }
        if ($is_new) {
            $obj_data->setCalculateFields(array(
                'nomSA',
                'prenomSA',
                'nom2SA',
                'prenom2SA',
                'dateCreation'
            ));
            $changeCommuneId = true;
        } else {
            // on vérifie si des données ont changé
            if ($obj_data->isUnchanged($old_data)) {
                $this->lastResponsableId = $obj_data->responsableId;
                return false;
            }
            try {
                if ($old_data->nom != $obj_data->nom) {
                    $obj_data->addCalculateField('nomSA');
                }
            } catch (ExceptionObjectData $e) {}
            try {
                if ($old_data->prenom != $obj_data->prenom) {
                    $obj_data->addCalculateField('prenomSA');
                }
            } catch (ExceptionObjectData $e) {}
            try {
                if ($old_data->nom2 != $obj_data->nom2) {
                    $obj_data->addCalculateField('nom2SA');
                }
            } catch (ExceptionObjectData $e) {}
            try {
                if ($old_data->prenom2 != $obj_data->prenom2) {
                    $obj_data->addCalculateField('prenom2SA');
                }
            } catch (ExceptionObjectData $e) {}
            try {
                $changeCommuneId = $old_data->communeId != $obj_data->communeId;
            } catch (ExceptionObjectData $e) {
                $changeCommuneId = false;
            }
            
            $obj_data->addCalculateField('dateModification');
        }
        parent::saveRecord($obj_data);
        if ($is_new) {
            $this->lastResponsableId = $this->getTableGateway()->getLastInsertValue();
        } else {
            $this->lastResponsableId = $obj_data->responsableId;
        }
        return $changeCommuneId;
    }

    public function setSelection($responsableId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(array(
            'responsableId' => $responsableId,
            'selection' => $selection
        ));
        parent::saveRecord($oData);
    }

    /**
     * Renvoie le dernier responsableId enregistré par saveRecord()
     *
     * @return number
     */
    public function getLastResponsableId()
    {
        return $this->lastResponsableId;
    }

    /**
     * Change les emails.
     * Attention, cette méthode ne met pas à jour la propriété lastResponsableId.
     *
     * @param string $email_old            
     * @param string $email_new            
     */
    public function changeEmail($email_old, $email_new)
    {
        $update = $this->table_gateway->update(array(
            'email' => $email_new
        ), array(
            'email' => $email_old
        ));
    }

    /**
     * Renvoie le `nom prénom` du responsable, éventuellement précédé du `titre`
     *
     * @param int $responsabled
     *            référence du respondable
     * @param bool $with_titre
     *            indique si le titre doit être mis ou non
     *            
     * @return string
     */
    public function getNomPrenom($responsabled, $with_titre = false)
    {
        $record = $this->getRecord($responsabled);
        return ($with_titre ? $record->titre . ' ' : '') . $record->nom . ' ' . $record->prenom;
    }

    /**
     * Renvoie un SbmCommun\Model\Db\ObjectDataInterface ou false s'il n'est pas trouvé.
     * Renvoi false si email est vide.
     *
     * @param string $email            
     *
     * @return boolean|\Zend\Db\ResultSet\object
     */
    public function getRecordByEmail($email)
    {
        if (empty($email)) {
            return false;
        }
        $resultset = $this->fetchAll(array(
            'email' => $email
        ));
        return $resultset->current();
    }

    /**
     * Renvoie un SbmCommun\Model\Db\ObjectDataInterface ou false s'il n'est pas trouvé.
     * Renvoie false si l'un des paramètres est vide.
     *
     * @param string $nom            
     * @param string $prenom            
     * @param string $telephone
     *            On cherche s'il y a une correspondance avec telephoneF ou telephoneP ou telephoneT
     *            
     * @return boolean|\Zend\Db\ResultSet\object
     */
    private function getRecordByNomPrenomTelephone($nom, $prenom, $telephone)
    {
        if (empty($nom) || empty($prenom) || empty($telephone)) {
            return false;
        }
        $resultset = $this->fetchAll(array(
            'nom' => $nom,
            'prenom' => $prenom,
            'telephoneF' => $telephone
        ));
        if (empty($resultset)) {
            $resultset = $this->fetchAll(array(
                'nom' => $nom,
                'prenom' => $prenom,
                'telephoneP' => $telephone
            ));
        }
        if (empty($resultset)) {
            $resultset = $this->fetchAll(array(
                'nom' => $nom,
                'prenom' => $prenom,
                'telephoneT' => $telephone
            ));
        }
        return $resultset->current();
    }

    /**
     * Renvoie un SbmCommun\Model\Db\ObjectDataInterface ou false s'il n'est pas trouvé.
     * Renvoie false si l'un des paramètres est vide.
     *
     * @param string $nom            
     * @param string $prenom            
     * @param string $adresse
     *            On cherche s'il y a une correspondance avec adresseL1 ou avec adresseL2
     * @param string $communeId            
     *
     * @return boolean|\Zend\Db\ResultSet\object
     */
    private function getRecordByNomPrenomAdresse($nom, $prenom, $adresse, $communeId)
    {
        if (empty($nom) || empty($prenom) || empty($adresse) || empty($communeId)) {
            return false;
        }
        $resultset = $this->fetchAll(array(
            'nom' => $nom,
            'prenom' => $prenom,
            'adresseL1' => $adresse,
            'communeId' => $communeId
        ));
        if (empty($resultset)) {
            $resultset = $this->fetchAll(array(
                'nom' => $nom,
                'prenom' => $prenom,
                'adresseL2' => $adresse,
                'communeId' => $communeId
            ));
        }
        return $resultset->current();
    }
}