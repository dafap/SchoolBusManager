<?php
/**
 * Vérifie qu'il s'agit de l'identifiant d'une table ou d'une vue Sql ou d'une chaine Sql
 *
 * @project sbm
 * @package SbmPdf/Model/Validator
 * @filesource RecordSource.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 juil. 2015
 * @version 2015-1
 */
namespace SbmPdf\Model\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;
use DafapSession\Model\Session;

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
    protected $messageTemplates = array(
        self::ERROR_BAD_QUERY => "Ce n'est ni l'identifiant d'une table ou d'une vue, ni une requête Sql"
    );

    /**
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $sm;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbLibService
     */
    private $db;

    public function __construct(array $options)
    {
        $this->sm = $options['sm'];
        $this->db = $this->sm->get('Sbm\Db\DbLib');
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
        if (array_key_exists($value, $this->db->getTableAliasList())) {
            // il s'agit d'une table ou d'une vue enregistrée dans le service manager
            return true;
        }
        // vérifie qu'il s'agit d'une requête Sql
        try {
            // remplacement des variables éventuelles : %millesime%, %date%, %heure% et %userId%
            $value = str_replace(array(
                '%date%',
                '%heure%',
                '%millesime%',
                '%userId%'
            ), array(
                date('Y-m-d'),
                date('H:i:s'),
                Session::get('millesime'),
                $this->sm->get('Dafap\Authenticate')
                ->by()
                ->getUserId()
            ), $value);
            $result = $this->db->getDbAdapter()->query($value, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            return true;
        } catch (\PDOException $e) {
            $this->error(self::ERROR_BAD_QUERY);
            return false;
        }
    }
}