<?php
/**
 * Objet contenant les données à manipuler pour la table 'zonage-index'
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource ZonageIndex.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 juin 2020
 * @version 2020-2.5.4
 */
namespace SbmCommun\Model\Db\ObjectData;

class ZonageIndex extends AbstractObjectData
{

    private $dictionnaire;

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName([
            'zonageId',
            'communeId',
            'mot'
        ]);
        $this->dictionnaire = [
            "a",
            "à",
            "d",
            "l",
            "de",
            "du",
            "des",
            "et",
            "la",
            "le",
            "les",
            "allee",
            "allée",
            "avenue",
            "boulevard",
            "chemin",
            "cite",
            "cité",
            "esplanade",
            "etablissement",
            "établissement",
            "ferme",
            "hameau",
            "hlm",
            "impasse",
            "lotissement",
            "parc",
            "parking",
            "passage",
            "place",
            "quai",
            "quartier",
            "residence",
            "résidence",
            "rond-point",
            "route",
            "rue",
            "traverse"
        ];
    }

    public function getMotsCles(string $nom): array
    {
        $nom = strtolower($nom);
        $nom = str_replace(
            [
                'lieu dit',
                'lieu-dit',
                'lieudit',
                'rond point',
                'rond-point',
                'rondpoint'
            ], '', $nom);
        $parts1 = explode(' ', $nom);
        $array = [];
        foreach ($parts1 as $mot1) {
            $parts2 = explode("'", $mot1);
            foreach ($parts2 as $mot2) {
                $parts3 = explode('-', $mot2);
                foreach ($parts3 as $value) {
                    if (! in_array($value, $this->dictionnaire)) {
                        $array[] = $value;
                    }
                }
            }
        }
        return $array;
    }
}