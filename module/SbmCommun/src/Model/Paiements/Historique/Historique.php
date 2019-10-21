<?php
/**
 * Décryptage des données de paiement trouvées dans la table history
 *
 * @project sbm
 * @package SbmCommun/src/Model/Paiements
 * @filesource Historique.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 oct. 2019
 * @version 2019-2.5.2
 */
namespace SbmCommun\Model\Paiements\Historique;

use SbmBase\Model\DateLib;

class Historique
{

    const IDX_DATE_DEPOT = 0;

    const IDX_DATE_PAIEMENT = 1;

    const IDX_DATE_VALEUR = 2;

    const IDX_RESPONSABLEID = 3;

    const IDX_ANNEE_SCOLAIRE = 4;

    const IDX_EXERCICE = 5;

    const IDX_MONTANT = 6;

    const IDX_CODE_MODE_PAIEMENT = 7;

    const IDX_CODE_CAISSE = 8;

    const IDX_BANQUE = 9;

    const IDX_TITULAIRE = 10;

    const IDX_REFERENCE = 11;

    const IDX_NOTE = 12;

    const IDX_NEW_RESPONSABLEID = 13;

    const IDX_NEW_MONTANT = 14;

    const IDX_NEW_CODE_MODE_PAIEMENT = 15;

    const IDX_NEW_CODE_CAISSE = 16;

    const IDX_NEW_BANQUE = 17;

    const IDX_NEW_TITULAIRE = 18;

    const ACTION_DELETE = 'delete';

    const ACTION_INSERT = 'insert';

    const ACTION_UPDATE = 'update';

    const GET_ACTION_DELETE = 'Suppr';

    const GET_ACTION_INSERT = 'Ajout';

    const GET_ACTION_UPDATE = 'Modif';

    /**
     *
     * @var string ('insert' | 'update' | 'delete')
     */
    private $action;

    /**
     *
     * @var int
     */
    private $paiementId;

    /**
     * Date de l'action
     *
     * @var string
     */
    private $dt;

    /**
     *
     * @var array
     */
    private $log;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\Table\Responsables
     */
    private $tResponsables;

    /**
     *
     * @var array
     */
    private $modesDePaiement;

    /**
     *
     * @var array
     */
    private $caisses;

    public function __construct($record = null)
    {
        if ($record) {
            $this->setRecord($record);
        }
    }

    public function setTResponsables($tResponsables)
    {
        $this->tResponsables = $tResponsables;
    }

    public function setModesDePaiement($modes)
    {
        $this->modesDePaiement = $modes;
    }

    public function setCaisses($caisses)
    {
        $this->caisses = $caisses;
    }

    public function setRecord($record)
    {
        $this->action = $record['action'];
        $this->paiementId = $record['id_int'];
        $this->dt = $record['dt'];
        $this->log = explode('|', $record['log']);
    }

    private function translateAction(string $action)
    {
        return [
            'delete' => self::GET_ACTION_DELETE, // 'Suppr',
            'insert' => self::GET_ACTION_INSERT, // 'Ajout',
            'update' => self::GET_ACTION_UPDATE // 'Modif'
        ][$action];
    }

    public function getAction()
    {
        return $this->translateAction($this->action);
    }

    public function getDate()
    {
        return DateLib::formatDateTimeFromMysql($this->dt);
    }

    public function getPaiementId()
    {
        return $this->paiementId;
    }

    public function getDateDepot()
    {
        return DateLib::formatDateTimeFromMysql($this->log[self::IDX_DATE_DEPOT]);
    }

    public function getDatePaiement()
    {
        return DateLib::formatDateTimeFromMysql($this->log[self::IDX_DATE_PAIEMENT]);
    }

    public function getDateValeur()
    {
        return DateLib::formatDateFromMysql($this->log[self::IDX_DATE_VALEUR]);
    }

    public function getResponsableId()
    {
        return $this->log[self::IDX_RESPONSABLEID];
    }

    public function getResponsable()
    {
        $r = $this->tResponsables->getRecord($this->getResponsableId());
        return $r->nomSA . ' ' . $r->prenomSA;
    }

    public function getAnneeScolaire()
    {
        return $this->log[self::IDX_ANNEE_SCOLAIRE];
    }

    public function getExercice()
    {
        return $this->log[self::IDX_EXERCICE];
    }

    public function getMontant()
    {
        return $this->log[self::IDX_MONTANT];
    }

    public function getCodeModePaiement()
    {
        return $this->log[self::IDX_CODE_MODE_PAIEMENT];
    }

    public function getModePaiement()
    {
        $code = $this->getCodeModePaiement();
        if ($code) {
            return $this->modesDePaiement[$code];
        }
        return '';
    }

    public function getCodeCaisse()
    {
        return $this->log[self::IDX_CODE_CAISSE];
    }

    public function getCaisse()
    {
        $code = $this->getCodeCaisse();
        if ($code) {
            return $this->caisses[$code];
        }
        return '';
    }

    public function getBanque()
    {
        return $this->log[self::IDX_BANQUE];
    }

    public function getTitulaire()
    {
        return $this->log[self::IDX_TITULAIRE];
    }

    public function getReference()
    {
        return $this->log[self::IDX_REFERENCE];
    }

    public function getNote()
    {
        if ($this->action != self::ACTION_INSERT &&
            array_key_exists(self::IDX_NOTE, $this->log)) {
            return $this->log[self::IDX_NOTE];
        }
        return '';
    }

    public function getNewResponsableId()
    {
        if ($this->action == self::ACTION_UPDATE &&
            array_key_exists(self::IDX_NEW_RESPONSABLEID, $this->log)) {
            return $this->log[self::IDX_NEW_RESPONSABLEID];
        }
        return 0;
    }

    public function getNewResponsable()
    {
        try {
            $r = $this->tResponsables->getRecord($this->getNewResponsableId());
            return $r->nomSA . ' ' . $r->prenomSA;
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getNewMontant()
    {
        if ($this->action == self::ACTION_UPDATE &&
            array_key_exists(self::IDX_NEW_MONTANT, $this->log)) {
            return $this->log[self::IDX_NEW_MONTANT];
        }
        return 0;
    }

    public function getNewCodeModePaiement()
    {
        if ($this->action == self::ACTION_UPDATE &&
            array_key_exists(self::IDX_NEW_CODE_MODE_PAIEMENT, $this->log)) {
            return $this->log[self::IDX_NEW_CODE_MODE_PAIEMENT];
        }
        return 0;
    }

    public function getNewModePaiement()
    {
        $code = $this->getNewCodeModePaiement();
        if ($code) {
            return $this->modesDePaiement[$code];
        }
        return '';
    }

    public function getNewCodeCaisse()
    {
        if ($this->action == self::ACTION_UPDATE &&
            array_key_exists(self::IDX_NEW_CODE_CAISSE, $this->log)) {
            return $this->log[self::IDX_NEW_CODE_CAISSE];
        }
        return 0;
    }

    public function getNewCaisse()
    {
        $code = $this->getNewCodeCaisse();
        if ($code) {
            return $this->caisses[$code];
        }
        return '';
    }

    public function getNewBanque()
    {
        if ($this->action == self::ACTION_UPDATE &&
            array_key_exists(self::IDX_NEW_BANQUE, $this->log)) {
            return $this->log[self::IDX_NEW_BANQUE];
        }
        return '';
    }

    public function getNewTitulaire()
    {
        if ($this->action == self::ACTION_UPDATE &&
            array_key_exists(self::IDX_NEW_TITULAIRE, $this->log)) {
            return $this->log[self::IDX_NEW_TITULAIRE];
        }
        return '';
    }

    public function updateDetail()
    {
        $detail = [];
        if ($this->action == self::ACTION_UPDATE) {
            if ($this->getMontant() != $this->getNewMontant()) {
                $detail['montant'] = [
                    'libelle' => 'Montant modifié',
                    'ancien' => $this->getMontant(),
                    'nouveau' => $this->getNewMontant()
                ];
            }
            if ($this->getResponsableId() != $this->getNewResponsableId()) {
                $detail['responsableId'] = [
                    'libelle' => 'Débiteur modifié',
                    'ancien' => $this->getResponsableId(),
                    'nouveau' => $this->getNewResponsableId()
                ];
            }
            if ($this->getCodeCaisse() != $this->getNewCodeCaisse()) {
                $detail['codeCaisse'] = [
                    'libelle' => 'Caisse modifiée',
                    'ancien' => $this->getCodeCaisse(),
                    'nouveau' => $this->getNewCodeCaisse()
                ];
            }
            if ($this->getCodeModePaiement() != $this->getNewCodeModePaiement()) {
                $detail['codeModePaiement'] = [
                    'libelle' => 'Mode de paiement modifié',
                    'ancien' => $this->getCodeModePaiement(),
                    'nouveau' => $this->$this->getNewCodeModePaiement()
                ];
            }
            if ($this->getBanque() != $this->getNewBanque()) {
                $detail['banque'] = [
                    'libelle' => 'Banque',
                    'ancien' => $this->getBanque(),
                    'nouveau' => $this->getNewBanque()
                ];
            }
            if ($this->getTitulaire() != $this->getNewTitulaire()) {
                $detail['titulaire'] = [
                    'libelle' => 'Titulaire du compte bancaire',
                    'ancien' => $this->getTitulaire(),
                    'nouveau' => $this->getNewTitulaire()
                ];
            }
        }
        return $detail;
    }
}