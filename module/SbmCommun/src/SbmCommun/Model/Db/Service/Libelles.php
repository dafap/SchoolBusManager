<?php
/**
 * Object 'Libelles' qui s'initialise par la lecture de la table 'Libelles' et qui possède une méthode _get()
 * permettant de voir la valeur d'un libellé comme celle d'un attribut 
 *
 * La méthode _get() est définie
 * 
 * @project project_name
 * @package package_name
 * @filesource Libelles.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmBase\Model\StdLib;

class Libelles implements FactoryInterface
{

    private $datas = [];

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $table = $serviceLocator->get('Sbm\Db\System\Libelles');
        $resultset = $table->fetchOpen();
        foreach ($resultset as $row) {
            $this->datas[\mb_strtolower($row->nature, 'utf-8')][$row->code] = mb_strtolower(
                $row->libelle, 'utf-8');
        }
        return $this;
    }

    public function getCode($nature, $libelle)
    {
        $nature = \mb_strtolower($nature, 'utf-8');
        $libelle = \mb_strtolower($libelle, 'utf-8');
        if (\array_key_exists($nature, $this->datas)) {
            $t = \array_flip($this->datas[$nature]);
            if (\array_key_exists($libelle, $t)) {
                return $t[$libelle];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getLibelle($nature, $code)
    {
        $nature = \mb_strtolower($nature, 'utf-8');
        if (StdLib::array_keys_exists(
            [
                $nature,
                $code
            ], $this->datas)) {
            return $this->datas[$nature][$code];
        } else {
            return false;
        }
    }
}