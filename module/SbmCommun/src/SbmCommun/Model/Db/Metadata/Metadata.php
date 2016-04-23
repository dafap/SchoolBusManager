<?php
/**
 * Extension de la classe Zend\Db\Metadata\Metadata pour récupérer les informations 
 * sur les colonnes auto_increment 
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Metadata
 * @filesource Metadata.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 avr. 2016
 * @version 2016-2
 */
namespace SbmCommun\Model\Db\Metadata;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Metadata\Metadata as ZendMetadata;
use Zend\Db\Metadata\Source as ZendSource;

class Metadata extends ZendMetadata
{

    /**
     * Surcharge du constructeur
     *
     * @param Adapter $adapter            
     * @return ZendSource\AbstractSource
     * @see Zend\Db\Metadata\Metadata
     */
    public function __construct(Adapter $adapter)
    {
        $this->source = Source\Factory::createSourceFromAdapter($adapter);
    }
}