<?php
/**
 * Liste des libellés pour une nature donnée
 *
 * @project sbm
 * @package SbmAdmin/Model/Db/Service/Libelle
 * @filesource Liste.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 janv. 2016
 * @version 2016-1.7.1
 */
namespace SbmAdmin\Model\Db\Service\Libelle;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;

class Liste implements FactoryInterface
{
    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbLibService
     */
    private $db;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->dbAdapter = $this->db->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

    public function forNature($nature)
    {
        $select = $this->sql->select();
        $select->from(array(
            'l' => $this->db->getCanonicName('libelles', 'system')
        ))
            ->where(array(
            'nature' => $nature
        ))
            ->order(array(
            'code'
        ));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
}
 