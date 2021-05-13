<?php
/**
 * Vérifie s'il ne reste pas des sommes dues de l'année antérieure
 *
 * @project sbm
 * @package
 * @filesource VerifSoldeAnterieur.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmParent\Model;

use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Arlysere\Tarification\Facture\Calculs;
use SbmCommun\Arlysere\Tarification\Facture\Resultats;

class VerifSoldeAnterieur
{

    /**
     *
     * @var DbManager
     */
    // private $db_manager;

    /**
     *
     * @var Calculs
     */
    private $oCalculs;

    /**
     *
     * @var int
     */
    private $responsableId;

    /**
     *
     * @var array
     */
    private $paiements;

    public function __construct(DbManager $db_manager, int $responsableId)
    {
        // $this->db_manager = $db_manager;
        $this->responsableId = $responsableId;
        $this->oCalculs = $db_manager->get('Sbm\Facture\Calculs');
    }

    /**
     * Le compte du respondable n'est pas en règle si pour le millesime indiqué
     * il y a eu des paiements annulés ou il y a eu des duplicatas
     * et si le solde restant dû est strictement positif
     *
     * @param int $millesime
     * @return bool
     */
    public function valid(int $millesime): bool
    {
        $this->oCalculs->setMillesime($millesime);
        $resultats = $this->oCalculs->getResultats($this->responsableId);
        $this->paiements = $resultats->getPaiementsDetail();
        if (! $this->hasAnnulations() && $resultats->getNbDuplicatas() == 0) {
            return true;
        }
        return $resultats->getSolde() <= 0;
    }

    /**
     *
     * @param int $millesime
     * @return array
     */
    public function getResultats(int $millesime): Resultats
    {
        return $this->oCalculs->setMillesime($millesime)->getResultats(
            $this->responsableId);
    }

    /**
     * Efface le résultat dans les structures imbriquées
     */
    public function clear()
    {
        $this->oCalculs->clearResultats();
    }

    private function hasAnnulations()
    {
        $annulations = false;
        foreach ($this->paiements as $paiement) {
            if ($paiement['mouvement'] == 0) {
                $annulations = true;
                break;
            }
        }
        return $annulations;
    }
}