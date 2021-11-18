<?php
/**
 * Outils pour valider ou obtenir la station origine demandée
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere
 * @filesource CtrlOrigine.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 nov. 2021
 * @version 2021-2.6.4
 */
namespace SbmCommun\Arlysere;

use SbmCommun\Model\Db\Service\DbManager;
use SbmBase\Model\Session;

class CtrlOrigine
{

    /**
     *
     * @var DbManager
     */
    private $db_manager;

    private $eleveId;

    private $millesime;

    public function __construct(DbManager $db_manager, int $eleveId, int $millesime = null)
    {
        $this->db_manager = $db_manager;
        $this->eleveId = $eleveId;
        $this->millesime = $millesime ?: Session::get('millesime');
    }

    /**
     * Renvoie l'identifiant de la station demandée
     *
     * @param int $trajet
     *            1 ou 2 selon R1 ou R2
     * @return int
     */
    private function getOrigineIdDemandee(int $trajet): int
    {
        try {
            $scolarite = $this->db_manager->get('Sbm\Db\Table\Scolarites')->getRecord(
                [
                    'millesime' => $this->millesime,
                    'eleveId' => $this->eleveId
                ]);
            return $scolarite->{'stationIdR' . $trajet};
        } catch (\SbmCommun\Model\Db\Exception\ExceptionInterface $e) {
            return - 1;
        }
    }

    /**
     * Renvoie le nom de la station demandée
     *
     * @param int $trajet
     *            1 ou 2 selon R1 ou R2
     * @return string
     */
    public function getOrigineDemandee(int $trajet): string
    {
        try {
            $station = $this->db_manager->get('Sbm\Db\Vue\Stations')->getRecord(
                $this->getOrigineIdDemandee($trajet));
            return $station->lacommune . ' - ' . $station->nom;
        } catch (\SbmCommun\Model\Db\Exception\ExceptionInterface $e) {
            return '';
        }
    }

    /**
     * Renvoie TRUE si la station demandée correspond bien au départ du domicile le matin ; FALSE sinon
     *
     * @param int $trajet
     * @return bool
     */
    public function valid(int $trajet): bool
    {
        $ok = true;
        $resultset = $this->db_manager->get('Sbm\Db\Table\Affectations')->fetchAll(
            [
                'millesime' => $this->millesime,
                'eleveId' => $this->eleveId,
                'trajet' => $trajet,
                'moment' => 1,
                'correspondance' => 1
            ]);
        if ($resultset->count()) {
            $origineId = $this->getOrigineIdDemandee($trajet);
            foreach ($resultset as $affectation) {
                $ok &= $affectation->station1Id == $origineId;
            }
        } else {
            $ok = false;
        }
        return $ok;
    }
}