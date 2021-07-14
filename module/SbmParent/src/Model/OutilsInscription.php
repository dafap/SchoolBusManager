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
 * @date 13 juil. 2021
 * @version 2021-2.5.13
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
    private $db_manager;

    /**
     * Identifiant de l'élève à réinscrire ou à modifier.
     * Il sera null pour une
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
     *
     * @var \SbmCommun\Model\Db\ObjectData\ObjectDataInterface
     */
    private $scolariteAnneePrecedente;

    /**
     * userId de la personne authentifiée.
     * Correspond à
     * $sm->get('SbmAuthentification\Authentication')->by('email')->getUserId()
     *
     * @var int
     */
    private $userId;

    /**
     *
     * @param \SbmCommun\Model\Db\Service\DbManager $db_manager
     * @param int $responsableId
     *            responsableId de la personne authentifiée.
     * @param int $userId
     *            userId de la personne authentifiée.
     * @param int|null $eleveId
     *            Identifiant de l'élève à réinscrire ou à modifier. Il sera null pour une
     *            inscription.
     */
    public function __construct($db_manager, $responsableId, $userId, $eleveId = null)
    {
        $this->millesime = Session::get('millesime');
        $this->db_manager = $db_manager;
        $this->responsableId = $responsableId;
        $this->userId = $userId;
        $this->eleveId = $eleveId;
    }

    private function getScolariteAnneePrecedente()
    {
        if ($this->eleveId) {
            $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
            try {
                $this->scolariteAnneePrecedente = $tScolarites->getRecord(
                    [
                        'millesime' => $this->millesime - 1,
                        'eleveId' => $this->eleveId
                    ])->getArrayCopy();
            } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                $this->scolariteAnneePrecedente = [];
            }
        } else {
            $this->scolariteAnneePrecedente = [];
        }
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
        $tResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        try {
            $email = $tResponsables->getRecord($responsableId)->email;
            if ($email) {
                $tUsers = $this->db_manager->get('Sbm\Db\Table\Users');
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
        $tResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        $oData = $tResponsables->getObjData();
        $oData->exchangeArray($data);
        if (! $oData->userId) {
            $oData->userId = $this->userId;
        }
        try {
            if ($tResponsables->saveRecord($oData)) {
                // on s'assure de rendre cette commune visible
                $this->db_manager->get('Sbm\Db\table\Communes')->setVisible(
                    $oData->communeId);
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
     * ce soit un nouvel enregistrement ou pas.
     * S'il y a garde alternée, enregistre
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
        $tEleves = $this->db_manager->get('Sbm\Db\Table\Eleves');
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
        return $tEleves->saveRecord($oData); // renvoie eleveId
    }

    /**
     * Enregistre la scolarité et renvoie un indicateur précisant si on doit recalculer
     * les distances.
     * Le recalcul des distances est nécessaire (true) si l'une des
     * condition est remplie :<ul> <li>l'établissement a changé,</li> <li>c'est un nouvel
     * enregistrement</li> <li>district = 0</li></ul>
     * Il est nécessaire de remettre accordR1 et accordR2 à 1 pour traiter le cas de non
     * ayant droits repassant ayant droit par changement d'établissement ou de domicile.
     *
     * @param array $data
     *            tableau de données contenant la scolarité
     * @param int|null $eleveId
     *            ne doit être passé que pour une réinscription
     * @return <b>boolean</b> : indicateur de recalcul de distances
     */
    public function saveScolarite($data, $eleveId = null)
    {
        $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $oData = $tScolarites->getObjData();
        if (is_null($eleveId)) {
            $array = [
                'millesime' => $this->millesime,
                'accordR1' => 1,
                'accordR2' => 1
            ];
        } else {
            $array = [
                'millesime' => $this->millesime,
                'eleveId' => $eleveId,
                'inscrit' => 1,
                'gratuit' => 1,
                'paiement' => 0,
                'internet' => 1,
                'duplicata' => 0,
                'anneeComplete' => 1,
                'subventionR1' => 0,
                'subventionR2' => 0,
                'accordR1' => 1,
                'accordR2' => 1
            ];
        }
        $oData->exchangeArray(array_merge($data, $array));

        return $tScolarites->saveRecord($oData);
    }

    private function repriseDistancesDistrictPrecedents()
    {
        $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
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
     * domicile, l'établissement ou le régime ont changé.
     * Si le domicile, l'établissement
     * et le régime n'ont pas changé alors enregistre les affectations de l'année
     * précédente sinon supprime la dérogation (s'il y en a une).
     *
     * @return boolean
     */
    public function repriseAffectations($demandeR1, $demandeR2)
    {
        $calculDroits = true; // indique s'il faut recalculer les droits et les distances
        if ($this->memeScolarite()) {
            $eleve = $this->db_manager->get('Sbm\Db\Table\Eleves')->getRecord(
                $this->eleveId);
            // affectations de l'année précédentes
            $affectations = $this->db_manager->get('Sbm\Db\Table\Affectations')->fetchAll(
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
        $responsable1 = $this->db_manager->get('Sbm\Db\Table\Responsables')->getRecord(
            $responsable1Id);
        $memeDomicileR1 = $this->memeDomicile($responsable1);
        try {
            $responsable2 = $this->db_manager->get('Sbm\Db\Table\Responsables')->getRecord(
                $responsable2Id);
            $memeDomicileR2 = $this->memeDomicile($responsable2);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $memeDomicileR2 = true;
        }
        $tAffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
        $reprise1 = $reprise2 = false;
        foreach ($affectations as $oaffectation) {
            // pour les affectations du responsable 1 à reprendre
            if ($demandeR1 && $memeDomicileR1 &&
                $oaffectation->responsableId == $responsable1Id) {
                $oaffectation->millesime = $this->millesime;
                $oaffectation->trajet = 1; // au cas où il y aurait inversion des
                                           // responsables 1 et
                                           // 2
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
        $as = $this->db_manager->get('Sbm\Db\System\Calendar')->fetchAll(
            [
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
        return $this->db_manager->get(Eleves::class)->memeScolarite($this->eleveId);
    }

    /**
     * Cette méthode doit être lancée à la fin de la méthode repriseAffectations()
     *
     * @return boolean
     */
    private function supprDerogation()
    {
        $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
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
        $tAffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
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

    /**
     * Supprime la photo si elle a plus de 2 ans le jour de la rentrée scolaire
     *
     * @param int $eleveId
     */
    public function supprAnciennePhoto(int $eleveId)
    {
        try {
            $tElevesPhotos = $this->db_manager->get('Sbm\Db\Table\ElevesPhotos');
            $tElevesPhotos->supprAncienne($eleveId);
        } catch (\Exception $e) {
            // Pas de photo, rien à supprimer
        }
    }
}