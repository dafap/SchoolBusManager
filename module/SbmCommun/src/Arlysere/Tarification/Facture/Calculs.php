<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package SbmCommun/Arlysere/Tarification/Facture
 * @filesource Calculs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere\Tarification\Facture;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;

class Calculs extends AbstractQuery
{

    /**
     *
     * @var Resultats
     */
    private $resultats;

    /**
     * Méthode publique unique permettant de renvoyer un résultat. Si un résultat a déjà
     * été préparé il est repris (sauf si force)
     *
     * @param int $responsableId
     * @param array $aEleveId
     * @param bool $force
     * @return Resultats
     */
    public function getResultats(int $responsableId, array $arrayEleveId = null,
        bool $force = false): Resultats
    {
        if ($force || $this->resultats->isEmpty()) {
            $this->analyse($responsableId, (array) $arrayEleveId);
        }
        return $this->resultats;
    }

    protected function init()
    {
        $this->resultats = new Resultats($this->millesime);
    }

    private function analyse(int $responsableId, array $arrayEleveId)
    {
        ;
    }
}