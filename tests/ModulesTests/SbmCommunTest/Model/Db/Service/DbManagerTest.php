<?php
/**
 * Teste la classe SbmCommun\Model\Db\Service\DbManager
 *
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/Db/Service
 * @filesource DbManagerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Db\Service;

use PHPUnit_Framework_TestCase;
use ModulesTests\Bootstrap;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;

class DbManagerTest extends PHPUnit_Framework_TestCase
{

    private $serviceManager;

    public function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
    }

    /**
     * Pour créer un db_manager il faut que la config de l'application contienne :<ul>
     * <li>une section <b>'db'</b> contenant le paramétrage de la connexion à la base de données ;</li>
     * <li>dans cette section, une clé <b>'prefix'</b> contenant une chaine de caractères ou vide ;</li>
     * <li>dans la section <i>service_manager</i> un factory dont l'alias est <b>'Zend\Db\Adapter\Adapter'</b>.<br>
     * En général, il est déclaré dans la configuration de l'application <i>`config/autoload`</i> ;</li>
     * <li>une section<b>'db_manager'</b> structurée en tableau associatif avec les clés <b>'invokables'</b>, <b>'factories'</b>, etc.</li></ul>
     * Ensuite, il faut que :<ul>
     * <li>la clé <b>'Sbm\DbManager'</b> soit déclarée en section <b>'factories'</b> de <b>'service_manager'</b>.</li>
     * <li>cette clé soit associée à la classe <b>DbManager</b>.</li></ul>
     */
    public function testConfigDbManager()
    {
        $config_application = $this->serviceManager->get('Config');
        $this->assertTrue(isset($config_application['db']), 
            'Config incorrecte : manque la section \'db\'.');
        $this->assertTrue(isset($config_application['db']['prefix']), 
            'Config incorrecte : manque la clé \'prefix\' dans la section \'db\'.');
        $this->assertTrue(isset($config_application['service_manager']), 
            'Config incorrecte : manque la section \'service_manager\' et la config de la base de données.');
        $this->assertTrue(
            isset(
                $config_application['service_manager']['factories']['Zend\Db\Adapter\Adapter']), 
            'Config incorrecte : manque la config de la base de données.');
        $this->assertTrue(isset($config_application['db_manager']), 
            'Config incorrecte : pas de configuration du <i>db_manager</i>.');
        $this->assertTrue(
            isset($config_application['service_manager']['factories']['Sbm\DbManager']), 
            'Config incorrecte : la clé \'Sbm\DbManager\' n\'est pas déclarée dans service_manager[\'factories\'].');
        $this->assertEquals(DbManager::class, 
            $config_application['service_manager']['factories']['Sbm\DbManager'], 
            'Config incorrecte : \'DbManager\' est mal déclarée dans le service_manager.');
    }

    public function testCreateService()
    {
        $this->assertInstanceOf('SbmCommun\Model\Db\Service\DbManager', 
            $this->serviceManager->get('Sbm\DbManager'), 'Echec de création du DbManager.');
    }

    public function testGetCanonicName()
    {
        /* prefix vide */
        $dbManager = new DbManager();
        $this->assertEquals('', $dbManager->getPrefix());
        // table
        $table_test = 'maTableTest';
        $expected = "t_$table_test";
        $this->assertEquals($expected, $dbManager->getCanonicName($table_test, 'table'));
        // vue
        $expected = "v_$table_test";
        $this->assertEquals($expected, $dbManager->getCanonicName($table_test, 'vue'));
        // system
        $expected = "s_$table_test";
        $this->assertEquals($expected, $dbManager->getCanonicName($table_test, 'system'));
        
        /* prefix utilisé dans la configuration de développement */
        $dbManager = $this->serviceManager->get('Sbm\DbManager');
        $prefix = $dbManager->getPrefix();
        if ($prefix) {
            // table
            $table_test = 'maTableTest';
            $expected = $prefix . "_t_$table_test";
            $this->assertEquals($expected, 
                $dbManager->getCanonicName($table_test, 'table'));
            // vue
            $expected = $prefix . "_v_$table_test";
            $this->assertEquals($expected, $dbManager->getCanonicName($table_test, 'vue'));
            // system
            $expected = $prefix . "_s_$table_test";
            $this->assertEquals($expected, 
                $dbManager->getCanonicName($table_test, 'system'));
        }
    }

    public function testExistsTable()
    {
        $dbManager = $this->serviceManager->get('Sbm\DbManager');
        $tables = $dbManager->getTableNames();
        
        // table qui existe
        if (count($tables)) {
            $table_test = 'communes';
            $this->assertTrue($dbManager->existsTable($table_test, 'table'), 
                'Pourtant la table `communes` existe !');
        }
        // table qui n'existe pas
        $table_test = 'maTableTest';
        $this->assertFalse($dbManager->existsTable($table_test, 'table'), 
            '`maTableTest` n\'existe pas !');
    }

    public function testGetDbAdapter()
    {
        $dbManager = $this->serviceManager->get('Sbm\DbManager');
        $this->assertInstanceOf('Zend\Db\Adapter\Adapter', $dbManager->getDbAdapter(), 
            'Le dbAdapter n\'a pas ete trouve.');
    }

    public function testGetMaxLengthArrayNonExistsTable()
    {
        $dbManager = $this->serviceManager->get('Sbm\DbManager');
        $except = false;
        try {
            $result = $dbManager->getMaxLengthArray('maTableTest', 'table');
        } catch (\SbmCommun\Model\Db\Exception $e) {
            $except = true;
        }
        $this->assertTrue($except, 
            'Aurait du provoquer une SbmCommun\Model\Db\Exception !');
    }

    public function testGetColumnsNonExistsTable()
    {
        $dbManager = $this->serviceManager->get('Sbm\DbManager');
        $except = false;
        try {
            $result = $dbManager->getColumns('maTableTest', 'table');
        } catch (\SbmCommun\Model\Db\Exception $e) {
            $except = true;
        }
        $this->assertTrue($except, 
            'Aurait du provoquer une SbmCommun\Model\Db\Exception !');
    }

    public function testGetColumnDefaultsNonExistsTable()
    {
        $dbManager = $this->serviceManager->get('Sbm\DbManager');
        $except = false;
        try {
            $result = $dbManager->getColumnDefaults('maTableTest', 'table');
        } catch (\SbmCommun\Model\Db\Exception $e) {
            $except = true;
        }
        $this->assertTrue($except, 
            'Aurait du provoquer une SbmCommun\Model\Db\Exception !');
    }

    public function testGetAreNullableColumnsNonExistsTable()
    {
        $dbManager = $this->serviceManager->get('Sbm\DbManager');
        $except = false;
        try {
            $result = $dbManager->getAreNullableColumns('maTableTest', 'table');
        } catch (\SbmCommun\Model\Db\Exception $e) {
            $except = true;
        }
        $this->assertTrue($except, 
            'Aurait du provoquer une SbmCommun\Model\Db\Exception !');
    }

    public function testHasPrimaryKeyNonExistsTable()
    {
        $dbManager = $this->serviceManager->get('Sbm\DbManager');
        $except = false;
        try {
            $result = $dbManager->hasPrimaryKey('maTableTest', 'table');
        } catch (\SbmCommun\Model\Db\Exception $e) {
            $except = true;
        }
        $this->assertTrue($except, 
            'Aurait du provoquer une SbmCommun\Model\Db\Exception !');
    }

    public function testIsAutoIncrementNonExistsTable()
    {
        $dbManager = $this->serviceManager->get('Sbm\DbManager');
        $except = false;
        try {
            $result = $dbManager->isAutoIncrement('maTableTestId', 'maTableTest', 'table');
        } catch (\SbmCommun\Model\Db\Exception $e) {
            $except = true;
        }
        $this->assertTrue($except, 
            'Aurait du provoquer une SbmCommun\Model\Db\Exception !');
    }

    public function testIsAutoIncrementNonExistsColumn()
    {
        $dbManager = $this->serviceManager->get('Sbm\DbManager');
        $except = false;
        try {
            $result = $dbManager->isAutoIncrement('prenom', 'communes', 'table');
        } catch (\SbmCommun\Model\Db\Exception $e) {
            $except = true;
        }
        $this->assertTrue($except, 
            'Aurait du provoquer une SbmCommun\Model\Db\Exception !');
    }

    public function testIsColumnNonExistsTable()
    {
        $dbManager = $this->serviceManager->get('Sbm\DbManager');
        $except = false;
        try {
            $result = $dbManager->isColumn('maTableTestId', 'maTableTest', 'table');
        } catch (\SbmCommun\Model\Db\Exception $e) {
            $except = true;
        }
        $this->assertTrue($except, 
            'Aurait du provoquer une SbmCommun\Model\Db\Exception !');
    }

    public function testIsDateTimeColumnNonExistsTable()
    {
        $dbManager = $this->serviceManager->get('Sbm\DbManager');
        $except = false;
        try {
            $result = $dbManager->isDateTimeColumn('maDate', 'maTableTest', 'table');
        } catch (\SbmCommun\Model\Db\Exception $e) {
            $except = true;
        }
        $this->assertTrue($except, 
            'Aurait du provoquer une SbmCommun\Model\Db\Exception !');
    }

    public function testIsNumericColumnNonExistsTable()
    {
        $dbManager = $this->serviceManager->get('Sbm\DbManager');
        $except = false;
        try {
            $result = $dbManager->isNumericColumn('maDate', 'maTableTest', 'table');
        } catch (\SbmCommun\Model\Db\Exception $e) {
            $except = true;
        }
        $this->assertTrue($except, 
            'Aurait du provoquer une SbmCommun\Model\Db\Exception !');
    }

    public function testIsTable()
    {
        $dbManager = $this->serviceManager->get('Sbm\DbManager');
        $this->assertTrue($dbManager->isTable('classes', 'table'), 
            'La table `classes` existe donc on aurait du recevoir true.');
        $this->assertFalse($dbManager->isTable('classes', 'vue'), 
            'La vue `classes` n\'est pas une table donc on aurait du recevoir false.');
        $this->assertFalse($dbManager->isTable('maTableTest', 'table'), 
            '`maTableTest` n\'existe pas donc on aurait du recevoir false.');
    }
}