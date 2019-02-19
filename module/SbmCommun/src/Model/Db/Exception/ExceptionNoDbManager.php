<?php
/**
 * Exception lancée par une classe ne trouvant pas de DbManager
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Exception
 * @filesource ExceptionNoDbManager.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Exception;

class ExceptionNoDbManager extends \Exception implements ExceptionInterface
{
} 