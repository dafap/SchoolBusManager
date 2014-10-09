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
use SbmCommun\Form\ButtonForm;
use SbmCommun\Model\StdLib;
use Zend\Filter\StringToUpper;
use SbmCommun\Filter\StringUcfirst;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }

    public function testAction()
    {
        die(var_dump(StdLib::addQuotesToString('maternelle')));
        return new ViewModel(array(
            'args' => array(
                $config['doctable']['columns'], $data
            )
        ));
    }
}