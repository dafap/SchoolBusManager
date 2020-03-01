<?php
/**
 * Objet contenant les données à manipuler pour la table EtablissementService
 * (à déclarer dans module.config.php)
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource EtablissementService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 fév. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\ObjectData;

use SbmCommun\Model\Validator\CodeEtablissement;
use SbmCommun\Model\Validator\CodeLigne;

class EtablissementService extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName(
            [
                'etablissementId',
                'millesime',
                'ligneId',
                'sens',
                'moment',
                'ordre'
            ]);
    }

    /**
     * Contrôle si l'id passé en paramètre est une chaine composée de 6 parties
     * etablissementId|millesime|ligneId|sens|moment|ordre où etablissementId est une
     * chaine validée par CodeEtablissement, millesime est un entier, ligneId est une
     * chaine validée par CodeLigne, sens prend les valeurs 1 ou 2, moment prend les
     * valeur 1, 2 ou 3 et ordre est un entier. (Utilisé pour sécuriser le paramètre id
     * passé par get dans la gestion des services)
     *
     * @param string $id
     *            $id devrait être une chaine de la forme
     *            etablissementId|millesime|ligneId|sens|moment|ordre sinon la fonction
     *            renvoie faux
     * @return bool
     */
    public function isValidId($id)
    {
        if (! is_string($id)) {
            return false;
        } else {
            $parts = explode('|', $id);
            if (count($parts) != 6) {
                return false;
            } else {
                $validator = new CodeEtablissement();
                // vérifie le premier élément et dépile $parts par le début
                if ($validator->isValid(array_shift($parts))) {
                    $validator = new CodeLigne();
                    $ok = is_int(array_shift($parts)) &&
                        $validator->isValid(array_shift($parts)) &&
                        in_array(array_shift($parts), [
                            1,
                            2
                        ]) && in_array(array_shift($parts), [
                            1,
                            2,
                            3
                        ]) && is_int(array_shift($parts));
                    return $ok;
                } else {
                    return false;
                }
            }
        }
    }
}