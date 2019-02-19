<?php
/**
 * Injection des objets dans Cartes
 * 
 * @project sbm
 * @package SbmGestion/Model/Cartes
 * @filesource CartesFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Cartes;

use SbmCommun\Model\Db\Service\DbManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CartesFactory implements FactoryInterface
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
            'tScolarites' => $serviceLocator->get('Sbm\Db\Table\Scolarites'),
            'table_affectations' => $serviceLocator->getCanonicName('affectations',
                'table'),
            'table_services' => $serviceLocator->getCanonicName('services', 'table'),
            'codesNatureCartes' => $this->prepareCodesForWhere($codesNatureCartes)
        ];
        return new Cartes($array);
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