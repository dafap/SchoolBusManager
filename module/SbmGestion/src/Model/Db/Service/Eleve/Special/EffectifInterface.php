<?php
/**
 * Interface spécial pour EffectifCircuits
 *
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Eleve/Special
 * @filesource EffectifInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmGestion\Model\Db\Service\Eleve\Special;

use SbmGestion\Model\Db\Service\EffectifInterface as BaseEffectifInterface;
use Zend\Db\Sql\Where;

interface EffectifInterface extends BaseEffectifInterface
{

    public function init(Where $where);
}