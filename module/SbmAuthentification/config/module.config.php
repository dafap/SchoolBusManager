<?php
/**
 * Configuration du module SbmAuthentification
 *
 * Initialidation des acl avec
 * - la liste des rôles,
 * - la correspondance avec la catégorie d'utilisateur,
 * - la ressource par défaut,
 * - la route à utiliser en cas d'accès non autorisé.
 *
 *
 * @project sbm
 * @package SbmAuthentification/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 avr. 2021
 * @version 2021-2.6.1
 */
use SbmAuthentification\Model\CategoriesInterface;
return [
    'acl' => [
        // association entre une categorieId (table users) et un rôle
        'roleId' => [
            CategoriesInterface::PARENT_ID => 'parent',
            CategoriesInterface::ORGANISME_ID => 'organisme',
            CategoriesInterface::TRANSPORTEUR_ID => 'transporteur',
            CategoriesInterface::GR_TRANSPORTEURS_ID => 'gr_transporteurs',
            CategoriesInterface::ETABLISSEMENT_ID => 'etablissement',
            CategoriesInterface::GR_ETABLISSEMENTS_ID => 'gr_etablissements',
            CategoriesInterface::COMMUNE_ID => 'commune',
            CategoriesInterface::GR_COMMUNES_ID => 'gr_communes',
            CategoriesInterface::SECRETARIAT_ID => 'secretariat',
            CategoriesInterface::GESTION_ID => 'gestion',
            CategoriesInterface::ADMINISTRATEUR_ID => 'admin',
            CategoriesInterface::SUPER_ADMINISTRATEUR_ID => 'sadmin'
        ],
        // hiérarchie des rôles
        'roles' => [
            'guest' => null,
            'transporteur' => 'guest',
            'gr_transporteurs' => 'transporteur',
            'etablissement' => 'guest',
            'gr_etablissements' => 'etablissement',
            'commune' => 'guest',
            'gr_communes' => 'commune',
            'secretariat' => 'guest',
            'parent' => 'guest',
            'organisme' => 'parent',
            'gestion' => 'organisme',
            'admin' => 'gestion',
            'sadmin' => 'admin'
        ],
        'resources' => [
            'home' => [
                'allow' => [
                    'roles' => [
                        'guest'
                    ]
                ]
            ]
        ],
        // routes de redirection lorsque l'accès n'est pas autorisé (en fonction du rôle)
        'redirectTo' => [
            'guest' => '/',
            'transporteur' => 'sbmportail/transporteur',
            'gr_transporteurs' => 'sbmportail/transporteur',
            'etablissement' => 'sbmportail/etablissement',
            'gr_etablissements' => 'sbmportail/etablissement',
            'commune' => 'sbmportail/commune',
            'gr_communes' => 'sbmportail/commune',
            'secretariat' => 'sbmportail',//'sbmportail/organisateur',
            'parent' => 'sbmparentconfig',
            'organisme' => 'sbmparent',
            'gestion' => 'sbmgestion',
            'admin' => 'sbmadmin',
            'sadmin' => 'sbminstall'
        ]
    ],
    'service_manager' => [
        'factories' => [
            'SbmAuthentification\Authentication' => 'SbmAuthentification\Authentication\AuthenticationServiceFactory',
            'SbmAuthentification\AclRoutes' => 'SbmAuthentification\Permissions\AclRoutes'
        ]
    ]
];