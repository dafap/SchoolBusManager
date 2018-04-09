<?php
/**
 * Objet contenant les données à manipuler pour la table EtablissementService
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource EtablissementService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\ObjectData;

use SbmCommun\Model\Validator\CodeEtablissement;
use SbmCommun\Model\Validator\CodeService;

class EtablissementService extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName(
            [
                'etablissementId',
                'serviceId'
            ]);
    }

    /**
     * Contrôle si l'id passé en paramètre est une chaine composée de 2 parties etablissementId|serviceId
     * où etablissementId est une chaine CodeEtablissement et serviceId est une chaine CodeService.
     * (Utilisé pour sécuriser le paramètre id passé par get dans la gestion des libellés)
     *
     * @param string $id
     *            $id devrait être une chaine de la forme etablissementId|serviceId sinon la fonction renvoie faux
     *            
     * @return bool
     */
    public function isValidId($id)
    {
        if (! is_string($id)) {
            return false;
        } else {
            $parts = explode('|', $id);
            if (count($parts) != 2) {
                return false;
            } else {
                $validator = new CodeEtablissement();
                if ($validator->isValid($parts[0])) {
                    $validator = new CodeService();
                    return $validator->isValid($parts[1]);
                } else {
                    return false;
                }
            }
        }
    }
} 