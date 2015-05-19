<?php
/**
 * Controleur du module SbmInstallation
 *
 *
 * @project sbm
 * @package module/SbmInstallation/src/SbmInstallation/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */
namespace SbmInstallation\Controller;

use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Response;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmInstallation\Model\CreateTables;
use SbmInstallation\Model\Exception;
use SbmInstallation\Form\DumpTables as FormDumpTables;
use SbmInstallation\Model\DumpTables;
use SbmCommun\Form\ButtonForm;

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

    public function versionAction()
    {}

    public function fichierslogAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = (array) $prg;
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbminstall');
        }
        $config = $this->getServiceLocator()->get('config')['sbm']['paiement'];
        $fileNamePaiement = strtolower($config['plateforme']) . '_error.log';
        $filePaiement = $config['path_filelog'] . DIRECTORY_SEPARATOR . $fileNamePaiement;
        $fileErrors = $this->getServiceLocator()->get('config')['php_settings']['error_log'];
        if (array_key_exists('fichier', $args)) {
            switch ($args['fichier']) {
                case 'paiement':
                    $config = $this->getServiceLocator()->get('config')['sbm']['paiement'];
                    $filename = $fileNamePaiement;
                    $filenameWithPath = $filePaiement;
                    break;
                case 'logerror':
                    $filenameWithPath = $fileErrors;
                    $parts = explode('/', $filenameWithPath);
                    $filename = $parts[count($parts) - 1];
                    break;
                default:
                    $this->redirect()->toRoute('sbminstall');
                    break;
            }
            if (array_key_exists('drop', $args)) {
                file_put_contents($filenameWithPath, '');
            } else {
                $fileinfo = finfo_open(FILEINFO_MIME_TYPE);
                $contentType = finfo_file($fileinfo, $filenameWithPath);
                
                $response = new \Zend\Http\Response\Stream();
                $response->setStream(fopen($filenameWithPath, 'r'));
                $response->setStatusCode(200);
                
                $headers = new \Zend\Http\Headers();
                $headers->addHeaderLine('Content-Type', $contentType)
                    ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $filename . '"')
                    ->addHeaderLine('Content-Length', filesize($filenameWithPath));
                
                $response->setHeaders($headers);
                return $response;
            }
        }
        return new ViewModel(array(
            'form1' => new ButtonForm(array(
                'fichier' => 'paiement'
            ), array(
                'drop' => array(
                    'class' => 'confirm default',
                    'value' => 'Vider le fichier'
                ),
                'download' => array(
                    'class' => 'confirm default',
                    'value' => 'Télécharger le fichier des transactions'
                )
            )),
            'form2' => new ButtonForm(array(
                'fichier' => 'logerror'
            ), array(
                'drop' => array(
                    'class' => 'confirm default',
                    'value' => 'Vider le fichier'
                ),
                'download' => array(
                    'class' => 'confirm default',
                    'value' => 'Télécharger le fichier d\'erreurs'
                ),
                'cancel' => array(
                    'class' => 'confirm default',
                    'value' => 'Quitter'
                )
            )),
            'paiement' => array(
                'date' => date('d/m/Y H:i:s', filemtime($filePaiement)),
                'taille' => filesize($filePaiement),
                'lignes' => count(file($filePaiement))
            ),
            'errors' => array(
                'date' => date('d/m/Y H:i:s', filemtime($fileErrors)),
                'taille' => filesize($fileErrors),
                'lignes' => count(file($fileErrors))
            )
        ));
    }

    /**
     * Simple besoin de développement - Cette méthode doit être supprimée dans la version finale
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function majResponsablesAction()
    {
        $ctrl = array();
        $tResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
        $tEleves = $this->getServiceLocator()->get('Sbm\Db\Table\Eleves');
        $resultset = $tEleves->fetchAll();
        foreach ($resultset as $row) {
            if (! is_null($row->responsable2Id)) {
                $responsableId = $row->responsable2Id;
                $responsable = $tResponsables->getRecord($responsableId);
                if ($responsable->x == 0.0) {
                    $oData = $tResponsables->getObjData();
                    $oData->exchangeArray(array(
                        'responsableId' => $responsableId,
                        'x' => $row->x2,
                        'y' => $row->y2
                    ));
                    $tResponsables->saveRecord($oData);
                    $ctrl[] = array(
                        $responsableId,
                        $row->x2,
                        $row->y2
                    );
                }
            }
        }
        return new ViewModel(array(
            'args' => $ctrl
        ));
    }

    public function createTablesAction()
    {
        $create = new CreateTables($this->getDbConfig(), $this->getDbAdapter());
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                return $this->redirect()->toRoute('sbminstall', array(
                    'action' => 'index'
                ));
                $viewArgs = array();
            } else {
                $viewArgs = array(
                    'args' => $create->run(),
                    'form' => null
                );
            }
        } else {
            $form = new ButtonForm(array(), array(
                'create' => array(
                    'class' => 'confirm default',
                    'value' => 'Confirmer'
                ),
                'cancel' => array(
                    'class' => 'confirm default',
                    'value' => 'Abandonner'
                )
            ));
            $viewArgs = array(
                'args' => $create->voir(),
                'form' => $form
            );
        }
        return new ViewModel($viewArgs);
    }

    public function dumpTablesAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                return $this->redirect()->toRoute('sbminstall', array(
                    'action' => 'index'
                ));
                $viewArgs = array();
            } else {
                // TRAITEMENT DE LA DEMANDE
                $tables = $request->getPost('tables', array());
                $systems = $request->getPost('systems', array());
                $onscreen = $request->getPost('onscreen', false);
                $tables = array_merge($tables, $systems);
                
                $oDumpTable = $this->getServiceLocator()->get('SbmInstallation\DumpTables');
                $oDumpTable->init($tables, $onscreen);
                $description = $oDumpTable->copy();
                
                $viewArgs = array(
                    'tables' => $tables,
                    'titre' => 'Copie du contenu des tables',
                    'description' => empty($description) ? 'La copie est terminée.' : $description,
                    'form' => null
                );
            }
        } else {
            // FORMULAIRE DE DEMANDE
            $form = new FormDumpTables();
            $form->setValueOptions('tables', $this->getDbTablesAlias('table'));
            $form->setValueOptions('systems', $this->getDbTablesAlias('system'));
            
            $viewArgs = array(
                'tables' => array(),
                'titre' => 'Copie du contenu des tables',
                'description' => 'Cochez les tables que vous souhaitez récupérer dans un fichier de configuration.',
                'form' => $form
            );
        }
        return new ViewModel($viewArgs);
    }

    public function tableAliasAction()
    {
        $config = $this->getServiceLocator()->get('config');
        return new ViewModel(array(
            'tables' => $this->getDbTablesAlias('table'),
            'systems' => $this->getDbTablesAlias('system'),
            'vues' => $this->getDbTablesAlias('vue')
        ));
    }

    /**
     * Renvoie l'adapter donné par le ServiceManager factories
     *
     * @return \Zend\Db\Adapter\Adapter
     */
    private function getDbAdapter()
    {
        return $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    }

    /**
     * Renvoie un tableau ayant pour clés
     * - 'database' : le nom de la base de données
     * - 'prefix' : le préfixe des tables et des vues pour cette application
     * - 'definer' : le DEFINER des vues Mysql
     *
     * @return array
     */
    private function getDbConfig()
    {
        $config = $this->getServiceLocator()->get('config');
        return $config['db'];
    }

    /**
     * Renvoie un tableau décrivant la structure des tables et des vues définies dans SbmInstallation/db_design.
     * (voir SbmInstallation/db_design/README.txt)
     *
     * @return array
     */
    private function getDbDesign()
    {
        $config = $this->getServiceLocator()->get('config');
        return $config['db_design'];
    }

    /**
     * Renvoie les alias des tables définies dans le factories du SystemManager
     *
     * @param string $filter
     *            prend comme valeur 'table', 'system' ou 'vue'
     */
    private function getDbTablesAlias($filter = '')
    {
        $filter = strtolower($filter);
        if (! is_string($filter) || ! ($filter == '' || in_array($filter, array(
            'table',
            'system',
            'vue'
        )))) {
            throw new \Exception('Filtre incorrect dans ' . __METHOD__);
        }
        if ($filter == '') {
            $filters = array(
                'Sbm\\Db\\Table\\',
                'Sbm\\Db\\System\\',
                'Sbm\\Db\\Vue\\'
            );
        } else {
            $filters = array(
                'Sbm\\Db\\' . ucfirst($filter) . '\\'
            );
        }
        $config = $this->getServiceLocator()->get('config');
        $result = array();
        foreach (array_keys($config['service_manager']['factories']) as $alias) {
            foreach ($filters as $f) {
                if (strpos($alias, $f) !== false) {
                    $result[] = $alias;
                    break;
                }
            }
        }
        return $result;
    }
}