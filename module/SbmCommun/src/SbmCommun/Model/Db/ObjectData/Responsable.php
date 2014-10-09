<?php
/**
 * Objet contenant les données à manipuler pour la table Responsables
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Responsable.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 juil. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\ObjectData;

use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\Factory;

class Responsable extends AbstractObjectData
{

    /**
     * Liste des champs calculés à mettre à jour par l'hydrator
     * 
     * @var array of string
     */
    private $calculate_fields = array();

    private $input_filter = array();

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('responsableId');
    }

    public function setCalculateFields($array)
    {
        $this->calculate_fields = $array;
    }

    public function getCalculateFields()
    {
        return $this->calculate_fields;
    }

    public function addCalculateField($str)
    {
        $this->calculate_fields[] = $str;
    }
}