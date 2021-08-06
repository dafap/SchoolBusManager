<?php
/**
 * Liste des libellés pour une nature donnée
 *
 * @project sbm
 * @package SbmAdmin/Model/Db/Service/Libelle
 * @filesource Liste.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 août 2021
 * @version 2021-2.5.14
 */
namespace SbmAdmin\Model\Db\Service\Libelle;

use SbmCommun\Model\Db\Exception\ExceptionNoDbManager as Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Liste implements FactoryInterface
{
    use \SbmCommun\Model\Traits\SqlStringTrait;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        return $this;
    }

    public function forNature($nature)
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectNature($nature));
        return $statement->execute();
    }

    protected function selectNature($nature)
    {
        return $this->sql->select()
            ->from([
            'l' => $this->db_manager->getCanonicName('libelles', 'system')
        ])
            ->where([
            'nature' => $nature
        ])
            ->order([
            'code'
        ]);
    }
}
