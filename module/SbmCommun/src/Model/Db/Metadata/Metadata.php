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
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Db\Metadata;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Metadata\Metadata as ZendMetadata;

class Metadata extends ZendMetadata
{

    /**
     * Surcharge du constructeur
     *
     * @param Adapter $adapter
     *
     * @return \Zend\Db\Metadata\Source\AbstractSource
     *
     * @see \Zend\Db\Metadata\Metadata
     */
    public function __construct(Adapter $adapter)
    {
        $this->source = Source\Factory::createSourceFromAdapter($adapter);
    }
}