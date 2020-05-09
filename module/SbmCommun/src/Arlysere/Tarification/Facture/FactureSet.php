<?php
/**
 * Objet Iterator donnant un ensemble de factures
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere
 * @filesource FactureSet.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere\Tarification\Facture;

use SbmBase\Model\Session;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FactureSet implements FactoryInterface, \Iterator, \Countable
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var int
     */
    private $millesime;

    /**
     *
     * @var int
     */
    private $responsableId;

    /**
     *
     * @var \Zend\Db\ResultSet\HydratingResultSet
     */
    private $rowset;

    /**
     *
     * @param int $responsableId
     * @return self
     */
    public function setResponsableId(int $responsableId): self
    {
        $this->responsableId = $responsableId;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db_manager = $serviceLocator;
        $this->millesime = Session::get('millesime');
        return $this;
    }

    /**
     *
     * @param int $responsableId
     * @return self
     */
    public function init(int $responsableId): self
    {
        // on s'assure d'abord que la dernière facture a été créée
        $this->db_manager->get('Sbm\Facture')
            ->setResponsableId($responsableId)
            ->facturer();
        // on initialise la liste des factures
        $tFactures = $this->db_manager->get('Sbm\Db\Table\Factures');
        try {
            $this->rowset = $tFactures->fetchAll(
                [
                    'millesime' => $this->millesime,
                    'responsableId' => $responsableId
                ], [
                    'numero DESC'
                ]);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\RuntimeException $e) {
        }
        return $this->setResponsableId($responsableId);
    }

    public function next()
    {
        $this->rowset->next();
    }

    public function valid()
    {
        return $this->rowset->valid();
    }

    public function current()
    {
        $objectdataFacture = $this->rowset->current();
        $resultats = unserialize($objectdataFacture->content);
        // ATTENTION !!! Il faut un clone pour un autre objet avec le même responsableId
        $facture = (clone $this->db_manager->get('Sbm\Facture'))->setResultats($resultats);
        return $facture->lire($objectdataFacture->numero);
    }

    public function rewind()
    {
        $this->rowset->rewind();
    }

    public function count()
    {
        return $this->rowset->count();
    }

    public function key()
    {
        return $this->rowset->key();
    }
}