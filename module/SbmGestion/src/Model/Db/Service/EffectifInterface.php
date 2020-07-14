<?php
/**
 * Interface pour compter des élèves ou des circuits
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service
 * @filesource EffectifInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service;

interface EffectifInterface
{

    /**
     * Renvoie un tableau des colonnes d'effectifs disponibles. Les possibilités sont
     * :<ul> <li>nombre de demandes</li> <li>nombre d'élèves transportés</li> </ul>
     *
     * @return array
     */
    public function getEffectifColumns();

    /**
     * Renvoie le nom de l'index du tableau des effectifs.
     */
    public function getIdColumn();

    /**
     * Lance l'exécution des calculs pour initialiser la structure Impose de donner une
     * valeur booléenne par défaut dans les classes implémentant cet interface (la valeur
     * par défaut devra être précisée et peut être différente).
     *
     * @param bool $sanspreinscrits
     *
     * @return void
     */
    //public function init(bool $sanspreinscrits = false);
}
