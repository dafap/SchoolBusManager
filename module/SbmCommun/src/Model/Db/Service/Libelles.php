<?php
/**
 * Object 'Libelles' qui s'initialise par la lecture de la table 'Libelles'
 * (enregistré dans db_manager sous la clé 'Sbm\Libelles')
 *
 * La méthode __get() donne un tableau [code => libelle]
 * La méthode getCode() donne le code à partir de la nature et du libelle
 * La méthode getLibelle() donne le libelle à partir de la nature et du code
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service
 * @filesource Libelles.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service;

use SbmBase\Model\StdLib;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Libelles implements FactoryInterface
{

    private $datas = [];

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $table = $serviceLocator->get('Sbm\Db\System\Libelles');
        $resultset = $table->fetchOpen();
        foreach ($resultset as $row) {
            $this->datas[mb_strtolower($row->nature, 'utf-8')][$row->code] = mb_strtolower(
                $row->libelle, 'utf-8');
        }
        return $this;
    }

    /**
     * Donne le tableau des [code => libelle] pour une nature donnée.
     * Lance une exception si la nature n'existe pas.
     *
     * @param string $nature
     * @throws \SbmCommun\Model\Db\Exception\DomainException
     * @return array
     */
    public function __get(string $nature)
    {
        $nature = mb_strtolower($nature, 'utf-8');
        if (array_key_exists($nature, $this->datas)) {
            return $this->datas[$nature];
        }
        throw new \SbmCommun\Model\Db\Exception\DomainException('Pas de libellé de cette nature.');
    }

    /**
     * Donne le code connaissant la nature et le libelle
     * Renvoie false si la nature ou le libelle n'existent pas
     *
     * @param string $nature
     * @param string $libelle
     * @return int|boolean
     */
    public function getCode(string $nature, string $libelle)
    {
        $nature = mb_strtolower($nature, 'utf-8');
        $libelle = mb_strtolower($libelle, 'utf-8');
        if (array_key_exists($nature, $this->datas)) {
            $t = array_flip($this->datas[$nature]);
            if (array_key_exists($libelle, $t)) {
                return $t[$libelle];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Donne le libelle connaissant la nature et le code
     * Renvoie false si la nature ou le code n'existent pas
     *
     * @param string $nature
     * @param int $code
     * @return string|boolean
     */
    public function getLibelle(string $nature, int $code)
    {
        $nature = mb_strtolower($nature, 'utf-8');
        if (StdLib::array_keys_exists([
            $nature,
            $code
        ], $this->datas)) {
            return $this->datas[$nature][$code];
        } else {
            return false;
        }
    }
}