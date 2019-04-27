<?php
/**
 * Objet Iterator donnant un ensemble de factures
 *
 * @project sbm
 * @package SbmCommun/src/Model/Paiements
 * @filesource FactureSet.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Paiements;

use SbmBase\Model\Session;

class FactureSet implements \Iterator, \Countable
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager;
     */
    private $db_manager;

    /**
     *
     * @var int
     */
    private $responsabeleId;

    /**
     *
     * @var \Zend\Db\ResultSet\HydratingResultSet
     */
    private $rowset;

    public function __construct($dbManager, $responsableId)
    {
        $this->db_manager = $dbManager;
        $tFactures = $dbManager->get('Sbm\Db\Table\Factures');
        try {
            $this->rowset = $tFactures->fetchAll(
                [
                    'millesime' => Session::get('millesime'),
                    'responsableId' => $responsableId
                ], [
                    'numero DESC'
                ]);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\RuntimeException $e) {
        }
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
        return new Facture($this->db_manager, $resultats);
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