<?php
/**
 * Controller principal du module SbmAdmin
 *
 *
 * @project sbm
 * @package module/SbmGestion/src/SbmAdmin/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 août 2014
 * @version 2014-1
 */
namespace SbmAdmin\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;
use Zend\Db\Sql\Where;
use SbmCommun\Model\Db\ObjectData\Criteres as ObjectDataCriteres;
use SbmCommun\Form\CriteresForm;
use SbmAdmin\Form\DocumentPdf as FormDocumentPdf;
use SbmAdmin\Form\Libelle as FormLibelle;
use SbmCommun\Form\ButtonForm;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }

    public function libelleListeAction()
    {
        $args = $this->initListe('libelles');
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\System\Libelles')
                ->paginator($args['where']),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_libelles', 10),
            'criteres_form' => $args['form']
        ));
    }

    public function libelleAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new FormLibelle();
        $params = array(
            'data' => array(
                'table' => 'libelles',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\Libelles'
            ),
            'form' => $form
        );
        $r = $this->addData($params);
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'libelle-liste',
                    'page' => $currentPage
                ));
                break;
            default:
                return new ViewModel(array(
                    'form' => $form,
                    'page' => $currentPage
                )
                // 'id' => null
                );
                break;
        }
    }

    public function libelleEditAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new FormLibelle();
        
        $params = array(
            'data' => array(
                'table' => 'libelles',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\Libelles',
                'id' => 'id'
            ),
            'form' => $form
        );
        
        $r = $this->editData($params);
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmadmin', array(
                        'action' => 'libelle-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form,
                        'page' => $currentPage,
                        'id' => $r->getResult()
                    ));
                    break;
            }
        }
    }

    public function libelleSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new ButtonForm(array(
            'supproui' => array(
                'class' => 'confirm',
                'value' => 'Confirmer'
            ),
            'supprnon' => array(
                'class' => 'confirm',
                'value' => 'Abandonner'
            )
        ), array(
            'id' => null
        ));
        $params = array(
            'data' => array(
                'alias' => 'Sbm\Db\System\Libelles',
                'id' => 'id'
            ),
            'form' => $form
        );
        
        $r = $this->supprData($params, function ($id, $tableLibelles) {
            return array(
                'id' => implode('|', $id),
                'data' => $tableLibelles->getRecord($id)
            );
        });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmadmin', array(
                        'action' => 'libelle-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form,
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'id' => StdLib::getParam('id', $r->getResult())
                    ));
                    break;
            }
        }
    }

    public function libelleGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $id_get = $this->params('id', - 1); // GET
        $tableLibelles = $this->getServiceLocator()->get('Sbm\Db\System\Libelles');
        if ($id_get == - 1 || ! $tableLibelles->getObjData()->isValidId($id_get)) {
            return $this->redirect()->toRoute('sbmadmin', array(
                'action' => 'libelle-liste',
                'page' => $currentPage
            ));
        }
        
        $config = $this->getServiceLocator()->get('Config');
        $nb_libelles_pagination = $config['liste']['paginator']['nb_libelles_pagination'];
        
        list ($nature, $code) = \explode('|', $id_get);
        $where = new Where();
        $where->expression('nature = ?', $nature);
        
        return new ViewModel(array(
            'paginator' => $tableLibelles->paginator($where),
            'page' => $currentPage,
            'nb_libelles_pagination' => $nb_libelles_pagination,
            'nature' => $nature
        ));
    }

    public function libellePdfAction()
    {
        $currentPage = $this->params('page', 1);
        
        $criteres_form = new CriteresForm('libelles');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        $session = new SessionContainer(str_replace('pdf', 'liste', $this->getSessionNamespace()));
        if (isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
        }
        
        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setParam('documentId', 9)
            ->setParam('recordSource', 'Sbm\Db\System\Libelles')
            ->setParam('where', $criteres_obj->getWhere())
            ->setParam('orderBy', array(
            'nature',
            'code'
        ))
            ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }

    public function pdfListeAction()
    {
        $args = $this->initListe('pdf');
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\System\Documents')
                ->paginator($args['where']),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_pdf', 10),
            'criteres_form' => $args['form']
        ));
    }

    public function pdfAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        $form = new FormDocumentPdf($this->getServiceLocator());
        $form->setValueOptions('recordSource', $db->getTableList());
        $params = array(
            'data' => array(
                'table' => 'documents',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\Documents'
            ),
            'form' => $form
        );
        $r = $this->addData($params);
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'pdf-liste',
                    'page' => $currentPage
                ));
                break;
            default:
                return new ViewModel(array(
                    'form' => $form,
                    'page' => $currentPage,
                    'documentId' => null
                ));
                break;
        }
    }

    public function pdfEditAction()
    {
        ;
    }

    public function pdfDupliquerAction()
    {
        ;
    }

    public function pdfSupprAction()
    {
        ;
    }

    public function pdfGroupAction()
    {
        ;
    }

    public function pdfPdfAction()
    {
        ;
    }

    public function pdfTexteAction()
    {
        return new ViewModel();
    }
}