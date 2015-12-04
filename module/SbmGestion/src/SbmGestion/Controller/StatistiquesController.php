<?php
/**
 * Statistiques de gestion
 *
 * 
 * @project sbm
 * @package SbmGestion/Controller
 * @filesource StatistiquesController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 nov. 2015
 * @version 2015-1
 */
namespace SbmGestion\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Response;
use DafapSession\Model\Session;
use Zend\Filter\Null;

class StatistiquesController extends AbstractActionController
{

    public function indexAction()
    {        
        $prg = $this->prg();
        if ($prg instanceof Response)
        {
            return $prg;
        }
        return new ViewModel(array());
    }

    public function parClasseAction()
    {
        return new ViewModel(array(
            'millesime' => Session::get('millesime'),
            'statistiques' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->statistiquesParClasse()
        ));
    }

    public function parCommuneAction()
    {
        return new ViewModel(array(
            'millesime' => Session::get('millesime'),
            'statistiques' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->statistiquesParCommune()
        ));
    }

    public function parCircuitAction()
    {
        return new ViewModel(array(
            'millesime' => Session::get('millesime'),
            'statistiques' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->statistiquesParCircuit()
        ));
    }

    public function parEtablissementAction()
    {
        return new ViewModel(array(
            'millesime' => Session::get('millesime'),
            'statistiques' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->statistiquesParEtablissement()
        ));
    }
    
    public function parCircuitCommuneAction()
    {
        return new ViewModel(array(
            'millesime' => Session::get('millesime'),
            'statistiques' => $this->getServiceLocator()
            ->get('Sbm\Db\Eleve\Effectif')
            ->statistiquesParCircuitCommune()
        ));
    }
    
    public function parCommuneCircuitAction()
    {
        return new ViewModel(array(
            'millesime' => Session::get('millesime'),
            'statistiques' => $this->getServiceLocator()
            ->get('Sbm\Db\Eleve\Effectif')
            ->statistiquesParCommuneCircuit()
        ));
    }
    
    public function parClasseEtablissementAction()
    {
        return new ViewModel(array(
            'millesime' => Session::get('millesime'),
            'statistiques' => $this->getServiceLocator()
            ->get('Sbm\Db\Eleve\Effectif')
            ->statistiquesParClasseEtablissement()
        ));
    }
    
    public function parEtablissementClasseAction()
    {
        return new ViewModel(array(
            'millesime' => Session::get('millesime'),
            'statistiques' => $this->getServiceLocator()
            ->get('Sbm\Db\Eleve\Effectif')
            ->statistiquesParEtablissementClasse()
        ));
    }
}