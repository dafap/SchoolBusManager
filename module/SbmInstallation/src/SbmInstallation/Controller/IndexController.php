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
 * @date 5 mars 2018
 * @version 2018-2.3.19
 */
namespace SbmInstallation\Controller;

use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Response;
use Zend\Stdlib\Glob;
use DrewM\MailChimp;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Form\ButtonForm;
use SbmInstallation\Model\CreateTables;
use SbmInstallation\Model\Exception;
use SbmInstallation\Form\DumpTables as FormDumpTables;
use SbmInstallation\Model\DumpTables;
use SbmInstallation\Form\UploadImage;
// pour integrationAction()
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use SbmBase\Model\Session;
use SbmCommun\Model\Db\ObjectData\Eleve;
use SbmCommun\Model\Db\ObjectData\Scolarite;

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
        $config_paiement = $this->config_paiement;
        $fileNamePaiement = strtolower($config_paiement['plateforme']) . '_error.log';
        $filePaiement = StdLib::concatPath($config_paiement['path_filelog'], $fileNamePaiement);
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

    public function gestionImagesAction()
    {
        $message = '';
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = (array) $prg;
        $config = $this->img;
        $files = scandir($config['path']['system']);
        $file_names = [];
        foreach ($files as $fname) {
            if (StdLib::getParamR([
                'administrer',
                $fname
            ], $config, false)) {
                // $infos = getimagesize($config['path']['system'] . DIRECTORY_SEPARATOR . $fname);
                $infos = getimagesize(StdLib::concatPath($config['path']['system'], $fname));
                $file_names[$fname] = [
                    'administrer' => $config['administrer'][$fname],
                    'width' => $infos[0],
                    'height' => $infos[1],
                    'type' => $infos[2],
                    'mime' => $infos['mime']
                ];
            }
        }
        return new ViewModel([
            'message' => $message,
            'path' => $config['path'],
            'file_names' => $file_names
        ]);
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
                $this->removeInSession('post', $this->getSessionNamespace());
                $this->flashMessenger()->addWarningMessage('Aucune image n\'a été modifiée.');
                return $this->redirect()->toRoute('sbminstall', [
                    'action' => 'gestion-images'
                ]);
            }
            $this->setToSession('post', $prg, $this->getSessionNamespace());
            if (array_key_exists('submit', $prg)) {
                if ($form->isValid()) {
                    $data = $form->getData();
                    // Form is valid, save the form!
                    $source = $data['image-file']['tmp_name'];
                    $dest = StdLib::concatPath($this->img['path']['system'], $data['image-file']['name']);
                    // $dest = $this->img']['path']['system'] . DIRECTORY_SEPARATOR . $data['image-file']['name'];
                    copy($source, $dest);
                    unlink($source);
                    $this->removeInSession('post', $this->getSessionNamespace());
                    $this->flashMessenger()->addSuccessMessage('L\'image a été enregistrée.');
                    return $this->redirect()->toRoute('sbminstall', [
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
                $form = new UploadImage('upload-form');
                $form->setData($prg);
                $label = $prg['label'];
            }
        } else {
            $args = $this->getFromSession('post', [], $this->getSessionNamespace());
            $config = $this->img;
            $form->setAttribute('action', $this->url()
                ->fromRoute('sbminstall', [
                'action' => 'upload-image'
            ]))
                ->setData([
                'fname' => $args['fname'],
                'label' => ($label = $config['administrer'][$args['fname']]['label']),
                'width' => $args['width'],
                'height' => $args['height'],
                'type' => $args['type'],
                'mime' => $args['mime']
            ]);
        }
        
        return array(
            'form' => $form,
            'label' => $label,
            'tempFile' => $tempFile
        );
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
        $config_db_manager = $this->db_manager->getDbManagerConfig();
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
            ->toRoute('SbmMail');
    }

    public function localisationAction()
    {
        $this->flashMessenger()->addWarningMessage('La localisation n\'est pas possible pour votre catégorie d\'utilisateurs.');
        return $this->redirect()->toRoute('sbminstall');
    }

    /**
     * ----------------------------------------------------------------------------------
     * Intégration des élèves lors de l'extension de la communauté de communes
     */
    public function integrationAction()
    {
        $this->integrationVideSbmEleves();
        $this->integrationSbmTEleves();
        $this->integrationSbmScolarite();
        // $this->integrationSbmAffectation();
        
        return new ViewModel();
    }

    private function integrationVideSbmEleves()
    {
        // recherche du premier eleveId à supprimer
        $where = new Where();
        $where->isNotNull('id_ccda');
        
        $sql = new Sql($this->db_manager->getDbAdapter());
        $select = $sql->select('sbm_t_eleves')
            ->columns([
            'first' => new Expression("MIN(`eleveId`)")
        ])
            ->where($where);
                    
        $statement = $sql->prepareStatementForSqlObject($select);
        $first = $statement->execute()->current()['first'];
        
        // suppression des scolarites pour eleveId >= $first
        $where = new Where();
        $where->greaterThanOrEqualTo('eleveId', $first);
        
        $sql = new Sql($this->db_manager->getDbAdapter());
        $delete = $sql->delete('sbm_t_scolarites')->where($where);
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        $statement->execute();
        
        // suppression des eleves
        $where = new Where();
        $where->isNotNull('id_ccda');
        
        $sql = new Sql($this->db_manager->getDbAdapter());
        $delete = $sql->delete('sbm_t_eleves')->where($where);
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        $statement->execute();
    }

    private function integrationSbmTEleves()
    {
        $where = new Where();
        $where->equalTo('c.desservie', 1);
        
        $sql = new Sql($this->db_manager->getDbAdapter());
        $select = $sql->select()
            ->from([
            'e' => 'eleves'
        ])
            ->columns([
            'nom',
            'nomSA',
            'prenom',
            'prenomSA',
            'dateN',
            'numero',
            'responsable1Id',
            'id_ccda' => 'tmp_id'
        ])
            ->join([
            'r' => 'responsables'
        ], 'r.id_ccda=e.id_ccda', [])
            ->join([
            'c' => 'sbm_t_communes'
        ], 'r.communeId=c.communeId', [])
            ->where($where);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = iterator_to_array($statement->execute());
        
        $tEleves = $this->db_manager->get('Sbm\Db\Table\Eleves');
        $oEleve = new Eleve();
        foreach ($result as $eleve) {
            if (is_null($eleve['dateN'])) {
                $eleve['dateN'] = '1950-01-01';
            }
            $eleve['dateCreation'] = null;
            $oEleve->exchangeArray($eleve);
            $oEleve->createNumero();
            $tEleves->saveRecord($oEleve);
        }
    }

    private function integrationSbmScolarite()
    {
        $millesime = Session::get('millesime');
        
        $sql = new Sql($this->db_manager->getDbAdapter());
        $select = $sql->select()
            ->from([
            'e' => 'eleves'
        ])
            ->columns([
            'etablissementId' => 'Ecole'
        ])
            ->join([
            'sbme' => 'sbm_t_eleves'
        ], 'sbme.id_ccda=e.tmp_id', [
            'eleveId'
        ])
            ->join([
            'c' => 'sbm_t_classes'
        ], 'e.Classe=c.aliasCG', [
            'classeId'
        ]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = iterator_to_array($statement->execute());
        
        $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $oScolarite = new Scolarite();
        foreach ($result as $scolarite) {
            $scolarite['dateInscription'] = null;
            $scolarite['dateDebut'] = '2017-09-04';
            $scolarite['dateFin'] = '2018-07-06';
            $scolarite['tarifId'] = 1;
            $scolarite['millesime'] = $millesime;
            $scolarite['gratuit'] = 1;
            $scolarite['paiement'] = 0;
            $scolarite['demandeR1'] = 2;
            $scolarite['internet'] = 0;
            $scolarite['commentaire'] = 'Paiement au CD12 - intégré lors de la fusion des CDC.';
            $oScolarite->exchangeArray($scolarite);
            $tScolarites->saveRecord($oScolarite);
        }
    }

    private function integrationSbmAffectation()
    {
        $millesime = Session::get('millesime');
    }
}