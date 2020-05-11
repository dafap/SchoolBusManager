<?php
/**
 * Classe des méthodes de marquage des élèves en R1 ou en R2
 *
 * @project sbm
 * @package
 * @filesource MarqueEleves.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere\Paiements;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmBase\Model\Session;

class MarqueEleves implements FactoryInterface
{

    private $db_manager;

    private $millesime;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db_manager = $serviceLocator;
        $this->millesime = Session::get('millesime');
        return $this;
    }

    /**
     *
     * @param array|int $eleveId
     * @param int $responsableId
     * @param bool $check
     */
    public function setPaiement($aEleveId, int $responsableId, bool $check)
    {
        $tEleves = $this->db_manager->get('Sbm\Db\Table\Eleves');
        $aRiEleveId = [
            - 1 => [], // eleveId n'est pas dans la table
            0 => [], // responsableId n'est pas responsable de eleveId
            1 => [], // R1 des élèves
            2 => []
        ];
        foreach ((array) $aEleveId as $eleveId) {
            $aRiEleveId[$tEleves->estResponsable($responsableId, $eleveId)][] = $eleveId;
        }
        $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $tScolarites->setPaiement($this->millesime, $aRiEleveId[1], $check, 1);
        $tScolarites->setPaiement($this->millesime, $aRiEleveId[2], $check, 2);
    }
}