<?php
/**
 * Remplace la classe Zend\Db\Sql\Ddl\CreateTable pour traiter le cas
 * spécifique de CREATE [TEMPORY] TABLE [IF NOT EXISTS] nom LIKE table_reference;
 *
 *
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Sql/Ddl
 * @filesource CreateLike.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Model\Db\Sql\Ddl;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\AbstractSql;

class CreateLike extends AbstractSql
{

    const SPECIFICATION = 'CREATE %3$sTABLE %4$s%1$s LIKE %2$s';

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
     * @var string
     */
    protected $likeTable;

    /**
     *
     * @param string $table
     *            Nom de la table à créer
     * @param string $likeTable
     *            Table de référence pour la construction des colonnes, des index et des
     *            contraintes
     * @param array $options
     *            Indique si c'est une table temporaire et si on ajoute 'If Not Exists'
     */
    public function __construct(string $table = '', string $likeTable = '',
        array $options = [])
    {
        $this->setTable($table)
            ->setLikeTable($likeTable)
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
     * @param string $likeTable
     * @return self
     */
    public function setLikeTable(string $likeTable): self
    {
        $this->likeTable = $likeTable;
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
     */
    public function setOption(string $option, bool $attribute)
    {
        $this->{$option} = $attribute;
    }

    /**
     *
     * {@inheritDoc}
     * @see \Zend\Db\Sql\AbstractSql::buildSqlString()
     */
    public function buildSqlString(PlatformInterface $platform,
        DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        $table = $this->resolveTable($this->table, $platform);
        $likeTable = $this->resolveTable($this->likeTable, $platform);
        $temporary = $this->isTemporary ? 'TEMPORARY ' : '';
        $ifNotExists = $this->ifNotExists ? 'IF NOT EXISTS ' : '';
        return sprintf(self::SPECIFICATION, $table, $likeTable, $temporary, $ifNotExists);
    }
}