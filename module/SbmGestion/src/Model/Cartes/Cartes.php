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
 * @date 28 juil. 2020
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
     * @param int $etatDemande
     * @return int affected rows
     */
    public function nouveauLot(int $millesime, string $dateDebut, int $etatDemande = 2)
    {
        $now = DateLib::nowToMysql();
        for ($cr = 0, $trajet = 1; $trajet <= 2; $trajet ++) {
            $where = new Where();
            $where->equalTo('millesime', $millesime)
                ->lessThan('dateCarteR' . $trajet, $dateDebut)
                ->expression('(demandeR' . $trajet . ' & ?) > 0', $etatDemande)
                ->in('eleveId', $this->selectElevesAvecAffectations($millesime, $trajet));
            $cr += $this->tScolarites->getTableGateway()->update(
                [
                    'dateCarteR' . $trajet => $now
                ], $where);
        }
        return $cr;
    }

    /**
     * Tous les élèves avec une ou plusieurs affectations
     *
     * @param int $millesime
     * @param int $trajet
     * @return \Zend\Db\Sql\Select
     */
    private function selectElevesAvecAffectations(int $millesime, int $trajet)
    {
        // préparation du WHERE
        $where = new Where();
        $where->equalTo('aff.millesime', $millesime)->equalTo('trajet', $trajet);
        // préparation du SELECT DISTINCT
        $select = new Select();
        $select->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
            'eleveId'
        ])
            ->from([
            'aff' => $this->table_affectations
        ])
            ->where($where);
        return $select;
    }
}