<?php
/**
 * Vérifie qu'il s'agit de l'identifiant d'une table ou d'une vue Sql ou d'une chaine Sql
 *
 * @project sbm
 * @package SbmPdf/Model/Validator
 * @filesource RecordSource.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 fév. 2019
 * @version 2019-2.4.7
 */
namespace SbmPdf\Model\Validator;

use SbmPdf\Model\QuerySourceTrait;								  
use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;
use SbmBase\Model\Session;

class RecordSource extends AbstractValidator
{
	use QuerySourceTrait;					 

    /**
     * Error constants
     */
    const ERROR_BAD_QUERY = 'badQuery';

    /**
     *
     * @var array Message templates
     */
    protected $messageTemplates = [
        self::ERROR_BAD_QUERY => "Ce n'est ni l'identifiant d'une table ou d'une vue, ni une requête Sql\n%msg%\n%sql%"
    ];

    protected $messageVariables = [
        'msg' => 'msg',
        'sql' => 'sql'
    ];

    protected $msg;

    /**
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $auth_userId;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    public function __construct(array $options)
    {
        $this->auth_userId = $options['auth_userId'];
        $this->db_manager = $options['db_manager'];
        parent::__construct($options);
    }

    /**
     * Valide si $value est une clé du tableau de TrecordSource ou si c'est le Sql d'une requête
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Validator\ValidatorInterface::isValid()
     */
    public function isValid($value)
    {
        $this->setValue($value);
        if (array_key_exists($value, $this->db_manager->getTableAliasList())) {
            // il s'agit d'une table ou d'une vue enregistrée dans le service manager
            return true;
        }
        // vérifie qu'il s'agit d'une requête Sql
        try {
            // remplacement des variables éventuelles : %millesime%, %date%, %heure% et %userId%
            // et des opérateurs %gt%, %gtOrEq%, %lt%, %ltOrEq%, %ltgt%, %notEq%
            $value = $this->decodeSource($value, $this->auth_userId);
            $this->db_manager->getDbAdapter()->query($value, 
                \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            return true;
        } catch (\PDOException $e) {
            $this->msg = $e->getMessage();
            $this->sql = $value;
            $this->error(self::ERROR_BAD_QUERY);
            return false;
        }
    }
}