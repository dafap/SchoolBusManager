<?php
/**
 * Interface définissant les constantes pour les categories
 *
 * @project sbm
 * @package SbmAuthentification/Model
 * @filesource CategoriesInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 août 2020
 * @version 2020-2.6.0
 */
namespace SbmAuthentification\Model;

interface CategoriesInterface
{

    const PARENT_ID = 1;

    const ROLE_PARENT = 'parent';

    const ORGANISME_ID = 50;

    const ROLE_ORGANISME = 'organisme';

    const TRANSPORTEUR_ID = 110;

    const ROLE_TRANSPORTEUR = 'transporteur';

    const ETABLISSEMENT_ID = 120;

    const ROLE_ETABLISSEMENT = 120;

    const COMMUNE_ID = 130;

    const ROLE_COMMUNE = 'commune';

    const SECRETARIAT_ID = 200;

    const ROLE_SECRETARIAT = 'secretariat';

    const GESTION_ID = 253;

    const ROLE_GESTION = 'gestion';

    const ADMINISTRATEUR_ID = 254;

    const ROLE_ADMINISTRATEUR = 'admin';

    const SUPER_ADMINISTRATEUR_ID = 255;

    const ROLE_SUPER_ADMINISTRATEUR = 'sadmin';
}