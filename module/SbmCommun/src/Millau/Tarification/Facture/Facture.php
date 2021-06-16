<?php
/**
 * Objet de manipulation d'une facture
 *
 * La création de l'objet va examiner les sommes dues et rechercher si une facture de la
 * table
 * présente la même signature pour le même millesime et le même responsableId. Si c'est le
 * cas,
 * elle vérifie que les resultats (SbmCommun\Model\Paiements\Resultats) sont les mêmes
 * sinon
 * elle crée une nouvelle facture avec le numéro séquentiel suivant.
 *
 * @project sbm
 * @package SbmCommun/src/Model/Paiements
 * @filesource Facture.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 juin 2021
 * @version 2021-4.5.11
 */
namespace SbmCommun\Millau\Tarification\Facture;

use SbmBase\Model\DateLib;
use SbmBase\Model\Session;
use SbmCommun\Model\Db\ObjectData\Facture as ObjectDataFacture;
use SbmCommun\Model\Paiements\FactureInterface;
use SbmCommun\Model\Paiements\ResultatsInterface;
use Zend\Db\Sql\Where;

class Facture implements FactureInterface
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
     * Attention, ne pas utiliser directement !!! Appeler la méthode getNouveauNumero()
     *
     * @var int
     */
    private $_nouveau_numero;

    /**
     *
     * @var float
     */
    private $tauxTva;

    /**
     *
     * @return \SbmCommun\Model\Paiements\ResultatsInterface
     */
    public function getResultats(): ResultatsInterface
    {
        return $this->resultats;
    }

    /**
     *
     * @return \SbmCommun\Model\Db\ObjectData\Facture;
     */
    public function getOFacture(): ObjectDataFacture
    {
        return $this->oFacture;
    }

    /**
     *
     * @return int
     */
    public function getResponsableId(): int
    {
        return $this->oFacture->responsableId;
    }

    /**
     *
     * @return number
     */
    public function getMillesime(): int
    {
        return $this->oFacture->millesime;
    }

    /**
     *
     * @return number
     */
    public function getExercice(): int
    {
        return $this->oFacture->exercice;
    }

    /**
     * Renvoie la date au format français
     *
     * @return string
     */
    public function getDate(): string
    {
        return $this->oFacture->date;
    }

    /**
     *
     * @return number
     */
    public function getNumero(): int
    {
        return $this->oFacture->numero;
    }

    /**
     *
     * @return float
     */
    public function getMontant(): float
    {
        return $this->oFacture->montant;
    }

    /**
     *
     * @return array
     */
    public function getFacturesPrecedentes(): array
    {
        return $this->facturesPrecedentes;
    }

    /**
     *
     * @return number
     */
    public function getMontantDejaFacture(): float
    {
        return $this->montantDejaFacture;
    }

    public function __construct($dbManager, $resultats)
    {
        $this->db_manager = $dbManager;
        $this->resultats = $resultats;
        $this->montantDejaFacture = 0;
        $this->facturesPrecedentes = [];
        $this->tauxTva = 0;
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
     * dernière facture pour ce débiteur.
     * Pour obtenir les facturesPrecedentes on se
     * contente de rechercher les factures de numéro antérieur à celle qu'on doit sortir.
     * En même temps on met à jour la propriété 'montantDejaFacture'
     *
     * @return \SbmCommun\Model\Paiements\FactureInterface
     */
    public function facturer(): FactureInterface
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

    public function lire($numero): FactureInterface
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
     * Donne un nouveau numéro.
     * Le redonne si on l'a déjà recherché.
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

    public function getTva(): float
    {
        return round($this->getMontant() * $this->tauxTva / (1 + $this->tauxTva), 2);
    }

    public function getMontantHT(): float
    {
        return $this->getMontant() - $this->getTva();
    }

    public function setTauxTva(float $taux): FactureInterface
    {
        $this->tauxTva = $taux;
        return $this;
    }

    public function setResponsableId(int $responsableId): FactureInterface
    {
        return $this->clearResultats()->initResultats($responsableId);
    }

    public function initResultats(int $responsableId)
    {
        try {
            $this->resulats = $this->db_manager->get('Sbm\Facture\Calculs')
                ->setMillesime($this->getMillesime())
                ->getResultats($this->responsableId);
        } catch (\Exception $e) {
            $this->resulats = null;
        }
        return $this;
    }

    public function clearResultats()
    {
        $this->resulats = null;
        return $this;
    }
}