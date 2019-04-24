<?php
/**
 * Enregistrement d'un service de calcul d'effectifs
 *
 * Cette classe dérive de AbstractQuery et est dérivée sur l'un des types proposés
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource AbstractEffectif.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use SbmGestion\Model\Db\Service\AbstractQuery;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractEffectif extends AbstractQuery implements FactoryInterface
{

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     *
     * @var integer
     */
    protected $millesime;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

    /**
     * Nom des tables dans la base de données (donné par la méthode
     * DbManager::getCanonicName())
     *
     * @var array
     */
    protected $tableNames;

    /**
     * Tableau structuré des effectifs
     *
     * @var array
     */
    protected $structure;

    public function createService(ServiceLocatorInterface $db_manager)
    {
        if (! ($db_manager instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($db_manager)));
        }
        $this->millesime = Session::get('millesime');
        $this->dbAdapter = $db_manager->getDbAdapter();
        $this->sql = new Sql($db_manager->getDbAdapter());
        foreach ([
            'affectations',
            'circuits',
            'classe',
            'communes',
            'eleves',
            'etablissements',
            'responsables',
            'scolarites',
            'services'
        ] as $table_name) {
            $this->tableNames[$table_name] = $db_manager->getCanonicName($table_name,
                'table');
        }
        return $this;
    }



    protected function getFiltreDemandes(bool $sanspreinscrits)
    {
        if ($sanspreinscrits) {
            return [
                'inscrit' => 1,
                [
                    'paiement' => 1,
                    'or',
                    'fa' => 1,
                    'or',
                    '>' => [
                        'gratuit',
                        0
                    ]
                ]
            ];
        } else {
            return [
                'inscrit' => 1
            ];
        }
    }

    protected function getFiltreTransportes(bool $sanspreinscrits)
    {
        if ($sanspreinscrits) {
            return [
                'inscrit' => 1,
                [
                    'paiement' => 1,
                    'or',
                    'fa' => 1,
                    'or',
                    '>' => [
                        'gratuit',
                        0
                    ]
                ],
                'correspondance' => 1,
                [
                    [
                        '>' => [
                            'demandeR1',
                            0
                        ],
                        'trajet' => 1
                    ],
                    'or',
                    [
                        '=' => [
                            'demandeR1',
                            0
                        ],
                        'trajet' => 2
                    ]
                ]
            ];
        } else {
            return [
                'inscrit' => 1,
                'correspondance' => 1,
                [
                    [
                        '>' => [
                            'demandeR1',
                            0
                        ],
                        'trajet' => 1
                    ],
                    'or',
                    [
                        '=' => [
                            'demandeR1',
                            0
                        ],
                        'trajet' => 2
                    ]
                ]
            ];
        }
    }
}