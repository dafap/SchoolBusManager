<?php
/**
 * Module SbmParent pour les parents
 * - création et modification d'un compte
 * - envoi d'un mot de passe
 * - inscription d'un enfant
 * - suivi de la fiche d'un enfant : consultation, modifications de certaines données, suspension de l'inscription
 * - suivi des factures
 * - paiement des factures (si l'option est active)
 *
 * @project sbm
 * @package module/SbmParent
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */
namespace SbmParent;

use ZfcBase\Module\AbstractModule;

class Module extends AbstractModule
{

    public function getDir()
    {
        return __DIR__;
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }
}
