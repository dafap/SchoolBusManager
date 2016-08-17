<?php
/**
 * Outils pour inscrire, réinscrire ou modifier un élève
 *
 * @project sbm
 * @package SbmParent/Model
 * @filesource OutilsInscription.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace SbmParent\Model;

use DateTime;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Db\Service\DbManager;
use SbmParent\Model\Db\Service\Query\Eleves;

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
     * Il sera null pour une inscription.
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
     * userId de la personne authentifiée.
     * Correspond à $sm->get('Dafap\Authenticate')->by('email')->getUserId()
     *
     * @var int
     */
    private $userId;

    /**
     *
     * @param DbManager $db_manager            
     * @param int $responsableId
     *            responsableId de la personne authentifiée.
     * @param int $userId
     *            userId de la personne authentifiée.
     * @param int|null $eleveId
     *            Identifiant de l'élève à réinscrire ou à modifier. Il sera null pour une inscription.
     */
    public function __construct($db_manager, $responsableId, $userId, $eleveId = null)
    {
        $this->millesime = Session::get('millesime');
        $this->db_manager = $db_manager;
        $this->responsableId = $responsableId;
        $this->userId = $userId;
        $this->eleveId = $eleveId;
    }

    /**
     * Indique si le responsable2 a été créé par l'utilisateur authentifié.
     * Dans ce cas, il est considéré
     * comme propriétaire et peut modifier la fiche.
     *
     * @param int $responsableId            
     *
     * @return <b>boolean</b>
     */
    public function isOwner_old($responsableId)
    {
        $tResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        try {
            $userId = $tResponsables->getRecord($responsableId)->userId;
            $owner = $userId == $this->userId;
        } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
            $owner = true;
        }
        return $owner;
    }

    /**
     * On est propriétaire d'un responsable s'il n'a pas de compte 
     * ou s'il a un compte non confirmé ou non activé.
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
        } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
            $owner = true;
        }
        return $owner;
    }

    /**
     * Enregistre les données d'un responsable dans la table des responsables,
     * met à jour la visibilité de la commune
     * et renvoie l'identifiant responsableId
     *
     * @param array $data            
     *
     * @return <b>int</b> : le responsableId enregistré, que ce soit un nouvel enregistrement
     *         ou la modification d'un ancien
     */
    public function saveResponsable($data)
    {
        $tResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        $oData = $tResponsables->getObjData();
        $oData->exchangeArray($data);
        if (! $oData->userId) {
            $oData->userId = $this->userId;
        }
        if ($tResponsables->saveRecord($oData)) {
            // on s'assure de rendre cette commune visible
            $this->db_manager->get('Sbm\Db\table\Communes')->setVisible($oData->communeId);
        }
        // on récupère le responsableId qui vient d'être enregistré,
        // que ce soit un insert, un update ou la reprise d'un autre responsable par son email
        return $tResponsables->getLastResponsableId();
    }

    /**
     * Enregistre les données dans la table eleves et renvoie l'identifiant de l'élève que ce soit un
     * nouvel enregistrement ou pas.
     * S'il y a garde alternée, enregistre l'identifiant du responsable 2.
     *
     * @param array $data
     *            données à enrgistrer
     * @param boolean $hasGa
     *            indique s'il y a une garde alternée
     * @param string $responsable2Id
     *            identifiant du responsable 2
     *            
     * @return <b>int</b> : identifiant de l'élève qui vient d'être enregistré.
     */
    public function saveEleve($data, $hasGa = false, $responsable2Id = null)
    {
        $tEleves = $this->db_manager->get('Sbm\Db\Table\Eleves');
        $oData = $tEleves->getObjData();
        $responsable1Id = StdLib::getParam('responsable1Id', $data);
        if (empty($responsable1Id)) {
            if ($hasGa) {
                $oData->exchangeArray(array_merge($data, [
                    'responsable1Id' => $this->responsableId,
                    'responsable2Id' => $responsable2Id
                ]));
            } else {
                $oData->exchangeArray(array_merge($data, [
                    'responsable1Id' => $this->responsableId
                ]));
            }
        } else {
            if ($hasGa) {
                $oData->exchangeArray(array_merge($data, [
                    'responsable2Id' => $responsable2Id
                ]));
            } else {
                $oData->exchangeArray($data);
            }
        }
        $tEleves->saveRecord($oData);
        $eleveId = $oData->eleveId;
        if (empty($eleveId)) {
            $eleveId = $tEleves->getTableGateway()->getLastInsertValue();
        }
        return $eleveId;
    }

    /**
     * Enregistre la scolarité et renvoie un indicateur précisant si on doit
     * recalculer les distances.
     * Le recalcul des distances est nécessaire (true)
     * si l'établissement a changé,
     * si c'est un nouvel enregistrement
     * ou si district = 0
     *
     * @param array $data
     *            tableau de données contenant la scolarité
     *            
     * @return <b>boolean</b> : indicateur de recalcul de distances
     */
    public function saveScolarite($data, $eleveId = null)
    {
        $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $oData = $tScolarites->getObjData();
        if (is_null($eleveId)) {
            $array = [
                'millesime' => $this->millesime,
                'tarifId' => $this->db_manager->get('Sbm\Db\Table\Tarifs')->getTarifId('inscription')
            ];
        } else {
            $array = [
                'millesime' => $this->millesime,
                'eleveId' => $eleveId,
                'inscrit' => 1,
                'paiement' => 0,
                'demandeR1' => 1,
                'demandeR2' => $data['demandeR2'] ? 1 : 0,
                'internet' => 1,
                'duplicata' => 0,
                'anneeComplete' => 1,
                'subventionR1' => 0,
                'subventionR2' => 0,
                'tarifId' => $this->db_manager->get('Sbm\Db\Table\Tarifs')->getTarifId('inscription')
            ];
        }
        $oData->exchangeArray(array_merge($data, $array));
        return $tScolarites->saveRecord($oData);
    }

    /**
     * Enregistre les affectations de l'année précédente si le domicile et l'établissement
     * n'ont pas changé.
     */
    public function repriseAffectations()
    {
        $eleve = $this->db_manager->get('Sbm\Db\Table\Eleves')->getRecord($this->eleveId);
        // affectations de l'année précédentes
        $affectations = $this->db_manager->get('Sbm\Db\Table\Affectations')->fetchAll([
            'millesime' => $this->millesime - 1,
            'eleveId' => $this->eleveId
        ]);
        if ($affectations->count()) {
            // il y a des affectations. Faut-il les reprendre ?
            $this->repriseAffectationsPour($affectations, $eleve->responsable1Id, 1);
            if ($eleve->responsable2Id) {
                $this->repriseAffectationsPour($affectations, $eleve->responsable2Id, 2);
            }
        }
    }

    /**
     * Reprend les affectations de l'année antérieurepour un responsable donné
     * et les enregistre pour cette année.
     *
     * @param \Zend\Db\ResultSet\HydratingResultSet $affectations
     *            Les affectations de l'élève l'année antérieure
     * @param int $responsableId
     *            L'identifiant du responsable concerné
     * @param int $trajet
     *            1 pour le parent n°1 : 2 pour le parent n°2 en cas de garde alternée
     */
    private function repriseAffectationsPour($affectations, $responsableId, $trajet)
    {
        $responsable = $this->db_manager->get('Sbm\Db\Table\Responsables')->getRecord($responsableId);
        if ($this->memeDomicile($responsable) && $this->memeEtablissement()) {
            $tAffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
            foreach ($affectations as $oaffectation) {
                if ($oaffectation->responsableId == $responsableId) {
                    $oaffectation->millesime = $this->millesime;
                    $oaffectation->trajet = $trajet;
                    $tAffectations->saveRecord($oaffectation);
                }
            }
        }
    }

    /**
     * Indique si le responsable a le même domicile depuis le début de l'année scolaire précédente
     *
     * @param int $responsableId            
     *
     * @return <b>boolean</b>
     */
    private function memeDomicile($responsable)
    {
        if (! $responsable->demenagement) {
            return true;
        }
        $as = $this->db_manager->get('Sbm\Db\System\Calendar')->fetchAll([
            'millesime' => $this->millesime - 1,
            'nature' => 'AS'
        ]);
        if (! $as->count()) {
            return false; // il n'y avait pas d'année scolaire précédente donc rien à reprendre
        }
        $dateRef = DateTime::createFromFormat('Y-m-d', $as->current()->dateDebut);
        $dateDemenagement = DateTime::createFromFormat('Y-m-d', $responsable->dateDemenagement);
        return $dateDemenagement < $dateRef;
    }

    /**
     * Indique si l'élève a le même établissement que l'année précédente.
     *
     * @return boolean
     */
    private function memeEtablissement()
    {
        return $this->db_manager->get(Eleves::class)->memeEtablissement($this->eleveId);
    }
}