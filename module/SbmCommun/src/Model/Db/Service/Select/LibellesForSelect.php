<?php
/**
 * Services fournissant des requêtes sur les libellés
 *
 * Les méthodes de la classe permettent de filtrer la table selon quelques critères :
 * - ouvertes : pour les select destinés aux listes déroulantes
 * - toutes : pour les select destinés aux administrateurs
 * - caisse : liste des libelles concernant les caisses du régisseur
 * - modeDePaiement : liste des libelles concernant les modes de paiement
 * - nature : liste des natures de libellés sans doublon
 * - motfsReduction : liste des réductions (avec en plus Pas de réduction)
 *
 * @project sbm
 * @package SbmAdmin/Model/Db/Service/Select
 * @filesource LibellesForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LibellesForSelect implements FactoryInterface
{

    private $db_manager;

    private $table_name;

    private $sql;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        $this->table_name = $this->db_manager->getCanonicName('libelles', 'system');
        return $this;
    }

    public function nature()
    {
        $where = new Where();
        $where->literal('ouvert = 1');
        $select = $this->sql->select($this->table_name);
        $select->columns([
            'nature'
        ])
            ->order('nature')
            ->quantifier($select::QUANTIFIER_DISTINCT)
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['nature']] = $row['nature'];
        }
        return $array;
    }

    public function modeDePaiement()
    {
        $where = new Where();
        $where->literal("nature = 'ModeDePaiement'")->AND->literal('ouvert = 1');
        $select = $this->sql->select($this->table_name);
        $select->columns([
            'code',
            'libelle'
        ])
            ->order('code')
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['code']] = $row['libelle'];
        }
        return $array;
    }

    public function caisse()
    {
        $where = new Where();
        $where->literal('nature = "Caisse"')->AND->literal('ouvert = 1');
        $select = $this->sql->select($this->table_name);
        $select->columns([
            'code',
            'libelle'
        ])
            ->order('code')
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['code']] = $row['libelle'];
        }
        return $array;
    }

    public function toutes()
    {
        $select = $this->sql->select($this->table_name);
        $select->columns([
            'code',
            'libelle'
        ])->order([
            'nature',
            'code'
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['nature'] . $row['code']] = $row['libelle'];
        }
        return $array;
    }

    public function ouvertes()
    {
        $where = new Where();
        $where->literal('ouvert = 1');
        $select = $this->sql->select($this->table_name);
        $select->columns([
            'code',
            'libelle'
        ])
            ->order([
            'nature',
            'code'
        ])
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['nature'] . $row['code']] = $row['libelle'];
        }
        return $array;
    }

    /**
     * Renvoie les motifs d réduction en rajoutant au début 'Pas de réduction' en 0 et en
     * remplaçant le chaine %dateDebut% et %echeance% par leurs valeurs
     *
     * @return string[]
     */
    public function motifsReduction()
    {
        $tCalendar = $this->db_manager->get('Sbm\Db\System\Calendar');
        $etatDuSite = $tCalendar->getEtatDuSite();
        $where = new Where();
        $where->literal('ouvert = 1')->equalTo('nature', 'MotifReduction');
        $select = $this->sql->select($this->table_name);
        $select->columns([
            'code',
            'libelle'
        ])
            ->order([
            'nature',
            'code'
        ])
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [
            'Pas de réduction'
        ];
        foreach ($rowset as $row) {
            $array[$row['code']] = str_replace([
                '%dateDebut%',
                '%echeance%'
            ],
                [
                    $etatDuSite['dateDebut']->format('d/m/Y'),
                    $etatDuSite['echeance']->format('d/m/Y')
                ], $row['libelle']);
        }
        return $array;
    }
}