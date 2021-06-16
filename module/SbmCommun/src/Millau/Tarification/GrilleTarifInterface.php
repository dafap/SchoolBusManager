<?php
/**
 * Description de la grille des tarifs
 *
 * @project sbm
 * @package SbmCommun/src/Millau/Tarification
 * @filesource GrilleTarifInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 juin 2021
 * @version 2021-2.5.11
 */
namespace SbmCommun\Millau\Tarification;

interface GrilleTarifInterface
{

    const DP_PLEIN_TARIF = 1;

    const DP_DEMI_TARIF = 2;

    const INTERNE = 3;

    const NON_AYANT_DROIT = 4;

    const DUPLICATA = 5;

    const DEGRESSIF = 1;

    const LINEAIRE = 2;

    // règle de distance pour avoir droit au transport
    const DISTANCE_MINI = 1;

    // règle de résidence pour avoir droit au transport, les autres ayant besoin d'une
    // dérogation
    const COMMUNES = 'membre';
}