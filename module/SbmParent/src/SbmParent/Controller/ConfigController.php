<?php
/**
 * Controller du module SbmParent permettant de gérer le compte de l'utilisateur et de revenir dans l'espace des parents
 *
 * @project sbm
 * @package SbmParent/Controller
 * @filesource ConfigController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 avr. 2016
 * @version 2016-2
 */
namespace SbmParent\Controller;

use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;
use SbmCartographie\Model\Point;
use SbmCommun\Model\Db\DbLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Model\StdLib;
use SbmCommun\Form;
use SbmCommun\Form\LatLng as LatLngForm;
use SbmParent\Form\ModifAdresse;
use SbmParent\Model\Responsable;
use Zend\Mvc\Controller\Plugin\Redirect;

class ConfigController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }

    public function messageAction()
    {
        $retour = $this->url()->fromRoute('login', array(
            'action' => 'home-page'
        ));
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('dafapmail');
    }

    public function modifCompteAction()
    {
        try {
            $responsable = $this->config['responsable']->get();
        } catch (Exception $e) {
            return $this->redirect()->toRoute('login', array(
                'action' => 'logout'
            ));
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // initialisation du formulaire à partir de l'identité de l'utilisateur autentifié
            $args = $responsable->getArrayCopy();
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                return $this->redirect()->toRoute('login', array(
                    'action' => 'home-page'
                ));
            }
        }
        // ici on a le tableau d'initialisation du formulaire dans $args
        $responsableId = $args['responsableId'];
        $hasEnfantInscrit = $this->config['db_manager']->get('Sbm\Db\Query\Responsables')->hasEnfantInscrit($responsableId);
        $tableResponsables = $this->config['db_manager']->get('Sbm\Db\Table\Responsables');
        $db = $this->config['db_manager'];
        // on ouvre le formulaire complet et on l'adapte
        $form = $this->config['form_manager']->get(Form\Responsable::class);
        if ($hasEnfantInscrit) {
            $form->setMaxLength($db->getMaxLengthArray('responsables', 'table'));
        } else {
            $value_options = $this->config['db_manager']->get('Sbm\Db\Select\Communes')->membres();
            $form->setValueOptions('communeId', $value_options)
                ->setValueOptions('ancienCommuneId', $value_options)
                ->setMaxLength($db->getMaxLengthArray('responsables', 'table'));
            unset($value_options);
        }
        $form->bind($tableResponsables->getObjData());
        $form->setData($args);
        if (array_key_exists('submit', $args)) {
            if ($form->isValid()) {
                // controle le csrf et contrôle les datas
                $tableResponsables->saveRecord($form->getData());
                $responsable->refresh();
                $this->flashMessenger()->addSuccessMessage('Modifications enregistrées.');
                return $this->redirect()->toRoute('login', array(
                    'action' => 'synchro-compte'
                ));
            }
            $this->flashMessenger()->addWarningMessage('Données invalides');
        }
        return new ViewModel(array(
            'hasEnfantInscrit' => $hasEnfantInscrit,
            'form' => $form->prepare(),
            'responsableId' => $responsableId,
            'responsable' => $responsable->getArrayCopy(),
            'demenagement' => StdLib::getParam('demenagement', $args, false)
        ));
    }

    /**
     * Modifie l'adresse et demande la localisation
     */
    public function modifAdresseAction()
    {
        try {
            $responsable = $this->config['responsable']->get();
        } catch (Exception $e) {
            return $this->redirect()->toRoute('login', array(
                'action' => 'logout'
            ));
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // initialisation du formulaire à partir de l'identité de l'utilisateur autentifié
            $args = $responsable->getArrayCopy();
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                return $this->redirect()->toRoute('login', array(
                    'action' => 'home-page'
                ));
            }
            if (array_key_exists('modif-adresse', $args)) {
                $args = $responsable->getArrayCopy();
            }
       }
        // ici on a le tableau d'initialisation du formulaire dans $args
        $responsableId = StdLib::getParam('responsableId', $args, $responsable->responsableId);
        $hasEnfantInscrit = $this->config['db_manager']->get('Sbm\Db\Query\Responsables')->hasEnfantInscrit($responsableId);
        $tableResponsables = $this->config['db_manager']->get('Sbm\Db\Table\Responsables');
        $db = $this->config['db_manager'];
        // on ouvre le formulaire complet et on l'adapte
        $form = $this->config['form_manager']->get(ModifAdresse::class);
        $value_options = $this->config['db_manager']->get('Sbm\Db\Select\Communes')->membres();
        $form->setValueOptions('communeId', $value_options)->setMaxLength($db->getMaxLengthArray('responsables', 'table'));
        unset($value_options);
        $form->bind($tableResponsables->getObjData());
        $form->setData($args);
        if (array_key_exists('submit', $args)) {
            if ($form->isValid()) {
                $changeAdresse = $tableResponsables->saveResponsable($form->getData(), true);
                $responsable->refresh();
                $this->flashMessenger()->addSuccessMessage('Modifications enregistrées.');
                if ($changeAdresse) {
                    return $this->redirect()->toRoute('sbmparentconfig', array(
                        'action' => 'localisation'
                    ));
                }
            } else {
                $this->flashMessenger()->addWarningMessage('Données inchangées');
            }
            return $this->redirect()->toRoute('sbmparent');
        }
        return new ViewModel(array(
            'form' => $form->prepare(),
            'responsableId' => $responsableId,
            'responsable' => $responsable->getArrayCopy(),
            'demenagement' => StdLib::getParam('demenagement', $args, false)
        ));
    }

    public function mdpChangeAction()
    {
        $retour = $this->url()->fromRoute('login', array(
            'action' => 'home-page'
        ));
        return $this->redirect()->toRoute('login', array(
            'action' => 'mdp-change'
        ));
    }

    public function emailChangeAction()
    {
        $retour = $this->url()->fromRoute('login', array(
            'action' => 'home-page'
        ));
        return $this->redirect()->toRoute('login', array(
            'action' => 'email-change'
        ));
    }

    /**
     * Le retour se fait par un redirectToOrigin()->back() ce qui veut dire qu'il faut avoir défini le redirectToOrigin()
     * avant l'appel.
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function createAction()
    {
        $identity = $this->config['authenticate']->by()->getIdentity();
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // initialisation du formulaire à partir de l'identité de l'utilisateur autentifié
            $args = $identity;
        } else {
            $args = $prg;
        }
        // ici on a le tableau d'initialisation du formulaire dans $args
        $responsableId = null;
        $tableResponsables = $this->config['db_manager']->get('Sbm\Db\Table\Responsables');
        $db = $this->config['db_manager'];
        // on ouvre le formulaire avec l'identité verrouillée et on l'adapte
        $form = $this->config['form_manager']->get(Form\ResponsableVerrouille::class);
        $value_options = $this->config['db_manager']->get('Sbm\Db\Select\Communes')->membres();
        $form->setValueOptions('communeId', $value_options)
            ->setValueOptions('ancienCommuneId', $value_options)
            ->setMaxLength($db->getMaxLengthArray('responsables', 'table'));
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
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    return $this->redirect()->toRoute('login', array(
                        'action' => 'home-page'
                    ));
                }
            }
            $this->flashMessenger()->addWarningMessage('Données invalides');
        }
        return new ViewModel(array(
            'form' => $form->prepare(),
            'responsableId' => $responsableId,
            'demenagement' => false
        ));
    }

    public function localisationAction()
    {
        try {
            $responsable = $this->config['responsable']->get();
        } catch (Exception $e) {
            return $this->redirect()->toRoute('login', array(
                'action' => 'logout'
            ));
        }
        // nécessaire pour valider lat et lng
        $configCarte = StdLib::getParam('parent', $this->config['cartographie_manager']->get('cartes'));
        $d2etab = $this->config['cartographie_manager']->get('SbmCarto\DistanceEtablissements');
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // initialisation du formulaire à partir de l'identité de l'utilisateur autentifié
            $args = $responsable->getArrayCopy();
            $point = new Point($args['x'], $args['y']);
            $pt = $d2etab->getProjection()->xyzVersgRGF93($point);
            $form = new LatLngForm([], [], $configCarte['valide']);
            $form->setData([
                'lat' => $pt->getLatitude(),
                'lng' => $pt->getLongitude()
            ]);
            if (! $form->isValid()) {
                // essayer de localiser par l'adresse avant de présenter la carte
                $array = $this->config['cartographie_manager']->get('SbmCarto\Geocoder')->geocode($responsable->adresseL1, $responsable->codePostal, $responsable->commune);
                $pt = new Point($array['lng'], $array['lat'], 0, 'degré');
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                return $this->redirect()->toRoute('login', array(
                    'action' => 'home-page'
                ));
            } elseif (array_key_exists('lng', $args) && array_key_exists('lat', $args)) {
                $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
            } else {
                return $this->redirect()->toRoute('login', array(
                    'action' => 'logout'
                ));
            }
        }
        // ici, le pt est initialisé en lat, lng, degré
        $form = new LatLngForm(array(
            'responsableId' => array(
                'id' => 'responsableId'
            )
        ), array(
            'submit' => array(
                'class' => 'button default submit left-95px',
                'value' => 'Enregistrer la localisation'
            ),
            'cancel' => array(
                'class' => 'button default cancel left-10px',
                'value' => 'Abandonner'
            )
        ), $configCarte['valide']);
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmparentconfig', array(
            'action' => 'localisation'
        )));
        $form->setData(array(
            'responsableId' => $responsable->responsableId,
            'lat' => $pt->getLatitude(),
            'lng' => $pt->getLongitude()
        ));
        if (array_key_exists('submit', $args)) {
            if ($args['responsableId'] != $responsable->responsableId) {
                // usurpation d'identité
                return $this->redirect()->toRoute('login', array(
                    'action' => 'logout'
                ));
            }
            // On vérifie qu'on a cliqué dans un rectangle autorisé
            $form->setData($args);
            if ($form->isValid()) {
                $point = $d2etab->getProjection()->gRGF93versXYZ($pt);
                $tableResponsables = $this->config['db_manager']->get('Sbm\Db\Table\Responsables');
                $oData = $tableResponsables->getObjData();
                $oData->exchangeArray(array(
                    'responsableId' => $responsable->responsableId,
                    'x' => $point->getX(),
                    'y' => $point->getY()
                ));
                $tableResponsables->saveRecord($oData);
                $responsable->refresh();
                $this->config['cartographie_manager']->get('Sbm\MajDistances')->pour($responsable->responsableId);
                $this->flashMessenger()->addSuccessMessage('La localisation du domicile est enregistrée.');
                return $this->redirect()->toRoute('login', array(
                    'action' => 'home-page'
                ));
            }
        }
        
        return new ViewModel(array(
            'responsable' => $responsable,
            'form' => $form->prepare(),
            'config' => $configCarte
        ));
    }
} 