<?php
/**
 * Objet de manipulation d'une facture
 *
 * Cet objet est déclaré dans module.config.php sous l'alias 'Sbm\Facture' en db_manager.
 * Utilisation :
 * $facture = $this->db_manager('Sbm\Facture');
 * $facture->setResponsableId($responsableId); // affecte la propriété et lance les calculs si nécessaire
 * $facture->lire($numero); // lit les factures de ce responsable jusqu'au numéro indiqué
 * $facture->facturer(); // lit les factures du responsable, compare la signature de la dernière avec celle
 *    du getResultats() et en crée une nouvelle si nécessaire. Affecte correctement la propriété oFacture.
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere
 * @filesource Facture.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere\Tarification\Facture;

use SbmBase\Model\DateLib;
use SbmBase\Model\Session;
use SbmCommun\Model\Db\ObjectData\Facture as ObjectDataFacture;
use SbmCommun\Model\Paiements\FactureInterface;
use SbmCommun\Model\Paiements\ResultatsInterface;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Facture implements FactoryInterface, FactureInterface
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
    private $responsableId;

    /**
     *
     * @var \SbmCommun\Model\Paiements\ResultatsInterface
     */
    private $resulats;

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
     *
     * @var \SbmCommun\Model\Db\Service\Table\Factures
     */
    private $tFactures;

    /**
     *
     * @var \SbmCommun\Model\Db\ObjectData\Facture
     */
    private $oFacture;

    /**
     *
     * @var float
     */
    private $tauxTva;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db_manager = $serviceLocator;
        $this->tFactures = $this->db_manager->get('Sbm\Db\Table\Factures');
        $this->montantDejaFacture = 0;
        $this->facturesPrecedentes = [];
        $this->_nouveau_numero = 0;
        $this->responsableId = - 1;
        $this->tauxTva = 0;
        $this->clearResultats();
        return $this;
    }

    /**
     * En même temps, lance les calculs si nécessaire
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::setResponsableId()
     */
    public function setResponsableId(int $responsableId): FactureInterface
    {
        $this->responsableId = $responsableId;
        return $this->clearResultats()->setResultats();
    }

    public function setResultats(
        \SbmCommun\Model\Paiements\ResultatsInterface $resulats = null)
    {
        if (is_null($resulats)) {
            try {
                $this->resulats = $this->db_manager->get('Sbm\Facture\Calculs')->getResultats(
                    $this->responsableId);
            } catch (\Exception $e) {
                $this->resulats = null;
            }
        } else {
            $this->resulats = $resulats;
        }
        return $this;
    }

    public function clearResultats()
    {
        $this->resulats = null;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::getNumero()
     */
    public function getNumero(): int
    {
        return $this->getOFacture()->numero;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::getFacturesPrecedentes()
     */
    public function getFacturesPrecedentes(): array
    {
        return $this->facturesPrecedentes;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::getResponsableId()
     */
    public function getResponsableId(): int
    {
        return $this->responsableId;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::getMontant()
     */
    public function getMontant(): float
    {
        return $this->getOFacture()->montant;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::getMontantDejaFacture()
     */
    public function getMontantDejaFacture(): float
    {
        return $this->montantDejaFacture;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::getResultats()
     */
    public function getResultats(): ResultatsInterface
    {
        if (! $this->resulats) {
            $this->setResultats();
        }
        if ($this->resulats instanceof ResultatsInterface) {
            return $this->resulats;
        }
        throw new \SbmCommun\Arlysere\Exception\RuntimeException('Pas de résultat.');
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::getExercice()
     */
    public function getExercice(): int
    {
        return $this->getOFacture()->exercice;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::getDate()
     */
    public function getDate(): string
    {
        return $this->getOFacture()->date;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::getMillesime()
     */
    public function getMillesime(): int
    {
        return $this->getOFacture()->millesime;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::getOFacture()
     */
    public function getOFacture(): ObjectDataFacture
    {
        if (! $this->oFacture) {
            $this->oFacture = $this->tFactures->getObjData();
            $this->oFacture->exchangeArray(
                [
                    'millesime' => Session::get('millesime'),
                    'exercice' => date('Y'),
                    'responsableId' => $this->getResponsableId(),
                    'date' => DateLib::todayToMysql(),
                    'montant' => 0
                ]);
        }
        return $this->oFacture;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::lire()
     */
    public function lire(int $numero): FactureInterface
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
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::facturer()
     */
    public function facturer(): FactureInterface
    {
        $this->facturesPrecedentes = [];
        $this->montantDejaFacture = 0;
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
                'responsableId' => $this->getResultats()
                    ->getResponsableId(),
                'date' => $this->getDate(),
                'montant' => $this->getResultats()
                    ->getMontantTotal() - $this->getMontantDejaFacture(),
                'signature' => $this->getResultats()
                    ->signature(),
                'content' => serialize($this->getResultats())
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

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::getTva()
     */
    public function getTva(): float
    {
        return round($this->getMontant() * $this->tauxTva / (1 + $this->tauxTva), 2);
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::getMontantHT()
     */
    public function getMontantHT(): float
    {
        return $this->getMontant() - $this->getTva();
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Paiements\FactureInterface::setTauxTva()
     */
    public function setTauxTva(float $taux): FactureInterface
    {
        $this->tauxTva = $taux;
        return $this;
    }
}