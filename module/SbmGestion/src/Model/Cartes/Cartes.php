<?php
/**
 * Méthodes pour gérer les cartes
 *
 *
 * @project sbm
 * @package SbmGestion/Model/Cartes
 * @filesource Cartes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Cartes;

use SbmBase\Model\DateLib;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Cartes
{

    /**
     *
     * @var array
     */
    private $config;

    /**
     * Les éléments du tableau sont les paramètres nécessaires. Ils sont initialisés dans
     * CartesFactory. Chaque paramètre s'obtient comme une propriété de la classe Cartes
     * par la méthode __get()
     *
     * @param array $config
     */
    public function __construct($config)
    {
        if (! is_array($config)) {
            throw new Exception(__METHOD__ . ' - Un tableau est attendu comme paramètre.');
        }
        $this->config = $config;
    }

    /**
     * Renvoie la valeur associée à la clé $param de la propriété $config
     *
     * @param string $param
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function __get($param)
    {
        if (array_key_exists($param, $this->config)) {
            return $this->config[$param];
        }
        $message = sprintf(
            'Le paramètre %s n\'est pas une propriété définie par le CartesFactory.',
            $param);
        throw new Exception($message);
    }

    /**
     * Rechercher les eleveId à marquer Marquer les dateCarteR1 dans scolarites pour les
     * eleveId trouvés
     *
     * @param int $millesime
     * @param string $dateDebut
     *            date au format Y-m-d H:i:s
     * @param int $natureCarte
     */
    public function nouveauLot($millesime, $dateDebut, $natureCarte)
    {
        $now = DateLib::nowToMysql();
        $where = new Where();
        $where->expression('millesime = ?', $millesime)
            ->lessThan('dateCarteR1', $dateDebut)
            ->in('eleveId', $this->selectNouveauLot($millesime, $natureCarte));

        return $this->tScolarites->getTableGateway()->update([
            'dateCarteR1' => $now
        ], $where);
    }

    private function selectNouveauLot($millesime, $naturecarte)
    {
        // préparation du WHERE
        $where = new Where();
        $where->equalTo('millesime', $millesime);
        $or = false;
        $predicate = null;
        foreach ($this->codesNatureCartes[$naturecarte] as $code) {
            if ($or) {
                $predicate->OR;
            } else {
                $predicate = $where->nest;
                $or = true;
            }
            $predicate->equalTo('s1.natureCarte', $code)->OR->equalTo('s2.natureCarte',
                $code);
        }
        if ($or) {
            $predicate->unnest;
        }

        // préparation du SELECT DISTINCT
        $select = new Select();
        $select->columns([
            'eleveId'
        ])
            ->from([
            'aff' => $this->table_affectations
        ])
            ->join([
            's1' => $this->table_services
        ], 'aff.service1Id = s1.serviceId', [])
            ->join([
            's2' => $this->table_services
        ], 'aff.service2Id = s2.serviceId', [], Select::JOIN_LEFT)
            ->where($where)
            ->quantifier(Select::QUANTIFIER_DISTINCT);
        return $select;
    }
}