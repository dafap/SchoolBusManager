<?php
/**
 * Paramètres de configuration des sessions
 *
 * Consulter la documentation https://secure.php.net/manual/fr/session.configuration.php
 *
 * Voici les différentes possibilités pour les classes :
 *   array['sbm_session']['config']['class']   : 'Zend\Session\Config\StandardConfig'
 *                                            ou 'Zend\Session\Config\SessionConfig'
 *   array['sbm_session']['config']['options'] : (voir plus loin)
 *   array['sbm_session']['storage']           : 'Zend\Session\Storage\ArrayStorage'
 *                                            ou 'Zend\Session\Storage\SessionStorage'
 *                                            ou 'Zend\Session\Storage\SessionArrayStorage'
 *                                            ou classe implémentant Zend\Session\Storage\StorageInterface
 *   array['sbm_session']['save_handler']      : null
 *                                            ou classe implémentant Zend\Session\SaveHandler\SaveHandlerInterface
 *   array['sbm_session']['validators']        :
 *
 *  Les options de ['config']['options']
 *   'cache_expire' => int            : durée de vie des données de sessions, en minutes.
 *                                      Par défaut, 180 (3 heures)
 *   'cookie_domain' => string        : domaine utilisé lors de la création du cookie.
 *                                      Par défaut, '' cad que c'est le nom de l'hôte du serveur
 *   'cookie_httponly' => boolean     : Marque le cookie pour qu'il ne soit accessible que via
 *                                      le protocole HTTP donc inaccessible des langages scripts.
 *                                      Par défaut, off.
 *   'cookie_lifetime' => int         : durée de vie du cookie en secondes.
 *                                      Par défaut 0 (cad "Jusqu'à ce que le navigateur soit éteint").
 *   'cookie_path' => string          : chemin utilisé lors de la création du cookie.
 *                                      Par défaut, /.
 *   'cookie_secure' => boolean       : les cookies ne sont émis que sur des connexions sécurisées.
 *                                      Par défaut, off.
 *   'entropy_file' => string         : chemin jusqu'à une source externe (un fichier), qui sera
 *                                      utilisée comme source additionnelle d'entropie pour la
 *                                      création de l'identifiant de session.
 *                                      Par défaut, ''.
 *   'entropy_length' => int          : nombre d'octets qui seront lus dans le fichier défini ci-dessus.
 *                                      Par défaut, il vaut 0, c'est-à-dire inactif.
 *   'gc_maxlifetime' => int          : durée de vie en secondes des données sur le serveur.
 *                                      Par défaut, 1440.
 *   'gc_divisor' => int              : probabilité que la routine gc (garbage collector) soit
 *                                      démarrée à chaque début de session. La probabilité est
 *                                      calculée en utilisant gc_probability/gc_divisor.
 *                                      Par défaut, 100.
 *   'gc_probability' => int          : probabilité, exprimée en pourcentage, en conjonction de
 *                                      session.gc_divisor, que la routine gc (garbage collector)
 *                                      soit démarrée à chaque requête.
 *                                      Par défaut, 1.
 *   'hash_bits_per_character' => int : nombre de bits utilisés pour chaque caractère lors des
 *                                      conversions des données binaires en éléments lisibles.
 *                                      Les valeurs possibles sont :
 *                                      '4' (0-9, a-f),
 *                                      '5' (0-9, a-v),
 *                                      '6' (0-9, a-z, A-Z, "-", ",")
 *                                      Par défaut, 4.
 *   'name' => string                 : nom de la session, utilisé comme nom de cookie.
 *                                      Par défaut, PHPSESSID.
 *   'remember_me_seconds' => int     : temps de vie en secondes du cookie de session, une fois le
 *                                      navigateur client fermé.
 *                                      Par défaut, 3600 (1 heure).
 *   'save_path' => string            : chemin passé au gestionnaire de sauvegarde. Pour le
 *                                      gestionnaire par défaut (par fichiers), c'est le dossier
 *                                      de sauvegarde des sessions.
 *                                      Par défaut, ''.
 *   'use_cookies' => boolean         : indique si le module utilisera les cookies pour stocker
 *                                      l'id de session côté client.
 *                                      Par défaut, true (c'est-à-dire actif).
 *   'use_only_cookies' => boolean    : indique si le module doit utiliser seulement les cookies
 *                                      pour stocker les identifiants de sessions du côté du
 *                                      navigateur pour éviterer les attaques qui utilisent des
 *                                      identifiants de sessions dans les URL.
 *                                      Par défaut, true (depuis PHP 5.3.0)
 *
 * @project sbm
 * @package SbmBase/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 mars 2019
 * @version 2019-2.5.0
 */
use SbmBase\Model\StdLib;
use SbmBase\Model\View\Helper;

return [
    'sbm_session' => [
        'config' => [
            // éventuellement, définir la clé 'class' => 'Zend\Session\Config\SessionConfig' dans
            // sbm.local.php
            'options' => [
                'cache_expire' => 180, // 3 heures
                'cookie_httponly' => true,
                'cookie_lifetime' => 10800, // 3 heures
                'gc_maxlifetime' => 3600, // 1 heure
                'name' => 'SBM_SESSION',
                'save_path' => realpath(StdLib::findParentPath(__DIR__, 'data/session'))
            ]
        ],

        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        'save_handler' => null,
        'validators' => [
            'Zend\Session\Validator\RemoteAddr',
            'Zend\Session\Validator\HttpUserAgent'
        ]
    ],
    'view_helpers' => [
        'aliases' => [
            'jQuery' => Helper\JQuery::class,
            'jquery' => Helper\JQuery::class,
            'JQuery' => Helper\JQuery::class
        ],
        'factories' => [
            Helper\JQuery::class => Helper\JQueryFactory::class
        ]
    ]
];