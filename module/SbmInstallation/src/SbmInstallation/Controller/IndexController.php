<?php
/**
 * Controleur du module SbmInstallation
 *
 * Compatible ZF3
 * 
 * @project sbm
 * @package module/SbmInstallation/src/SbmInstallation/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 avr. 2016
 * @version 2016-2
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
use DrewM\MailChimp;

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
        $config_paiement = $this->config['config_paiement'];
        $fileNamePaiement = strtolower($config_paiement['plateforme']) . '_error.log';
        $filePaiement = $config_paiement['path_filelog'] . DIRECTORY_SEPARATOR . $fileNamePaiement;
        $fileErrors = $this->config['error_log'];
        if (array_key_exists('fichier', $args)) {
            switch ($args['fichier']) {
                case 'paiement':
                    $filename = $fileNamePaiement;
                    $filenameWithPath = $filePaiement;
                    break;
                case 'logerror':
                    $filenameWithPath = $fileErrors;
                    $parts = explode('/', $filenameWithPath);
                    $filename = $parts[count($parts) - 1];
                    break;
                default:
                    return $this->redirect()->toRoute('sbminstall');
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
        return new ViewModel([
            'form1' => new ButtonForm([
                'fichier' => 'paiement'
            ], [
                'drop' => [
                    'class' => 'confirm default',
                    'value' => 'Vider le fichier'
                ],
                'download' => [
                    'class' => 'confirm default',
                    'value' => 'Télécharger le fichier des transactions'
                ]
            ]),
            'form2' => new ButtonForm([
                'fichier' => 'logerror'
            ], [
                'drop' => [
                    'class' => 'confirm default',
                    'value' => 'Vider le fichier'
                ],
                'download' => [
                    'class' => 'confirm default',
                    'value' => 'Télécharger le fichier d\'erreurs'
                ],
                'cancel' => [
                    'class' => 'confirm default',
                    'value' => 'Quitter'
                ]
            ]),
            'paiement' => [
                'date' => date('d/m/Y H:i:s', filemtime($filePaiement)),
                'taille' => filesize($filePaiement),
                'lignes' => count(file($filePaiement))
            ],
            'errors' => [
                'date' => date('d/m/Y H:i:s', filemtime($fileErrors)),
                'taille' => filesize($fileErrors),
                'lignes' => count(file($fileErrors))
            ]
        ]);
    }

    public function createTablesAction()
    {
        $create = new CreateTables($this->getDbConfig(), $this->getDbAdapter());
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                return $this->redirect()->toRoute('sbminstall', [
                    'action' => 'index'
                ]);
                $viewArgs = [];
            } else {
                $viewArgs = [
                    'args' => $create->run(),
                    'form' => null
                ];
            }
        } else {
            $form = new ButtonForm([], [
                'create' => [
                    'class' => 'confirm default',
                    'value' => 'Confirmer'
                ],
                'cancel' => [
                    'class' => 'confirm default',
                    'value' => 'Abandonner'
                ]
            ]);
            $viewArgs = [
                'args' => $create->voir(),
                'form' => $form
            ];
        }
        return new ViewModel($viewArgs);
    }

    public function dumpTablesAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                return $this->redirect()->toRoute('sbminstall', [
                    'action' => 'index'
                ]);
                $viewArgs = [];
            } else {
                // TRAITEMENT DE LA DEMANDE
                $tables = $request->getPost('tables', []);
                $systems = $request->getPost('systems', []);
                $onscreen = $request->getPost('onscreen', false);
                $tables = array_merge($tables, $systems);
                
                $oDumpTable = new DumpTables($this->config['db_manager']);
                $oDumpTable->init($tables, $onscreen);
                $description = $oDumpTable->copy();
                
                $viewArgs = [
                    'tables' => $tables,
                    'titre' => 'Copie du contenu des tables',
                    'description' => empty($description) ? 'La copie est terminée.' : $description,
                    'form' => null
                ];
            }
        } else {
            // FORMULAIRE DE DEMANDE
            $form = new FormDumpTables();
            $form->setValueOptions('tables', $this->getDbTablesAlias('table'));
            $form->setValueOptions('systems', $this->getDbTablesAlias('system'));
            
            $viewArgs = [
                'tables' => [],
                'titre' => 'Copie du contenu des tables',
                'description' => 'Cochez les tables que vous souhaitez récupérer dans un fichier de configuration.',
                'form' => $form
            ];
        }
        return new ViewModel($viewArgs);
    }

    public function tableAliasAction()
    {
        return new ViewModel([
            'tables' => $this->getDbTablesAlias('table'),
            'systems' => $this->getDbTablesAlias('system'),
            'vues' => $this->getDbTablesAlias('vue')
        ]);
    }

    /**
     * Renvoie l'adapter donné par le DbManager
     *
     * @return \Zend\Db\Adapter\Adapter
     */
    private function getDbAdapter()
    {
        return $this->config['db_manager']->getDbAdapter();
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
        return $this->config['db_config'];
    }

    /**
     * Renvoie un tableau décrivant la structure des tables et des vues définies dans SbmInstallation/db_design.
     * (voir SbmInstallation/db_design/README.txt)
     *
     * @return array
     */
    private function getDbDesign()
    {
        return $this->config['db_design'];
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
        if (! is_string($filter) || ! ($filter == '' || in_array($filter, [
            'table',
            'system',
            'vue'
        ]))) {
            throw new \Exception('Filtre incorrect dans ' . __METHOD__);
        }
        if ($filter == '') {
            $filters = [
                'Sbm\\Db\\Table\\',
                'Sbm\\Db\\System\\',
                'Sbm\\Db\\Vue\\'
            ];
        } else {
            $filters = [
                'Sbm\\Db\\' . ucfirst($filter) . '\\'
            ];
        }
        $config_db_manager = $this->config['db_manager']->getDbManagerConfig();
        $result = [];
        foreach (array_keys($config_db_manager['factories']) as $alias) {
            foreach ($filters as $f) {
                if (strpos($alias, $f) !== false) {
                    $result[] = $alias;
                    break;
                }
            }
        }
        return $result;
    }

    public function modifCompteAction()
    {
        $retour = $this->url()->fromRoute('sbminstall');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', [
            'action' => 'modif-compte'
        ]);
    }

    public function mdpChangeAction()
    {
        $retour = $this->url()->fromRoute('sbminstall');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', [
            'action' => 'mdp-change'
        ]);
    }

    public function emailChangeAction()
    {
        $retour = $this->url()->fromRoute('sbminstall');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', [
            'action' => 'email-change'
        ]);
    }

    public function messageAction()
    {
        $retour = $this->url()->fromRoute('sbminstall');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('dafapmail');
    }

    public function localisationAction()
    {
        $this->flashMessenger()->addWarningMessage('La localisation n\'est pas possible pour votre catégorie d\'utilisateurs.');
        return $this->redirect()->toRoute('sbminstall');
    }
}