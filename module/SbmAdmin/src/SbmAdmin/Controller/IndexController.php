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
use SbmCommun\Model\StdLib;
use SbmAdmin\Form\Libelle as FormLibelle;
use SbmCommun\Form\ButtonForm;
use SbmAdmin\Form\User;
use SbmAdmin\Form\Export as ExportForm;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
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
                    'form' => $form->prepare(),
                    'page' => $currentPage
                ));
                // 'id' => null
                
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
                        'form' => $form->prepare(),
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
            'id' => null
        ), array(
            'supproui' => array(
                'class' => 'confirm',
                'value' => 'Confirmer'
            ),
            'supprnon' => array(
                'class' => 'confirm',
                'value' => 'Abandonner'
            )
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
                        'form' => $form->prepare(),
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
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        list ($nature, $code) = explode('|', StdLib::getParam('id', $args, array(
            false,
            false
        )));
        if ($nature === false) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmadmin', array(
                'action' => 'libelle-liste',
                'page' => $currentPage
            ));
        }
        return new ViewModel(array(
            'data' => $this->getServiceLocator()
                ->get('Sbm\Db\Libelle\Liste')
                ->forNature($nature),
            'page' => $currentPage,
            'nature' => $nature,
            'code' => $code
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

    public function userListeAction()
    {
        $args = $this->initListe('users');
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Table\Users')
                ->paginator($args['where'], array(
                'categorieId Desc',
                'nom',
                'prenom'
            )),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_users', 20),
            'criteres_form' => $args['form']
        ));
    }

    public function userAjoutAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } else {
            $args = (array) $prg;
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage('Création abandonnée.');
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'user-liste',
                    'page' => $this->params('page', 1)
                ));
            }
        }
        $form = new User($this->getServiceLocator());
        $tUser = $this->getServiceLocator()->get('Sbm\Db\Table\Users');
        $form->setData(array(
            'userId' => null
        ))->bind($tUser->getObjData());
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $tUser->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage('Compte créé');
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'user-liste',
                    'page' => $this->params('page', 1)
                ));
            }
        }
        return new ViewModel(array(
            'form' => $form->prepare(),
            'page' => $this->params('page', 1)
        ));
    }

    public function userEditAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', false);
            if ($args === false || ! array_key_exists('userId', $args)) {
                return $this->redirect()->toRoute('login', array(
                    'action' => 'home-page'
                ));
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args) || ! array_key_exists('userId', $args)) {
                $this->flashMessenger()->addWarningMessage('Modification abandonnée.');
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'user-liste',
                    'page' => $this->params('page', 1)
                ));
            } elseif (! array_key_exists('submit', $args)) {
                $this->setToSession('post', $args);
            }
        }
        $form = new User($this->getServiceLocator());
        $tUser = $this->getServiceLocator()->get('Sbm\Db\Table\Users');
        $form->bind($tUser->getObjData());
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $tUser->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage('Modification enregistrée');
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'user-liste',
                    'page' => $this->params('page', 1)
                ));
            }
        }
        $user = $tUser->getRecord($args['userId']);
        $form->setData($user->getArrayCopy());
        return new ViewModel(array(
            'form' => $form->prepare(),
            'user' => $user,
            'page' => $this->params('page', 1)
        ));
    }

    public function userSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new ButtonForm(array(
            'id' => null
        ), array(
            'supproui' => array(
                'class' => 'confirm default',
                'value' => 'Confirmer'
            ),
            'supprnon' => array(
                'class' => 'confirm default',
                'value' => 'Abandonner'
            )
        ));
        $params = array(
            'data' => array(
                'alias' => 'Sbm\Db\Table\Users',
                'id' => 'userId'
            ),
            'form' => $form
        );
        
        $r = $this->supprData($params, function ($id, $tUsers) {
            return array(
                'id' => $id,
                'data' => $tUsers->getRecord($id)
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
                        'action' => 'user-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    $data = StdLib::getParam('data', $r->getResult());
                    $autorise = ($this->getServiceLocator()
                        ->get('Sbm\Db\Table\Responsables')
                        ->getRecordByEmail($data->email) === false);
                    return new ViewModel(array(
                        'autorise' => $autorise,
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => $data,
                        'userId' => StdLib::getParam('id', $r->getResult()),
                        'page' => $this->params('page', 1)
                    ));
                    break;
            }
        }
    }

    public function userLinkAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', false);
            if ($args === false || ! array_key_exists('email', $args)) {
                return $this->redirect()->toRoute('login', array(
                    'action' => 'home-page'
                ));
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args) || ! array_key_exists('email', $args)) {
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'user-liste',
                    'page' => $this->params('page', 1)
                ));
            } elseif (! array_key_exists('submit', $args)) {
                $this->setToSession('post', $args);
            }
        }
        // récupère l'email pour rechercher un responsable
        return new ViewModel(array(
            'user' => $this->getServiceLocator()
                ->get('Sbm\Db\Table\Users')
                ->getRecord($args['userId']),
            'responsable' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Responsables')
                ->getRecordByEmail($args['email']),
            'page' => $this->params('page', 1)
        ));
    }

    public function exportAction()
    {
        return new ViewModel();
    }

    public function exportEleveAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $form = new ExportForm('eleve', $this->getServiceLocator());
        if ($prg !== false) {
            if (array_key_exists('cancel', $prg)) {
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'export'
                ));
            } else {
                $form->setData($prg);
                if ($form->isValid()) {
                    $where = $form->whereEleve();
                    $resultset = $this->getServiceLocator()
                        ->get('Sbm\Db\Query\ElevesResponsables')
                        ->getLocalisation($where, array(
                        'nom_eleve',
                        'prenom_eleve'
                    ));
                    $data = iterator_to_array($resultset);
                    if (! empty($data)) {
                        $fields = array_keys(current($data));
                        return $this->csvExport('eleves.csv', $fields, $data);
                    } else {
                        $this->flashMessenger()->addInfoMessage('Il n\'y a pas de données correspondant aux critères indiqués.');
                    }
                }
            }
        }
        
        return new ViewModel(array(
            'form' => $form
        ));
    }

    public function exportEtablissementAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $form = new ExportForm('etablissement', $this->getServiceLocator());
        if ($prg !== false) {
            if (array_key_exists('cancel', $prg)) {
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'export'
                ));
            } else {
                $form->setData($prg);
                if ($form->isValid()) {
                    $where = $form->whereEtablissement();
                    $resultset = $this->getServiceLocator()
                        ->get('Sbm\Db\Query\Etablissements')
                        ->getLocalisation($where, array(
                        'commune',
                        'nom'
                    ));
                    $data = iterator_to_array($resultset);
                    if (! empty($data)) {
                        $fields = array_keys(current($data));
                        return $this->csvExport('etablissements.csv', $fields, $data);
                    } else {
                        $this->flashMessenger()->addInfoMessage('Il n\'y a pas de données correspondant aux critères indiqués.');
                    }
                }
            }
        }
        
        return new ViewModel(array(
            'form' => $form
        ));
    }

    public function exportResponsableAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $form = new ExportForm('responsable', $this->getServiceLocator());
        if ($prg !== false) {
            if (array_key_exists('cancel', $prg)) {
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'export'
                ));
            } else {
                $form->setData($prg);
                if ($form->isValid()) {
                    $where = $form->whereResponsable();
                    $resultset = $this->getServiceLocator()
                        ->get('Sbm\Db\Vue\Responsables')
                        ->fetchAll($where, array(
                        'commune',
                        'nom'
                    ));
                    $data = $resultset->toArray();
                    if (! empty($data)) {
                        $fields = array_keys(current($data));
                        return $this->csvExport('responsables.csv', $fields, $data);
                    } else {
                        $this->flashMessenger()->addInfoMessage('Il n\'y a pas de données correspondant aux critères indiqués.');
                    }
                }
            }
        }
        
        return new ViewModel(array(
            'form' => $form
        ));
    }

    public function exportStationAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $form = new ExportForm('station', $this->getServiceLocator());
        if ($prg !== false) {
            if (array_key_exists('cancel', $prg)) {
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'export'
                ));
            } else {
                $form->setData($prg);
                if ($form->isValid()) {
                    $where = $form->whereStation();
                    $resultset = $this->getServiceLocator()
                        ->get('Sbm\Db\Query\Stations')
                        ->getLocalisation($where, array(
                        'commune',
                        'nom'
                    ));
                    $data = iterator_to_array($resultset);
                    if (! empty($data)) {
                        $fields = array_keys(current($data));
                        return $this->csvExport('stations.csv', $fields, $data);
                    } else {
                        $this->flashMessenger()->addInfoMessage('Il n\'y a pas de données correspondant aux critères indiqués.');
                    }
                }
            }
        }
        
        return new ViewModel(array(
            'form' => $form
        ));
    }
    
    // ===========================================================================================================
    // méthodes du menu Bienvenue
    //
    public function modifCompteAction()
    {
        $retour = $this->url()->fromRoute('sbmadmin');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', array(
            'action' => 'modif-compte'
        ));
    }

    public function mdpChangeAction()
    {
        $retour = $this->url()->fromRoute('sbmadmin');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', array(
            'action' => 'mdp-change'
        ));
    }

    public function emailChangeAction()
    {
        $retour = $this->url()->fromRoute('sbmadmin');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', array(
            'action' => 'email-change'
        ));
    }

    public function messageAction()
    {
        $retour = $this->url()->fromRoute('sbmadmin');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('dafapmail');
    }
}