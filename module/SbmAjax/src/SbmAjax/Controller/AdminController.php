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
 * @date 11 juin 2018
 * @version 2018-2.4.1
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
                'etablissementId' => $this->params('etablissementId')
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
        ;
    }

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

    public function rpicommunetableAction()
    {
        $rpiId = $this->params('rpiId');
        $tRpiCommunes = $this->db_manager->get('Sbm\Db\Table\RpiCommunes');
        $rcommunes = $tRpiCommunes->getCommunes($rpiId);
        $structure = [];
        foreach ($rcommunes as $row) {
            $structure[] = $row;
        }
        $view = new ViewModel([
            'rpiId' => $rpiId
        ]);
        $content = $view->rpiCommunes($structure);
        $response = $this->getResponse();
        $response->setContent($content);
    }

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
        ;
    }

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