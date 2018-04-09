<?php
/**
 * Classe abstraite pour redéfinir l'hydrator qui permet de tenir à jour 
 * la modification d'un ObjectDataInterface dans une table de la base de donnée.
 *
 * Cette classe abstraite surcharge Zend\Stdlib\Hydrator\ArraySerializable::extract() 
 * afin de prendre en compte les champs calculés.
 * Pour chaque table comprenant des champs calculés, une classe dérivée doit définir 
 * la méthode calculate()
 * 
 * @project sbm
 * @package SbmCommun/Model/Hydrator
 * @filesource AbstractHydrator.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Hydrator;

/*
 * @deprecated use Zend\Stdlib\Hydrator\ArraySerializable;
 */
use Zend\Hydrator\ArraySerializable;

abstract class AbstractHydrator extends ArraySerializable
{

    /**
     * Copie de l'objet qui sera modifiée par calculate() avant extraction
     *
     * @var object qui implemente la methode getArrayCopy()
     */
    protected $object;

    public function extract($object)
    {
        return parent::extract($this->calculate($object));
    }

    /**
     * L'objet doit implémenter la méthode getArrayCopy()
     *
     * @param object $object            
     *
     * @return object
     */
    protected abstract function calculate($object);
}