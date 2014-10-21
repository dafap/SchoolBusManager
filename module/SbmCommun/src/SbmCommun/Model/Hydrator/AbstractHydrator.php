<?php
/**
 * Classe abstraite pour redéfinir l'hydrator qui permet de tenir à jour la modification d'un ObjectDataInterface dans une table de la base de donnée.
 *
 * Cette classe abstraite redéfinit la méthode extract d'un Zend\Stdlib\Hydrator\ArraySerializable afin de prendre en compte les champs calculés.
 * Pour chaque table comprenant des champs calculés, une classe dérivée doit définir la méthode calculate()
 * 
 * @project sbm
 * @package package_name
 * @filesource AbstractHydrator.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 oct. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Hydrator;
 
use Zend\Stdlib\Hydrator\ArraySerializable;

abstract class AbstractHydrator extends ArraySerializable
{
    protected  $object;
    
    public function extract($object)
    {
        $this->object = $object;
        $this->calculate();
        $data = $object->getArrayCopy();
        $filter = $this->getFilter();
    
        foreach ($data as $name => $value) {
            if (! $filter->filter($name)) {
                unset($data[$name]);
                continue;
            }
            $extractedName = $this->extractName($name, $object);
            // replace the original key with extracted, if differ
            if ($extractedName !== $name) {
                unset($data[$name]);
                $name = $extractedName;
            }
            $data[$name] = $this->extractValue($name, $value, $object);
        }
    
        return $data;
    }
    
    protected abstract function calculate();
}