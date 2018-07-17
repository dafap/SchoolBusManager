<?php
/**
 * Test de la classe SbmBase\Session
 * 
 * @project sbm
 * @package ModulesTests/SbmBaseTest/Model
 * @filesource SessionTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 août 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmBaseTest\Model;

use PHPUnit\Framework\TestCase;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;

class SessionTest extends TestCase
{

    private $namespace;

    public function setUp()
    {
        $this->namespace = 'azerty';
        Session::set('test', 'valeur', $this->namespace);
    }

    public function testGet()
    {
        $this->assertEquals('valeur', Session::get('test', 'default', $this->namespace));
        $this->assertEquals('default', 
            Session::get('inconnu', 'default', $this->namespace));
    }

    public function testRemove()
    {
        Session::remove('inconnu', $this->namespace);
        Session::remove('test', 'autreNs');
        $this->assertEquals('valeur', Session::get('test', 'default', $this->namespace));
        Session::remove('test', $this->namespace);
        $this->assertEquals('default', Session::get('test', 'default', $this->namespace));
    }
}