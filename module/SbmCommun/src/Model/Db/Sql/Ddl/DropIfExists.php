<?php
/**
 * Supprime une table si elle existe
 *
 * Cette classe suit le modèle des autres classes de ce dossier. Aussi elle n'est pas une
 * extension de la classe DropTable de Zend.
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Sql/Ddl
 * @filesource DropIfExists.php
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

class DropIfExists extends AbstractSql
{

    const SPECIFICATION = 'DROP TABLE IF EXISTS %s';

    /**
     * Table à créer
     *
     * @var string
     */
    protected $table;

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
    public function __construct(string $table = '')
    {
        $this->setTable($table);
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
     * {@inheritdoc}
     * @see \Zend\Db\Sql\AbstractSql::buildSqlString()
     */
    public function buildSqlString(PlatformInterface $platform,
        DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        $table = $this->resolveTable($this->table, $platform);
        return sprintf(self::SPECIFICATION, $table);
    }
}