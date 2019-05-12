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
 * @date 27 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmPdf\Controller;

use SbmBase\Model\Session;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmGestion\Model\Db\Filtre\Eleve\Filtre as FiltreEleve;
use SbmPdf\Model\Tcpdf;
use Zend\Http\PhpEnvironment\Response;

class DocumentController extends AbstractActionController
{

    /**
     * Catégorie de l'utilisateur
     *
     * @var int
     */
    private $categorie;

    public function indexAction()
    {
    }

    private function init($sessionNameSpace)
    {
    }

    public function lesFacturesAction()
    {
        $responsableId = $this->getResponsableIdFromSession('nsArgsFacture');
        // factureset est un objet Iterator
        $factureset = new \SbmCommun\Model\Paiements\FactureSet($this->db_manager,$responsableId);
        $this->pdf_manager->get(Tcpdf::class)
        ->setParams(
            [
                'documentId' => 'Facture à un responsable',
                'layout' => 'sbm-pdf/layout/facture.phtml',
                'args' => [
                    'vendeur' => $this->organisateur,
                    'acheteur' => $this->db_manager->get('Sbm\Db\Vue\Responsables')
                    ->getRecord($responsableId)
                ]
            ])
            ->setData($factureset)
            ->run();
    }

    public function factureAction()
    {
        $responsableId = $this->getResponsableIdFromSession('nsArgsFacture');
        // objet qui calcule les résultats financiers pour le responsableId indiqué
        // et qui prépare les éléments de la facture
        $facture = new \SbmCommun\Model\Paiements\Facture($this->db_manager,
            $this->db_manager->get(
                \SbmCommun\Model\Db\Service\Query\Paiement\Calculs::class)->getResultats(
                $responsableId));
        $this->pdf_manager->get(Tcpdf::class)
            ->setParams(
            [
                'documentId' => 'Facture à un responsable',
                'layout' => 'sbm-pdf/layout/facture.phtml',
                'args' => [
                    'vendeur' => $this->organisateur,
                    'acheteur' => $this->db_manager->get('Sbm\Db\Vue\Responsables')
                        ->getRecord($responsableId)
                ]
            ])
            ->setData([
            $facture
        ])
            ->run();

        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }

    /**
     * Action pour générer les horaires au format pdf Reçoit éventuellement en post un
     * 'serviceId'
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function horairesAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
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
                    $responsable = $this->responsable_manager->get();
                } catch (\Exception $e) {
                    return $this->redirect()->toRoute('login', [
                        'action' => 'logout'
                    ]);
                }
                try {
                    $affectations = $this->db_manager->get('Sbm\Db\Table\Affectations')->fetchAll(
                        [
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
                } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                    $this->flashMessenger()->addInfoMessage(
                        'Vos enfants n\'ont pas été affectés sur un circuit.');
                    return $this->redirect()->toRoute('login', [
                        'action' => 'home-page'
                    ]);
                }
                break;
            case 2: // transporteur
                try {
                    $transporteurId = $this->db_manager->get(
                        'Sbm\Db\Table\UsersTransporteurs')->getTransporteurId($userId);
                    $oservices = $this->db_manager->get('Sbm\Db\Table\Services')->fetchAll(
                        [
                            'transporteurId' => $transporteurId
                        ]);
                    $services = [];
                    foreach ($oservices as $objectService) {
                        $services[] = $objectService->lotId;
                    }
                } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                    $this->flashMessenger()->addInfoMessage(
                        'Pas d\'enfants affectés sur vos circuits.');
                    return $this->redirect()->toRoute('login', [
                        'action' => 'home-page'
                    ]);
                }
                break;
            case 3: // établissement
                try {
                    $etablissementId = $this->db_manager->get(
                        'Sbm\Db\Table\UsersEtablissements')->getEtablissementId($userId);
                    $oservices = $this->db_manager->get(
                        'Sbm\Db\Table\EtablissementsServices')->fetchAll(
                        [
                            'etablissementId' => $etablissementId
                        ]);
                    $services = [];
                    foreach ($oservices as $objectService) {
                        $services[] = $objectService->lotId;
                    }
                } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                    $this->flashMessenger()->addInfoMessage(
                        'Aucun service dessert votre établissement.');
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
                        $services[] = $objectService->lotId;
                    }
                } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                    $this->flashMessenger()->addInfoMessage(
                        'Impossible d\'obtenir la liste des services.');
                    return $this->redirect()->toRoute('login', [
                        'action' => 'home-page'
                    ]);
                }
                break;
            default:
                $this->flashMessenger()->addErrorMessage(
                    'La catégorie de cet utilisateur est inconnue.');
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
        // ici, $services contient les 'serviceId' dont on veut obtenir les horaires
        // (tableau
        // indexé ordonné)
        $qCircuits = $this->db_manager->get('Sbm\Db\Query\Circuits');
        $qListe = $this->db_manager->get('Sbm\Db\Eleve\Liste');
        $ahoraires = []; // c'est un tableau
        foreach ($services as $serviceId) {
            $ahoraires[$serviceId] = [
                'aller' => $qCircuits->complet($serviceId, 'matin',
                    function ($arret) use ($qListe, $millesime) {
                        return $this->detailHoraireArret($arret, $qListe, $millesime);
                    }),
                'retour' => $qCircuits->complet($serviceId, 'soir',
                    function ($arret) use ($qListe, $millesime) {
                        return $this->detailHoraireArret($arret, $qListe, $millesime);
                    })
            ];
        }
        if (count($ahoraires)) {
            $this->pdf_manager->get(Tcpdf::class)
                ->setParams(
                [
                    'documentId' => 'Horaires détaillés',
                    'layout' => 'sbm-pdf/layout/horaires.phtml'
                ])
                ->setData($ahoraires)
                ->run();
        } else {
            $this->flashMessenger()->addInfoMessage('Rien à imprimer');
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
    }

    private function detailHoraireArret($arret, $qListe, $millesime)
    {
        // pour les parents, on ne montre que les inscrits
        $liste = $qListe->queryGroup($millesime,
            FiltreEleve::byCircuit($arret['serviceId'], $arret['stationId'],
                $this->categorie == 1), [
                'nom',
                'prenom'
            ]);
        $arret['effectif'] = $liste->count();
        $arret['liste'] = [];
        foreach ($liste as $eleve) {
            $arret['liste'][] = $eleve['nom'] . ' ' . $eleve['prenom'] . ' - ' .
                $eleve['classe'];
        }
        return $arret;
    }

    /**
     * Action permettant de générer la liste des élèves au format pdf dans le portail de
     * l'organisateur
     */
    public function orgPdfAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        // on doit être authentifié
        $auth = $this->authenticate->by('email');
        if (! $auth->hasIdentity() || $auth->getCategorieId() < 200) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
        // formulaire des critères de recherche
        $criteres_form = new \SbmPortail\Form\CriteresForm();
        // initialiser le form pour les select ...
        $criteres_form->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis())
            ->setValueOptions('classeId',
            $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout())
            ->setValueOptions('serviceId',
            $this->db_manager->get('Sbm\Db\Select\Services'))
            ->setValueOptions('stationId',
            $this->db_manager->get('Sbm\Db\Select\Stations')
                ->toutes());

        // créer un objectData qui contient la méthode getWhere() adhoc
        $criteres_obj = new \SbmPortail\Model\Db\ObjectData\Criteres(
            $criteres_form->getElementNames());

        if ($this->sbm_isPost) {
            $criteres_form->setData($args);
            if ($criteres_form->isValid()) {
                $criteres_obj->exchangeArray($criteres_form->getData());
            }
        }
        // récupère les données de la session si le post n'a pas été validé dans le
        // formulaire (pas de post ou invalide)
        if (! $criteres_form->hasValidated() && ! empty($args)) {
            $criteres_obj->exchangeArray($args);
            $criteres_form->setData($criteres_obj->getArrayCopy());
        }

        $where = $criteres_obj->getWhereForEleves();
        $data = $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')->getScolaritesR(
            $where, [
                'nom',
                'prenom'
            ]);

        $this->pdf_manager->get(Tcpdf::class)
            ->setParams(
            [
                'documentId' => 'List élèves portail organisateur',
                'layout' => 'sbm-pdf/layout/org-pdf.phtml'
            ])
            ->setData(iterator_to_array($data))
            ->run();

        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }
}