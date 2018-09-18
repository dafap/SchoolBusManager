<?php
/**
 * Vérifie qu'il s'agit de l'identifiant d'une table ou d'une vue Sql ou d'une chaine Sql
 *
 * @project sbm
 * @package SbmPdf/Model/Validator
 * @filesource RecordSource.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 sept. 2018
 * @version 2016-2.4.5
 */
namespace SbmPdf\Model\Validator;

use SbmBase\Model\Session;
use Zend\Validator\AbstractValidator;

class RecordSource extends AbstractValidator
{

    /**
     * Error constants
     */
    const ERROR_BAD_QUERY = 'badQuery';

    /**
     *
     * @var array Message templates
     */
    protected $messageTemplates = [
        self::ERROR_BAD_QUERY => "Ce n'est ni l'identifiant d'une table ou d'une vue, ni une requête Sql\n%msg%"
    ];

    protected $messageVariables = [
        'msg' => 'msg'
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
            $value = str_replace([
                '%date%',
                '%heure%',
                '%millesime%',
                '%userId%'
            ],
                [
                    date('Y-m-d'),
                    date('H:i:s'),
                    Session::get('millesime'),
                    $this->auth_userId
                ], $value);
            $this->db_manager->getDbAdapter()->query($value,
                \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            return true;
        } catch (\PDOException $e) {
            $this->msg = $e->getMessage();
            $this->error(self::ERROR_BAD_QUERY);
            return false;
        }
    }
}