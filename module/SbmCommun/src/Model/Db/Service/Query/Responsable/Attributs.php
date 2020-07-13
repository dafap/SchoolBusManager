<?php
/**
 * Classe donnant certains caractères d'un responsable
 *
 * Méthode pour connaitre à partir du responsableId :
 * - la categorieId (1 par défaut)
 * - le email
 * - l'identité (titre nom prénom)
 * - le nom de l'organisme s'il est de catégorie Organisme ou chaine vide sinon
 * - le organismeId si la categorie est dans l'intervalle [2, 99] ou 0 sinon
 * - le userId s'il a un compte d'utilisateur associé ou 0 sinon
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Attributs
 * @filesource Attributs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 juil. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Responsable;

use SbmCommun\Model\Db\Service\Query\AbstractQuery as ServiceAbstractQuery;
use Zend\Db\Sql\Select;

class Attributs extends ServiceAbstractQuery
{

    private $responsableId;

    private $data;

    private $eleveId;

    private $oEleve;

    protected function init()
    {
        $this->responsableId = 0;
        $this->data = null;
        $this->eleveId = 0;
        $this->oEleve = null;
    }

    public function getResponsableId(int $eleveId, int $r = 1)
    {
        if ($eleveId != $this->eleveId) {
            $this->eleveId = $eleveId;
            $tEleves = $this->db_manager->get('Sbm\Db\Table\Eleves');
            $this->oEleve = $tEleves->getRecord($eleveId);
        }
        return $this->oEleve->{'responsable' . $r . 'Id'};
    }

    /**
     * Renvoie l'identifiant de la catégorie ou 1 par défaut
     *
     * @param int $responsableId
     * @return int
     */
    public function getCategorieId(int $responsableId): int
    {
        if ($responsableId != $this->responsableId) {
            $this->loadData($responsableId);
        }
        return $this->data->categorieId ?: 1;
    }

    /**
     *
     * @param int $responsableId
     * @return string
     */
    public function getEmail(int $responsableId): string
    {
        if ($responsableId != $this->responsableId) {
            $this->loadData($responsableId);
        }
        return $this->data->email;
    }

    /**
     * Identité : Titre nom prénom
     *
     * @param int $responsableId
     * @return string
     */
    public function getIdentite(int $responsableId): string
    {
        if ($responsableId != $this->responsableId) {
            $this->loadData($responsableId);
        }
        return sprintf('%s %s %s', $this->data->titre, $this->data->nom,
            $this->data->prenom);
    }

    /**
     * Renvoie l'identifiant de l'organisme ou 0 par défaut
     *
     * @param int $responsableId
     * @return int
     */
    public function getOrganismeId(int $responsableId): int
    {
        if ($responsableId != $this->responsableId) {
            $this->loadData($responsableId);
        }
        return $this->data->organismeId ?: 0;
    }

    /**
     * Renvoie le nom de l'organisme ou chaine vide par défaut
     *
     * @param int $responsableId
     * @return string
     */
    public function getOrganisme(int $responsableId): string
    {
        if ($responsableId != $this->responsableId) {
            $this->loadData($responsableId);
        }
        return $this->data->organisme ?: '';
    }

    /**
     * Renvoie l'identifiant de l'utilisateur associé ou 0 par défaut
     *
     * @param int $responsableId
     * @return int
     */
    public function getUserId(int $responsableId): int
    {
        if ($responsableId != $this->responsableId) {
            $this->loadData($responsableId);
        }
        return $this->data->userId ?: 0;
    }

    /**
     * Charge les données
     *
     * @param int $responsableId
     */
    private function loadData(int $responsableId)
    {
        $this->data = $this->renderResult($this->selectData($responsableId))
            ->current();
        if ($this->data instanceof \ArrayObject) {
            $this->data->setFlags(\ArrayObject::ARRAY_AS_PROPS);
        }
    }

    protected function selectData(int $responsableId)
    {
        $select = $this->sql->select();
        return $select->columns([
            'responsableId',
            'titre',
            'nom',
            'prenom',
            'email'
        ])
            ->from([
            'res' => $this->db_manager->getCanonicName('responsables')
        ])
            ->join([
            'usr' => $this->db_manager->getCanonicName('users')
        ], 'res.email=usr.email', [
            'userId',
            'categorieId'
        ], Select::JOIN_LEFT)
            ->join([
            'u2o' => $this->db_manager->getCanonicName('users-organismes')
        ], 'usr.userId=u2o.userId', [
            'organismeId'
        ], Select::JOIN_LEFT)
            ->join([
            'org' => $this->db_manager->getCanonicName('organismes')
        ], 'u2o.organismeId=org.organismeId', [
            'organisme' => 'nom'
        ], Select::JOIN_LEFT);
    }
}