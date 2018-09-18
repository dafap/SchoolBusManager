<?php
/**
 * Objet contenant les données à manipuler pour la table Eleves
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Eleve.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 juil. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\ObjectData;

class Eleve extends AbstractObjectData
{

    const BASE = 99991;
 // un nombre premier proche de 100000
    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('eleveId');
    }

    /**
     * Renvoie un numéro d'élève calculé sans controle d'existance.
     * Dans la méthode saveRecord() de la table eleves il faut s'assurer que ce numero est libre avant d'enregistrer.
     *
     * @return number
     */
    public function createNumero()
    {
        $cle = substr($this->nom . str_repeat(' ', 11), 0, 11) . substr($this->prenom . str_repeat(' ', 4), 0, 4);
        $mots = str_split($cle, 2);
        $u = 0;
        foreach ($mots as $mot) {
            $cars = str_split($mot);
            if (count($cars) == 1) {
                $x = ord($mot);
            } else {
                for ($x = 0, $i = 0; $i <= 1; $i ++) {
                    $x *= 256;
                    $x += ord($cars[$i]);
                }
            }
            if (($n = $u + $x * $x) > PHP_INT_MAX) {
                $d = floor($n / self::BASE);
                $n -= $d * self::BASE;
            }
            $u = $n % self::BASE;            
        }
        
        return $u == 0 ? self::BASE : $u;
    }
}