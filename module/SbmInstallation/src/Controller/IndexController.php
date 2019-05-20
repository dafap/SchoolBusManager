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
 * @date 20 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmInstallation\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCartographie\Model as Carto;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmInstallation\Form;
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
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        if (! $prg) {
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        }
        if (StdLib::getParam('cancel', $prg, false)) {
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        }
        $create = new CreateTables($this->getDbConfig(), $this->getDbAdapter());
        if (StdLib::getParam('create', $prg, false)) {
            $viewArgs = [
                'args' => $create->run(),
                'form' => null
            ];
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
            $form = $this->form_manager->get(Form\DumpTables::class);
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
                // $infos = getimagesize($config['path']['system'] . DIRECTORY_SEPARATOR .
                // $fname);
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
                    $dest = StdLib::concatPath($this->img['path']['system'], $prg['fname']);
                    //    $data['image-file']['name']);
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
     * Renvoie un tableau ayant pour clés - 'database' : le nom de la base de données -
     * 'prefix' : le préfixe des tables et des vues pour cette application - 'definer' :
     * le DEFINER des vues Mysql
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
        if (! is_string($filter) ||
            ! ($filter == '' || in_array($filter, [
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

    public function gestionConfigAction()
    {
        return new ViewModel(
            [
                'theme' => $this->theme,
                'files' => scandir(StdLib::findParentPath(__DIR__, 'config/themes'))
            ]);
    }

    public function changeThemeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('theme', $args)) {
            $this->theme->setTheme($args['theme']);
        }
        return $this->redirect()->toRoute('sbminstall', [
            'action' => 'gestion-config'
        ]);
    }

    public function editClientAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        } elseif (array_key_exists('submit', $args)) {
            $this->theme->setConfigFileN2Idx('client.config.php', $args, [
                'adresse'
            ]);
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        }
        $view = new ViewModel(
            [
                'titrePage' => 'Informations de l\'organisateur',
                'theme' => $this->theme->getTheme(),
                'config_client' => $this->theme->getConfigFile('client.config.php'),
                'labelButton' => 'Ajouter une ligne ',
                'fieldsN2' => [
                    'adresse'
                ]
            ]);
        return $view->setTemplate('sbm-installation/index/edit-arrayn2idx.phtml');
    }

    /**
     * Présente un menu de modification des paramètres de configuration des cartes -
     * etablissement - station - gestion - parent Pour chaque item, on devra définir un
     * rectangle qui représente la zone autorisée par les latitudes nord et sud et les
     * longitudes est et ouest. On indique également le centre des cartes à l'overture et
     * le zoom à utiliser. Cette action appelle l'action editConfigCarte qui présente les
     * cartes pour unitem donné.
     */
    public function editCartesAction()
    {
    }

    public function editConfigCartesAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        $items = [
            'etablissement',
            'station',
            'gestion',
            'parent'
        ];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        } elseif (array_key_exists('submit', $args)) {
            // traitement du résultat
            die(var_dump($args));
            // puis on revient sur la liste pour prendre un autre point
        }
        // affichage de la liste des points
        $configcartes = $this->theme->getConfigFile('cartes.config.php');
        $points = [];
        foreach ($items as $item) {
            $points[$item] = $this->donnePoints($item, $configcartes[$item]);
        }
        echo '<pre>';
        print_r($configcartes, $points);
        die('</pre>');
        return new ViewModel(
            [
                'theme' => $this->theme->getTheme(),
                'configcarte' => $configcartes,
                'items'=> $items,
                'points' => $points
            ]);
    }

    private function donnePoints($item, $array)
    {
        $centre = new Carto\Point();
        $nord = new Carto\Point();
        $sud = new Carto\Point();
        $ouest = new Carto\Point();
        $est = new Carto\Point();
        $centre->setLatitude($array['centre']['lat'], 'degré');
        $centre->setLongitude($array['centre']['lng'], 'degré');
        $centre->setAttribute('item', $item);
        $nord->setLatitude($array['valide']['lat'][0], 'degré');
        $nord->setLongitude($array['centre']['lng'], 'degré');
        $nord->setAttribute('item', $item);
        $sud->setLatitude($array['valide']['lat'][1], 'degré');
        $sud->setLongitude($array['centre']['lng'], 'degré');
        $sud->setAttribute('item', $item);
        $ouest->setLatitude($array['centre']['lat'], 'degré');
        $ouest->setLongitude($array['valide']['lng'][0], 'degré');
        $ouest->setAttribute('item', $item);
        $est->setLatitude($array['centre']['lat'], 'degré');
        $est->setLongitude($array['valide']['lng'][1], 'degré');
        $est->setAttribute('item', $item);
        return [
            'centre' => $centre,
            'nord' => $nord,
            'sud' => $sud,
            'ouest' => $ouest,
            'est' => $est
        ];
    }

    public function editConfigCalendarAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        } elseif (array_key_exists('submit', $args)) {
            $content = [];
            for ($i = 0; $i < count($args['ordinal']); $i ++) {
                if (! empty($args['ordinal'][$i])) {
                    $content[] = [
                        'ordinal' => $args['ordinal'][$i],
                        'nature' => $args['nature'][$i],
                        'rang' => $args['rang'][$i],
                        'libelle' => $args['libelle'][$i],
                        'description' => $args['description'][$i],
                        'exercice' => $args['exercice'][$i]
                    ];
                }
            }
            $this->theme->setConfigFile('calendar.config.php', $content);
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        }
        return new ViewModel(
            [
                'theme' => $this->theme->getTheme(),
                'config' => $this->theme->getConfigCalendar()
            ]);
    }

    public function editMailchimpAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        } elseif (array_key_exists('submit', $args)) {
            $this->theme->setConfigFileN2Idx('mailchimp.config.php', $args, []);
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        }
        $view = new ViewModel(
            [
                'titrePage' => 'Configuration de l\'API de MAilChimp',
                'theme' => $this->theme->getTheme(),
                'config_client' => $this->theme->getConfigFile('mailchimp.config.php'),
                'labelButton' => 'Ajouter une ligne ',
                'fieldsN2' => []
            ]);
        return $view->setTemplate('sbm-installation/index/edit-arrayn2idx.phtml');
    }

    public function editCleverSmsAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        } elseif (array_key_exists('submit', $args)) {
            $this->theme->setConfigFileN2Idx('cleversms.config.php', $args, []);
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        }
        $view = new ViewModel(
            [
                'titrePage' => 'Configuration de l\'API de CleverSMS Light',
                'theme' => $this->theme->getTheme(),
                'config_client' => $this->theme->getConfigFile('cleversms.config.php'),
                'labelButton' => 'Ajouter une ligne ',
                'fieldsN2' => []
            ]);
        return $view->setTemplate('sbm-installation/index/edit-arrayn2idx.phtml');
    }

    public function editSitesAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        } elseif (array_key_exists('submit', $args)) {
            $this->theme->setConfigFileN2Asso('sites.config.php', $args,
                [
                    'header-gauche',
                    'header-droite'
                ]);
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        }
        $view = new ViewModel(
            [
                'titrePage' => 'Gestion des liens',
                'theme' => $this->theme->getTheme(),
                'config_sites' => $this->theme->getConfigFile('sites.config.php'),
                'labelButton' => 'Ajouter une ligne ',
                'fieldsN2' => [
                    'header-droite',
                    'header-gauche'
                ]
            ]);
        return $view->setTemplate('sbm-installation/index/edit-arrayn2asso.phtml');
    }

    /**
     * On reçoit enpost un request de la forme :
     * D:\www\photon\SchoolBusManager\module\SbmInstallation\src\Controller\IndexController.php:777:
     * array (size=21) 'transport|mode' => string 'smtp' (length=4)
     * 'transport|smtpOptions|name' => string 'smtp.free.fr' (length=12)
     * 'transport|smtpOptions|host' => string 'smtp.free.fr' (length=12)
     * 'transport|smtpOptions|port' => string '25' (length=2)
     * 'transport|smtpOptions|connexion_class' => string 'plain' (length=5)
     * 'transport|smtpOptions|connexion_config|username' => string '' (length=0)
     * 'transport|smtpOptions|connexion_config|password' => string '' (length=0)
     * 'transport|smtpOptions|connexion_config|ssl' => string 'ssl' (length=3)
     * 'transport|smtpOptions|connexion_config|use_complete_quit' => string '0' (length=1)
     * 'message|from|email' => string 'sbm@transport-decazeville-communaute.fr'
     * (length=39) 'message|from|name' => string 'Ne pas répondre' (length=16)
     * 'message|replyTo|email' => string 'sbm@dafap.fr' (length=12) 'message|replyTo|name'
     * => string 'Transports scolaires' (length=20) 'message|subject' => string
     * '[Transport scolaire] ' (length=21) 'message|body|text' => string '1' (length=1)
     * 'message|body|html' => string '1' (length=1) 'message|destinataires|0|email' =>
     * string 'contact@transport-decazeville-communaute.fr' (length=43)
     * 'message|destinataires|0|name' => string 'Service de transport scolaire'
     * (length=29) 'message|destinataires|1|email' => string 'test' (length=4)
     * 'message|destinataires|1|name' => string 'azert' (length=5) 'submit' => string
     * 'Enregistrer' (length=11) Une fois controlé le post, on supprime le submit et on
     * recompose le tableau de config
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function editMailAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        } elseif (array_key_exists('submit', $args)) {
            unset($args['submit']);
            $configmail = [];
            foreach ($args as $key => $value) {
                $configmail = $this->array_merge_recursive2($configmail,
                    $this->place(explode('|', $key), $value));
            }
            $configmail['transport']['smtpOptions']['connexion_config'] = array_filter(
                $configmail['transport']['smtpOptions']['connexion_config'],
                function ($v) {
                    return ! empty($v);
                });
            $this->theme->setConfigFile('mail.config.php', $configmail);
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        }
        $configmail = $this->theme->getConfigFile('mail.config.php');
        if (! StdLib::getParamR(
            [
                'transport',
                'smtpOptions',
                'connexion_config',
                'use_complete_quit'
            ], $configmail)) {
            $configmail['transport']['smtpOptions']['connexion_config']['use_complete_quit'] = true;
        }
        $destinataires = StdLib::getParamR([
            'message',
            'destinataires'
        ], $configmail, [
            [
                'email' => '',
                'name' => ''
            ]
        ]);
        $form = new Form\ConfigMail('config-mail');
        $form->setData($configmail);
        $view = new ViewModel(
            [
                'titrePage' => 'Configuration du courrier électronique',
                'theme' => $this->theme->getTheme(),
                'form' => $form,
                'nbDestinataires' => count($destinataires)
            ]);
        $view->setTemplate('sbm-installation/index/edit-config-mail.phtml');
        return $view;
    }

    /**
     * Fonction récursive pour editMailAction()
     *
     * @param array $array
     * @param array|string|number $value
     * @return array|string|int
     */
    private function place($array, $value)
    {
        $key = array_pop($array);
        if (is_null($key)) {
            return $value;
        }
        return $this->place($array, [
            (string) $key => $value
        ]);
    }

    /**
     * Fonction récursive pour editMailAction()
     *
     * @param array|string|number|null $paArray1
     * @param array|null $paArray2
     * @return array
     */
    private function array_merge_recursive2($paArray1, $paArray2)
    {
        if (! is_array($paArray1) or ! is_array($paArray2)) {
            return $paArray2;
        }
        foreach ($paArray2 as $sKey2 => $sValue2) {
            if (! array_key_exists($sKey2, $paArray1)) {
                $paArray1[$sKey2] = null;
            }
            $paArray1[$sKey2] = $this->array_merge_recursive2($paArray1[$sKey2], $sValue2);
        }
        return $paArray1;
    }

    public function editJqueryAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        } elseif (array_key_exists('submit', $args)) {
            $config = [];
            $translate = [];
            $delete = [];
            unset($args['submit']);
            foreach ($args as $key => $value) {
                $parts = explode('|', $key);
                if ($parts[1] == 'library') {
                    if (empty($value)) {
                        $delete[] = $parts[0]; // suppression du paquet
                    } else {
                        $translate[$parts[0]] = $value; // vrai nom du paquet (car .
                                                        // remplacé par _)
                    }
                } else {
                    if (! in_array($parts[0], $delete)) {
                        if (is_array($value)) {
                            $value = array_filter($value);
                        }
                        if (! empty($value)) {
                            $config[$translate[$parts[0]]][$parts[1]] = $value;
                        }
                    }
                }
            }
            $this->theme->setConfigFile('jquery.config.php', $config);
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        }

        return new ViewModel(
            [
                'theme' => $this->theme->getTheme(),
                'config' => $this->theme->getConfigFile('jquery.config.php')
            ]);
    }

    public function editGoogleMapsApiAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        } elseif (array_key_exists('submit', $args)) {
            $this->theme->setConfigFileN2Asso('google-maps-api.config.php', $args,
                [
                    'url_serveur',
                    'url_browser'
                ]);
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        }
        $view = new ViewModel(
            [
                'titrePage' => 'Configuration de Google MAps API',
                'theme' => $this->theme->getTheme(),
                'config_sites' => $this->theme->getConfigFile(
                    'google-maps-api.config.php'),
                'labelButton' => 'Ajouter une ligne ',
                'fieldsN2' => [
                    'url_browser',
                    'url_serveur'
                ]
            ]);
        $view->setTemplate('sbm-installation/index/edit-arrayn2asso.phtml');
        return $view;
    }

    public function editSystempayAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        } elseif (array_key_exists('submit', $args)) {
            $this->theme->setConfigFileN2Asso('systempay.config.php', $args,
                [
                    'certificat',
                    'authorized_ip'
                ]);
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        }
        $view = new ViewModel(
            [
                'titrePage' => 'Configuration de Systempay',
                'theme' => $this->theme->getTheme(),
                'config_sites' => $this->theme->getConfigFile('systempay.config.php'),
                'labelButton' => 'Ajouter une ligne ',
                'fieldsN2' => [
                    'authorized_ip',
                    'certificat'
                ]
            ]);
        $view->setTemplate('sbm-installation/index/edit-arrayn2asso.phtml');
        return $view;
    }

    public function editCssAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        if (! $prg || array_key_exists('cancel', (array) $prg)) {
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        }
        $form = $this->form_manager->get(Form\FileContent::class);
        if (array_key_exists('submit', $prg)) {
            $form->setData($prg);
            if ($form->isValid()) {
                $args = $form->getData();
                if ($this->theme->setCssFile($args['filename'], $args['content']) !== false) {
                    $this->flashMessenger()->addSuccessMessage(
                        'La feuille de style a été enregistrée.');
                } else {
                    $this->flashMessenger()->addErrorMessage(
                        'Impossible d\'enregistrer la feuille de style.');
                }
                return $this->redirect()->toRoute('sbminstall',
                    [
                        'action' => 'gestion-config'
                    ]);
            } else {
                $cssFileName = StdLib::getParam('filename', $prg, 'sbm.css');
            }
        } else {
            $cssFileName = StdLib::getParam('filename', $prg, 'sbm.css');
            $form->setData(
                [
                    'filename' => $cssFileName,
                    'content' => $this->theme->getCssFile($cssFileName)
                ]);
        }
        $view = new ViewModel(
            [
                'theme' => $this->theme,
                'filename' => $cssFileName,
                'form' => $form,
                'type' => 'css'
            ]);
        $view->setTemplate('sbm-installation/index/file-content.phtml');
        return $view;
    }

    public function editPageAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        if (! $prg || array_key_exists('cancel', (array) $prg) ||
            ! StdLib::getParam('filename', $prg, false)) {
            return $this->redirect()->toRoute('sbminstall',
                [
                    'action' => 'gestion-config'
                ]);
        }
        $form = $this->form_manager->get(Form\FileContent::class);
        if (array_key_exists('submit', $prg)) {
            $form->setData($prg);
            if ($form->isValid()) {
                $args = $form->getData();
                if ($this->theme->setHtmlFile($args['filename'], $args['content']) !==
                    false) {
                    $this->flashMessenger()->addSuccessMessage(
                        'La page a été enregistrée.');
                } else {
                    $this->flashMessenger()->addErrorMessage(
                        'Impossible d\'enregistrer la page.');
                }
                return $this->redirect()->toRoute('sbminstall',
                    [
                        'action' => 'gestion-config'
                    ]);
            } else {
                $htmlFileName = $prg['filename'];
            }
        } else {
            $htmlFileName = $prg['filename'];
            $form->setData(
                [
                    'filename' => $htmlFileName,
                    'content' => $this->theme->getHtmlFile($htmlFileName)
                ]);
        }
        $view = new ViewModel(
            [
                'theme' => $this->theme,
                'filename' => $htmlFileName,
                'form' => $form,
                'type' => 'html'
            ]);
        $view->setTemplate('sbm-installation/index/file-content.phtml');
        return $view;
    }

    public function varEnvAction()
    {
        return [];
    }
}