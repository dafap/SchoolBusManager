<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 févr. 2015
 * @version 2015-1
 */
namespace SbmParent\Controller;

use SbmParent\Form\CreerCompte;
use Zend\View\Model\ViewModel;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use DafapSession\Model\Session;
use SbmParent\Model\Responsable;


class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        try {
            $responsable = new Responsable($this->getServiceLocator());
        } catch (Exception $e) {
            return $this->redirect()->toRoute('login', array('action' => 'logout'));
        }
        $query = $this->getServiceLocator()->get('Sbm\Db\Query\ElevesScolarites');
        $paiements = $this->getServiceLocator()->get('Sbm\Db\Vue\Paiements');
        return new ViewModel(array(
            'inscrits' => $query->getElevesInscrits($responsable->responsableId),
            'preinscrits' => $query->getElevesPreinscrits($responsable->responsableId),
            'paiements' => $paiements->fetchAll(array('responsableId' => $responsable->responsableId)),
            'affectations' => $this->getServiceLocator()->get('Sbm\Db\Query\AffectationsServicesStations')
        ));
    }
} 