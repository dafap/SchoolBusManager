<?php
/**
 * Calcul de la validité d'une photo
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Eleve
 * @filesource ElevesScolarites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 juin 2021
 * @version 2021-2.5.12
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

/**
 *
 * @author alain
 *
 */
trait ElevePhotoTrait
{


    private function xHasPhoto($dateDebutAS)
    {
        $conditionCreation = '(photos.dateCreation >= DATE_SUB("%1$s", INTERVAL 2 YEAR))';
        $conditionModification = '(photos.dateModification >= DATE_SUB("%1$s", INTERVAL 2 YEAR))';
        $conditionDate = "( $conditionCreation OR $conditionModification)";
        $format = "CASE WHEN ISNULL(photos.eleveId) THEN FALSE ELSE $conditionDate END";
        return sprintf($format, $dateDebutAS);
    }

    private function xSansPhoto($dateDebutAS)
    {
        $conditionCreation = '(photos.dateCreation < DATE_SUB("%1$s", INTERVAL 2 YEAR))';
        $conditionModification = '(photos.dateModification < DATE_SUB("%1$s", INTERVAL 2 YEAR))';
        $conditionDate = "( $conditionCreation AND $conditionModification)";
        $format = "CASE WHEN ISNULL(photos.eleveId) THEN TRUE ELSE $conditionDate END";
        return sprintf($format, $dateDebutAS);
    }
}

