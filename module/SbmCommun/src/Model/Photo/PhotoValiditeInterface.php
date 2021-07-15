<?php
/**
 * Interface pour la gestion des photos
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Db/Table
 * @filesource ElevesPhotos.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 juil. 2021
 * @version 2021-2.6.3
 */
namespace SbmCommun\Model\Photo;

/**
 *
 * @author alain
 *
 */
interface PhotoValiditeInterface
{

    /**
     * En nombre d'années, validité d'une photo
     *
     * @var integer
     */
    const VALIDITE = 3;
}