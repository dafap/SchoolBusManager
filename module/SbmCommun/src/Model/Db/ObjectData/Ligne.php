<?php
/**
 * Objet contenant les données à manipuler pour la table Lignes
 * (à déclarer dans module.config.php)
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Db/ObjectData
 * @filesource Ligne.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 fév. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\ObjectData;

use Zend\I18n\Validator\Alnum;
use Zend\Validator\Digits;

class Ligne extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName([
            'millesime',
            'ligneId'
        ]);
    }

    /**
     * Contrôle si l'id passé en paramètre est une chaine composée de 2 parties millesime|ligneId
     * où millesime est un nombre entier positif et ligneId est une chaine alnum.
     * (Utilisé pour sécuriser le paramètre id passé par get dans la gestion des lignes)
     *
     * @param mixed $id
     *            $id devrait être une chaine de la forme millesime|ligneId sinon la fonction renvoie
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
                $validator = new Digits();
                if ($validator->isValid($parts[0])) {
                    $validator = new Alnum();
                    return $validator->isValid($parts[1]);
                } else {
                    return false;
                }
            }
        }
    }
}