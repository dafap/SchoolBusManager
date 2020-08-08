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
 * @date 7 juin 2020
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
     * filtres, il est possible de passer 3 paramètres (le premier est obligatoirement un
     * tableau de 4 valeurs au moins) ou de les passer sous la forme d'un seul tableau
     * associatif ou d'un tableau ordonné (ligneId, sens, moment, ordre, stationId,
     * inscrit). Les 5ème et 6ème éléments du tableau sont optionnels mais il faut pouvoir
     * récupérer stationId d'une façon ou d'une autre. On peut passer une partie des
     * paramètres par tableau et d'autres par les paramètres de la fonction.
     *
     * @param array $args
     *            tableau ligneId, sens, moment, ordre, avec éventuellement stationId
     *            et/ou inscrit
     * @param int $stationId
     *            valeur recherchée
     * @param bool $inscrit
     *            si true alors ne renvoie que les élèves inscrits sinon renvoie aussi les
     *            préinscrits
     * @param bool $montee
     *            par défaut (true) filtre les élèves qui montent (station1Id), sinon
     *            (false) ceux qui descendent
     * @throws \SbmGestion\Model\Db\Service\Exception
     *
     * @return array : tableau structuré pour la méthode
     *         \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byCircuit($args, $stationId = null, $inscrit = false,
        $montee = true)
    {
        if (is_array($args)) {
            if (array_key_exists('ligneId', $args) && array_key_exists('sens', $args) &&
                array_key_exists('moment', $args) && array_key_exists('ordre', $args)) {
                // tableau associatif (au moins les 4 clés d'identification d'un service)
                $ligneId = $args['ligneId'];
                $sens = $args['sens'];
                $moment = $args['moment'];
                $ordre = $args['ordre'];
                $stationId = StdLib::getParam('stationId', $args, $stationId);
                $inscrit = StdLib::getParam('inscrit', $args, $inscrit);
            } elseif (count($args) == 6) {
                // tableau indexé de 6 éléments
                list ($ligneId, $sens, $moment, $ordre, $stationId, $inscrit) = $args;
            } elseif (count($args) == 5) {
                // tableau indexé de 5 éléments
                list ($ligneId, $sens, $moment, $ordre, $stationId) = $args;
            } elseif (count($args) == 4) {
                // tableau indexé de 4 éléments
                list ($ligneId, $sens, $moment, $ordre) = $args;
            } else {
                throw new Exception(
                    __METHOD__ .
                    ' : Le tableau passé en paramètre n\'a pas 5 ou 6 éléments.');
            }
        } elseif (is_null($stationId)) {
            throw new Exception(__METHOD__ . ' : Station indéterminée.');
        } elseif (is_scalar($args)) {
            $ligneId = $args;
            throw new Exception(__METHOD__ . ' : Code à mettre à jour.');
        } else {
            throw new Exception(__METHOD__ . ' : Premier paramètre de type incorrect.');
        }
        if ($inscrit) {
            if ($montee) {
                return [
                    'inscrit' => 1,
                    [
                        'paiementR1' => 1,
                        'or',
                        'gratuit' => 1
                    ],
                    'ligne1Id' => $ligneId,
                    'sensligne1' => $sens,
                    'moment' => $moment,
                    'ordreligne1' => $ordre,
                    'station1Id' => $stationId
                ];
            } else {
                return [
                    'inscrit' => 1,
                    [
                        'paiementR1' => 1,
                        'or',
                        'gratuit' => 1
                    ],
                    'ligne1Id' => $ligneId,
                    'sensligne1' => $sens,
                    'moment' => $moment,
                    'ordreligne1' => $ordre,
                    'station2Id' => $stationId
                ];
            }
        } else {
            if ($montee) {
                return [
                    'inscrit' => 1,
                    'ligne1Id' => $ligneId,
                    'sensligne1' => $sens,
                    'moment' => $moment,
                    'ordreligne1' => $ordre,
                    'station1Id' => $stationId
                ];
            } else {
                return [
                    'inscrit' => 1,
                    'ligne1Id' => $ligneId,
                    'sensligne1' => $sens,
                    'moment' => $moment,
                    'ordreligne1' => $ordre,
                    'station2Id' => $stationId
                ];
            }
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
     *            etablissementId ou tableau contenant tous les paramètres
     * @param string|array $arrayServiceId
     *            ligneId ou tableau des paramètres identifiant le service (ligneId, sens,
     *            moment, ordre)
     * @param int $sens
     * @param int $moment
     * @param int $ordre
     *
     * @throws \SbmGestion\Model\Db\Service\Exception
     *
     * @return array : tableau structuré pour la méthode
     *         \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byEtablissementService($args, $arrayServiceId = null, $sens = 0,
        $moment = 0, $ordre = 0)
    {
        if (is_array($args)) {
            if (array_key_exists('etablissementId', $args) &&
                array_key_exists('ligneId', $args) && array_key_exists('sens', $args) &&
                array_key_exists('moment', $args) && array_key_exists('ordre', $args)) {
                $etablissementId = $args['etablissementId'];
                $ligneId = $args['ligneId'];
                $sens = $args['sens'];
                $moment = $args['moment'];
                $ordre = $args['ordre'];
            } elseif (count($args) == 5) {
                list ($etablissementId, $ligneId, $sens, $moment, $ordre) = $args;
            } else {
                throw new Exception(
                    __METHOD__ . ' : Le tableau passé en paramètre n\'a pas 2 éléments.');
            }
        } elseif (is_null($arrayServiceId)) {
            throw new Exception(__METHOD__ . ' : Service indéterminé.');
        } elseif (is_string($args) && is_array($arrayServiceId)) {
            $etablissementId = $args;
            if (array_key_exists('ligneId', $arrayServiceId) &&
                array_key_exists('sens', $arrayServiceId) &&
                array_key_exists('moment', $arrayServiceId) &&
                array_key_exists('ordre', $arrayServiceId)) {
                $ligneId = $args['ligneId'];
                $sens = $args['sens'];
                $moment = $args['moment'];
                $ordre = $args['ordre'];
            } elseif (count($args) == 4) {
                list ($ligneId, $sens, $moment, $ordre) = $arrayServiceId;
            } else {
                throw new Exception(
                    __METHOD__ .
                    ' : Le tableau passé en deuxième paramètre n\'a pas 4 éléments.');
            }
        } elseif (is_string($args) && is_string($arrayServiceId)) {
            $etablissementId = $args;
            $ligneId = $arrayServiceId;
        } else {
            throw new Exception(__METHOD__ . ' : Premier paramètre de type incorrect.');
        }
        return [
            'inscrit' => 1,
            's.etablissementId' => $etablissementId,
            'a.ligneId' => $ligneId,
            'a.sens' => $sens,
            'a.moment' => $moment,
            'a.ordre' => $ordre
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
     * @param string|array $args
     *            tous les paramètres ou ligneId seulement
     * @param number $sens
     * @param number $moment
     * @param number $ordre
     *
     * @return array : tableau structuré pour la méthode
     *         \SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byService($args, $sens = 0, $moment = 0, $ordre = 0)
    {
        if (is_array($args)) {
            if (array_key_exists('ligneId', $args) && array_key_exists('sens', $args) &&
                array_key_exists('moment', $args) && array_key_exists('ordre', $args)) {
                // tableau associatif (au moins les 4 clés d'identification d'un service)
                $ligneId = $args['ligneId'];
                $sens = $args['sens'];
                $moment = $args['moment'];
                $ordre = $args['ordre'];
            } elseif (count($ligneId) == 4) {
                list ($ligneId, $sens, $moment, $ordre) = $args;
            } else {
                $ligneId = $args;
            }
        } else {
            $ligneId = $args;
        }
        return [
            'inscrit' => 1,
            [
                [
                    'ligne1Id' => $ligneId,
                    'sensligne1' => $sens,
                    'moment' => $moment,
                    'ordreligne1' => $ordre
                ],
                'or',
                [
                    'ligne2Id' => $ligneId,
                    'sensligne2' => $sens,
                    'moment' => $moment,
                    'ordreligne2' => $ordre
                ]
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
                [
                    'stationIdR1' => $stationId,
                    '>' => [
                        'demandeR1',
                        0
                    ]
                ],
                'or',
                [
                    'stationIdR2' => $stationId,
                    '>' => [
                        'demandeR2',
                        0
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
    public static function byGrilleTarif($grilleTarif, $reduction)
    {
        return [
            'inscrit' => 1,
            [
                [
                    '>' => [
                        'sco.demandeR1',
                        0
                    ],
                    'sco.grilleTarifR1' => $grilleTarif,
                    'sco.reductionR1' => $reduction
                ],
                'or',
                [
                    '>' => [
                        'sco.demandeR2',
                        0
                    ],
                    'sco.grilleTarifR2' => $grilleTarif,
                    'sco.reductionR2' => $reduction
                ]
            ]
        ];
    }
}