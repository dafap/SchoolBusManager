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
 * @date 24 août 2015
 * @version 2015-1
 */
namespace SbmPdf\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Response;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Sql;
use DafapSession\Model\Session;
use SbmCommun\Model\StdLib;
use SbmCommun\Form\ButtonForm;
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
        $auth = $this->getServiceLocator()
            ->get('Dafap\Authenticate')
            ->by('email');
        if (! $auth->hasIdentity()) {
            return $this->redirect()->toRoute('login', array(
                'action' => 'home-page'
            ));
        }
        $userId = $auth->getUserId();
        $this->categorie = $auth->getCategorieId();
        // qui est-ce ?
        switch ($this->categorie) {
            case 1: // parent
                try {
                    $responsable = new Responsable($this->getServiceLocator());
                } catch (Exception $e) {
                    return $this->redirect()->toRoute('login', array(
                        'action' => 'logout'
                    ));
                }
                try {
                    $affectations = $this->getServiceLocator()
                        ->get('Sbm\Db\Table\Affectations')
                        ->fetchAll(array(
                        'responsableId' => $responsable->responsableId,
                        'millesime' => $millesime
                    ));
                    $services = array();
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
                    return $this->redirect()->toRoute('login', array(
                        'action' => 'home-page'
                    ));
                }
                break;
            case 2: // transporteur
                try {
                    $transporteurId = $this->getServiceLocator()
                        ->get('Sbm\Db\Table\UsersTransporteurs')
                        ->getTransporteurId($userId);
                    $oservices = $this->getServiceLocator()
                        ->get('Sbm\Db\Table\Services')
                        ->fetchAll(array(
                        'transporteurId' => $transporteurId
                    ));
                    $services = array();
                    foreach ($oservices as $objectService) {
                        $services[] = $objectService->serviceId;
                    }
                } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
                    $this->flashMessenger()->addInfoMessage('Pas d\'enfants affectés sur vos circuits.');
                    return $this->redirect()->toRoute('login', array(
                        'action' => 'home-page'
                    ));
                }
                break;
            case 3: // établissement
                try {
                    $etablissementId = $this->getServiceLocator()
                        ->get('Sbm\Db\Table\UsersEtablissements')
                        ->getEtablissementId($userId);
                    $oservices = $this->getServiceLocator()
                        ->get('Sbm\Db\Table\EtablissementsServices')
                        ->fetchAll(array(
                        'etablissementId' => $etablissementId
                    ));
                    $services = array();
                    foreach ($oservices as $objectService) {
                        $services[] = $objectService->serviceId;
                    }
                } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
                    $this->flashMessenger()->addInfoMessage('Aucun service dessert votre établissement.');
                    return $this->redirect()->toRoute('login', array(
                        'action' => 'home-page'
                    ));
                }
                break;
            case 200: // secrétariat
            case 253: // gestion
            case 254: // admin
            case 255: // sadmin
                try {
                    $services = array();
                    $oservices = $this->getServiceLocator()
                        ->get('Sbm\Db\Table\Services')
                        ->fetchAll();
                    foreach ($oservices as $objectService) {
                        $services[] = $objectService->serviceId;
                    }
                } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
                    $this->flashMessenger()->addInfoMessage('Impossible d\'obtenir la liste des services.');
                    return $this->redirect()->toRoute('login', array(
                        'action' => 'home-page'
                    ));
                }
                break;
            default:
                $this->flashMessenger()->addErrorMessage('La catégorie de cet utilisateur est inconnue.');
                return $this->redirect()->toRoute('login', array(
                    'action' => 'logout'
                ));
                break;
        }
        if (array_key_exists('serviceId', $args)) {
            if (in_array($args['serviceId'], $services)) {
                $services = (array) $args['serviceId'];
            } else {
                $services = array();
            }
        }
        if (! empty($services)) {
            asort($services);
        }
        // ici, $services contient les 'serviceId' dont on veut obtenir les horaires (tableau indexé ordonné)
        $qCircuits = $this->getServiceLocator()->get('Sbm\Db\Query\Circuits');
        $qListe = $this->getServiceLocator()->get('Sbm\Db\Eleve\Liste');
        $ahoraires = array(); // c'est un tableau
        foreach ($services as $serviceId) {
            $ahoraires[$serviceId] = array(
                'aller' => $qCircuits->complet($serviceId, 'matin', function ($arret) use($qListe, $millesime) {
                    return $this->detailHoraireArret($arret, $qListe, $millesime);
                }),
                'retour' => $qCircuits->complet($serviceId, 'soir', function ($arret) use($qListe, $millesime) {
                    return $this->detailHoraireArret($arret, $qListe, $millesime);
                })
            );
        }
        // il faudra essayer de passer des params basés sur un document de la table système
        $params = array(
            'documentId' => 'Horaires détaillés'
        );
        $pdf = new Tcpdf($this->getServiceLocator(), $params);
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $layout = new ViewModel();
        $layout->setTemplate('sbm-pdf/document/horaires.phtml');
        foreach ($ahoraires as $serviceId => $allerRetour) {
            $oservice = $this->getServiceLocator()
                ->get('Sbm\Db\Table\Services')
                ->getRecord($serviceId);
            $otransporteur = $this->getServiceLocator()
                ->get('Sbm\Db\Table\Transporteurs')
                ->getRecord($oservice->transporteurId);
            $transporteur = $otransporteur->nom;
            $nbPlaces = $oservice->nbPlaces;
            $telephone = $otransporteur->telephone;
            $part_gauche = "Circuit n° $serviceId - car $transporteur - $nbPlaces places";
            $part_droite = "Tél $transporteur : $telephone";
            $pdf->AddPage();
            $pdf->Write(0, $part_gauche, '', false, 'L');
            $pdf->Write(0, $part_droite, '', false, 'R', true);
            // die(var_dump($allerRetour));
            $layout->setVariables(array(
                'allerRetour' => $allerRetour
            ));
            $codeHtml = $viewRender->render($layout);
            // die(var_dump($codeHtml));
            $pdf->writeHTML($codeHtml, true, false, false, false, '');
        }
        $pdf->Output($pdf->getConfig('document', 'out_name', 'doc.pdf'), $pdf->getConfig('document', 'out_mode', 'I'));
    }

    private function detailHoraireArret($arret, $qListe, $millesime)
    {
        if ($this->categorie == 1) {
            // pour les parents, on ne montre que les inscrits
            $filtre = array(
                array(
                    'inscrit' => 1,
                    'paiement' => 1
                ),
                array(
                    'service1Id' => $arret['serviceId'],
                    'station1Id' => $arret['stationId']
                ),
                'or',
                array(
                    'service2Id' => $arret['serviceId'],
                    'station2Id' => $arret['stationId']
                )
            );
        } else {
            // pour les autres, on montre tout le monde (inscrits et préinscrits)
            $filtre = array(
                array(
                    'inscrit' => 1
                ),
                array(
                    'service1Id' => $arret['serviceId'],
                    'station1Id' => $arret['stationId']
                ),
                'or',
                array(
                    'service2Id' => $arret['serviceId'],
                    'station2Id' => $arret['stationId']
                )
            );
        }
        
        $liste = $qListe->byCircuit($millesime, $filtre, array(
            'nom',
            'prenom'
        ));
        $arret['effectif'] = count($liste);
        $arret['liste'] = array();
        foreach ($liste as $eleve) {
            $arret['liste'][] = $eleve['nom'] . ' ' . $eleve['prenom'] . ' - ' . $eleve['classe'];
        }
        return $arret;
    }
}