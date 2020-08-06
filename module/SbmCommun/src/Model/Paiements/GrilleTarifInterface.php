<?php
/**
 * Description de la grille des tarifs
 *
 * @project sbm
 * @package SbmCommun/src/Model/Paiements
 * @filesource GrilleTarifInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 août 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Paiements;

interface GrilleTarifInterface
{

    const NORMAL = 0;

    const REDUIT = 1;

    const GRATUIT = 2;

    const TARIF_ARLYSERE = 1;

    const HORS_ARLYSERE = 2;

    const RPI = 3;

    const CARTE_R2 = 4;

    const ABONNEMENT = 0;

    const DUPLICATA = 5;

    const DEGRESSIF = 1;

    const LINEAIRE = 2;

    // règle de distance pour avoir droit au transport
    const DISTANCE_MINI = 1;

    // règle de résidence pour avoir droit au transport, les autres ayant besoin d'une
    // dérogation
    const COMMUNES = 'membre';
}