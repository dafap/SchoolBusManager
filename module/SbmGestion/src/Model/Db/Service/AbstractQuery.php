<?php
/**
 * Méthodes communes aux classes de ce dossier
 *
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Eleve
 * @filesource AbstractQuery.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 août 2021
 * @version 2021-2.5.14
 */
namespace SbmGestion\Model\Db\Service;

abstract class AbstractQuery
{
    use \SbmCommun\Model\Traits\ArrayToWhereTrait, \SbmCommun\Model\Traits\SqlStringTrait;
}
