<?php
/**
 * Actions destinées aux réponses à des demandes ajax pour les élèves et les responsables
 *
 * Le layout est désactivé dans ce module
 * 
 * @project sbm
 * @package SbmAjax/Controller
 * @filesource EleveController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmAjax\Controller;

use SbmBase\Model\Session;
use SbmCartographie\GoogleMaps;
use SbmCartographie\Model\Point;
use Zend\Json\Json;
use Zend\View\Model\ViewModel;

class EleveController extends AbstractActionController
{

    const ROUTE = 'sbmajaxeleve';

    public function indexAction()
    {
        return [
            'args' => $this->params('args', null)
        ];
    }

    /**
     * ajax - cocher la case sélection des responsables
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectionresponsableAction()
    {
        try {
            $responsableId = $this->params('responsableId');
            $this->db_manager->get('Sbm\Db\Table\Responsables')->setSelection(
                $responsableId, 1);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décocher la case sélection des responsables
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectionresponsableAction()
    {
        try {
            $responsableId = $this->params('responsableId');
            $this->db_manager->get('Sbm\Db\Table\Responsables')->setSelection(
                $responsableId, 0);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - cocher la case sélection des élèves
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectioneleveAction()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Eleves')->setSelection($eleveId, 1);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décocher la case sélection des élèves
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectioneleveAction()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Eleves')->setSelection($eleveId, 0);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - change responsable
     * Renvoie les données d'un responsable dont la référence est passée par GET
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function getresponsableAction()
    {
        try {
            $responsableId = $this->params('responsableId');
            $responsable = $this->db_manager->get('Sbm\Db\Vue\Responsables')->getRecord(
                $responsableId);

            return $this->getResponse()->setContent(
                Json::encode(
                    array_merge($responsable->getArrayCopy(), [
                        'success' => 1
                    ])));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * Initialise et renvoie le formulaire d'affectation
     * (méthode utilisée dans les méthodes publiques formaffectationAction() et
     * formaffectationvalidateAction()
     *
     * @return \SbmGestion\Form\AffectationDecision
     */
    private function getFormAffectationDecision($trajet)
    {
        $values_options1 = $this->db_manager->get('Sbm\Db\Select\Stations')->ouvertes();
        $values_options2 = $this->db_manager->get('Sbm\Db\Select\Services');
        $form = new \SbmGestion\Form\AffectationDecision($trajet, 2);
        $form->remove('back');
        $form->setAttribute('action',
            $this->url()
                ->fromRoute(self::ROUTE, [
                'action' => 'formaffectationvalidate'
            ]));
        $form->setValueOptions('station1Id', $values_options1)
            ->setValueOptions('station2Id', $values_options1)
            ->setValueOptions('service1Id', $values_options2)
            ->setValueOptions('service2Id', $values_options2);
        return $form;
    }

    /**
     * Renvoie le code html d'un formulaire
     * $trajet est le numéro du responsable (1 ou 2)
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function formaffectationAction()
    {
        $trajet = $this->params('trajet', 1);
        $station1Id = $this->params('station1Id', null);
        $station2Id = $this->params('station2Id', null);
        $aData = [
            'millesime' => Session::get('millesime'),
            'eleveId' => $this->params('eleveId', 0),
            'trajet' => $trajet,
            'jours' => '31', // Lu Ma Me Je Ve
            'sens' => '3', // aller-retour
            'correspondance' => $this->params('correspondance', 1),
            'responsableId' => $this->params('responsableId', 0),
            'station1Id' => $station1Id,
            'station2Id' => $station2Id,
            'service1Id' => $this->params('service1Id', null),
            'service2Id' => $this->params('service2Id', null),
            'op' => $this->params('op', null)
        ];

        return new ViewModel(
            [
                'trajet' => $trajet,
                'station1Id' => $station1Id,
                'station2Id' => $station2Id,
                'form' => $this->getFormAffectationDecision($trajet)->setData($aData),
                'is_xmlhttprequest' => 1
            ]);
    }

    /**
     * Traite le post du formulaire formaffectation
     *
     * @method POST
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function formaffectationvalidateAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        if ($request->isPost()) {
            if ($request->getPost('cancel') || $request->getPost('submit') == 'cancel') {
                $messages = 'Opération abandonnée.';
                $this->flashMessenger()->addInfoMessage($messages);
                $response->setContent(Json::encode([
                    'cr' => $messages,
                    'success' => 1
                ]));
            } else {
                $form = $this->getFormAffectationDecision($request->getPost('trajet'))
                    ->setData($request->getPost());
                if (! $form->isValid()) {
                    $errors = $form->getMessages();
                    $messages = '';
                    foreach ($errors as $key => $row) {
                        if (! empty($row) && $key != 'submit') {
                            foreach ($row as $rower) {
                                // save error(s) per-element that needed by Javascript
                                $messages .= $key . ' : ' . _($rower) . "\n";
                            }
                        }
                    }
                    $response->setContent(
                        Json::encode([
                            'cr' => $messages,
                            'success' => 0
                        ]));
                } else {
                    $tAffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
                    $oData = $tAffectations->getObjData();
                    $oData->exchangeArray($form->getData());
                    try {
                        switch ($form->getData()['op']) {
                            case 'add':
                                // ré-écrire saveRecord pour calculer le bon correspondance
                                $tAffectations->insertRecord($oData);
                                $messages = 'Nouvelle affectation enregistrée.';
                                $this->flashMessenger()->addSuccessMessage(
                                    'Nouvelle affectation enregistrée.');
                                break;
                            case 'edit':
                                $tAffectations->updateRecord($oData);
                                $messages = 'Affectation modifiée.';
                                $this->flashMessenger()->addSuccessMessage(
                                    'Affectation modifiée.');
                                break;
                            case 'delete':
                                // ré-écrire deleteRecord pour mettre à jour les correspondances
                                // qui restent
                                $tAffectations->deleteRecord($oData);
                                $messages = 'Affectation supprimée.';
                                $this->flashMessenger()->addSuccessMessage(
                                    'Affectation supprimée.');
                                break;
                            default:
                                $messages = 'Demande incorrecte.';
                                $this->flashMessenger()->addWarningMessage(
                                    'Demande incorrecte.');
                                break;
                        }
                        $response->setContent(
                            Json::encode([
                                'cr' => "$messages",
                                'success' => 1
                            ]));
                    } catch (\Exception $e) {
                        $this->flashMessenger()->addErrorMessage(
                            'Une erreur s\'est produite pendant le traitement de la demande.');
                        $response->setContent(
                            Json::encode([
                                'cr' => $e->getMessage(),
                                'success' => 0
                            ]));
                    }
                }
            }
        } else {
            $response->setContent(
                Json::encode([
                    'cr' => 'Pas de post !',
                    'success' => 0
                ]));
        }

        return $response;
    }

    private function getFormPriseEnChargePaiement()
    {
        $form = new \SbmGestion\Form\Eleve\PriseEnChargePaiement();

        $form->setAttribute('action',
            $this->url()
                ->fromRoute(self::ROUTE, [
                'action' => 'formpaiementvalidate'
            ]));
        $form->setValueOptions('organismeId',
            $this->db_manager->get('Sbm\Db\Select\Organismes'));
        return $form;
    }

    public function formpaiementAction()
    {
        $response = $this->getResponse();
        $eleveId = $this->params('eleveId', 0);
        if ($eleveId) {
            $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
            $oData = $tScolarites->getRecord(
                [
                    'millesime' => Session::get('millesime'),
                    'eleveId' => $eleveId
                ]);
            $aData = [
                'eleveId' => $eleveId,
                'gratuit' => $oData->gratuit,
                'organismeId' => $oData->organismeId
            ];
            return new ViewModel(
                [
                    'form' => $this->getFormPriseEnChargePaiement()->setData($aData),
                    'is_xmlhttprequest' => 1
                ]);
        } else {
            $response->setContent(
                Json::encode([
                    'cr' => 'Pas de référence élève !',
                    'success' => 0
                ]));
            return $response;
        }
    }

    public function formpaiementvalidateAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        if ($request->isPost()) {
            if ($request->getPost('cancel') || $request->getPost('submit') == 'cancel') {
                $messages = 'Opération abandonnée.';
                $this->flashMessenger()->addInfoMessage($messages);
                $response->setContent(Json::encode([
                    'cr' => $messages,
                    'success' => 1
                ]));
            } else {
                $form = $this->getFormPriseEnChargePaiement()->setData(
                    $request->getPost());
                if (! $form->isValid()) {
                    $errors = $form->getMessages();
                    $messages = '';
                    foreach ($errors as $key => $row) {
                        if (! empty($row) && $key != 'submit') {
                            foreach ($row as $rower) {
                                // save error(s) per-element that needed by Javascript
                                $messages .= $key . ' : ' . _($rower) . "\n";
                            }
                        }
                    }
                    $response->setContent(
                        Json::encode([
                            'cr' => $messages,
                            'success' => 0
                        ]));
                } else {
                    $data = $form->getData();
                    $eleveId = $data['eleveId'];
                    $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
                    try {
                        $oData = $tScolarites->getRecord(
                            [
                                'millesime' => Session::get('millesime'),
                                'eleveId' => $eleveId
                            ]);
                        $oData->gratuit = $data['gratuit'];
                        if ($data['gratuit'] == 2) {
                            $oData->organismeId = $data['organismeId'];
                        } else {
                            $oData->organismeId = 0;
                        }
                        $tScolarites->updateRecord($oData);
                        $messages = 'La prise en charge du paiement a été modifiée.';
                        $this->flashMessenger()->addSuccessMessage($messages);
                        $response->setContent(
                            Json::encode([
                                'cr' => "$messages",
                                'success' => 1
                            ]));
                    } catch (\Exception $e) {
                        $this->flashMessenger()->addErrorMessage(
                            'Une erreur s\'est produite pendant le traitement de la demande.');
                        $response->setContent(
                            Json::encode([
                                'cr' => $e->getMessage(),
                                'success' => 0
                            ]));
                    }
                }
            }
        } else {
            $response->setContent(
                Json::encode([
                    'cr' => 'Pas de post !',
                    'success' => 0
                ]));
        }

        return $response;
    }

    /**
     * Renvoie la distance d'un domicile à un établissement
     * L'argument passé en GET dans args contient une chaine de la forme
     * responsableId:valeur/etablissementId:valeur
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function donnedistanceAction()
    {
        $response = $this->getResponse();
        try {
            $etablissementId = $this->params('etablissementId', false);
            $responsableId = $this->params('responsableId', false);
            $distance = 0;
            if ($etablissementId && $responsableId) {
                $responsable = $this->db_manager->get('Sbm\Db\Table\Responsables')->getRecord(
                    $responsableId);
                $origine = new Point($responsable->x, $responsable->y);
                $etablissement = $this->db_manager->get('Sbm\Db\Table\Etablissements')->getRecord(
                    $etablissementId);
                $destination = new Point($etablissement->x, $etablissement->y);
                $oOrigineDestination = $this->cartographie_manager->get(
                    GoogleMaps\DistanceMatrix::class);
                $distance = $oOrigineDestination->calculDistance($origine, $destination);
                if ($distance) {
                    $distance = round($distance / 1000, 1);
                }
            }
            $response->setContent(
                Json::encode([
                    'distance' => $distance,
                    'success' => 1
                ]));
        } catch (GoogleMaps\Exception\ExceptionNoAnswer $e) {
            $response->setContent(Json::encode([
                'distance' => 99,
                'success' => 1
            ]));
        } catch (\Exception $e) {
            $response->setContent(
                Json::encode(
                    [
                        'cr' => sprintf("%s\n%s", $e->getMessage(), $e->getTraceAsString()),
                        'success' => 0
                    ]));
        }

        return $response;
    }

    public function blockaffectationsAction()
    {
        $query = $this->db_manager->get('Sbm/Db/Query/AffectationsServicesStations');
        $structure = [
            'annee_courante' => null,
            'annee_precedente' => null
        ];
        $eleveId = $this->params('eleveId');
        $trajet = $this->params('trajet');
        $resultset = $query->getAffectations($eleveId, $trajet, false);
        if ($resultset->count()) {
            $structure['annee_courante'] = [];
            foreach ($resultset as $affectation) {
                $structure['annee_courante'][$affectation['jours']][$affectation['sens']][$affectation['correspondance']] = [
                    'service1Id' => $affectation['service1Id'],
                    'station1Id' => $affectation['station1Id'],
                    'station1' => $affectation['station1'],
                    'service2Id' => $affectation['service2Id'],
                    'station2Id' => $affectation['station2Id'],
                    'station2' => $affectation['station2']
                ];
            }
        }
        $resultset = $query->getAffectations($eleveId, $trajet, true);
        if ($resultset->count()) {
            $structure['annee_precedente'] = [];
            foreach ($resultset as $affectation) {
                $structure['annee_precedente'][$affectation['jours']][$affectation['sens']][$affectation['correspondance']] = [
                    'service1Id' => $affectation['service1Id'],
                    'station1Id' => $affectation['station1Id'],
                    'station1' => $affectation['station1'],
                    'service2Id' => $affectation['service2Id'],
                    'station2Id' => $affectation['station2Id'],
                    'station2' => $affectation['station2']
                ];
            }
        }
        return new ViewModel(
            [
                'identite' => $this->params('identite'),
                'structure' => $structure,
                'trajet' => $trajet
            ]);
    }

    public function enableaccordbuttonAction()
    {
        $eleveId = $this->params('eleveId');
        $trajet = $this->params('trajet');
        $resultset = $this->db_manager->get('Sbm/Db/Query/AffectationsServicesStations')->getAffectations(
            $eleveId, $trajet);
        $enable = $resultset->count() ? 0 : 1;
        return $this->getResponse()->setContent(
            Json::encode([
                'enable' => $enable,
                'success' => 1
            ]));
    }

    public function getstationsforselectAction()
    {
        $serviceId = $this->params('serviceId');
        $queryStations = $this->db_manager->get('Sbm\Db\Select\Stations');
        $stations = $queryStations->surcircuit($serviceId, Session::get('millesime'));
        return $this->getResponse()->setContent(
            Json::encode([
                'data' => $stations,
                'success' => 1
            ]));
    }

    /**
     * ajax - cocher la case accordR1 des élèves
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkaccordR1Action()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setAccord(
                Session::get('millesime'), $eleveId, 'R1', 1);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décocher la case accordR1 des élèves
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckaccordR1Action()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setAccord(
                Session::get('millesime'), $eleveId, 'R1', 0);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - cocher la case accordR2 des élèves
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkaccordR2Action()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setAccord(
                Session::get('millesime'), $eleveId, 'R2', 1);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décocher la case accordR2 des élèves
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckaccordR2Action()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setAccord(
                Session::get('millesime'), $eleveId, 'R2', 0);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décrémenter duplicata dans la table scolarites
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function decrementeduplicataAction()
    {
        try {
            $eleveId = $this->params('eleveId');
            $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
            $odata = $tScolarites->getRecord(
                [
                    'millesime' => Session::get('millesime'),
                    'eleveId' => $eleveId
                ]);
            if ($odata->duplicata > 0) {
                $odata->duplicata --;
            }
            $tScolarites->saveRecord($odata);
            return $this->getResponse()->setContent(
                Json::encode([
                    'duplicata' => $odata->duplicata,
                    'success' => 1
                ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - incrémenter duplicata dans la table scolarites
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function incrementeduplicataAction()
    {
        try {
            $eleveId = $this->params('eleveId');
            $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
            $odata = $tScolarites->getRecord(
                [
                    'millesime' => Session::get('millesime'),
                    'eleveId' => $eleveId
                ]);
            $odata->duplicata ++;
            $tScolarites->saveRecord($odata);
            return $this->getResponse()->setContent(
                Json::encode([
                    'duplicata' => $odata->duplicata,
                    'success' => 1
                ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * Reçoie en POST les données du formulaire \SbmCommun\Model\Photo\Photo::getForm()<ul>
     * <li>eleveId (post)</li>
     * <li>filephoto (files)</li></ul>
     * Vérifie si elles sont valide et les enregistre dans la table `elevesphotos`
     * Renvoie un compte rendu :<ul>
     * <li>success = 1 et src = la chaine src à placer dans la balise `img`</li>
     * <li>success = 10x et cr = la chaine à afficher en cas d'erreur</li></ul>
     *
     * @method POST
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function savephotoAction()
    {
        $request = $this->getRequest();
        if (! $request->isPost()) {
            // ce n'est pas un post : on renvoie une erreur
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => 'Action incorrecte.',
                    'success' => 102
                ]));
        }
        $post = array_merge_recursive($request->getPost()->toArray(),
            $request->getFiles()->toArray());
        $ophoto = new \SbmCommun\Model\Photo\Photo();
        $form = $ophoto->getFormWithInputFilter($this->img['path']['tmpuploads'])->prepare();
        $form->setData($post);
        if ($form->isValid()) {
            $data = $form->getData();
            $source = $data['filephoto']['tmp_name'];
            try {
                $blob = $ophoto->getImageJpegAsString($source);
                unlink($source);
            } catch (\Exception $e) {
                // problème de fichier, de format de fichier ou d'image dont le format n'est pas
                // traité
                return $this->getResponse()->setContent(
                    Json::encode([
                        'cr' => $e->getMessage(),
                        'success' => 101
                    ]));
            }
        } else {
            return $this->getResponse()->setContent(
                Json::encode(
                    [
                        'cr' => implode(', ', $ophoto->getMessagesFilePhotoElement()),
                        'success' => 100
                    ]));
        }
        // base de données
        $tPhotos = $this->db_manager->get('Sbm\Db\Table\ElevesPhotos');
        $odata = $tPhotos->getObjData();
        $odata->exchangeArray(
            [
                'eleveId' => $data['eleveId'],
                'photo' => addslashes($blob)
            ]);
        $tPhotos->saveRecord($odata);
        return $this->getResponse()->setContent(
            Json::encode([
                'src' => $ophoto->img_src($blob),
                'success' => 1
            ]));
    }

    /**
     * Reçoie en POST le paramètre eleveId et supprime la photo dans la table ElevesPhotos.
     * Renvoie un compte rendu : <ul>
     * <li>success = 1 et src = sans photo gif</li>
     * <li>success = 20x et cr = message d'erreur à afficher en cas d'erreur</li></ul>
     *
     * @method POST
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function supprphotoAction()
    {
        if (! $this->getRequest()->isPost()) {
            // ce n'est pas un post : on renvoie une erreur
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => 'Action incorrecte.',
                    'success' => 202
                ]));
        }
        if ($eleveId = $this->getRequest()->getPost('eleveId', null)) {
            $tPhotos = $this->db_manager->get('Sbm\Db\Table\ElevesPhotos');
            $success = $tPhotos->deleteRecord($eleveId);
            if ($success) {
                $ophoto = new \SbmCommun\Model\Photo\Photo();
                return $this->getResponse()->setContent(
                    Json::encode(
                        [
                            'src' => $ophoto->img_src($ophoto->getSansPhotoGifAsString(),
                                'gif'),
                            'success' => 1
                        ]));
            } else {
                return $this->getResponse()->setContent(
                    Json::encode(
                        [
                            'cr' => 'Impossible de supprimer cette photo',
                            'success' => 201
                        ]));
            }
            ;
        }
    }
}