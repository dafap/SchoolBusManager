<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource CarteController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 avr. 2015
 * @version 2015-1
 */
namespace SbmCartographie\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use SbmCommun\Model\StdLib;
use SbmCartographie\Model\Point;
use Zend\Http\PhpEnvironment\Response;

class CarteController extends AbstractActionController
{
    private $_initcarto;
    /**
     * projection utilisée
     * @var \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface
     */
    private $projection;
    public function indexAction()
    {
        return new ViewModel();
    }

    public function etablissementsAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } 
        $args = (array) $prg;
        if (array_key_exists('back', $args)) {
            $this->redirectToOrigin()->setBack($args['back']);
        }
        if (array_key_exists('cancel', $args)) {
            try {
                $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                $this->redirect()->toRoute('home');
            }
        }
        $this->init();
        $tEtablissements = $this->getServiceLocator()->get('Sbm\Db\Vue\Etablissements');
        $ptEtablissements = array();
        foreach ($tEtablissements->fetchAll() as $etablissement) {
            $pt = new Point($etablissement->x, $etablissement->y);
            $pt->setAttribute('etablissement', $etablissement);
            $ptEtablissements[] = $this->projection->xyzVersgRGF93($pt);
        }
        
        return new ViewModel(array(
            'ptEtablissements' => $ptEtablissements,
            'config' => StdLib::getParamR(array('sbm', 'cartes', 'etablissements'), $this->getServiceLocator()->get('config'))
        ));
    }
    
    private function init()
    {
        if (!$this->_initcarto) {
            $ns = '\\' . explode('\\', __NAMESPACE__)[0] . '\\ConvertSystemGeodetic\\Projection\\';
            $config = $this->getServiceLocator()->get('Config');
            $this->system = $ns . StdLib::getParamR(array(
                'cartographie',
                'system'
            ), $config);
            $nzone = StdLib::getParamR(array(
                'cartographie',
                'nzone'
            ), $config, 0);
            $this->projection = new $this->system($nzone);
            $this->_init = true;
        }
    }
    
} 