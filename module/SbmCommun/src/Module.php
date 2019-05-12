<?php
/**
 * Module SbmCommun qui regroupe les classes, stratégies, filtres, formulaires communs aus
 * - paramétrages
 * - données générales
 * - structures d'exportation et d'importation
 *
 * @project sbm
 * @package module/SbmCommun
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmBase\Module\AbstractModule;
use SbmCommun\Model\Strategy\Semaine;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\View\Helper\Doctype;

class Module extends AbstractModule implements BootstrapListenerInterface
{

    public function getDir()
    {
        return __DIR__;
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function onBootstrap(EventInterface $e)
    {
        $doctypeHelper = new Doctype();
        $doctypeHelper('HTML5');
        $sm = $e->getApplication()->getServiceManager();
        $db_manager = $sm->get('Sbm\DbManager');
        $tCalendar = $db_manager->get('Sbm\Db\System\Calendar');
        if ($sm->get('SbmAuthentification\Authentication')
            ->by()
            ->hasIdentity()) {
            for ($millesime = Session::get('millesime', false); ! $millesime; $millesime = Session::get(
                'millesime', false)) {
                Session::set('millesime', $tCalendar->getDefaultMillesime());
            }
        } else {
            $millesime = $tCalendar->getDefaultMillesime();
            Session::set('millesime', $millesime);
        }
        try {
            Session::set('as', $tCalendar->getAnneeScolaire($millesime));
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\RuntimeException $ex) {
        }
        $application = $e->getParam('application');
        $config = $application->getConfig();
        $this->getSemaine(StdLib::getParamR([
            'sbm',
            'semaine'
        ], $config, null));
    }

    public static function getSemaine($init = null)
    {
        static $semaine = [
            Semaine::CODE_SEMAINE_LUNDI => 'lun',
            Semaine::CODE_SEMAINE_MARDI => 'mar',
            Semaine::CODE_SEMAINE_MERCREDI => 'mer',
            Semaine::CODE_SEMAINE_JEUDI => 'jeu',
            Semaine::CODE_SEMAINE_VENDREDI => 'ven',
            Semaine::CODE_SEMAINE_SAMEDI => 'sam',
            Semaine::CODE_SEMAINE_DIMANCHE => 'dim'
        ];
        if ($init) {
            $semaine = $init;
        }
        return $semaine;
    }
}