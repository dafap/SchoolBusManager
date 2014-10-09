<?php
/**
 * Module SbmUser pour les parents
 * - création et modification d'un compte
 * - envoi d'un mot de passe
 * - inscription d'un enfant
 * - suivi de la fiche d'un enfant : consultation, modifications de certaines données, suspension de l'inscription
 * - suivi des factures
 * - paiement des factures (si l'option est active)
 *
 * @project sbm
 * @package module/SbmUser
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */
namespace SbmUser;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
