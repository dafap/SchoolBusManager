<?php
/**
 * Remplace la classe Zend\Db\Sql\Ddl\CreateTable pour traiter le cas
 * spécifique de CREATE [TEMPORY] TABLE [IF NOT EXISTS] nom SELECT selectSql;
 * où selectSql est une chaine SQL valide commençant par SELECT
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Sql/Ddl
 * @filesource CreateSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Model\Db\Sql\Ddl;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\AbstractSql;
use Zend\Db\Sql\Select;

class CreateSelect extends AbstractSql
{

    const SPECIFICATION = 'CREATE %3$sTABLE %4$s%1$s %2$s';

    const TEMPORARY = 'isTemporary';

    const IF_NOT_EXISTS = 'ifNotExists';

    /**
     * Table à créer
     *
     * @var string
     */
    protected $table;

    /**
     *
     * @var bool
     */
    protected $isTemporary;

    /**
     *
     * @var bool
     */
    protected $ifNotExists;

    /**
     * Table modèle
     *
     * @var Select
     */
    protected $select;

    /**
     *
     * @param string $table
     *            Nom de la table à créer
     * @param Select $select
     *            Reuête SQL pour la construction de la table et son peuplement
     * @param array $options
     *            Indique si c'est une table temporaire et si on ajoute 'If Not Exists'
     */
    public function __construct(string $table = '', Select $select = null,
        array $options = [])
    {
        $this->setTable($table)
            ->setSelect($select)
            ->setOptions($options);
    }

    /**
     *
     * @param string $table
     * @return self
     */
    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     *
     * @param Select $select
     * @return self
     */
    public function setSelect(Select $select): self
    {
        $this->select = $select;
        return $this;
    }

    /**
     *
     * @param string $options
     * @return self
     */
    public function setOptions(array $options): self
    {
        foreach ($options as $option => $attribute) {
            if ($option == self::TEMPORARY || $option == self::IF_NOT_EXISTS) {
                $this->setOption($option, (bool) $attribute);
            }
        }
        return $this;
    }

    /**
     *
     * @param string $option
     * @param bool $attribute
     * @return self
     */
    public function setOption(string $option, bool $attribute): self
    {
        $this->{$option} = $attribute;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Zend\Db\Sql\AbstractSql::buildSqlString()
     */
    public function buildSqlString(PlatformInterface $platform,
        DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        $table = $this->resolveTable($this->table, $platform);
        $selectSql = $this->select->getSqlString($platform);
        $temporary = $this->isTemporary ? 'TEMPORARY ' : '';
        $ifNotExists = $this->ifNotExists ? 'IF NOT EXISTS ' : '';
        return sprintf(self::SPECIFICATION, $table, $selectSql, $temporary, $ifNotExists);
    }
}