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
 * @date 24 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmInstallation\Controller;

use SbmBase\Model\StdLib;
use SbmBase\Model\Session;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmInstallation\Form\DumpTables as FormDumpTables;
use SbmInstallation\Form\UploadImage;
use SbmInstallation\Model\CreateTables;
use SbmInstallation\Model\DumpTables;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

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
    {
        return new ViewModel(
            [
                'url_maps_api' => StdLib::getParam('js',
                    $this->cartographie_manager->get('google_api_browser'))
            ]);
    }

    public function fichierslogAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbminstall');
        }
        $config_paiement = $this->config_paiement;
        $fileNamePaiement = strtolower($config_paiement['plateforme']) . '_error.log';
        $filePaiement = StdLib::concatPath($config_paiement['path_filelog'],
            $fileNamePaiement);
        if (! file_exists($filePaiement)) {
            $f = fopen($filePaiement, 'w');
            fclose($f);
        }
        $fileErrors = $this->error_log;
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
                    ->addHeaderLine('Content-Disposition',
                    'attachment; filename="' . $filename . '"')
                    ->addHeaderLine('Content-Length', filesize($filenameWithPath));

                $response->setHeaders($headers);
                return $response;
            }
        }
        return new ViewModel(
            [
                'form1' => new ButtonForm([
                    'fichier' => 'paiement'
                ],
                    [
                        'drop' => [
                            'class' => 'confirm default',
                            'value' => 'Vider le fichier'
                        ],
                        'download' => [
                            'class' => 'confirm default',
                            'value' => 'Télécharger le fichier des transactions'
                        ],
                        'voir' => [
                            'class' => 'confirm default',
                            'value' => 'Voir le contenu',
                            'formaction' => '/install/fichierslog-voir'
                        ]
                    ]),
                'form2' => new ButtonForm([
                    'fichier' => 'logerror'
                ],
                    [
                        'drop' => [
                            'class' => 'confirm default',
                            'value' => 'Vider le fichier'
                        ],
                        'download' => [
                            'class' => 'confirm default',
                            'value' => 'Télécharger le fichier d\'erreurs'
                        ],
                        'voir' => [
                            'class' => 'confirm default',
                            'value' => 'Voir le contenu',
                            'formaction' => '/install/fichierslog-voir'
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

    public function fichierslogVoirAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        if ($prg === false || ! array_key_exists('fichier', $prg)) {
            return $this->redirect()->toRoute('sbminstall', [
                'action' => 'fichierslog'
            ]);
        }
        if ($prg['fichier'] == 'paiement') {
            $title = 'Historique des transactions de la plateforme de paiement';
            $config_paiement = $this->config_paiement;
            $filename = strtolower($config_paiement['plateforme']) . '_error.log';
            $filename = StdLib::concatPath($config_paiement['path_filelog'], $filename);
        } else {
            $title = 'Contenu du fichier d\'erreurs';
            $filename = $this->error_log;
        }
        $acontent = [];
        if (file_exists($filename)) {
            $acontent = file($filename);
        }
        if (empty($acontent)) {
            $acontent = [
                'Le fichier est vide'
            ];
        }
        return new ViewModel([
            'title' => $title,
            'acontent' => $acontent
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
            $form = new ButtonForm([],
                [
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
                $plugin = $request->getPost('plugin', []);
                $onscreen = $request->getPost('onscreen', false);
                $tables = array_merge($tables, $systems, $plugin);

                $oDumpTable = new DumpTables($this->db_manager);
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
            $form->setValueOptions('plugin', $this->getDbTablesAlias('plugin'));

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
        return new ViewModel(
            [
                'tables' => $this->getDbTablesAlias('table'),
                'systems' => $this->getDbTablesAlias('system'),
                'plugin' => $this->getDbTablesAlias('plugin'),
                'vues' => $this->getDbTablesAlias('vue')
            ]);
    }

    public function gestionImagesAction()
    {
        $message = '';
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        // $args = $prg ?: [];
        $config = $this->img;
        $files = scandir($config['path']['system']);
        $this->installationImages($config['administrer'], $files,
            $config['path']['system']);
        $file_names = [];
        foreach ($files as $fname) {
            if (StdLib::getParamR([
                'administrer',
                $fname
            ], $config, false)) {
                // $infos = getimagesize($config['path']['system'] . DIRECTORY_SEPARATOR . $fname);
                $infos = getimagesize(
                    StdLib::concatPath($config['path']['system'], $fname));
                $file_names[$fname] = [
                    'administrer' => $config['administrer'][$fname],
                    'width' => $infos[0],
                    'height' => $infos[1],
                    'type' => $infos[2],
                    'mime' => $infos['mime']
                ];
            }
        }
        return new ViewModel(
            [
                'message' => $message,
                'path' => $config['path'],
                'file_names' => $file_names
            ]);
    }

    private function installationImages($besoins, &$presents, $path)
    {
        foreach ($besoins as $key => $description) {
            if (! in_array($key, $presents)) {
                // créer l'image manquante
                $image = imagecreatetruecolor($description['width'],
                    $description['height']);
                $ext = substr($key, - 3);
                switch ($ext) {
                    case 'png':
                        imagepng($image, StdLib::concatPath($path, $key));
                        break;
                    case 'jpg':
                    case 'jpeg':
                        imagejpeg($image, StdLib::concatPath($path, $key));
                        break;
                    case 'gig':
                        imagegif($image, StdLib::concatPath($path, $key));
                        break;
                    default:
                        $msg = "key: $key\next: $ext\nCe formant de fichier n'est pas géré.";
                        throw new \SbmCommun\Model\Photo\Exception($msg);
                        break;
                }
                $presents[] = $key;
            }
        }
    }

    public function uploadImageAction()
    {
        $tmpuploads = $this->img['path']['tmpuploads'];
        $form = new UploadImage('upload-form', [
            'tmpuploads' => $tmpuploads
        ]);
        $tempFile = null;
        $prg = $this->fileprg($form);
        if ($prg instanceof Response) {
            return $prg;
        } elseif (is_array($prg)) {
            if (array_key_exists('cancel', $prg)) {
                Session::remove('post', $this->getSessionNamespace());
                $this->flashMessenger()->addWarningMessage(
                    'Aucune image n\'a été modifiée.');
                return $this->redirect()->toRoute('sbminstall',
                    [
                        'action' => 'gestion-images'
                    ]);
            }

            $label = $prg['label'];
            $image = StdLib::concatPath($this->img['path']['url'], $prg['fname']);
            $descriptif = $prg;
            Session::set('post', $descriptif, $this->getSessionNamespace());
            if (array_key_exists('submit', $prg)) {
                if ($form->isValid()) {
                    $data = $form->getData();
                    // Form is valid, save the form!
                    $source = $data['image-file']['tmp_name'];
                    $dest = StdLib::concatPath($this->img['path']['system'],
                        $data['image-file']['name']);
                    // $dest = $this->img']['path']['system'] . DIRECTORY_SEPARATOR .
                    // $data['image-file']['name'];
                    copy($source, $dest);
                    unlink($source);
                    Session::remove('post', $this->getSessionNamespace());
                    $this->flashMessenger()->addSuccessMessage(
                        'L\'image a été enregistrée.');
                    return $this->redirect()->toRoute('sbminstall',
                        [
                            'action' => 'gestion-images'
                        ]);
                } else {
                    // Form not valid, but file uploads might be valid...
                    // Get the temporary file information to show the user in the view
                    $fileErrors = $form->get('image-file')->getMessages();
                    if (empty($fileErrors)) {
                        $tempFile = $form->get('image-file')->getValue();
                    }
                }
            } else {
                $form->get('image-file')->setMessages([]);
                $form->setData($prg);
            }
        } else {
            $form->get('image-file')->setMessages([]);
            $descriptif = Session::get('post', [], $this->getSessionNamespace());
            $label = $this->img['administrer'][$descriptif['fname']]['label'];
            if (empty($descriptif)) {
                $descriptif = [
                    'fname' => $descriptif['fname'],
                    'label' => $label,
                    'width' => $descriptif['width'],
                    'height' => $descriptif['height'],
                    'type' => $descriptif['type'],
                    'mime' => $descriptif['mime']
                ];
            }
            $form->setAttribute('action',
                $this->url()
                    ->fromRoute('sbminstall', [
                    'action' => 'upload-image'
                ]))
                ->setData($descriptif);
            $image = StdLib::concatPath($this->img['path']['url'], $descriptif['fname']);
        }
        return [
            'form' => $form,
            'label' => $label,
            'tempFile' => $tempFile,
            'image' => $image,
            'descriptif' => $descriptif
        ];
    }

    /**
     * Renvoie l'adapter donné par le DbManager
     *
     * @return \Zend\Db\Adapter\Adapter
     */
    private function getDbAdapter()
    {
        return $this->db_manager->getDbAdapter();
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
        return $this->db_config;
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
        if (! is_string($filter) || ! ($filter == '' || in_array($filter,
            [
                'table',
                'system',
                'plugin',
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
            if ($filter != 'plugin') {
                $filters = [
                    'Sbm\\Db\\' . ucfirst($filter) . '\\'
                ];
            }
        }
        $config_db_manager = $this->db_manager->getDbManagerConfig();
        $result = [];
        if ($filter != 'plugin') {
            foreach (array_keys($config_db_manager['factories']) as $alias) {
                foreach ($filters as $f) {
                    if (strpos($alias, $f) !== false) {
                        $result[] = $alias;
                        break;
                    }
                }
            }
        }
        // ajout éventuel du plugin de paiement
        if ($filter == '' || $filter == 'plugin') {
            $result[] = 'SbmPaiement\\Plugin\\Table';
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
            ->toRoute('SbmMail');
    }

    public function localisationAction()
    {
        $this->flashMessenger()->addWarningMessage(
            'La localisation n\'est pas possible pour votre catégorie d\'utilisateurs.');
        return $this->redirect()->toRoute('sbminstall');
    }

    public function updateHostnameValidatorAction()
    {
        return [];
    }

    public function updateCacertAction()
    {
        return [];
    }
}