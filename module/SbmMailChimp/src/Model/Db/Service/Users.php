<?php
/**
 * Requêtes sur la table users pour peupler la liste de MailChimp
 *
 * @project sbm
 * @package SbmMailChimp/Model/Db/Service
 * @filesource Users.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmMailChimp\Model\Db\Service;

use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Having;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Users implements FactoryInterface
{

    /**
     * Millesime d'inscription pour les parents
     *
     * @var int
     */
    private $millesime;

    /**
     * Date de début des inscriptions
     *
     * @var string
     */
    private $dateDebut;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    protected $db_manager;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'DbManager attendu. On a reçu %s.';
            throw new \SbmCommun\Model\Db\Exception\ExceptionNoDbManager(
                sprintf($message), gettype($serviceLocator));
        }
        $tCalendar = $serviceLocator->get('Sbm\Db\System\Calendar');
        $this->dateDebut = $tCalendar->etatDuSite()['dateDebut']->format('Y-m-d H:i:s');
        $this->millesime = $tCalendar->getDefaultMillesime();
        $this->db_manager = $serviceLocator;
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

    /**
     * Renvoie la chaine de requête (après l'appel de la requête)
     *
     * @param \Zend\Db\Sql\Select $select
     *
     * @return string
     */
    public function getSqlString($select)
    {
        return $select->getSqlString($this->dbAdapter->getPlatform());
    }

    /**
     * Renvoie les données à transmettre à la liste de MailChimp
     *
     * @param int $limit
     *            Si 0 (par défaut) pas de limit; sinon indiquer la valeur
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getMembersForMailChimpListe($limit = 0)
    {
        $sub_having = new Having();
        $sub_having->isNotNull('r.email');
        $sub_select = $this->sql->select(
            [
                'r' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns([
            'email',
            'nbelv' => new Expression('count(s.eleveId)')
        ])
            ->join([
            'e' => $this->db_manager->getCanonicName('eleves', 'table')
        ], 'r.responsableId = e.responsable1Id OR r.responsableId = e.responsable2Id', [])
            ->join([
            's' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'e.eleveId = s.eleveId', [])
            ->where([
            's.millesime' => $this->millesime,
            'e.mailchimp' => 1
        ])
            ->group([
            'r.email'
        ])
            ->having($sub_having);
        // die($this->getSqlString($sub_select));
        $select = $this->sql->select([
            'usr' => $this->usrUtiles()
        ])
            ->columns(
            [
                'email_address' => 'email',
                'PRENOM' => 'prenom',
                'NOM' => 'nom',
                'CATEGORIE' => 'categorieId',
                'CONFIRME' => 'confirme',
                'NBELV' => new Expression('IFNULL(`res`.`nbelv`, 0)'),
                'TERM' => new Expression(
                    'IFNULL(`term`.`userId`, 0) / IFNULL(`term`.`userId`, 1)'),
                'HORSTERM' => new Expression(
                    'IFNULL(`horsterm`.`userId`, 0) / IFNULL(`horsterm`.`userId`, 1)')
            ])
            ->join([
            'res' => $sub_select
        ], 'usr.email = res.email', [], Select::JOIN_LEFT)
            ->join([
            'term' => $this->usrTerminales()
        ], 'term.userId = usr.userId', [], Select::JOIN_LEFT)
            ->join([
            'horsterm' => $this->usrHorsTerminales()
        ], 'horsterm.userId = usr.userId', [], Select::JOIN_LEFT)
            ->where([
            'usr.categorieId' => 1
        ]);
        if ($limit) {
            $select->limit($limit);
        }
        // die($this->getSqlString($select));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        // TEST
        // $result = $statement->execute();
        // foreach ($result as $member) {
        // var_dump($member);
        // }
        // die();
        return $statement->execute();
    }

    /**
     * Ce sont les usr qui ont des enfants inscrits cette année ou qui en avait l'an
     * dernier. Pour 2016 cela donne : SELECT DISTINCT `u1`.`email`, `u1`.`prenom`,
     * `u1`.`nom`, `u1`.`categorieId`, `u1`.`confirme` FROM `sbm_t_users` AS `u1` INNER
     * JOIN `sbm_t_responsables` AS `r1` ON `r1`.`email` =`u1`.`email` INNER JOIN
     * `sbm_t_eleves` AS `e1` ON `e1`.`responsable1Id` = `r1`.`responsableId` OR
     * `e1`.`responsable2Id` = `r1`.`responsableId` INNER JOIN `sbm_t_scolarites` AS `s1`
     * ON `e1`.`eleveId` = `s1`.`eleveId` WHERE `s1`.`millesime` = '2015' UNION SELECT
     * DISTINCT email, prenom, nom, categorieId, confirme FROM `sbm_t_users` AS `u2` WHERE
     * `u2`.`dateCreation` > '2016-05-01'
     */
    private function usrUtiles()
    {
        $where = new Where();
        $where->greaterThanOrEqualTo('dateCreation', $this->dateDebut);
        $select2 = $this->sql->select(
            [
                'u2' => $this->db_manager->getCanonicName('users', 'table')
            ])
            ->columns([
            'userId',
            'email',
            'prenom',
            'nom',
            'categorieId',
            'confirme'
        ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where($where);

        $select1 = $this->sql->select(
            [
                'u1' => $this->db_manager->getCanonicName('users', 'table')
            ])
            ->columns([
            'userId',
            'email',
            'prenom',
            'nom',
            'categorieId',
            'confirme'
        ])
            ->join([
            'r1' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'r1.email = u1.email', [])
            ->join([
            'e1' => $this->db_manager->getCanonicName('eleves', 'table')
        ], 'r1.responsableId = e1.responsable1Id OR r1.responsableId = e1.responsable2Id',
            [])
            ->join([
            's1' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'e1.eleveId = s1.eleveId', [])
            ->group([
            'userId'
        ])
            ->where([
            's1.millesime' => $this->millesime - 1
        ]);
        return $select1->combine($select2);
    }

    private function usrTerminales()
    {
        $where = new Where();
        $where->equalTo('s3.millesime', $this->millesime - 1)
            ->literal('e3.mailchimp = 1')
            ->equalTo('c3.niveau', 8)
            ->isNull('c3.suivantId');
        $select = $this->sql->select(
            [
                'u3' => $this->db_manager->getCanonicName('users', 'table')
            ])
            ->columns([
            'userId'
        ])
            ->join([
            'r3' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'r3.email = u3.email', [])
            ->join([
            'e3' => $this->db_manager->getCanonicName('eleves', 'table')
        ], 'r3.responsableId = e3.responsable1Id OR r3.responsableId = e3.responsable2Id',
            [])
            ->join([
            's3' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'e3.eleveId = s3.eleveId', [])
            ->join([
            'c3' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'c3.classeId = s3.classeId', [])
            ->where($where);
        return $select;
    }

    private function usrHorsTerminales()
    {
        $where = new Where();
        $where->equalTo('s4.millesime', $this->millesime - 1)
            ->literal('e4.mailchimp = 1')
            ->nest()
            ->notEqualTo('c4.niveau', 8)->or->isNotNull('c4.suivantId')->unnest();
        $select = $this->sql->select(
            [
                'u4' => $this->db_manager->getCanonicName('users', 'table')
            ])
            ->columns([
            'userId'
        ])
            ->join([
            'r4' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'r4.email = u4.email', [])
            ->join([
            'e4' => $this->db_manager->getCanonicName('eleves', 'table')
        ], 'r4.responsableId = e4.responsable1Id OR r4.responsableId = e4.responsable2Id',
            [])
            ->join([
            's4' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'e4.eleveId = s4.eleveId', [])
            ->join([
            'c4' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'c4.classeId = s4.classeId', [])
            ->where($where);
        return $select;
    }
}