<?php
/**
 * Objet de gestion des enregistrements de la table système `libelles`
 *
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmCommun\Model\Db\ObjectData\Sys
 * @filesource Libelle.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 sept.2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Db\ObjectData\Sys;

use SbmCommun\Model\Db\ObjectData\AbstractObjectData;
use Zend\I18n\Validator\Alnum;
use Zend\Validator\Digits;

class Libelle extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName([
            'nature',
            'code'
        ]);
    }

    /**
     * Contrôle si l'id passé en paramètre est une chaine composée de 2 parties nature|code
     * où nature est une chaine alnum et code est un nombre entier positif.
     * (Utilisé pour sécuriser le paramètre id passé par get dans la gestion des libellés)
     *
     * @param mixed $id
     *            $id devrait être une chaine de la forme nature|code sinon la fonction renvoie
     *            faux
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
                $validator = new Alnum();
                if ($validator->isValid($parts[0])) {
                    $validator = new Digits();
                    return $validator->isValid($parts[1]);
                } else {
                    return false;
                }
            }
        }
    }
} 