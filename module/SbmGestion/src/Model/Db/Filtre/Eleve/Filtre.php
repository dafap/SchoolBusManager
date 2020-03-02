<?php
/**
 * Filtres pour les requêtes de la classe \SbmGestion\Model\Db\Service\Eleve\Liste
 *
 * Définition du filtre selon la structure utilisée dans la construction du Where.
 * (voir méthode \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere($where, $filtre))
 *
 * @project sbm
 * @package SbmGestion/Model/Db/Filtre/Eleve
 * @filesource Filtre.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Db\Filtre\Eleve;

use SbmBase\Model\StdLib;
use SbmGestion\Model\Db\Service\Exception;

abstract class Filtre
{

    /**
     * Renvoie le filtre à utiliser comme paramètre dans l'appel de la méthode du même
     * nom. Ne renvoie pas les élèves rayés. Pour la compatibilité avec les autres
     * filtres, il est possible de passer 3 paramètres ou de les passer sous la forme d'un
     * tableau associatif ou d'un tableau ordonné (serviceId, stationId, inscrit). Le 3ème
     * élément du tableau est optionnel. Si le passage de paramètres se fait par un
     * tableau, les autres paramètres de la fonction sont ignorés.
     *
     * @param string|array $args
     *            valeur recherchée pour serviceId ou tableau des 3 paramètres
     * @param int $stationId
     *            valeur recherchée
     * @param bool $inscrit
     *            si true alors ne renvoie que les élèves inscrits sinon renvoie aussi les
     *            préinscrits
     * @throws \SbmGestion\Model\Db\Service\Exception
     *
     * @return array : tableau structuré pour la méthode
     *         \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byCircuit($args, $stationId = null, $inscrit = false)
    {
        if (is_array($args)) {
            if (array_key_exists('serviceId', $args) &&
                array_key_exists('stationId', $args)) {
                $serviceId = $args['serviceId'];
                $stationId = $args['stationId'];
                $inscrit = StdLib::getParam('inscrit', $args, false);
            } elseif (count($args) == 3) {
                list ($serviceId, $stationId, $inscrit) = $args;
            } elseif (count($args) == 2) {
                list ($serviceId, $stationId) = $args;
                $inscrit = false;
            } else {
                throw new Exception(
                    __METHOD__ .
                    ' : Le tableau passé en paramètre n\'a pas 2 ou 3 éléments.');
            }
        } elseif (is_null($stationId)) {
            throw new Exception(__METHOD__ . ' : Station indéterminée.');
        } elseif (is_scalar($args)) {
            $serviceId = $args;
        } else {
            throw new Exception(__METHOD__ . ' : Premier paramètre de type incorrect.');
        }
        if ($inscrit) {
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
                [
                    [
                        'service1Id' => $serviceId,
                        'station1Id' => $stationId
                    ],
                    'or',
                    [
                        'service2Id' => $serviceId,
                        'station2Id' => $stationId
                    ]
                ]
            ];
        } else {
            return [
                'inscrit' => 1,
                [
                    [
                        'service1Id' => $serviceId,
                        'station1Id' => $stationId
                    ],
                    'or',
                    [
                        'service2Id' => $serviceId,
                        'station2Id' => $stationId
                    ]
                ]
            ];
        }
    }

    /**
     *
     * @param int $classeId
     *
     * @return array : tableau structuré pour la méthode
     *         \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byClasse($classeId)
    {
        return [
            'inscrit' => 1,
            'sco.classeId' => $classeId
        ];
    }

    /**
     *
     * @param string $communeId
     *
     * @return array : tableau structuré pour la méthode
     *         \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byCommune($communeId)
    {
        return [
            'inscrit' => 1,
            [
                'sco.communeId' => $communeId,
                'or',
                'res.communeId' => $communeId
            ]
        ];
    }

    /**
     *
     * @param string $etablissementId
     *
     * @return array : tableau structuré pour la méthode
     *         \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byEtablissement($etablissementId)
    {
        return [
            'inscrit' => 1,
            'sco.etablissementId' => $etablissementId
        ];
    }

    /**
     * Filtre pour la requête byEtablissementService()<ul> <li>la condition
     * `s.etablissementId` porte sur la table scolarites</li> <li>la condition
     * `a.serviceId` porte sur une requête UNION</li></ul>
     *
     * @param string|array $args
     * @param string $serviceId
     *
     * @throws \SbmGestion\Model\Db\Service\Exception
     *
     * @return array : tableau structuré pour la méthode
     *         \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byEtablissementService($args, $serviceId = null)
    {
        if (is_array($args)) {
            if (array_key_exists('etablissementId', $args) &&
                array_key_exists('serviceId', $args)) {
                $etablissementId = $args['etablissementId'];
                $serviceId = $args['serviceId'];
            } elseif (count($args) == 2) {
                list ($etablissementId, $serviceId) = $args;
            } else {
                throw new Exception(
                    __METHOD__ . ' : Le tableau passé en paramètre n\'a pas 2 éléments.');
            }
        } elseif (is_null($serviceId)) {
            throw new Exception(__METHOD__ . ' : Service indéterminé.');
        } elseif (is_string($args)) {
            $etablissementId = $args;
        } else {
            throw new Exception(__METHOD__ . ' : Premier paramètre de type incorrect.');
        }
        return [
            'inscrit' => 1,
            's.etablissementId' => $etablissementId,
            'a.serviceId' => $serviceId
        ];
    }

    /**
     *
     * @param int $lotId
     *
     * @return array : tableau structuré pour la méthode
     *         \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byLot($lotId)
    {
        return [
            'inscrit' => 1,
            'lotId' => $lotId
        ];
    }

    /**
     *
     * @param string $serviceId
     *
     * @return array : tableau structuré pour la méthode
     *         \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byService($serviceId)
    {
        return [
            'inscrit' => 1,
            [
                'service1Id' => $serviceId,
                'or',
                'service2Id' => $serviceId
            ]
        ];
    }

    /**
     *
     * @param int $stationId
     *
     * @return array : tableau structuré pour la méthode
     *         \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byStation($stationId)
    {
        return [
            'inscrit' => 1,
            [
                'station1Id' => $stationId,
                'or',
                [
                    'station2Id' => $stationId,
                    'isNotNull' => [
                        'ligne2Id'
                    ]
                ]
            ]
        ];
    }

    /**
     *
     * @param int $transporteurId
     *
     * @return array : tableau structuré pour la méthode
     *         \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byTransporteur($transporteurId)
    {
        return [
            'inscrit' => 1,
            'ser.transporteurId' => $transporteurId
        ];
    }

    /**
     *
     * @param int $organismeId
     *
     * @return array : tableau structuré pour la méthode
     *         \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byOrganisme($organismeId)
    {
        return [
            'inscrit' => 1,
            'organismeId' => $organismeId
        ];
    }

    /**
     *
     * @param int $tarifId
     *
     * @return array : tableau structuré pour la méthode
     *         \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byTarif($tarifId)
    {
        return [
            'inscrit' => 1,
            'tarifId' => $tarifId
        ];
    }
}