<?php
/**
 * Configuration du module SbmAuthentification
 * 
 * Initialidation des acl avec 
 *  - la liste des rôles, 
 *  - la correspondance avec la catégorie d'utilisateur,
 *  - la ressource par défaut,
 *  - la route à utiliser en cas d'accès non autorisé.
 * 
 *
 * @project sbm
 * @package SbmAuthentification/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 sept. 2018
 * @version 2018-2.4.5
 */
return [
    'acl' => [
        // association entre une categorieId (table users) et un rôle
        'roleId' => [
            1 => 'parent',
            2 => 'transporteur',
            3 => 'etablissement',
            200 => 'secretariat',
            253 => 'gestion',
            254 => 'admin',
            255 => 'sadmin'
        ],
        // hiérarchie des rôles
        'roles' => [
            'guest' => null,
            'transporteur' => 'guest',
            'etablissement' => 'guest',
            'secretariat' => 'guest',
            'parent' => 'guest',
            'gestion' => 'parent',
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
            'transporteur' => 'sbmportail',
            'etablissement' => 'sbmportail',
            'secretariat' => 'sbmportail',
            'parent' => 'sbmparent',
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