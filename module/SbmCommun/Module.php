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
 * @date 22 janv. 2014
 * @version 2014-1
 */
namespace SbmCommun;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\EventManager\EventInterface;
use ZfcBase\Module\AbstractModule;
use Zend\View\Helper\Doctype;
use DafapSession\Model\Session;
use SbmCommun\Model\StdLib;
use SbmCommun\Model\Strategy\Semaine;

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
        $tCalendar = $e->getApplication()->getServiceManager()->get('Sbm\Db\System\Calendar'); 
        for ($millesime = Session::get('millesime', false); !$millesime; $millesime = Session::get('millesime', false)) {
            Session::set('millesime', $tCalendar->getDefaultMillesime());
        }
        Session::set('as', $tCalendar->getAnneeScolaire($millesime));
        $application = $e->getParam('application');
        $config = $application->getConfig();
        $this->getSemaine(StdLib::getParamR(array(
            'sbm',
            'semaine'
        ), $config, null));
    }

    public static function getSemaine($init = null)
    {
        static $semaine = array(
            Semaine::CODE_SEMAINE_LUNDI => 'lun',
            Semaine::CODE_SEMAINE_MARDI => 'mar',
            Semaine::CODE_SEMAINE_MERCREDI => 'mer',
            Semaine::CODE_SEMAINE_JEUDI => 'jeu',
            Semaine::CODE_SEMAINE_VENDREDI => 'ven',
            Semaine::CODE_SEMAINE_SAMEDI => 'sam',
            Semaine::CODE_SEMAINE_DIMANCHE => 'dim'
        );
        if ($init) {
            $semaine = $init;
        }
        return $semaine;
    }
}
