<?php
/**
 * Paramètres de configuration des sessions
 *
 * Consulter la documentation http://php.net/manual/fr/session.configuration.php
 * 
 * Voici les différentes possibilités pour les classes :
 *   array['dafap_session']['config']['class'] : 'Zend\Session\Config\StandardConfig' 
 *                                            ou 'Zend\Session\Config\SessionConfig'
 *   array['dafap_session']['storage']         : 'Zend\Session\Storage\ArrayStorage' 
 *                                            ou 'Zend\Session\Storage\SessionStorage'
 *                                            ou 'Zend\Session\Storage\SessionArrayStorage'
 *                                            ou un storage personnalisé implémentant Zend\Session\Storage\StorageInterface
 *   array['dafap_session']['save_handler']    : null ou une classe implémentant Zend\Session\SaveHandler\SaveHandlerInterface
 *
 * @project sbm
 * @package DafapSession/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 févr. 2015
 * @version 2015-1
 */
//die(realpath(__DIR__ . '/../../../vendor/zendframework/zendframework/resources/languages'));
return array(
    'acl' => array(
        // association entre une categorieId (table users) et un rôle
        'roleId' => array(
            1 => 'parent',
            2 => 'transporteur',
            3 => 'etablissement',
            253 => 'gestion',
            254 => 'admin',
            255 => 'sadmin'
        ),
        // hiérarchie des rôles
        'roles' => array(
            'guest' => null,
            'parent' => 'guest',
            'gestion' => 'parent',
            'admin' => 'gestion',
            'sadmin' => 'admin'
        ),
        'resources' => array(
            'home' => array(
                'allow' => array(
                    'roles' => array(
                        'guest'
                    )
                )
            )
        ),
        // routes de redirection lorsque l'accès n'est pas autorisé (en fonction du rôle)
        'redirectTo' => array(
            'parent' => 'sbmparent',
            'gestion' => 'sbmgestion',
            'admin' => 'sbmadmin',
            'sadmin' => 'sbminstall',
            'etablissement' => 'sbmeta',
            'transporteur' => 'sbmtra'
        )
    ),
    'dafap_session' => array(
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'cache_expire' => 180, // (int) Specifies time-to-live for cached session pages in minutes
                                       // 'cookie_domain' => '', // (string) Specifies the domain to set in the session cookie
                'cookie_httponly' => true, // (boolean) Marks the cookie as accessible only through the HTTP protocol
                'cookie_lifetime' => 10800, // (int) Specifies the lifetime of the cookie in seconds which is sent to the browser
                                            // 'cookie_path' => '/', // (string) Specifies path to set in the session cookie
                                            // 'cookie_secure' => false, // (boolean) Specifies whether cookies should only be sent over secure connections
                                            // 'entropy_length' => 0, // (int) Specifies the number of bytes which will be read from the file specified in entropy_file
                                            // 'entropy_file' => '', // (string) Defines a path to an external resource (file) which will be used as an additional entropy
                'gc_maxlifetime' => 3600, // (int) Specifies the number of seconds after which data will be seen as ‘garbage’
                                          // 'gc_divisor' => 100, // (int) Defines the probability that the gc process is started on every session initialization
                                          // 'gc_probability' => 1, // (int) Defines the probability that the gc process is started on every session initialization
                                          // 'hash_bits_per_character' => 6, // (int) Defines how many bits are stored in each character when converting the binary hash data (4, 5 or 6)
                'name' => 'SBM_SESSION', // (string) Specifies the name of the session which is used as cookie name
                                         // 'remember_me_seconds' => 3600, // (int) Specifies how long to remember the session before clearing data
                'save_path' => realpath(__DIR__ . '/../../../data/session')
            )
        ), // (string) Defines the argument which is passed to the save handler
          // 'use_cookies' => true, // (boolean) Spécifie si le module utilisera les cookies pour stocker l'id de session côté client
          // 'use_only_cookies' => true, // (boolean) Spécifie si le module doit utiliser seulement les cookies pour stocker les identifiants de sessions du côté du navigateur
        
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        'save_handler' => null, // null ou un Zend\Session\SaveHandler\SaveHandlerInterface si nécessaire
        'validators' => array(
            'Zend\Session\Validator\RemoteAddr',
            'Zend\Session\Validator\HttpUserAgent'
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'Dafap\AdapterByEmail' => 'DafapSession\Authentication\AdapterEmail',
            'Dafap\AdapterByToken' => 'DafapSession\Authentication\AdapterToken'
        ),
        'factories' => array(
            'Dafap\Authenticate' => 'DafapSession\Authentication\AuthenticationServiceFactory'
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator'
        )
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'DafapSessionAclRoutes' => 'DafapSession\Permissions\AclRoutes'
        )
    )
);