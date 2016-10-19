<?php
/**
 * Hydrator dérivé de SbmCommun\Model\Hydrator\AbstractHydrator qui ne fait rien
 *
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/Hydrator
 * @filesource HydratorNeutre.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\TestAsset;

use SbmCommun\Model\Hydrator\AbstractHydrator;

class HydratorNeutre extends AbstractHydrator
{
    protected function calculate($object)
    {
        return $object;
    }
}