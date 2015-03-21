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
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        $dbAdapter = $db->getDbAdapter();
        $sql = new Sql($dbAdapter);
        $select1 = new Select();
        $select1->from('sbm_t_circuits')
            ->columns(array(
            'stationId'
        ))
            ->where(array(
            'millesime' => 2014
        ));
        $select = $sql->select();
        $select->from(array(
            's' => 'sbm_t_stations'
        ))
            ->join(array(
            'c' => $select1
        ), 's.stationId=c.stationId', array(), Select::JOIN_LEFT)
            ->where(function ($where) {
            $where->isNull('c.stationId');
        });
        $statement = $sql->prepareStatementForSqlObject($select);
        $statement->execute();
        $result = array();
        foreach ($statement->execute() as $row) {
            $result[] = $row;
        }
        return new ViewModel(array(
            'args' => $result,
            'x' => $statement->getSql()
        ));
    }
}