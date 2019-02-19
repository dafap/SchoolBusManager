<?php
/**
 * ObjectData dérivé de AbtractObjectData pour les tests unitaires
 *
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/TestAsset
 * @filesource ObjectSbmObjectData.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\TestAsset;

use SbmCommun\Model\Db\ObjectData\AbstractObjectData;

class ObjectSbmObjectData extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('testId');
    }

    public function setDataSource($data)
    {
        $this->dataSource = $data;
    }

    public function setIdFieldName($name)
    {
        parent::setIdFieldName($name);
    }
}