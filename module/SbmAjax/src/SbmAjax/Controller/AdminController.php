<?php
/**
 * Actions destinées aux réponses à des demandes ajax pour les administrateurs
 *
 * Le layout est désactivé dans ce module
 * 
 * @project sbm
 * @package SbmAjax/Controller
 * @filesource AdminController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 août 2018
 * @version 2018-2.4.2
 */
namespace SbmAjax\Controller;

use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use SbmAjax\Form;
use SbmCommun\Model\Strategy\Niveau;

class AdminController extends AbstractActionController
{

    const ROUTE = 'sbmajaxadmin';

    /**
     * ajax - cocher la sélection des rpi
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectionrpiAction()
    {
        try {
            $rpiId = $this->params('rpiId');
            $this->db_manager->get('Sbm\Db\Table\Rpi')->setSelection($rpiId, 1);
            return $this->getResponse()->setContent(
                Json::encode([
                    'success' => 1
                ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode(
                    [
                        'cr' => $e->getMessage(),
                        'success' => 0
                    ]));
        }
    }

    /**
     * ajax - décocher la sélection des rpi
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectionrpiAction()
    {
        try {
            $rpiId = $this->params('rpiId');
            $this->db_manager->get('Sbm\Db\Table\Rpi')->setSelection($rpiId, 0);
            return $this->getResponse()->setContent(
                Json::encode([
                    'success' => 1
                ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode(
                    [
                        'cr' => $e->getMessage(),
                        'success' => 0
                    ]));
        }
    }

    /**
     * ajax - cocher la case sélection des users
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectionuserAction()
    {
        try {
            $userId = $this->params('userId');
            $this->db_manager->get('Sbm\Db\Table\Users')->setSelection($userId, 1);
            return $this->getResponse()->setContent(
                Json::encode([
                    'success' => 1
                ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode(
                    [
                        'cr' => $e->getMessage(),
                        'success' => 0
                    ]));
        }
    }

    /**
     * ajax - décocher la case sélection des users
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectionuserAction()
    {
        try {
            $userId = $this->params('userId');
            $this->db_manager->get('Sbm\Db\Table\Users')->setSelection($userId, 0);
            return $this->getResponse()->setContent(
                Json::encode([
                    'success' => 1
                ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode(
                    [
                        'cr' => $e->getMessage(),
                        'success' => 0
                    ]));
        }
    }

    /**
     * Renvoie le code html du tableau des classes
     *
     * @return \Zend\StdLib\Response
     */
    public function rpiclassetableAction()
    {
        $etablissementId = $this->params('etablissementId');
        $vEtablissements = $this->db_manager->get('Sbm\Db\Vue\Etablissements');
        $oEtablissement = $vEtablissements->getRecord($etablissementId);
        $tRpiClasses = $this->db_manager->get('Sbm\Db\Table\RpiClasses');
        $rclasses = $tRpiClasses->getClasses($etablissementId);
        $structure = [
            'etablissementId' => $etablissementId,
            'nom' => $oEtablissement->nom,
            'commune' => $oEtablissement->commune,
            'classes' => []
        ];
        foreach ($rclasses as $row) {
            $structure['classes'][] = $row;
        }
        // utilisation particulière du viewhelper rpiCommunes dans le controller
        $rpiClasses = $this->viewHelperManager->get('rpiClasses');
        $content = str_replace('etablissementId:?', "etablissementId:$etablissementId", 
            $rpiClasses($structure));
        // construction et renvoi d'une réponse html
        try {
            $response = $this->getResponse();
        } catch (\Exception $e) {
            $response = new \Zend\StdLib\Response();
        }
        $response->setContent($content);
        return $response;
    }

    /**
     * Prépare le formulaire de saisie d'une classe
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function rpiclasseformAction()
    {
        $op = $this->params('op');
        $classe = $this->params('classe');
        $etablissement = $this->params('etablissement');
        $commune = $this->params('commune');
        $niveau = json_decode($this->params('niveau'));
        $form = $this->getFormRpiClasse($niveau, $op);
        $form->setData(
            [
                'op' => $op,
                'classeId' => $this->params('classeId'),
                'etablissementId' => $this->params('etablissementId'),
                'niveau' => $this->params('niveau')
            ]);
        return new ViewModel(
            [
                'form' => $form->prepare(),
                'is_xmlhttprequest' => 1,
                'op' => $op,
                'classe' => $classe,
                'etablissement' => $etablissement,
                'commune' => $commune
            ]);
    }

    public function rpiclassevalidateAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
        
        if ($request->isPost()) {
            if ($request->getPost('cancel') || $request->getPost('submit') == 'cancel') {
                $messages = 'Abandon action (add ou delete classe d\'un établissement).';
                $success = 2;
            } else {
                $op = $request->getPost('op');
                $niveau = json_decode($request->getPost('niveau'));
                $form = $this->getFormRpiClasse($niveau, $op);
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    $tRpiClasses = $this->db_manager->get('Sbm\Db\Table\RpiClasses');
                    $oData = $tRpiClasses->getObjData();
                    $oData->exchangeArray($form->getData());
                    try {
                        switch ($op) {
                            case 'add':
                                $result = $tRpiClasses->insertRecord($oData);
                                if ($result === false) {
                                    $messages = 'Erreur lors de l\'insertion.';
                                    $success = 0;
                                    break;
                                } elseif ($result) {
                                    $messages = 'Classe insérée dans cet établissement.';
                                    $success = 1;
                                } else {
                                    $messages = 'Classe déjà présente dans cet établissement.';
                                    $success = 0;
                                }
                                break;
                            case 'delete':
                                $tRpiClasses->deleteRecord($oData);
                                $messages = 'Classe retirée de cet établissement.';
                                $success = 1;
                                break;
                            default:
                                $messages = 'Demande incorrecte.';
                                $success = 0;
                                break;
                        }
                    } catch (\Exception $e) {
                        $messages = 'Une erreur s\'est produite pendant le traitement de la demande.';
                        $success = 0;
                    }
                } else {
                    $messages = $this->getFormErrorMessages($form->getMessages());
                    $success = 0;
                }
            }
        } else {
            $messages = 'Pas de post !';
            $success = 0;
        }
        $response->setContent(
            Json::encode(
                [
                    'cr' => $messages,
                    'success' => $success
                ]));
        return $response;
    }

    /**
     * Renvoie un formulaire adapté à l'action passée en paramètre
     *
     * @param array $niveau
     *            table des niveaux concernés pour ce rpi
     * @param string $op
     *            'add' pour ajout, 'delete' pour suppression
     *            
     * @return \SbmAjax\Form\RpiClasse
     */
    private function getFormRpiClasse($niveau, $op = 'add')
    {
        $form = new Form\RpiClasse($op);
        $form->setAttribute('action', 
            $this->url()
                ->fromRoute(self::ROUTE, 
                [
                    'action' => 'rpiclassevalidate'
                ]));
        if ($op == 'add') {
            $values_options = $this->db_manager->get('Sbm\Db\Select\Classes')->niveau(
                $niveau, 'in');
            $form->setValueOptions('classeId', $values_options);
        }
        return $form;
    }

    /**
     * Renvoie le code html du tableau des communes
     *
     * @return \Zend\StdLib\Response
     */
    public function rpicommunetableAction()
    {
        $rpiId = $this->params('rpiId');
        $tRpiCommunes = $this->db_manager->get('Sbm\Db\Table\RpiCommunes');
        $rcommunes = $tRpiCommunes->getCommunes($rpiId);
        $structure = [];
        foreach ($rcommunes as $row) {
            $structure[] = $row;
        }
        // utilisation particulière du viewhelper rpiCommunes dans le controller
        $rpiCommunes = $this->viewHelperManager->get('rpiCommunes');
        $content = str_replace('rpiId:?', "rpiId:$rpiId", $rpiCommunes($structure));
        // construction et renvoi d'une réponse html
        try {
            $response = $this->getResponse();
        } catch (\Exception $e) {
            $response = new \Zend\StdLib\Response();
        }
        $response->setContent($content);
        return $response;
    }

    /**
     * Prépare le formulaire de saisi d'une commune
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function rpicommuneformAction()
    {
        $op = $this->params('op');
        $rpiId = $this->params('rpiId');
        $communeId = $this->params('communeId');
        $commune = $this->params('commune');
        $form = $this->getFormRpiCommune($op);
        $form->setData(
            [
                'op' => $op,
                'rpiId' => $rpiId,
                'communeId' => $communeId
            ]);
        return new ViewModel(
            [
                'form' => $form,
                'is_xmlhttprequest' => 1,
                'op' => $op,
                'commune' => $commune
            ]);
    }

    /**
     * Traitement d'une action demandée sur une commune (ajout ou suppression)
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function rpicommunevalidateAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
        
        if ($request->isPost()) {
            if ($request->getPost('cancel') || $request->getPost('submit') == 'cancel') {
                $messages = 'Abandon action (add ou delete commune pour un rpi).';
                $success = 2;
            } else {
                $op = $request->getPost('op');
                $form = $this->getFormRpiCommune($op);
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    $tRpiCommunes = $this->db_manager->get('Sbm\Db\Table\RpiCommunes');
                    $oData = $tRpiCommunes->getObjData();
                    $oData->exchangeArray($form->getData());
                    try {
                        switch ($op) {
                            case 'add':
                                $result = $tRpiCommunes->insertRecord($oData);
                                if ($result === false) {
                                    $messages = 'Erreur lors de l\'insertion.';
                                    $success = 0;
                                    break;
                                } elseif ($result) {
                                    $messages = 'Commune insérée dans ce RPI.';
                                    $success = 1;
                                } else {
                                    $messages = 'Commune déjà présente dans ce RPI.';
                                    $success = 0;
                                }
                                break;
                            case 'delete':
                                $tRpiCommunes->deleteRecord($oData);
                                $messages = 'Commune retirée du RPI.';
                                $success = 1;
                                break;
                            default:
                                $messages = 'Demande incorrecte.';
                                $success = 0;
                                break;
                        }
                    } catch (\Exception $e) {
                        $messages = 'Une erreur s\'est produite pendant le traitement de la demande.';
                        $success = 0;
                    }
                } else {
                    $messages = $this->getFormErrorMessages($form->getMessages());
                    $success = 0;
                }
            }
        } else {
            $messages = 'Pas de post !';
            $success = 0;
        }
        $response->setContent(
            Json::encode(
                [
                    'cr' => $messages,
                    'success' => $success
                ]));
        return $response;
    }

    /**
     * Renvoie un formulaire adapté à l'action passée en paramètre
     *
     * @param string $op
     *            'add' pour ajout, 'delete' pour suppression
     *            
     * @return \SbmAjax\Form\RpiCommune
     */
    private function getFormRpiCommune($op = 'add')
    {
        $form = new Form\RpiCommune($op);
        $form->setAttribute('action', 
            $this->url()
                ->fromRoute(self::ROUTE, 
                [
                    'action' => 'rpicommunevalidate'
                ]));
        if ($op == 'add') {
            $values_options = $this->db_manager->get('Sbm\Db\Select\Communes')->membres();
            $form->setValueOptions('communeId', $values_options);
        }
        return $form;
    }

    /**
     * Renvoie le code html du tableau des communes
     *
     * @return \Zend\StdLib\Response
     */
    public function rpietablissementtableAction()
    {
        $rpiId = $this->params('rpiId');
        $tRpiEtablissements = $this->db_manager->get('Sbm\Db\Table\RpiEtablissements');
        $tRpiClasses = $this->db_manager->get('Sbm\Db\Table\RpiClasses');
        $retablissements = $tRpiEtablissements->getEtablissements($rpiId);
        $structure = [];
        foreach ($retablissements as $row) {
            $structure[] = array_merge($row, 
                [
                    'classes' => $tRpiClasses->getClasses($row['etablissementId'])
                ]);
            ;
        }
        // utilisation particulière du viewhelper rpiCommunes dans le controller
        $rpiEtablissements = $this->viewHelperManager->get('rpiEtablissements');
        $viewHelperRpiClasses = $this->viewHelperManager->get('rpiClasses');
        $content = str_replace('rpiId:?', "rpiId:$rpiId", 
            $rpiEtablissements($structure, 
                function ($etablissement) use($viewHelperRpiClasses) {
                    $etablissementId = $etablissement['etablissementId'];
                    $lignesClasses = $viewHelperRpiClasses($etablissement);
                    return <<<EOT
<table id="rpi-classes-$etablissementId">
    $lignesClasses
</table>
EOT;
                }));
        // construction et renvoi d'une réponse html
        try {
            $response = $this->getResponse();
        } catch (\Exception $e) {
            $response = new \Zend\StdLib\Response();
        }
        $response->setContent($content);
        return $response;
    }

    /**
     * Prépare le formulaire de saisie d'un établissement scolaire
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function rpietablissementformAction()
    {
        $op = $this->params('op');
        $rpiId = $this->params('rpiId');
        $etablissementId = $this->params('etablissementId');
        $etablissement = $this->params('etablissement');
        $commune = $this->params('commune');
        $form = $this->getFormRpiEtablissement($op);
        $form->setData(
            [
                'op' => $op,
                'rpiId' => $rpiId,
                'etablissementId' => $etablissementId
            ]);
        return new ViewModel(
            [
                'form' => $form,
                'is_xmlhttprequest' => 1,
                'op' => $op,
                'etablissement' => $etablissement,
                'commune' => $commune
            ]);
    }

    public function rpietablissementvalidateAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
        
        if ($request->isPost()) {
            if ($request->getPost('cancel') || $request->getPost('submit') == 'cancel') {
                $messages = 'Abandon action (add ou delete etablissement pour un rpi).';
                $success = 2;
            } else {
                $op = $request->getPost('op');
                $form = $this->getFormRpiEtablissement($op);
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    $tRpiEtablissements = $this->db_manager->get(
                        'Sbm\Db\Table\RpiEtablissements');
                    $oData = $tRpiEtablissements->getObjData();
                    $oData->exchangeArray($form->getData());
                    try {
                        switch ($op) {
                            case 'add':
                                $result = $tRpiEtablissements->insertRecord($oData);
                                if ($result === false) {
                                    $messages = 'Erreur lors de l\'insertion.';
                                    $success = 0;
                                    break;
                                } elseif ($result) {
                                    $messages = 'Établissement inséré dans ce RPI.';
                                    $success = 1;
                                } else {
                                    $messages = 'Établissement déjà présent dans ce RPI.';
                                    $success = 0;
                                }
                                break;
                            case 'delete':
                                $tRpiEtablissements->deleteRecord($oData);
                                $messages = 'Établissement retiré du RPI.';
                                $success = 1;
                                break;
                            default:
                                $messages = 'Demande incorrecte.';
                                $success = 0;
                                break;
                        }
                    } catch (\Exception $e) {
                        $messages = 'Une erreur s\'est produite pendant le traitement de la demande.';
                        $success = 0;
                    }
                } else {
                    $messages = $this->getFormErrorMessages($form->getMessages());
                    $success = 0;
                }
            }
        } else {
            $messages = 'Pas de post !';
            $success = 0;
        }
        $response->setContent(
            Json::encode(
                [
                    'cr' => $messages,
                    'success' => $success
                ]));
        return $response;
    }

    /**
     * Renvoie un formulaire adapté à l'action passée en paramètre
     *
     * @param string $op
     *            'add' pour ajout, 'delete' pour suppression
     *            
     * @return \SbmAjax\Form\RpiCommune
     */
    private function getFormRpiEtablissement($op = 'add')
    {
        $form = new Form\RpiEtablissement($op);
        $form->setAttribute('action', 
            $this->url()
                ->fromRoute(self::ROUTE, 
                [
                    'action' => 'rpietablissementvalidate'
                ]));
        if ($op == 'add') {
            $values_options = $this->db_manager->get('Sbm\Db\Select\Etablissements')->enRpi();
            $form->setValueOptions('etablissementId', $values_options);
        }
        return $form;
    }

    private function getFormErrorMessages($errors)
    {
        $messages = '';
        foreach ($errors as $key => $row) {
            if (! empty($row) && $key != 'submit') {
                foreach ($row as $keyer => $rower) {
                    // save error(s) per-element that needed by Javascript
                    $messages .= $key . ' : ' . _($rower) . "\n";
                }
            }
        }
        return $messages;
    }
}