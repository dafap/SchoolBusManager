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
 * @date 30 avr. 2020
 * @version 2020-2.6.0
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
        $factureset = new \SbmCommun\Model\Paiements\FactureSet($this->db_manager,
            $responsableId,
            $this->db_manager->get('Sbm\Facture\Calculs')->getResultats($responsableId));
        if ($factureset->count()) {
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
                ->setEndOfScriptFunction(
                function () {
                    $this->flashMessenger()
                        ->addSuccessMessage("Édition de factures.");
                })
                ->run();
        } else {
            return $this->factureAction();
        }
    }

    public function factureAction()
    {
        $responsableId = $this->getResponsableIdFromSession('nsArgsFacture');
        // objet qui calcule les résultats financiers pour le responsableId indiqué
        // et qui prépare les éléments de la facture
        $facture = $this->db_manager->get('Sbm\Facture')->setResponsableId($responsableId);
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
            $facture->facturer()
        ])
            ->setEndOfScriptFunction(
            function () {
                $this->flashMessenger()
                    ->addSuccessMessage("Édition d'une facture.");
            })
            ->run();
    }

    /**
     * Action pour générer les horaires au format pdf Reçoit éventuellement en post un
     * 'ligneId', 'sens', 'moment', 'ordre'
     *
     * @todo : à refaire en entier
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function horairesAction()
    {
        $this->flashMessenger()->addErrorMessage('Procédure ' . __METHOD__ . 'à écrire.');
        return $this->redirect()->toRoute('login', [
            'action' => 'home-page'
        ]);
        // ANCIENNE PROCEDURE
        /*
         * Ce qui a changé : chaque enregistrement de circuit ne présente qu'un horaire.
         * Une fiche horaire devrait présenter les horaires du matin, du midi, du soir et
         * les jours de circulation.
         */
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        $table = null;
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
                    $table = $this->db_manager->get('Sbm\Db\Table\Affectations');
                    $affectations = $table->fetchAll(
                        [
                            'responsableId' => $responsable->responsableId,
                            'millesime' => $millesime
                        ]);
                    $services = [];
                    // construction d'une table sans doublons
                    foreach ($affectations as $affectation) {
                        $tmp = $affectation->getEncodeServiceId(1);
                        $services[tmp] = $tmp;
                        if (! empty($affectation->ligne2Id)) {
                            $tmp = $affectation->getEncodeServiceId(2);
                            $services[tmp] = $tmp;
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
            case 110: // transporteur
                try {
                    $transporteurId = $this->db_manager->get(
                        'Sbm\Db\Table\UsersTransporteurs')->getTransporteurId($userId);
                    $table = $this->db_manager->get('Sbm\Db\Table\Services');
                    $oservices = $table->fetchAll([
                        'transporteurId' => $transporteurId
                    ]);
                    $services = [];
                    foreach ($oservices as $objectService) {
                        $services[] = $objectService->getEncodeServiceId();
                    }
                } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                    $this->flashMessenger()->addInfoMessage(
                        'Pas d\'enfant transporté sur vos circuits.');
                    return $this->redirect()->toRoute('login', [
                        'action' => 'home-page'
                    ]);
                }
                break;
            case 120: // établissement
                try {
                    $etablissementId = $this->db_manager->get(
                        'Sbm\Db\Table\UsersEtablissements')->getEtablissementId($userId);
                    $table = $this->db_manager->get('Sbm\Db\Table\EtablissementsServices');
                    $oservices = $table->fetchAll(
                        [
                            'etablissementId' => $etablissementId
                        ]);
                    $services = [];
                    foreach ($oservices as $objectService) {
                        $services[] = $objectService->getEncodeServiceId();
                    }
                } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                    $this->flashMessenger()->addInfoMessage(
                        'Aucun service dessert votre établissement.');
                    return $this->redirect()->toRoute('login', [
                        'action' => 'home-page'
                    ]);
                }
                break;
            case 130: // commune
                try {
                    $communeId = $this->db_manager->get('Sbm\Db\Table\UsersCommunes')->getCommuneId(
                        $userId);
                    $services = [];
                    foreach ($this->db_manager->get('Sbm\Db\Query\Circuits')->getServicesViaCommune(
                        $communeId) as $service) {
                            $services[] = $service['serviceId'];
                    }
                } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                    $this->flashMessenger()->addInfoMessage(
                        'Pas d\'enfant transporté de votre commune.');
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
                    $table = $this->db_manager->get('Sbm\Db\Table\Services');
                    $oservices = $table->fetchAll();
                    foreach ($oservices as $objectService) {
                        $services[] = $objectService->getEncodeServiceId();
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
        if (array_key_exists('ligneId', $args) && array_key_exists('sens', $args) &&
            array_key_exists('moment', $args) && array_key_exists('ordre', $args)) {
            $tmp = $table->getEncodeServiceId(
                [
                    'ligneId' => $args['ligneId'],
                    'sens' => $args['sens'],
                    'moment' => $args['moment'],
                    'ordre' => $args['ordre']
                ]);
            if (in_array($tmp, $services)) {
                $services = (array) $tmp;
            } else {
                $services = [];
            }
        } elseif (array_key_exists('serviceId', $args)) {
            if (in_array($args['serviceId'], $services)) {
                $services = (array) $args['serviceId'];
            } else {
                $services = [];
            }
        }
        if (! empty($services)) {
            asort($services);
        }
        // CODE VERIFIE JUSQU'ICI
        // ici, $services contient les 'serviceId' dont on veut obtenir les horaires
        // (tableau indexé ordonné)
        $qCircuits = $this->db_manager->get('Sbm\Db\Query\Circuits');
        $qListe = $this->db_manager->get('Sbm\Db\Eleve\Liste');
        $ahoraires = []; // c'est un tableau
        foreach ($services as $idServiceAsString) {
            $ahoraires[$idServiceAsString] = [
                'aller' => $qCircuits->complet($idServiceAsString, 'matin',
                    function ($arret) use ($qListe, $millesime) {
                        return $this->detailHoraireArret($arret, $qListe, $millesime);
                    }),
                'retour' => $qCircuits->complet($idServiceAsString, 'soir',
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
                ->setEndOfScriptFunction(
                function () {
                    $this->FlashMessenger()
                        ->addSuccessMessage('Édition des horaires.');
                })
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
            $this->db_manager->get('Sbm\Db\Select\Services')
                ->tout())
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
        $data = $this->db_manager->get('Sbm\Db\Query\ElevesDivers')->getScolaritesR(
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
            ->setEndOfScriptFunction(
            function () {
                $this->flashMessenger()
                    ->addSuccessMessage("Création d'un pdf.");
            })
            ->run();
    }
}