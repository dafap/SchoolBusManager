<?php
/**
 * Controller du module SbmParent permettant de gérer le compte de l'utilisateur et de revenir dans l'espace des parents
 *
 * @project sbm
 * @package SbmParent/Controller
 * @filesource ConfigController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmParent\Controller;

use SbmBase\Model\StdLib;
use SbmCartographie\GoogleMaps;
use SbmCartographie\Model\Point;
use SbmCommun\Form;
use SbmCommun\Filter\SansAccent;
use SbmCommun\Form\LatLng as LatLngForm;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmParent\Form\ModifAdresse;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

class ConfigController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }

    public function messageAction()
    {
        $retour = $this->url()->fromRoute('login', [
            'action' => 'home-page'
        ]);
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('SbmMail');
    }

    public function modifCompteAction()
    {
        try {
            $responsable = $this->responsable->get();
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('login', [
                'action' => 'logout'
            ]);
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // initialisation du formulaire à partir de l'identité de l'utilisateur
            // autentifié
            $args = $responsable->getArrayCopy();
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                return $this->redirect()->toRoute('login', [
                    'action' => 'home-page'
                ]);
            }
        }
        // ici on a le tableau d'initialisation du formulaire dans $args
        $responsableId = $args['responsableId'];
        $hasEnfantInscrit = $this->db_manager->get('Sbm\Db\Query\Responsables')->hasEnfantInscrit(
            $responsableId);
        $tableResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        // on ouvre le formulaire complet et on l'adapte
        $form = $this->form_manager->get(Form\Responsable::class);
        if ($this->db_manager->get('Sbm\Db\Table\Responsables')->getCategorieId(
            $responsableId) == 1) {
            $value_options = $this->db_manager->get('Sbm\Db\Select\Communes')->desservies();
        } else {
            $value_options = $this->db_manager->get('Sbm\Db\Select\Communes')->visibles();
        }
        $form->setValueOptions('communeId', $value_options)
            ->setValueOptions('ancienCommuneId', $value_options)
            ->setMaxLength($this->db_manager->getMaxLengthArray('responsables', 'table'));
        $form->bind($tableResponsables->getObjData());
        $form->setData($args);
        if (array_key_exists('submit', $args)) {
            if ($form->isValid()) {
                // controle le csrf et contrôle les datas
                $tableResponsables->saveResponsable($form->getData(), true);
                $responsable->refresh();
                $this->flashMessenger()->addSuccessMessage('Modifications enregistrées.');
                return $this->redirect()->toRoute('login',
                    [
                        'action' => 'synchro-compte'
                    ]);
            }
            $this->flashMessenger()->addWarningMessage('Données invalides');
        }
        return new ViewModel(
            [
                'hasEnfantInscrit' => $hasEnfantInscrit,
                'form' => $form->prepare(),
                'responsableId' => $responsableId,
                'responsable' => $responsable->getArrayCopy(),
                'demenagement' => StdLib::getParam('demenagement', $args, false)
            ]);
    }

    /**
     * Modifie l'adresse et demande la localisation
     */
    public function modifAdresseAction()
    {
        try {
            $responsable = $this->responsable->get();
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('login', [
                'action' => 'logout'
            ]);
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // initialisation du formulaire à partir de l'identité de l'utilisateur
            // autentifié
            $args = $responsable->getArrayCopy();
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage('Abandon : données inchangées');
                return $this->redirect()->toRoute('login', [
                    'action' => 'home-page'
                ]);
            }
            if (array_key_exists('modif-adresse', $args)) {
                $args = $responsable->getArrayCopy();
            }
        }
        // ici on a le tableau d'initialisation du formulaire dans $args
        $responsableId = StdLib::getParam('responsableId', $args,
            $responsable->responsableId);
        $tableResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        // on ouvre le formulaire complet et on l'adapte
        $form = $this->form_manager->get(ModifAdresse::class);
        if ($this->db_manager->get('Sbm\Db\Table\Responsables')->getCategorieId(
            $responsable) == 1) {
            $form->setValueOptions('communeId',
                $this->db_manager->get('Sbm\Db\Select\Communes')
                    ->desservies());
        } else {
            $form->setValueOptions('communeId',
                $this->db_manager->get('Sbm\Db\Select\Communes')
                    ->visibles());
        }
        $form->setMaxLength($this->db_manager->getMaxLengthArray('responsables', 'table'));
        $form->bind($tableResponsables->getObjData());
        $form->setData($args);
        if (array_key_exists('submit', $args)) {
            if ($form->isValid()) {
                $changeAdresse = $tableResponsables->saveResponsable($form->getData(),
                    true);
                $responsable->refresh();
                $this->flashMessenger()->addSuccessMessage('Modifications enregistrées.');
                if ($changeAdresse) {
                    return $this->redirect()->toRoute('sbmparentconfig',
                        [
                            'action' => 'localisation'
                        ]);
                }
                return $this->redirect()->toRoute('sbmparent');
            } else {
                $this->flashMessenger()->addWarningMessage('Données inchangées');
            }
        }
        return new ViewModel(
            [
                'form' => $form->prepare(),
                'responsableId' => $responsableId,
                'responsable' => $responsable->getArrayCopy(),
                'demenagement' => StdLib::getParam('demenagement', $args, false)
            ]);
    }

    public function mdpChangeAction()
    {
        return $this->redirect()->toRoute('login', [
            'action' => 'mdp-change'
        ]);
    }

    public function emailChangeAction()
    {
        return $this->redirect()->toRoute('login', [
            'action' => 'email-change'
        ]);
    }

    /**
     * Le retour se fait par un redirectToOrigin()->back() ce qui veut dire qu'il faut
     * avoir défini le redirectToOrigin() avant l'appel. Si un responsable de même nom et
     * prénom existe déjà, présenter son identité et proposer de s'identifier à cette
     * personne ou de créer un nouveau
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function createAction()
    {
        $identity = $this->authenticate->by()->getIdentity();
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // initialisation du formulaire à partir de l'identité de l'utilisateur
            // autentifié
            $args = $identity;
            // vérification d'existence de ce responsable
            $filterSA = new SansAccent();
            $vueResponsables = $this->db_manager->get('Sbm\Db\Vue\Responsables');
            $rows = $vueResponsables->fetchAll(
                [
                    'nomSA' => $filterSA->filter($args['nom']),
                    'prenomSA' => $filterSA->filter($args['prenom'])
                ]);
            if ($rows->count()) {
                $view = new ViewModel([
                    'data' => $rows,
                    'identity' => $identity
                ]);
                $view->setTemplate('sbm-parent/config/existe.phtml');
                return $view;
            }
        } else {
            $args = $prg;
        }
        // ici on a le tableau d'initialisation du formulaire dans $args
        $responsableId = null;
        $tableResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        // on ouvre le formulaire avec l'identité verrouillée et on l'adapte
        $form = $this->form_manager->get(Form\ResponsableVerrouille::class);
        $value_options = $this->db_manager->get('Sbm\Db\Select\Communes')->desservies(
            [
                'departement' => 73
            ]);
        $form->setValueOptions('communeId', $value_options)
            ->setValueOptions('ancienCommuneId', $value_options)
            ->setMaxLength($this->db_manager->getMaxLengthArray('responsables', 'table'));
        unset($value_options);

        $form->bind($tableResponsables->getObjData());
        $form->setData($args);
        if (array_key_exists('submit', $args)) {
            if ($form->isValid()) {
                // controle le csrf et contrôle les datas
                $tableResponsables->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("La fiche a été enregistrée.");
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                    return $this->redirect()->toRoute('login', [
                        'action' => 'home-page'
                    ]);
                }
            }
            $this->flashMessenger()->addWarningMessage('Données invalides');
        }
        return new ViewModel(
            [
                'form' => $form->prepare(),
                'responsableId' => $responsableId,
                'identity' => $identity,
                'demenagement' => false
            ]);
    }

    public function localisationAction()
    {
        try {
            $responsable = $this->responsable->get();
            // préparer le nom de la commune selon les règes de la méthode
            // GoogleMaps\Geocoder::geocode
            $sa = new \SbmCommun\Filter\SansAccent();
            $responsable->lacommune = $sa->filter($responsable->lacommune);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('login', [
                'action' => 'logout'
            ]);
        }
        // nécessaire pour valider lat et lng
        $configCarte = StdLib::getParam('parent',
            $this->cartographie_manager->get('cartes'));
        $oDistanceMatrix = $this->cartographie_manager->get(
            GoogleMaps\DistanceMatrix::class);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // initialisation du formulaire à partir de l'identité de l'utilisateur
            // autentifié
            $args = $responsable->getArrayCopy();
            $point = new Point($args['x'], $args['y']);
            $pt = $oDistanceMatrix->getProjection()->xyzVersgRGF93($point);
            $pt->setLatLngRange($configCarte['valide']['lat'],
                $configCarte['valide']['lng']);
            if (! $pt->isValid()) {
                // essayer de localiser par l'adresse avant de présenter la carte
                $array = $this->cartographie_manager->get(GoogleMaps\Geocoder::class)->geocode(
                    $responsable->adresseL1, $responsable->codePostal,
                    $responsable->lacommune);
                $pt = new Point($array['lng'], $array['lat'], 0, 'degré');
                $pt->setLatLngRange($configCarte['valide']['lat'],
                    $configCarte['valide']['lng']);
                if (! $pt->isValid() && ! empty($responsable->adresseL2)) {
                    $array = $this->cartographie_manager->get(GoogleMaps\Geocoder::class)->geocode(
                        $responsable->adresseL2, $responsable->codePostal,
                        $responsable->lacommune);
                    $pt->setLatitude($array['lat']);
                    $pt->setLongitude($array['lng']);
                    if (! $pt->isValid() && ! empty($responsable->adresseL3)) {
                        $array = $this->cartographie_manager->get(
                            GoogleMaps\Geocoder::class)->geocode($responsable->adresseL3,
                            $responsable->codePostal, $responsable->lacommune);
                        $pt->setLatitude($array['lat']);
                        $pt->setLongitude($array['lng']);
                        if (! $pt->isValid()) {
                            $pt->setLatitude($configCarte['centre']['lat']);
                            $pt->setLongitude($configCarte['centre']['lng']);
                        }
                    }
                }
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                return $this->redirect()->toRoute('login', [
                    'action' => 'home-page'
                ]);
            } elseif (array_key_exists('lng', $args) && array_key_exists('lat', $args)) {
                $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
            } else {
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            }
        }
        // ici, le pt est initialisé en lat, lng, degré
        $form = new LatLngForm([
            'responsableId' => [
                'id' => 'responsableId'
            ]
        ],
            [
                'submit' => [
                    'class' => 'button default submit left-95px',
                    'value' => 'Enregistrer la localisation'
                ],
                'cancel' => [
                    'class' => 'button default cancel left-10px',
                    'value' => 'Abandonner'
                ]
            ], $configCarte['valide']);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmparentconfig', [
                'action' => 'localisation'
            ]));
        $form->setData(
            [
                'responsableId' => $responsable->responsableId,
                'lat' => $pt->getLatitude(),
                'lng' => $pt->getLongitude()
            ]);
        if (array_key_exists('submit', $args)) {
            if ($args['responsableId'] != $responsable->responsableId) {
                // usurpation d'identité
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            }
            // On vérifie qu'on a cliqué dans un rectangle autorisé
            $form->setData($args);
            if ($form->isValid()) {
                $point = $oDistanceMatrix->getProjection()->gRGF93versXYZ($pt);
                $tableResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
                $oData = $tableResponsables->getObjData();
                $oData->exchangeArray(
                    [
                        'responsableId' => $responsable->responsableId,
                        'x' => $point->getX(),
                        'y' => $point->getY()
                    ]);
                $tableResponsables->saveRecord($oData);
                $responsable->refresh();
                $msg = $this->cartographie_manager->get('Sbm\MajDistances')->pour(
                    $responsable->responsableId);
                // ne pas afficher l'échec de mise à jour des distances pour les élèves
                unset($msg);
                $this->flashMessenger()->addSuccessMessage(
                    'La localisation du domicile est enregistrée.');
                return $this->redirect()->toRoute('login', [
                    'action' => 'home-page'
                ]);
            }
        }

        return new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'responsable' => $responsable,
                'form' => $form->prepare(),
                'config' => $configCarte,
                'url_api' => $this->cartographie_manager->get('google_api_browser')['js']
            ]);
    }

    public function inscriptionListeDeDiffusionAction()
    {
        return [];
    }
}