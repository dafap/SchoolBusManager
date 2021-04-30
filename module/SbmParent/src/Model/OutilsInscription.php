<?php
/**
 * Outils pour inscrire, réinscrire ou modifier un élève
 *
 * Version de Millau Grands Causses
 *
 * @project sbm
 * @package SbmParent/Model
 * @filesource OutilsInscription.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmParent\Model;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmParent\Model\Db\Service\Query\Eleves;
use DateTime;

class OutilsInscription
{

    /**
     * Millesime de l'année scolaire active
     *
     * @var int
     */
    private $millesime;

    /**
     * Db manager
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $local_manager;

    /**
     * Identifiant de l'élève à réinscrire ou à modifier. Il sera null pour une
     * inscription.
     *
     * @var int|null
     */
    private $eleveId;

    /**
     * responsableId de la personne authentifiée
     *
     * @var int
     */
    private $responsableId;

    /**
     * Identifiant de l'organisme si le responsable est de cette catégorie
     *
     * @var int
     */
    private $organismeId;

    /**
     *
     * @var \SbmCommun\Model\Db\ObjectData\Scolarite
     */
    private $oScolarite;

    /**
     *
     * @var \SbmCommun\Model\Db\ObjectData\ObjectDataInterface[]
     */
    private $aEntities;

    /**
     * userId de la personne authentifiée. Correspond à
     * $sm->get('SbmAuthentification\Authentication')->by('email')->getUserId()
     *
     * @var int
     */
    private $userId;

    /**
     * Compte-rendu permettant de connaitre l'état de l'évolution des données. En
     * particulier on enregistre le retour des différents saveRecord()
     *
     * @var array
     */
    private $cr;

    /**
     * Messages à transmettre au controller
     *
     * @var array
     */
    private $messages;

    /**
     *
     * @param \SbmCommun\Model\Db\Service\DbManager $local_manager
     * @param int $responsableId
     *            responsableId de la personne authentifiée.
     * @param int $userId
     *            userId de la personne authentifiée.
     * @param int|null $eleveId
     *            Identifiant de l'élève à réinscrire ou à modifier. Il sera null pour une
     *            inscription.
     */
    public function __construct($local_manager, $responsableId, $userId, $eleveId = null)
    {
        $this->millesime = Session::get('millesime');
        $this->local_manager = $local_manager;
        $this->responsableId = $responsableId;
        $this->userId = $userId;
        $this->organismeId = 0;
        $this->eleveId = $eleveId;
        $this->cr = [];
        $this->messages = [];
        $this->aEntities = [
            'eleve' => null,
            'scolarite' => null,
            'responsable1' => null,
            'responsable2' => null
        ];
    }

    public function findOrganismeId()
    {
        $tUsersOrganismes = $this->local_manager->get('Sbm\DbManager')->get(
            'Sbm\Db\Table\UsersOrganismes');
        $this->organismeId = $tUsersOrganismes->getOrganismeId($this->userId);
    }

    /**
     * On est propriétaire d'un responsable s'il n'a pas de compte ou s'il a un compte non
     * confirmé ou non activé.
     *
     * @param int $responsableId
     * @return boolean
     */
    public function isOwner($responsableId)
    {
        $tResponsables = $this->local_manager->get('Sbm\DbManager')->get(
            'Sbm\Db\Table\Responsables');
        try {
            $email = $tResponsables->getRecord($responsableId)->email;
            if ($email) {
                $tUsers = $this->local_manager->get('Sbm\DbManager')->get(
                    'Sbm\Db\Table\Users');
                $user = $tUsers->getRecordByEmail($email);
                $owner = ! $user->confirme || ! $user->active;
            } else {
                $owner = true;
            }
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $owner = true;
        }
        return $owner;
    }

    /**
     * Enregistre les données d'un responsable dans la table des responsables, met à jour
     * la visibilité de la commune et renvoie l'identifiant responsableId Si l'email du
     * responsable existe déjà, pas d'enregistrement et on renvoie tout simplement
     * l'identifiant de ce responsable.
     *
     * @param array $data
     *
     * @return <b>int</b> : le responsableId enregistré, que ce soit un nouvel
     *         enregistrement ou la modification d'un ancien
     * @throws \Exception
     */
    public function saveResponsable($data)
    {
        $tResponsables = $this->local_manager->get('Sbm\DbManager')->get(
            'Sbm\Db\Table\Responsables');
        $oData = $tResponsables->getObjData();
        $oData->exchangeArray($data);
        if (! $oData->userId) {
            $oData->userId = $this->userId;
        }
        try {
            if ($tResponsables->saveRecord($oData)) {
                // on s'assure de rendre cette commune visible
                $this->local_manager->get('Sbm\DbManager')
                    ->get('Sbm\Db\table\Communes')
                    ->setVisible($oData->communeId);
            }
            // on récupère le responsableId qui vient d'être enregistré,
            // que ce soit un insert, un update ou la reprise d'un autre responsable par
            // son email
            return $tResponsables->getLastResponsableId();
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            // vraissemblablement ce responsable est déjà présent dans la base
            // parce que son email est présent (Integrity constraint violation)
            // Traitement spécifique à une base MYSQL
            if ($e->getPrevious()->errorInfo[1] == 1062) {
                $oResponsable = $tResponsables->getRecordByEmail($oData->email);
                return $oResponsable->responsableId;
            } else {
                throw $e;
            }
        }
    }

    /**
     * Enregistre les données dans la table eleves et renvoie l'identifiant de l'élève que
     * ce soit un nouvel enregistrement ou pas. S'il y a garde alternée, enregistre
     * l'identifiant du responsable 2.
     *
     * @param array $data
     *            données à enrgistrer
     * @param boolean $hasGa
     *            indique s'il y a une garde alternée
     * @param string $responsable2Id
     *            identifiant du responsable 2
     * @return <b>int</b> : identifiant de l'élève qui vient d'être enregistré.
     */
    public function saveEleve($data, $hasGa = false, $responsable2Id = null)
    {
        $tEleves = $this->local_manager->get('Sbm\DbManager')->get('Sbm\Db\Table\Eleves');
        $oData = $tEleves->getObjData();
        $responsable1Id = StdLib::getParam('responsable1Id', $data);
        if (empty($responsable1Id)) {
            if ($hasGa) {
                $oData->exchangeArray(
                    array_merge($data,
                        [
                            'responsable1Id' => $this->responsableId,
                            'responsable2Id' => $responsable2Id
                        ]));
            } else {
                $oData->exchangeArray(
                    array_merge($data, [
                        'responsable1Id' => $this->responsableId
                    ]));
            }
        } else {
            if ($hasGa) {
                $oData->exchangeArray(
                    array_merge($data, [
                        'responsable2Id' => $responsable2Id
                    ]));
            } else {
                $oData->exchangeArray($data);
            }
        }
        $eleveId = $tEleves->saveRecord($oData); // renvoie eleveId
        if (! $this->eleveId) {
            $this->eleveId = $eleveId;
        }
        return $eleveId;
    }

    /**
     * Enregistre la scolarité et renvoie un indicateur précisant si on doit recalculer
     * les distances. Le recalcul des distances est nécessaire (true) si l'une des
     * condition est remplie :<ul> <li>l'établissement a changé,</li> <li>c'est un nouvel
     * enregistrement</li> <li>district = 0</li></ul> Il est nécessaire de remettre
     * accordR1 et accordR2 à 1 pour traiter le cas de non ayant droits repassant ayant
     * droit par changement d'établissement ou de domicile.
     *
     * @param array $data
     *            tableau de données contenant la scolarité
     * @param string $mode
     *            Prend les valeurs 'insciption', 'reinscription' ou 'edit'
     * @return <b>boolean</b> : indicateur de recalcul de distances
     */
    public function saveScolarite(array $data, string $mode)
    {
        $tScolarites = $this->local_manager->get('Sbm\DbManager')->get(
            'Sbm\Db\Table\Scolarites');
        $oData = $tScolarites->getObjData();
        if ($mode == 'edit') {
            $array = [
                'millesime' => $this->millesime,
                'eleveId' => $this->eleveId,
                'accordR1' => 1,
                'accordR2' => 1,
                'organismeId' => $this->organismeId
            ];
        } else {
            $array = [
                'millesime' => $this->millesime,
                'eleveId' => $this->eleveId,
                'inscrit' => 1,
                'paiementR1' => 0,
                'paiementR2' => 0,
                'internet' => 1,
                'duplicataR1' => 0,
                'duplicataR2' => 0,
                'anneeComplete' => 1,
                'subventionR1' => 0,
                'subventionR2' => 0,
                'accordR1' => 1,
                'accordR2' => 1,
                'organismeId' => $this->organismeId
            ];
        }
        if ($this->organismeId) {
            $array['gratuit'] = 2;
        }
        $oData->exchangeArray(array_merge($data, $array));
        $this->cr['saveScolarite'] = $tScolarites->saveRecord($oData);
        if ($this->getOScolarite()->hasAdressePerso()) {
            try {
                $this->cr['geolocalisation'] = ($this->getOScolarite()->x +
                    $this->getOScolarite()->y) == 0;
            } catch (\Exception $e) {
                $this->cr['geolocalisation'] = true;
            }
        } else {
            $this->cr['geolocalisation'] = false;
        }
    }

    /**
     * Examine ce qu'il faut mettre à jour après l'inscription ou la modification :
     * distanceR1, distanceR2, grilleTarifR1, grilleTarifR2, affectations pour le R1,
     * affectations pour le R2. Puis lance les méthodes de mises à jour. Au fur et à
     * mesure, les erreurs et problèmes seront enregistrés dans la propriété messages
     *
     * @param string $mode
     *            Prend les valeurs 'inscription', 'reinscription' ou 'edit'
     */
    public function apresInscription(string $mode)
    {
        if ($mode == 'edit') {
            if ($this->cr['saveScolarite']['etablissementChange']) {
                $this->supprAffectations();
            } elseif ($this->cr['saveScolarite']['gaChange']) {
                $this->supprAffectations(true);
            }
        }
        if (! $this->getOScolarite()->hasAdressePerso() ||
            ($this->getOScolarite()->x + $this->getOScolarite()->y) != 0) {
            // lorsqu'il y a une adresse perso et que la géolocalisation n'est pas faite
            // il est inutile de tenter de calculer les distances. Il faudra demander la
            // géolocalisation et le faire ensuite.
            $this->majDistances($mode);
        }
        $this->majGrillesTarif($mode, 1);
        $this->rechercheTrajets($mode, 1);
        if ($this->getOEleve()->responsable2Id && $this->getOScolarite()->demandeR2 > 0) {
            $this->majGrillesTarif($mode, 2);
            $this->rechercheTrajets($mode, 2);
        }
    }

    public function doitGeolocaliser()
    {
        return StdLib::getParam('geolocalisation', $this->cr, false);
    }

    public function getMessages()
    {
        return $this->messages;
    }

    private function addMessage($messages)
    {
        if (is_string($messages)) {
            $messages = (array) $messages;
        }
        $this->messages = array_merge($this->messages, $messages);
    }

    /**
     * On lance la procédure majDistanceDistrict() de la classe
     * \SbmCommun\Arlysere\CalculDroits
     *
     * @param string $mode
     *            Prend les valeurs 'inscription', 'reinscription' ou 'edit'
     */
    private function majDistances(string $mode)
    {
        if ($mode == 'edit') {
            $calculDistance = $this->cr['saveScolarite']['distanceR1Inconnue'] ||
                $this->cr['saveScolarite']['distanceR2Inconnue'] ||
                $this->cr['saveScolarite']['etablissementChange'];
        } else {
            $calculDistance = $this->cr['saveScolarite']['distanceR1Inconnue']; // reprise
                                                                                // des
                                                                                // données
                                                                                // 2019
            $calculDistance |= ! ($this->memeDomicile($this->getOResponsable(1)) &&
                $this->memeScolarite());
            if (! $calculDistance) {
                $r2 = $this->getOResponsable(2);
                if ($r2) {
                    $calculDistance = ! $this->memeDomicile($r2);
                }
            }
        }
        if ($calculDistance) {
            $majDistances = $this->local_manager->get('Sbm\CartographieManager')->get(
                'Sbm\CalculDroitsTransport');
            try {
                $majDistances->setOEleve($this->getOEleve())
                    ->setOScolarite($this->getOScolarite())
                    ->majDistancesDistrict($this->eleveId, false);
                $cr = $majDistances->getCompteRendu();
                $this->addMessage($cr);
            } catch (\OutOfBoundsException $e) {
                $this->addMessage($e->getMessage());
            }
        }
    }

    /**
     * En mode 'inscription' ou 'reinscription' on met à jour la grilleTarifR1 et la
     * reductionR1 et, si nécessaire, la grilleTarifR2 et la reductionR2. En mode 'edit'
     * les grilleTarif ne changent pas (pas de changement de domicile) et les reductions
     * ne changent que si on a obtenu un reductionChange dans le cr.
     *
     * @param string $mode
     *            Prend les valeurs 'insciption', 'reinscription' ou 'edit'
     * @param int $rang
     *            1 pour le responsable1 et 2 pour le responsable2
     */
    private function majGrillesTarif(string $mode, int $rang)
    {
        if ($mode != 'edit' || $this->cr['saveScolarite']['reductionChange']) {
            if ($rang == 1) {
                $this->local_manager->get('Sbm\DbManager')
                    ->get('Sbm\GrilleTarifR1')
                    ->setOEleve($this->getOEleve())
                    ->setOScolarite($this->getOScolarite())
                    ->appliquerTarif($this->eleveId);
            } elseif ($this->getOEleve()->responsable2Id &&
                $this->getOScolarite()->demandeR2) {
                $this->local_manager->get('Sbm\DbManager')
                    ->get('Sbm\GrilleTarifR2')
                    ->setOEleve($this->getOEleve())
                    ->setOScolarite($this->getOScolarite())
                    ->appliquerTarif($this->eleveId);
            }
        }
    }

    /**
     * On lance une recherche de trajet par les méthodes de l'objet
     * \SbmCommun\Arlysere\ChercheTrajet En mode inscription ou reinscription il faut
     * rechercher un trajet. En mode edit, la recherche de trajets n'est nécessaire que si
     * on change de domicile ou si on change d'établissement scolaire
     *
     * @param string $mode
     *            Prend les valeurs 'insciption', 'reinscription' ou 'edit'
     * @param int $rang
     *            1 pour le responsable1 et 2 pour le responsable2
     */
    private function rechercheTrajets(string $mode, int $rang)
    {
        if ($mode != 'edit' || $this->cr['saveScolarite']['etablissementChange']) {
            $this->local_manager->get('Sbm\DbManager')
                ->get('Sbm\ChercheItineraires')
                ->setEtablissementId($this->getOScolarite()->etablissementId)
                ->setStationId($this->getOScolarite()->{'stationIdR' . $rang})
                ->setEleveId($this->eleveId)
                ->setJours($this->getOScolarite()->{'joursTransportR' . $rang})
                ->setTrajet($rang)
                ->setResponsableId($this->getOEleve()->{'responsable' . $rang . 'Id'})
                ->setRegimeId($this->getOScolarite()->regimeId)
                ->run();
        }
    }

    /**
     *
     * @return \SbmCommun\Model\Db\ObjectData\ObjectDataInterface
     */
    public function getOEleve()
    {
        if (! $this->aEntities['eleve']) {
            $this->aEntities['eleve'] = $this->local_manager->get('Sbm\DbManager')
                ->get('Sbm\Db\Table\Eleves')
                ->getRecord($this->eleveId);
        }
        return $this->aEntities['eleve'];
    }

    /**
     * Pour le responsable2, cela peut renvoyer null
     *
     * @param int $rang
     * @return \SbmCommun\Model\Db\ObjectData\ObjectDataInterface
     */
    private function getOResponsable(int $rang)
    {
        if (! $this->aEntities['responsable' . $rang]) {
            try {
                $this->aEntities['responsable' . $rang] = $this->local_manager->get(
                    'Sbm\DbManager')
                    ->get('Sbm\Db\Table\Responsables')
                    ->getRecord($this->getOEleve()->{'responsable' . $rang . 'Id'});
            } catch (\Exception $e) {
            }
        }
        return $this->aEntities['responsable' . $rang];
    }

    /**
     * Renvoie la scolarité de l'élève
     *
     * @return \SbmCommun\Model\Db\ObjectData\ObjectDataInterface
     */
    public function getOScolarite()
    {
        if (! $this->aEntities['scolarite']) {
            $this->aEntities['scolarite'] = $this->local_manager->get('Sbm\DbManager')
                ->get('Sbm\Db\Table\Scolarites')
                ->getRecord(
                [
                    'millesime' => $this->millesime,
                    'eleveId' => $this->eleveId
                ]);
        }
        return $this->aEntities['scolarite'];
    }

    private function repriseDistancesDistrictPrecedents()
    {
        $tScolarites = $this->local_manager->get('Sbm\DbManager')->get(
            'Sbm\Db\Table\Scolarites');
        try {
            // recherche la scolarité de l'année précédente
            $oDataPrecedent = $tScolarites->getRecord(
                [
                    'millesime' => $this->millesime - 1,
                    'eleveId' => $this->eleveId
                ]);
            // recherche la scolarité de l'année en cours
            $oData = $tScolarites->getRecord(
                [
                    'millesime' => $this->millesime,
                    'eleveId' => $this->eleveId
                ]);
            // reprise de données
            $oData->distanceR1 = $oDataPrecedent->distanceR1;
            $oData->distanceR2 = $oDataPrecedent->distanceR2;
            $oData->district = $oDataPrecedent->district;
            // enregistrement
            $tScolarites->updateRecord($oData);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
        }
    }

    /**
     * Cette méthode renvoie un booléen indiquant qu'il faut recalculer les droits si un
     * domicile, l'établissement ou le régime ont changé. Si le domicile, l'établissement
     * et le régime n'ont pas changé alors enregistre les affectations de l'année
     * précédente sinon supprime la dérogation (s'il y en a une).
     *
     * @return boolean
     */
    public function repriseAffectations($demandeR1, $demandeR2)
    {
        $calculDroits = true; // indique s'il faut recalculer les droits et les distances
        if ($this->memeScolarite()) {
            $eleve = $this->local_manager->get('Sbm\DbManager')
                ->get('Sbm\Db\Table\Eleves')
                ->getRecord($this->eleveId);
            // affectations de l'année précédentes
            $affectations = $this->local_manager->get('Sbm\DbManager')
                ->get('Sbm\Db\Table\Affectations')
                ->fetchAll(
                [
                    'millesime' => $this->millesime - 1,
                    'eleveId' => $this->eleveId
                ]);
            if ($affectations->count()) {
                // il y a des affectations. Faut-il les reprendre ?
                $calculDroits = $this->repriseAffectationsPour($affectations,
                    $eleve->responsable1Id, $eleve->responsable2Id, $demandeR1, $demandeR2);
            }
        }
        if ($calculDroits) {
            $this->supprDerogation();
        } else {
            $this->repriseDistancesDistrictPrecedents();
        }
        return $calculDroits;
    }

    /**
     * Reprend les affectations de l'année antérieure pour un responsable donné et les
     * enregistre pour cette année.
     *
     * @param \Zend\Db\ResultSet\HydratingResultSet $affectations
     *            Les affectations de l'élève l'année antérieure
     * @param int $responsable1Id
     *            L'identifiant du responsable 1
     * @param int $responsable2Id
     *            L'identifiant du responsable 2
     * @param bool $demandeR1
     *            Demande de transport sur le trajet 1 (domicile du responsable 1)
     * @param bool $demandeR2
     *            Demande de transport sur le trajet 2 (domicile du responsable 2)
     * @param int $trajet
     *            1 pour le parent n°1 : 2 pour le parent n°2 en cas de garde alternée
     */
    private function repriseAffectationsPour($affectations, $responsable1Id,
        $responsable2Id, $demandeR1, $demandeR2)
    {
        $responsable1 = $this->local_manager->get('Sbm\DbManager')
            ->get('Sbm\Db\Table\Responsables')
            ->getRecord($responsable1Id);
        $memeDomicileR1 = $this->memeDomicile($responsable1);
        try {
            $responsable2 = $this->local_manager->get('Sbm\DbManager')
                ->get('Sbm\Db\Table\Responsables')
                ->getRecord($responsable2Id);
            $memeDomicileR2 = $this->memeDomicile($responsable2);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $memeDomicileR2 = true;
        }
        $tAffectations = $this->local_manager->get('Sbm\DbManager')->get(
            'Sbm\Db\Table\Affectations');
        $reprise1 = $reprise2 = false;
        foreach ($affectations as $oaffectation) {
            // pour les affectations du responsable 1 à reprendre
            if ($demandeR1 && $memeDomicileR1 &&
                $oaffectation->responsableId == $responsable1Id) {
                $oaffectation->millesime = $this->millesime;
                $oaffectation->trajet = 1; // au cas où il y aurait inversion des
                                           // responsables 1 et 2
                $tAffectations->saveRecord($oaffectation);
                $reprise1 = true;
            }
            // pour les affectations du responsable 2 à reprendre
            if ($demandeR2 && $memeDomicileR2 &&
                $oaffectation->responsableId == $responsable2Id) {
                $oaffectation->millesime = $this->millesime;
                $oaffectation->trajet = 2; // au cas où il y aurait inversion des
                                           // responsables 1 et
                                           // 2
                $tAffectations->saveRecord($oaffectation);
                $reprise2 = true;
            }
        }
        /**
         * Le calcul des droits est nécessaire si - il y a une demandeR1 et responsable1Id
         * n'est pas 0 et pas de reprise1 ou - il y a une demandeR2 et responsable2Id
         * n'est pas 0 et pas de reprise2
         */
        $calculDroitsNecessaire = false;
        if ($demandeR1 && $responsable1Id) {
            $calculDroitsNecessaire = ! $reprise1;
        }
        if ((! $calculDroitsNecessaire) && $demandeR2 && $responsable2Id) {
            $calculDroitsNecessaire = ! $reprise2;
        }
        return $calculDroitsNecessaire;
    }

    /**
     * Indique si le responsable a le même domicile depuis le début de l'année scolaire
     * précédente (memeDomicile si pas de déménagement ou dateDéménagement avant dateDébut
     * de l'AS précédente)
     *
     * @param \SbmCommun\Model\Db\ObjectData\ObjectDataInterface $responsable
     *
     * @return <b>boolean</b>
     */
    private function memeDomicile($responsable)
    {
        if (! $responsable->demenagement) {
            return true;
        }
        $as = $this->local_manager->get('Sbm\DbManager')
            ->get('Sbm\Db\System\Calendar')
            ->fetchAll([
            'millesime' => $this->millesime - 1,
            'nature' => 'AS'
        ]);
        if (! $as->count()) {
            return false; // il n'y avait pas d'année scolaire précédente donc rien à
                          // reprendre
        }
        $dateRef = DateTime::createFromFormat('Y-m-d', $as->current()->dateDebut);
        $dateDemenagement = DateTime::createFromFormat('Y-m-d',
            $responsable->dateDemenagement);
        return $dateDemenagement < $dateRef;
    }

    /**
     * Indique si l'élève enregistré dans la table scolarites a le même établissement et
     * le même régime que l'année précédente.
     *
     * @return boolean
     */
    private function memeScolarite()
    {
        return $this->local_manager->get('Sbm\DbManager')
            ->get(Eleves::class)
            ->memeScolarite($this->eleveId);
    }

    /**
     * Cette méthode doit être lancée à la fin de la méthode repriseAffectations()
     *
     * @return boolean
     */
    private function supprDerogation()
    {
        $tScolarites = $this->local_manager->get('Sbm\DbManager')->get(
            'Sbm\Db\Table\Scolarites');
        $tScolarites->setDerogation($this->millesime, $this->eleveId, 0);
    }

    /**
     * Supprime les affectations de cet élève pour ce millesime parce qu'il y a eu un
     * changement d'établissement ou parce que le responsable2 ne demande plus de
     * transport.
     *
     * @param bool $r2seulement
     */
    public function supprAffectations($r2seulement = false)
    {
        $tAffectations = $this->local_manager->get('Sbm\DbManager')->get(
            'Sbm\Db\Table\Affectations');
        if ($r2seulement) {
            $tAffectations->getTableGateway()->delete(
                [
                    'millesime' => $this->millesime,
                    'eleveId' => $this->eleveId,
                    'trajet' => 2
                ]);
        } else {
            $tAffectations->getTableGateway()->delete(
                [
                    'millesime' => $this->millesime,
                    'eleveId' => $this->eleveId
                ]);
        }
    }
}