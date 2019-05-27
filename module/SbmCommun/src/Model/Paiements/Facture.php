<?php
/**
 * Objet de manipulation d'une facture
 *
 * La création de l'objet va examiner les sommes dues et rechercher si une facture de la table
 * présente la même signature pour le même millesime et le même responsableId. Si c'est le cas,
 * elle vérifie que les resultats (SbmCommun\Model\Paiements\Resultats) sont les mêmes sinon
 * elle crée une nouvelle facture avec le numéro séquentiel suivant.
 *
 * @project sbm
 * @package SbmCommun/src/Model/Paiements
 * @filesource Facture.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Paiements;

use SbmBase\Model\DateLib;
use SbmBase\Model\Session;
use Zend\Db\Sql\Where;

class Facture
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager;
     */
    private $db_manager;

    /**
     *
     * @var Resultats
     */
    private $resultats;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\Table\Factures
     */
    private $tFactures;

    /**
     *
     * @var \SbmCommun\Model\Db\ObjectData\Facture;
     */
    private $oFacture;

    /**
     *
     * @var array
     */
    private $facturesPrecedentes;

    /**
     *
     * @var float
     */
    private $montantDejaFacture;

    /**
     * Attention, ne pas utiliser directement !!! Appeler la méthode getNonveauNumero()
     *
     * @var int
     */
    private $_nouveau_numero;

    /**
     *
     * @return \SbmCommun\Model\Paiements\Resultats
     */
    public function getResultats()
    {
        return $this->resultats;
    }

    /**
     *
     * @return \SbmCommun\Model\Db\ObjectData\Facture;
     */
    public function getOFacture()
    {
        return $this->oFacture;
    }

    /**
     *
     * @return int
     */
    public function getResponsableId()
    {
        return $this->oFacture->responsableId;
    }

    /**
     *
     * @return number
     */
    public function getMillesime()
    {
        return $this->oFacture->millesime;
    }

    /**
     *
     * @return number
     */
    public function getExercice()
    {
        return $this->oFacture->exercice;
    }

    /**
     * Renvoie la date au format français
     *
     * @return string
     */
    public function getDate()
    {
        return $this->oFacture->date;
    }

    /**
     *
     * @return number
     */
    public function getNumero()
    {
        return $this->oFacture->numero;
    }

    /**
     *
     * @return float
     */
    public function getMontant()
    {
        return $this->oFacture->montant;
    }

    /**
     *
     * @return array
     */
    public function getFacturesPrecedentes()
    {
        return $this->facturesPrecedentes;
    }

    /**
     *
     * @return number
     */
    public function getMontantDejaFacture()
    {
        return $this->montantDejaFacture;
    }

    public function __construct($dbManager, $resultats)
    {
        $this->db_manager = $dbManager;
        $this->resultats = $resultats;
        $this->montantDejaFacture = 0;
        $this->facturesPrecedentes = [];
        $this->_nouveau_numero = 0;
        $this->tFactures = $dbManager->get('Sbm\Db\Table\Factures');
        $this->oFacture = $this->tFactures->getObjData();
        $this->oFacture->exchangeArray(
            [
                'millesime' => Session::get('millesime'),
                'exercice' => date('Y'),
                'responsableId' => $this->resultats->getResponsableId(),
                'date' => DateLib::todayToMysql(),
                'montant' => 0
            ]);
    }

    /**
     * Si on veut facturer, la facture sera créée à partir de la propriété 'resultats'
     * lorsque la signature de ce resultats n'est pas la même que la signature de la
     * dernière facture pour ce débiteur. Pour obtenir les facturesPrecedentes on se
     * contente de rechercher les factures de numéro antérieur à celle qu'on doit sortir.
     * En même temps on met à jour la propriété 'montantDejaFacture'
     *
     * @return \SbmCommun\Model\Paiements\Facture
     */
    public function facturer()
    {
        $this->facturesPrecedentes = [];
        $this->montantDejaFacture = 0;
        // try {
        $rowset = $this->tFactures->fetchAll(
            [
                'millesime' => $this->getMillesime(),
                'responsableId' => $this->getResponsableId()
            ], [
                'numero DESC'
            ]);
        $creer = true;
        if ($rowset->count()) {
            $derniereFacture = $this->tFactures->derniereFacture($this->getMillesime(),
                $this->getResponsableId());
            if ($derniereFacture &&
                $derniereFacture->signature == $this->getResultats()->signature()) {
                $creer = false;
                $this->oFacture = $derniereFacture;
            }
            foreach ($rowset as $row) {
                if ($creer || $row->numero != $derniereFacture->numero) {
                    $this->facturesPrecedentes[] = [
                        'numero' => $row->exercice . '-' . $row->numero,
                        'date' => $row->date,
                        'montant' => $row->montant
                    ];
                    $this->montantDejaFacture += $row->montant;
                }
            }
        }
        if ($creer) {
            $this->add();
        }
        return $this;
        // } catch (\SbmCommun\Model\Db\Service\Table\Exception\RuntimeException $e) {
        // }
    }

    protected function lire($numero)
    {
        $this->facturesPrecedentes = [];
        $this->montantDejaFacture = 0;
        // try {
        $where = new Where();
        $where->equalTo('millesime', $this->getMillesime())
            ->equalTo('responsableId', $this->getResponsableId())
            ->lessThanOrEqualTo('numero', $numero);
        $rowset = $this->tFactures->fetchAll($where, [
            'numero DESC'
        ]);
        if ($rowset->count()) {
            foreach ($rowset as $row) {
                if ($row->numero == $numero) {
                    $this->oFacture = $row;
                } else {
                    $this->facturesPrecedentes[] = [
                        'numero' => $row->exercice . '-' . $row->numero,
                        'date' => $row->date,
                        'montant' => $row->montant
                    ];
                    $this->montantDejaFacture += $row->montant;
                }
            }
        }
        return $this;
        // } catch (\SbmCommun\Model\Db\Service\Table\Exception\RuntimeException $e) {
        // }
    }

    /**
     * Crée une facture dans la table factures si son montant n'est pas null en ajoutant
     * le résultat dans la table et mets à jour la propriété numero et date
     */
    protected function add()
    {
        $this->oFacture->exchangeArray(
            [
                'exercice' => $this->getExercice(),
                'numero' => $this->getNouveauNumero(),
                'millesime' => $this->getMillesime(),
                'responsableId' => $this->resultats->getResponsableId(),
                'date' => $this->getDate(),
                'montant' => $this->resultats->getMontantTotal() -
                $this->getMontantDejaFacture(),
                'signature' => $this->resultats->signature(),
                'content' => serialize($this->resultats)
            ]);
        if ($this->oFacture->montant) {
            $this->tFactures->saveRecord($this->oFacture);
        }
    }

    /**
     * Donne un nouveau numéro. Le redonne si on l'a déjà recherché.
     *
     * @return int
     */
    private function getNouveauNumero(): int
    {
        if (! $this->_nouveau_numero) {
            $this->_nouveau_numero = $this->tFactures->dernierNumero($this->getExercice()) +
                1;
        }
        return $this->_nouveau_numero;
    }
}