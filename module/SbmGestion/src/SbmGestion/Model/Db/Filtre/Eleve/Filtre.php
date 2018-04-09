<?php
/**
 * Filtres pour les requêtes de la classe SbmGestion\Model\Db\Service\Eleve\Liste
 *
 * Définition du filtre selon la structure utilisée dans la construction du Where.
 * (voir méthode SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere($where, $filtre))
 * 
 * @project sbm
 * @package SbmGestion/Model/Db/Filtre/Eleve
 * @filesource Filtre.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmGestion\Model\Db\Filtre\Eleve;

abstract class Filtre
{

    /**
     * Renvoie le filtre à utiliser comme paramètre dans l'appel de la méthode du même nom.
     * Ne renvoie pas les élèves rayés
     *
     * @param string $serviceId
     *            valeur recherchée
     * @param int $stationId
     *            valeur recherchée
     * @param bool $inscrit
     *            si true alors ne renvoie que les élèves inscrits
     *            sinon renvoie aussi les préinscrits
     *            
     * @return array : tableau structuré pour la méthode SbmGestion\Model\Db\Service\Eleve\Liste::arrayToWhere()
     */
    public static function byCircuit($serviceId, $stationId, $inscrit = false)
    {
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

    public static function byClasse($classeId)
    {
        return [
            'inscrit' => 1,
            'sco.classeId' => $classeId
        ];
    }

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

    public static function byEtablissement($etablissementId)
    {
        return [
            'inscrit' => 1,
            'sco.etablissementId' => $etablissementId
        ];
    }

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
                        'service2Id'
                    ]
                ]
            ]
        ];
    }

    public static function byTransporteur($transporteurId)
    {
        return [
            'inscrit' => 1,
            'transporteurId' => $transporteurId
        ];
    }

    public static function byOrganisme($organismeId)
    {
        return [
            'inscrit' => 1,
            'organismeId' => $organismeId
        ];
    }

    public static function byTarif($tarifId)
    {
        return [
            'inscrit' => 1,
            'tarifId' => $tarifId
        ];
    }
}