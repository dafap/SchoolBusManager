<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package
 * @filesource Test.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 sept. 2020
 * @version 2020-2.6.0
 */
namespace SbmFront\Factory\Test\Query;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Expression;

class Test extends \SbmCommun\Model\Db\Service\Query\AbstractQuery
{
    use \SbmCommun\Model\Traits\ExpressionSqlTrait;

    protected function init()
    {
    }

    public function get(Where $where = null)
    {
        return $this->renderResult($this->mySelect($where));
    }

    private function mySelect(Where $where = null): Select
    {
        $oSelect = new \SbmCommun\Arlysere\Itineraire\CollegienDP($this->db_manager);
        die($this->getSqlString($oSelect->selectMatin()));
        return $oSelect->selectMatin();
    }
}