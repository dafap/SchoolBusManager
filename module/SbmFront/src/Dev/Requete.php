<?php
/**
 * Enregistré dans module.config.php sous 'Sbm\Sadmin\Dev\Requete'
 * pour usage exclusif dans TestController en localhost
 *
 * La modifier ou la compléter à la demande sans changer son nom
 *
 * @project sbm
 * @package SbmFront\Dev
 * @filesource Requete.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmFront\Dev;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;

class Requete extends AbstractQuery
{
    protected function init()
    {
    }

    public function getZonage()
    {
        return $this->renderResult($this->selectZonage());
    }
    protected function selectZonage()
    {
        return $this->sql->select()->from('zonage');
    }

    public function getZonageIndex()
    {
        return $this->renderResult($this->selectZonageIndex());
    }
    protected function selectZonageIndex()
    {
        return $this->sql->select()->from('zonage-index');
    }
}