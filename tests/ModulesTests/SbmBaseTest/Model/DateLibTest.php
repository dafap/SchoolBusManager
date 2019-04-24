<?php
/**
 * Test de la classe SbmBase\DateLib
 * 
 * @project sbm
 * @package PHPUnit/Framework/TestCase
 * @filesource DateLibTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 août 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmBaseTest\Model;

use PHPUnit\Framework\TestCase;
use SbmBase\Model\DateLib;
use SbmBase\Model\Exception;

class DateLibTest extends TestCase
{

    public function testDateFromMysqlToMysql()
    {
        $date = DateLib::todayToMysql();
        $this->assertEquals($date,
            DateLib::formatDateToMysql(DateLib::formatDateFromMysql($date)));
        $dt = DateLib::nowToMysql();
        list ($date, $time) = explode(' ', $dt);
        $this->assertEquals($date,
            DateLib::formatDateToMysql(DateLib::formatDateFromMysql($dt)));
    }

    public function testDateTimeFromMysqlToMysql()
    {
        $dt = DateLib::nowToMysql();
        $this->assertEquals($dt,
            DateLib::formatDateTimeToMysql(DateLib::formatDateTimeFromMysql($dt)));
        $date = DateLib::todayToMysql();
        $this->assertEquals("$date 00:00:00",
            DateLib::formatDateTimeToMysql(DateLib::formatDateTimeFromMysql($date)));
    }

    public function testDateToMysqlFromMysql()
    {
        $date = DateLib::today();
        $this->assertEquals($date,
            DateLib::formatDateFromMysql(DateLib::formatDateToMysql($date)));
        $dt = DateLib::now();
        list ($date, $time) = explode(' ', $dt);
        $this->assertEquals($date,
            DateLib::formatDateFromMysql(DateLib::formatDateToMysql($dt)));
    }

    public function testDateTimeToMysqlFromMysql()
    {
        $dt = DateLib::now();
        $this->assertEquals($dt,
            DateLib::formatDateTimeFromMysql(DateLib::formatDateTimeToMysql($dt)));
        $date = DateLib::today();
        $this->assertEquals("$date 00:00:00",
            DateLib::formatDateTimeFromMysql(DateLib::formatDateTimeToMysql($date)));
    }

    public function testErrorFormatDateFromMysql()
    {
        $date = DateLib::today();
        try {
            $result = DateLib::formatDateFromMysql($date);
            $this->assertTrue(false, 'Aurait du lancer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e);
        }
    }

    public function testErrorFormatDateTimeFromMysql()
    {
        $date = DateLib::now();
        try {
            $result = DateLib::formatDateTimeFromMysql($date);
            $this->assertTrue(false, 'Aurait du lancer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e);
        }
    }

    public function testErrorFormatDateToMysql()
    {
        $date = DateLib::todayToMysql();
        try {
            $result = DateLib::formatDateToMysql($date);
            $this->assertTrue(false, 'Aurait du lancer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e);
        }
    }

    public function testErrorFormatDateTimeToMysql()
    {
        $date = DateLib::nowToMysql();
        try {
            $result = DateLib::formatDateTimeToMysql($date);
            $this->assertTrue(false, 'Aurait du lancer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e);
        }
    }
}