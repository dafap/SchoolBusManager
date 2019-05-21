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
 * @date 21 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmBase\Model\DateLib;
use SbmCommun\Filter\SansAccent;
use SbmCommun\Model\Db\ObjectData\Exception as ExceptionObjectData;
use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;

class Responsables extends AbstractSbmTable
{

    private $lastResponsableId;

    private $filtre_sa;

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
        $this->filtre_sa = new SansAccent();
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
        return $this->saveResponsable($obj_data);
    }

    /**
     *
     * @see \SbmCommun\Model\Db\Service\Table\Responsables
     *
     * @param ObjectDataInterface $obj_data
     * @param boolean $checkDemenagement
     *            false par défaut.
     *            Si true alors on vérifie si l'adresse a changé et on met à jour les
     *            éléments concernant le déménagement si nécessaire.
     *
     * @return boolean Si checkDemenagement alors on renvoie vrai si l'adresse a changé
     *         (adresseL1 ou adresseL2 ou codePostal ou commune).
     *         Si non on renvoie vrai si la commune a changé.
     */
    public function saveResponsable(ObjectDataInterface $obj_data,
        $checkDemenagement = false)
    {
        try {
            $old_data = $this->getRecord($obj_data->getId());
            $is_new = false;
        } catch (Exception\ExceptionInterface $e) {
            try {
                $email = $obj_data->email;
                $nom = $this->filtre_sa->filter($obj_data->nom);
                $prenom = $this->filtre_sa->filter($obj_data->prenom);
                $adresseL1 = $obj_data->adresseL1;
                $adresseL2 = $obj_data->adresseL2;
                $communeId = $obj_data->communeId;
                $telephones = [
                    isset($obj_data->telephoneF) ? $obj_data->telephoneF : null,
                    isset($obj_data->telephoneP) ? $obj_data->telephoneP : null,
                    isset($obj_data->telephoneT) ? $obj_data->telephoneT : null
                ];
                $email = $obj_data->email;
                $old_data = $this->getRecordByEmail($email);
                // $old_data est false si pas trouvé
                if (! $old_data) {
                    $old_data = $this->getRecordByNomPrenomAdresse($nom, $prenom,
                        $adresseL1, $communeId);
                    if (! $old_data) {
                        $old_data = $this->getRecordByNomPrenomAdresse($nom, $prenom,
                            $adresseL2, $communeId);
                        if (! $old_data) {
                            $old_data = $this->getRecordByNomPrenomTelephone($nom, $prenom,
                                $telephones);
                        }
                    }
                }
                $is_new = ! $old_data;
                if (! $is_new) {
                    // dans ce cas, c'est le responsable. On le met à jour.
                    $obj_data_array = $obj_data->getArrayCopy();
                    // Les clés sont responsableId, userId, nature, titre, nom, prenom, titre2,
                    // nom2, prenom2,
                    // adresseL1, adresseL2, codePostal, communeId, telephoneF, telephoneP,
                    // telephoneT, email
                    unset($obj_data_array['responsableId']);
                    $obj_data->exchangeArray(
                        array_merge($old_data->getArrayCopy(), $obj_data_array));
                }
            } catch (ExceptionObjectData\ExceptionInterface $e) {
                $is_new = true;
            }
        }
        if ($is_new) {
            $obj_data->setCalculateFields(
                [
                    'nomSA',
                    'prenomSA',
                    'nom2SA',
                    'prenom2SA',
                    'dateCreation'
                ]);
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
            } catch (ExceptionObjectData\ExceptionInterface $e) {}
            try {
                if ($old_data->prenom != $obj_data->prenom) {
                    $obj_data->addCalculateField('prenomSA');
                }
            } catch (ExceptionObjectData\ExceptionInterface $e) {}
            try {
                if ($old_data->nom2 != $obj_data->nom2) {
                    $obj_data->addCalculateField('nom2SA');
                }
            } catch (ExceptionObjectData\ExceptionInterface $e) {}
            try {
                if ($old_data->prenom2 != $obj_data->prenom2) {
                    $obj_data->addCalculateField('prenom2SA');
                }
            } catch (ExceptionObjectData\ExceptionInterface $e) {}
            if ($checkDemenagement) {
                try {
                    $demenagement = $old_data->adresseL1 != $obj_data->adresseL1;
                    $demenagement |= $old_data->adresseL2 != $obj_data->adresseL2;
                    $demenagement |= $old_data->codePostal != $obj_data->codePostal;
                    $demenagement |= $old_data->communeId != $obj_data->communeId;
                    if ($demenagement) {
                        $dataDemenagement = [
                            'demenagement' => 1,
                            'dateDemenagement' => DateLib::todayToMysql(),
                            'ancienAdresseL1' => $old_data->adresseL1,
                            'ancienAdresseL2' => $old_data->adresseL2,
                            'ancienCodePostal' => $old_data->codePostal,
                            'ancienCommuneId' => $old_data->communeId
                        ];
                        $obj_data->exchangeArray(
                            array_merge($obj_data->getArrayCopy(), $dataDemenagement));
                    }
                    $changeCommuneId = $demenagement;
                } catch (ExceptionObjectData\ExceptionInterface $e) {
                    $changeCommuneId = false;
                }
            } else {
                try {
                    $changeCommuneId = $old_data->communeId != $obj_data->communeId;
                } catch (ExceptionObjectData\ExceptionInterface $e) {
                    $changeCommuneId = false;
                }
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
        $oData->exchangeArray(
            [
                'responsableId' => $responsableId,
                'selection' => $selection
            ]);
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
     *
     * @return int
     */
    public function changeEmail($email_old, $email_new)
    {
        return $this->table_gateway->update([
            'email' => $email_new
        ], [
            'email' => $email_old
        ]);
    }

    /**
     * Renvoie le `nom prénom` du responsable, éventuellement précédé du `titre`
     *
     * @param int $responsableId
     *            référence du respondable
     * @param bool $with_titre
     *            indique si le titre doit être mis ou non
     *
     * @return string
     */
    public function getNomPrenom($responsableId, $with_titre = false)
    {
        $record = $this->getRecord($responsableId);
        return ($with_titre ? $record->titre . ' ' : '') . $record->nom . ' ' .
            $record->prenom;
    }

    /**
     * Renvoie un SbmCommun\Model\Db\ObjectDataInterface ou false s'il n'est pas trouvé.
     * Renvoi false si email est vide.
     *
     * @param string $email
     *
     * @return boolean|\SbmCommun\Model\Db\ObjectData\Responsable
     */
    public function getRecordByEmail($email)
    {
        if (empty($email)) {
            return false;
        }
        $resultset = $this->fetchAll([
            'email' => $email
        ]);
        return $resultset->current();
    }

    /**
     * Renvoie un SbmCommun\Model\Db\ObjectDataInterface ou false s'il n'est pas trouvé.
     * Renvoie false si l'un des paramètres est vide.
     *
     * @param string $nom
     * @param string $prenom
     * @param string $telephone
     *            On cherche s'il y a une correspondance avec telephoneF ou telephoneP ou
     *            telephoneT
     *
     * @return boolean|\SbmCommun\Model\Db\ObjectData\Responsable
     */
    private function getRecordByNomPrenomTelephone($nom, $prenom, $telephones)
    {
        $telephones = array_filter($telephones);
        if (empty($nom) || empty($prenom) || empty($telephones)) {
            return false;
        }
        $resultset = false;
        foreach ($telephones as $telephone) {
            if (empty($telephone)) {
                continue;
            }
            $resultset = $this->fetchAll(
                [
                    'nomSA' => $nom,
                    'prenomSA' => $prenom,
                    'telephoneF' => $telephone
                ]);
            if ($resultset->count()) {
                break;
            }

            $resultset = $this->fetchAll(
                [
                    'nomSA' => $nom,
                    'prenomSA' => $prenom,
                    'telephoneP' => $telephone
                ]);
            if ($resultset->count()) {
                break;
            }

            $resultset = $this->fetchAll(
                [
                    'nomSA' => $nom,
                    'prenomSA' => $prenom,
                    'telephoneT' => $telephone
                ]);
            if ($resultset->count()) {
                break;
            }

            $resultset = $this->fetchAll(
                [
                    'nom2SA' => $nom,
                    'prenom2SA' => $prenom,
                    'telephoneF' => $telephone
                ]);
            if ($resultset->count()) {
                break;
            }

            $resultset = $this->fetchAll(
                [
                    'nom2SA' => $nom,
                    'prenom2SA' => $prenom,
                    'telephoneP' => $telephone
                ]);
            if ($resultset->count()) {
                break;
            }

            $resultset = $this->fetchAll(
                [
                    'nom2SA' => $nom,
                    'prenom2SA' => $prenom,
                    'telephoneT' => $telephone
                ]);
            if ($resultset->count()) {
                break;
            }
        }
        return $resultset->count() ? $resultset->current() : false;
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
     * @return boolean|\SbmCommun\Model\Db\ObjectData\Responsable
     */
    private function getRecordByNomPrenomAdresse($nom, $prenom, $adresse, $communeId)
    {
        if (empty($nom) || empty($prenom) || empty($adresse) || empty($communeId)) {
            return false;
        }
        $resultset = $this->fetchAll(
            [
                'nomSA' => $nom,
                'prenomSA' => $prenom,
                'adresseL1' => $adresse,
                'communeId' => $communeId
            ]);
        if (! $resultset->count()) {
            $resultset = $this->fetchAll(
                [
                    'nomSA' => $nom,
                    'prenomSA' => $prenom,
                    'adresseL2' => $adresse,
                    'communeId' => $communeId
                ]);
        }
        if (! $resultset->count()) {
            $resultset = $this->fetchAll(
                [
                    'nom2SA' => $nom,
                    'prenom2SA' => $prenom,
                    'adresseL1' => $adresse,
                    'communeId' => $communeId
                ]);
        }
        if (! $resultset->count()) {
            $resultset = $this->fetchAll(
                [
                    'nom2SA' => $nom,
                    'prenom2SA' => $prenom,
                    'adresseL2' => $adresse,
                    'communeId' => $communeId
                ]);
        }
        return $resultset->current();
    }
}