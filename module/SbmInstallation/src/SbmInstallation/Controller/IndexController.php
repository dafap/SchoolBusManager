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
        $result = array();
        return new ViewModel(array(
            'args' => $result
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
            $form = new ButtonForm(array(
                'create' => array(
                    'class' => 'confirm',
                    'value' => 'Confirmer'
                ),
                'cancel' => array(
                    'class' => 'confirm',
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