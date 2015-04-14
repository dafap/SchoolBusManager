<?php
/**
 * Controleur principal de l'application
 *
 *
 * @project sbm
 * @package module/SbmFront
 * @filesource src/SbmFront/Controller/IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 avr. 2014
 * @version 2014-1
 */
namespace SbmFront\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use SbmFront\Form\Login;
use SbmCommun\Model\StdLib;
use Zend\Crypt\Password\Bcrypt;
use Zend\Session\Container;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use SbmCommun\Filter\SansAccent;
use SbmCartographie\Model\Point;
use SbmCartographie\GoogleMaps\DistanceEtablissements;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $form = new Login($this->getServiceLocator());
        $form->setAttribute('action', $this->url()
            ->fromRoute('login', array(
            'action' => 'login'
        )));
        $tCalendar = $this->getServiceLocator()->get('Sbm\Db\System\Calendar');
        $rEtat = $tCalendar->etatDuSite();
        return new ViewModel(array(
            'form' => $form,
            'client' => StdLib::getParamR(array(
                'sbm',
                'client'
            ), $this->getServiceLocator()->get('config')),
            'etat' => $rEtat['etat'],
            'msg' => $rEtat['msg']
        ));
    }

    public function testAction()
    {
        $point = new Point(2.318593, 44.567649, 0,'degré');
        //$point = new Point(1639509.56, 3259755.14);
        //$point->setCommuneId('12013'); 
        $point->setCommuneId('12100');
        $distanceEtablissements = $this->getServiceLocator()->get('SbmCarto\DistanceEtablissements');
        return new ViewModel(array(
            'args' => $distanceEtablissements->collegesPrisEnCompte($point),
            'x' => null
        ));
    }
}