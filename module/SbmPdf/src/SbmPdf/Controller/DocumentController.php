<?php
/**
 * Controller des documents particuliers
 *
 * Ces documents sont définis à partir de templates html : une action par document
 * 
 * @project sbm
 * @package SbmPdf/Controller
 * @filesource DocumentController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 oct. 2016
 * @version 2016-2.2.1
 */
namespace SbmPdf\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Response;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Sql;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Form\ButtonForm;
use SbmGestion\Model\Db\Filtre\Eleve\Filtre as FiltreEleve;
use SbmParent\Model\Responsable;
use SbmPdf\Model\Tcpdf;

class DocumentController extends AbstractActionController
{

    /**
     * Catégorie de l'utilisateur
     *
     * @var int
     */
    private $categorie;

    public function indexAction()
    {}

    private function init($sessionNameSpace)
    {}

    /**
     * Reçoit éventuellement en post un
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function horairesAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = (array) $prg;
        $millesime = Session::get('millesime');
        // on doit être authentifié
        $auth = $this->authenticate->by('email');
        if (! $auth->hasIdentity()) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
        $userId = $auth->getUserId();
        $this->categorie = $auth->getCategorieId();
        // qui est-ce ?
        switch ($this->categorie) {
            case 1: // parent
                try {
                    $responsable = $this->responsable->get();
                } catch (Exception $e) {
                    return $this->redirect()->toRoute('login', [
                        'action' => 'logout'
                    ]);
                }
                try {
                    $affectations = $this->db_manager->get('Sbm\Db\Table\Affectations')->fetchAll([
                        'responsableId' => $responsable->responsableId,
                        'millesime' => $millesime
                    ]);
                    $services = [];
                    // construction d'une table sans doublons
                    foreach ($affectations as $affectation) {
                        $services[$affectation->service1Id] = $affectation->service1Id;
                        if (! empty($affectation->service2Id)) {
                            $services[$affectation->service2Id] = $affectation->service2Id;
                        }
                    }
                    if (! empty($services)) {
                        $services = array_values($services);
                    }
                } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
                    $this->flashMessenger()->addInfoMessage('Vos enfants n\'ont pas été affectés sur un circuit.');
                    return $this->redirect()->toRoute('login', [
                        'action' => 'home-page'
                    ]);
                }
                break;
            case 2: // transporteur
                try {
                    $transporteurId = $this->db_manager->get('Sbm\Db\Table\UsersTransporteurs')->getTransporteurId($userId);
                    $oservices = $this->db_manager->get('Sbm\Db\Table\Services')->fetchAll([
                        'transporteurId' => $transporteurId
                    ]);
                    $services = [];
                    foreach ($oservices as $objectService) {
                        $services[] = $objectService->serviceId;
                    }
                } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
                    $this->flashMessenger()->addInfoMessage('Pas d\'enfants affectés sur vos circuits.');
                    return $this->redirect()->toRoute('login', [
                        'action' => 'home-page'
                    ]);
                }
                break;
            case 3: // établissement
                try {
                    $etablissementId = $this->db_manager->get('Sbm\Db\Table\UsersEtablissements')->getEtablissementId($userId);
                    $oservices = $this->db_manager->get('Sbm\Db\Table\EtablissementsServices')->fetchAll([
                        'etablissementId' => $etablissementId
                    ]);
                    $services = [];
                    foreach ($oservices as $objectService) {
                        $services[] = $objectService->serviceId;
                    }
                } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
                    $this->flashMessenger()->addInfoMessage('Aucun service dessert votre établissement.');
                    return $this->redirect()->toRoute('login', [
                        'action' => 'home-page'
                    ]);
                }
                break;
            case 200: // secrétariat
            case 253: // gestion
            case 254: // admin
            case 255: // sadmin
                try {
                    $services = [];
                    $oservices = $this->db_manager->get('Sbm\Db\Table\Services')->fetchAll();
                    foreach ($oservices as $objectService) {
                        $services[] = $objectService->serviceId;
                    }
                } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
                    $this->flashMessenger()->addInfoMessage('Impossible d\'obtenir la liste des services.');
                    return $this->redirect()->toRoute('login', [
                        'action' => 'home-page'
                    ]);
                }
                break;
            default:
                $this->flashMessenger()->addErrorMessage('La catégorie de cet utilisateur est inconnue.');
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
                break;
        }
        if (array_key_exists('serviceId', $args)) {
            if (in_array($args['serviceId'], $services)) {
                $services = (array) $args['serviceId'];
            } else {
                $services = [];
            }
        }
        if (! empty($services)) {
            asort($services);
        }
        // ici, $services contient les 'serviceId' dont on veut obtenir les horaires (tableau indexé ordonné)
        $qCircuits = $this->db_manager->get('Sbm\Db\Query\Circuits');
        $qListe = $this->db_manager->get('Sbm\Db\Eleve\Liste');
        $ahoraires = []; // c'est un tableau
        foreach ($services as $serviceId) {
            $ahoraires[$serviceId] = [
                'aller' => $qCircuits->complet($serviceId, 'matin', function ($arret) use($qListe, $millesime) {
                    return $this->detailHoraireArret($arret, $qListe, $millesime);
                }),
                'retour' => $qCircuits->complet($serviceId, 'soir', function ($arret) use($qListe, $millesime) {
                    return $this->detailHoraireArret($arret, $qListe, $millesime);
                })
            ];
        }
        $this->pdf_manager->get(Tcpdf::class)
            ->setParams([
            'documentId' => 'Horaires détaillés',
            'layout' => 'sbm-pdf/document/horaires.phtml'
        ])
            ->setData($ahoraires)
            ->run();
    }

    private function detailHoraireArret($arret, $qListe, $millesime)
    {
        // pour les parents, on ne montre que les inscrits
        $liste = $qListe->query($millesime, FiltreEleve::byCircuit($arret['serviceId'], $arret['stationId'], $this->categorie == 1), [
            'nom',
            'prenom'
        ]);
        $arret['effectif'] = count($liste);
        $arret['liste'] = [];
        foreach ($liste as $eleve) {
            $arret['liste'][] = $eleve['nom'] . ' ' . $eleve['prenom'] . ' - ' . $eleve['classe'];
        }
        return $arret;
    }
}