<?php
/**
 * Injection des objets dans Cartes
 * 
 * @project sbm
 * @package SbmGestion/Model/Cartes
 * @filesource PhotosFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 janv. 2019
 * @version 2019-2.4.6
 */
namespace SbmGestion\Model\Photos;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCommun\Model\Db\Service\DbManager;

class PhotosFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! $serviceLocator instanceof DbManager) {
            throw new \Exception(
                __METHOD__ . ' - ServiceLocator incorrect : DbManager attendu.');
        }
        $tLibelles = $serviceLocator->get('Sbm\Db\System\Libelles');
        $result = $tLibelles->fetchAll([
            'nature' => 'NatureCartes'
        ]);
        $codesNatureCartes = [];
        foreach ($result as $obj) {
            $codesNatureCartes[] = $obj->code;
        }
        $array = [
            'dbAdapter' =>  $serviceLocator->getDbAdapter(),
            'tElevesPhotos' => $serviceLocator->get('Sbm\Db\Table\ElevesPhotos'),
            'table_affectations' => $serviceLocator->getCanonicName('affectations', 
                'table'),
            'table_eleves' => $serviceLocator->getCanonicName('eleves', 'table'),
            'table_elevesphotos' => $serviceLocator->getCanonicName('elevesphotos', 'table'),
            'table_services' => $serviceLocator->getCanonicName('services', 'table'),
            'codesNatureCartes' => $this->prepareCodesForWhere($codesNatureCartes)
        ];
        return new Photos($array);
    }

    private function prepareCodesForWhere($array)
    {
        $result = [];
        foreach ($array as $code) {
            $result[$code] = $this->recherche($array, $code);
        }
        return $result;
    }

    private function recherche($array, $i)
    {
        $result = [];
        $imax = count($array) - 1;
        $max = 2 * $array[$imax];
        for ($j = 1; $j < $max; $j ++) {
            if ($j & $i) {
                $result[] = $j;
            }
        }
        return $result;
    }
}