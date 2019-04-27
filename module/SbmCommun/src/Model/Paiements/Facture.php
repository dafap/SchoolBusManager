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
 * @date 27 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Paiements;

use SbmBase\Model\DateLib;
use SbmBase\Model\Session;

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
     * Resultats de la facture précédente dans le cas où il y en a une
     *
     * @var Resultats
     */
    private $lastResultats;

    /**
     *
     * @var float
     */
    private $montantDejaFacture;

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
        $this->lastResultats = null;
        $this->montantDejaFacture = 0;
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
        $this->search();
    }

    /**
     * Recherche une facture par sa signature dans le millesime en cours et pour le
     * responsable indiqué dans resultats. Si la facture existe, met à jour les propriétés
     * numero et date. Si elle n'existe pas, crée une nouvelle facture dans la table
     */
    protected function search()
    {
        $facturesPrecedentes = [];
        try {
            $rowset = $this->tFactures->fetchAll(
                [
                    'millesime' => $this->getMillesime(),
                    'responsableId' => $this->getResponsableId()
                ], [
                    'numero DESC'
                ]);
            $creer = true;
            if ($rowset->count()) {
                foreach ($rowset as $row) {
                    if ($this->trouve($row)) {
                        $creer = false;
                        $this->oFacture = $row;
                    } else {
                        if (! $this->lastResultats) {
                            $this->lastResultats = unserialize($row->content);
                        }
                        $facturesPrecedentes[] = [
                            'numero' => $row->exercice . '-' . $row->numero,
                            'date' => $row->date,
                            'montant' => $row->montant
                        ];
                    }
                }
            }
            if ($creer) {
                $this->add();
            }
            //
            $numero = sprintf("%s-%s", $this->getExercice(), $this->getNumero());
            foreach ($facturesPrecedentes as $array) {
                if ($array['numero'] < $numero) {
                    $this->facturesPrecedentes[] = $array;
                    $this->montantDejaFacture += $array['montant'];
                }
            }
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\RuntimeException $e) {
        }
    }

    protected function trouve(\SbmCommun\Model\Db\ObjectData\Facture $ofacture)
    {
        $resultats = unserialize($ofacture->content);
        return $ofacture->signature == $this->resultats->signature() &&
            $this->resultats->equalTo($resultats);
    }

    /**
     * Crée une facture dans la table factures en ajoutant le résultat dans la table et
     * mets à jour la propriété numero et date
     */
    protected function add()
    {
        $this->oFacture->exchangeArray(
            [
                'exercice' => $this->getExercice(),
                'numero' => $this->nouveauNumero(),
                'millesime' => $this->getMillesime(),
                'responsableId' => $this->resultats->getResponsableId(),
                'date' => $this->getDate(),
                'montant' => $this->resultats->getMontantTotal() -
                $this->getMontantDejaFacture(),
                'signature' => $this->resultats->signature(),
                'content' => serialize($this->resultats)
            ]);
        $this->tFactures->saveRecord($this->oFacture);
    }

    private function nouveauNumero()
    {
        return $this->tFactures->dernierNumero($this->getExercice()) + 1;
    }
}