<?php
/**
 * Classe permettant de construire une structure pour les vues des élèves
 *
 * Cette structure sert à afficher les affectations des élèves sur les circuits
 * et à rappeler les affectations de l'année antérieure
 *
 * @project sbm
 * @package SbmCommun/src/Model/View
 * @filesource StructureAffectations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\View;

use SbmBase\Model\DateLib;

class StructureAffectations
{

    public static function get(\Iterator $resultset)
    {
        if ($resultset->count()) {
            $structure = [];
            foreach ($resultset as $affectation) {
                $structure[$affectation['semaine']][$affectation['moment']][$affectation['correspondance']] = [
                    'ligne1Id' => $affectation['ligne1Id'],
                    'sensligne1' => $affectation['sensligne1'],
                    'ordreligne1' => $affectation['ordreligne1'],
                    'station1Id' => $affectation['station1Id'],
                    'station1' => $affectation['station1'],
                    'horaire' => DateLib::formatHoraire($affectation['horaire1']),
                    'ligne2Id' => $affectation['ligne2Id'],
                    'sensligne2' => $affectation['sensligne2'],
                    'ordreligne2' => $affectation['ordreligne2'],
                    'station2Id' => $affectation['station2Id'],
                    'station2' => $affectation['station2']
                ];
            }
            return $structure;
        } else {
            return null;
        }
    }
}