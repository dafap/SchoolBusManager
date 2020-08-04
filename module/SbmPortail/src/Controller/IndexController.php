<?php
/**
 * Controller du portail ouvert aux invités en consultation
 *
 * transporteur, etablissement, secretariat
 *
 * Modifier l'initialisation des propriétés `transporteur_sanspreinscrits` et
 * `etablissement_sanspreinscrits` selon convenance pour adapter les données
 * communiquées par le portail.
 *
 * @project sbm
 * @package SbmPortail/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2020
 * @version 2020-2.6.0
 */
namespace SbmPortail\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmGestion\Model\Db\Filtre\Eleve\Filtre as FiltreEleve;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    use \SbmCommun\Model\Traits\DebugTrait;

    /**
     * Indique si le portail des transporteur cache les préinscrits ou pas.
     *
     * @var bool
     */
    private $transporteur_sanspreinscrits = true;

    /**
     * Indique si le portail des établissements cache les préinscrits ou pas.
     *
     * @var bool
     */
    private $etablissement_sanspreinscrits = true;

    /**
     * Indique si le portail des communes cache les préinscrits ou pas.
     *
     * @var boolean
     */
    private $commune_sanspreinscrits = false;

    public function indexAction()
    {
        $auth = $this->authenticate->by('email');
        if (! $auth->hasIdentity()) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
        // pour le moment, j'utilise la même entrée pour tous les rôles.
        // Le filtre programmé va limiter la vue aux données concernant l'utilisateur
        switch ($auth->getCategorieId()) {
            case 110:
                return $this->redirect()->toRoute('sbmportail', [
                    'action' => 'tr-index'
                ]);
                break;
            case 120:
                return $this->redirect()->toRoute('sbmportail', [
                    'action' => 'et-index'
                ]);
                break;
            case 130:
                return $this->redirect()->toRoute('sbmportail',
                    [
                        'action' => 'com-index'
                    ]);
                break;
            case 200:
            case 253:
            case 254:
            case 255:
                return $this->redirect()->toRoute('sbmportail',
                    [
                        'action' => 'org-index'
                    ]);
                break;
            default:
                return $this->redirect()->toRoute('login', [
                    'action' => 'home-page'
                ]);
                break;
        }
    }

    public function retourAction()
    {
        try {
            return $this->redirectToOrigin()->back();
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('sbmportail');
        }
    }

    /**
     * Entrée des services de l'organisateur en consultation
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function orgIndexAction()
    {
        $statEleve = $this->db_manager->get('Sbm\Statistiques\Eleve');
        $statResponsable = $this->db_manager->get('Sbm\Statistiques\Responsable');
        $millesime = Session::get('millesime');
        $resultNbEnregistres = $statEleve->getNbEnregistresByMillesime($millesime);
        $nbDpEnregistres = current($resultNbEnregistres)['effectif'];
        $nbInternesEnregistres = next($resultNbEnregistres)['effectif'];
        return new ViewModel(
            [
                'elevesDpEnregistres' => $nbDpEnregistres,
                'elevesIntEnregistres' => $nbInternesEnregistres,
                'elevesInscrits' => current(
                    $statEleve->getNbInscritsByMillesime($millesime))['effectif'],
                'elevesInscritsRayes' => current(
                    $statEleve->getNbRayesByMillesime($millesime, true))['effectif'],
                'elevesPreinscrits' => current(
                    $statEleve->getNbPreinscritsByMillesime($millesime))['effectif'],
                'elevesPreinscritsRayes' => current(
                    $statEleve->getNbRayesByMillesime($millesime, false))['effectif'],
                'elevesFamilleAcceuil' => current(
                    $statEleve->getNbFamilleAccueilByMillesime($millesime))['effectif'],
                'elevesGardeAlternee' => current(
                    $statEleve->getNbGardeAlterneeByMillesime($millesime))['effectif'],
                'elevesMoins1km' => current(
                    $statEleve->getNbMoins1KmByMillesime($millesime))['effectif'],
                'elevesDe1A3km' => current(
                    $statEleve->getNbDe1A3KmByMillesime($millesime))['effectif'],
                'eleves3kmEtPlus' => current(
                    $statEleve->getNb3kmEtPlusByMillesime($millesime))['effectif'],
                'elevesDistanceInconnue' => current(
                    $statEleve->getNbDistanceInconnue($millesime))['effectif'],
                'responsablesEnregistres' => current($statResponsable->getNbEnregistres())['effectif'],
                'responsablesAvecEnfant' => current($statResponsable->getNbAvecEnfant())['effectif'],
                'responsablesSansEnfant' => current($statResponsable->getNbSansEnfant())['effectif'],
                'responsablesHorsZone' => current(
                    $statResponsable->getNbCommuneNonMembre())['effectif'],
                'responsablesDemenagement' => current(
                    $statResponsable->getNbDemenagement())['effectif']
            ]);
    }

    public function orgElevesAction()
    {
        $auth = $this->authenticate->by('email');
        if (! $auth->hasIdentity() || $auth->getCategorieId() < 200) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }

        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Prendre les paramètres éventuellement dans la
            // session (login ou cas du paginator ou de F5 )
            $this->sbm_isPost = false;
            $args = Session::get('post', [], $this->getSessionNamespace('org-eleves'));
        } else {
            // c'était un post ; on le met en session si ce n'est pas un retour ou un
            // cancel
            $args = $prg;
            $retour = StdLib::getParam('op', $args, '') == 'retour';
            if ($retour) {
                // dans ce cas, il s'agit du retour d'une action de type suppr, ajout ou
                // edit.
                // Comme pour un get, on récupère ce qui est en session.
                $this->sbm_isPost = false;
                $args = Session::get('post', [], $this->getSessionNamespace('org-eleves'));
            } else {
                if (array_key_exists('cancel', $args)) {
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                        return $this->redirect()->toRoute('sbmportail',
                            [
                                'action' => 'index'
                            ]);
                    }
                } elseif (array_key_exists('origine', $args)) {
                    $this->redirectToOrigin()->setBack($args['origine']);
                    unset($args['origine']);
                }
                $this->sbm_isPost = true;
                Session::set('post', $args, $this->getSessionNamespace('org-eleves'));
            }
        }
        // formulaire des critères de recherche
        $criteres_form = new \SbmPortail\Form\CriteresOrgForm();
        // initialiser le form pour les select ...
        $criteres_form->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis())
            ->setValueOptions('classeId',
            $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout())
            ->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->desservies());

        // créer un objectData qui contient la méthode getWhere() adhoc
        $criteres_obj = new \SbmPortail\Model\Db\ObjectData\CriteresOrg(
            $criteres_form->getElementNames(), false);

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

        $categorie = 200;
        $where = $criteres_obj->getWhereForEleves();
        $paginator = $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->paginatorScolaritesR2(
            $where, [
                'nom',
                'prenom'
            ]);
        return new ViewModel(
            [
                'categorie' => $categorie,
                'paginator' => $paginator,
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
                'criteres_form' => $criteres_form
            ]);
    }

    public function orgPdfAction()
    {
        $auth = $this->authenticate->by('email');
        if (! $auth->hasIdentity() || $auth->getCategorieId() < 200) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }

        $criteres_form = new \SbmPortail\Form\CriteresOrgForm();
        $criteres_obj = new \SbmPortail\Model\Db\ObjectData\CriteresOrg(
            $criteres_form->getElementNames(), false);
        $criteres = Session::get('post', [], $this->getSessionNamespace('org-eleves'));
        if (! empty($criteres)) {
            $criteres_obj->exchangeArray($criteres);
        }
        $where = $criteres_obj->getWherePdfForEleves();
        $documentId = 'List élèves portail organisateur';

        $this->RenderPdfService->setParam('documentId', $documentId)
            ->setParam('layout', 'sbm-pdf/layout/org-pdf.phtml')
            ->setParam('where', $where)
            /*->setData(
            $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')
                ->withScolaritesR2($where, [
                'nom',
                'prenom'
            ]))*/
            ->setEndOfScriptFunction(
            function () {
                $this->flashMessenger()
                    ->addSuccessMessage("Création d'un pdf.");
            })
            ->renderPdf();
    }

    public function orgElevesDownloadAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $columns = [
            'Nom' => 'nom',
            'Prénom' => 'prenom',
            'Établissement' => 'etablissement',
            'Commune de l\'établissement' => 'lacommuneEtablissement',
            'Classe' => 'classe',
            'R1 Identité' => 'responsable1NomPrenom',
            'R1 Adresse ligne 1' => 'adresseL1R1',
            'R1 Adresse ligne 2' => 'adresseL2R1',
            'R1 Adresse ligne 3' => 'adresseL3R1',
            'R1 Commune' => 'lacommuneR1',
            'R1 Téléphone 1' => 'telephoneFR1',
            'R1 Téléphone 2' => 'telephonePR1',
            'R1 Téléphone 3' => 'telephoneTR1',
            'R1 email' => 'emailR1',
            'R2 Identité' => 'responsable2NomPrenom',
            'R2 Adresse ligne 1' => 'adresseL1R2',
            'R2 Adresse ligne 2' => 'adresseL2R2',
            'R2 Adresse ligne 3' => 'adresseL3R2',
            'R2 Commune' => 'lacommuneR2',
            'R2 Téléphone 1' => 'telephoneFR2',
            'R2 Téléphone 2' => 'telephonePR2',
            'R2 Téléphone 3' => 'telephoneTR2',
            'R2 email' => 'emailR2'
        ];
        // index du tableau $columns correspondant à des n° de téléphones
        $aTelephoneIndexes = [];
        $idx = 0;
        foreach ($columns as $column_field) {
            if (substr($column_field, 0, 9) == 'telephone') {
                $aTelephoneIndexes[] = $idx;
            }
            $idx ++;
        }
        // contrôle de l'identité de l'utilisateur
        $auth = $this->authenticate->by('email');
        if (! $auth->hasIdentity() || $auth->getCategorieId() < 200) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
        // reprise des critères
        $criteres = Session::get('post', [], $this->getSessionNamespace('org-eleves'));
        // formulaire des critères de recherche
        $criteres_form = new \SbmPortail\Form\CriteresOrgForm();
        // initialiser le form pour les select ...
        $criteres_form->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis())
            ->setValueOptions('classeId',
            $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout())
            ->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->desservies());
        // créer un objectData qui contient la méthode getWhere() adhoc
        $criteres_obj = new \SbmPortail\Model\Db\ObjectData\CriteresOrg(
            $criteres_form->getElementNames(), false);
        $criteres_form->setData($criteres);
        if ($criteres_form->isValid()) {
            $criteres_obj->exchangeArray($criteres_form->getData());
        }
        // lancement de la requête selon la catégorie de l'utilisateur
        $where = $criteres_obj->getWhereForEleves();
        try {
            $result = $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->withScolaritesR2(
                $where, [
                    'nom',
                    'prenom'
                ]);
        } catch (\Exception $e) {
            die('Erreur dans ' . __METHOD__);
        }
        // et construction d'un tabeau des datas
        $data = [];
        foreach ($result as $eleve) {
            $aEleve = $eleve->getArrayCopy(); // var_dump($aEleve);
            $ligne = [];
            foreach ($columns as $value) {
                $ligne[] = $aEleve[$value];
            }
            $data[] = $ligne;
        }
        // exportation en formatant les n° de téléphones pour qu'ils soient encadrés par
        // le caractère d'enclosure
        $viewhelper = new \SbmCommun\Model\View\Helper\Telephone();
        return $this->csvExport('eleves.csv', array_keys($columns), $data,
            function ($item) use ($aTelephoneIndexes, $viewhelper) {
                foreach ($aTelephoneIndexes as $idx) {
                    $item[$idx] = $viewhelper($item[$idx]);
                }
                return $item;
            });
    }

    public function orgCircuitsAction()
    {
        $this->flashMessenger()->addErrorMessage('Procédure ' . __METHOD__ . ' à écrire.');
        return $this->redirect()->toRoute('login', [
            'action' => 'home-page'
        ]);
        try {
            $services = $this->db_manager->get('Sbm\Db\Query\Services')->paginatorServicesWithEtablissements();
            $effectifServices = $this->db_manager->get('Sbm\Db\Eleve\EffectifServices');
            $effectifServices->init();
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $services = [];
            $effectifServices = null;
        }
        return new ViewModel(
            [
                'paginator' => $services,
                'effectifServices' => $effectifServices,
                'page' => $this->params('page', 1)
            ]);
    }

    /**
     *
     * @return \Zend\Http\Response|array
     */
    private function initCommune()
    {
        $auth = $this->authenticate->by('email');
        if (! $auth->hasIdentity() || $auth->getCategorieId() != 130) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
        $userId = $auth->getUserId();
        try {
            $communeId = $this->db_manager->get('Sbm\Db\Table\UsersCommunes')->getCommuneId(
                $userId);
            $commune = $this->db_manager->get('Sbm\Db\Table\Communes')->getRecord(
                $communeId)->alias;
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
        return [
            'userId' => $userId,
            'communeId' => $communeId,
            'nom' => $commune
        ];
    }

    public function comIndexAction()
    {
        $commune = $this->initCommune();
        if ($commune instanceof \Zend\Http\Response) {
            return $commune;
        }
        $statEleve = $this->db_manager->get('Sbm\Statistiques\Eleve');
        $millesime = Session::get('millesime');
        return new ViewModel(
            [
                'commune' => $commune['nom'],
                'elevesEnregistres' => current(
                    $statEleve->getNbEnregistresByMillesime($millesime,
                        $commune['communeId']))['effectif'],
                'elevesInscrits' => current(
                    $statEleve->getNbInscritsByMillesime($millesime, $commune['communeId']))['effectif'],
                'elevesPreinscrits' => current(
                    $statEleve->getNbPreinscritsByMillesime($millesime,
                        $commune['communeId']))['effectif'],
                'elevesRayes' => current(
                    $statEleve->getNbRayesByMillesime($millesime, true,
                        $commune['communeId']))['effectif'],
                'elevesFamilleAcceuil' => current(
                    $statEleve->getNbFamilleAccueilByMillesime($millesime,
                        $commune['communeId']))['effectif'],
                'elevesGardeAlternee' => current(
                    $statEleve->getNbGardeAlterneeByMillesime($millesime,
                        $commune['communeId']))['effectif'],
                'elevesMoins1km' => current(
                    $statEleve->getNbMoins1KmByMillesime($millesime, $commune['communeId']))['effectif'],
                'elevesDe1A3km' => current(
                    $statEleve->getNbDe1A3KmByMillesime($millesime, $commune['communeId']))['effectif'],
                'eleves3kmEtPlus' => current(
                    $statEleve->getNb3kmEtPlusByMillesime($millesime,
                        $commune['communeId']))['effectif']
            ]);
    }

    /**
     * Présente la liste des élèves de la commune
     */
    public function comElevesAction()
    {
        $commune = $this->initCommune();
        if ($commune instanceof \Zend\Http\Response) {
            return $commune;
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $this->sbm_isPost = false; // un GET
            $args = Session::get('post', [], $this->getSessionNamespace('com-eleves'));
        } else {
            $args = $prg; // un POST redirigé
            if (StdLib::getParam('op', $args, '') == 'retour') {
                // dans ce cas, il s'agit du retour d'une action de type suppr, ajout ou
                // edit. Comme pour un get, on récupère ce qui est en session.
                $this->sbm_isPost = false;
                $args = Session::get('post', [], $this->getSessionNamespace('com-eleves'));
            } else {
                if (array_key_exists('cancel', $args)) {
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                        return $this->redirect()->toRoute('sbmportail',
                            [
                                'action' => 'index'
                            ]);
                    }
                } elseif (array_key_exists('origine', $args)) {
                    $this->redirectToOrigin()->setBack($args['origine']);
                    unset($args['origine']);
                }
                $this->sbm_isPost = true;
                Session::set('post', $args, $this->getSessionNamespace('com-eleves'));
            }
        }
        // formulaire des critères de recherche
        $criteres_form = new \SbmPortail\Form\CriteresCommuneForm();
        // initialiser le form pour les select ...
        $criteres_form->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis())
            ->setValueOptions('classeId',
            $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout());
        // créer un objectData qui contient la méthode getWhere() adhoc
        $criteres_obj = new \SbmPortail\Model\Db\ObjectData\CriteresCommune(
            $criteres_form->getElementNames(), $this->commune_sanspreinscrits);
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
        $where = $criteres_obj->getWhere()
            ->nest()
            ->equalTo('r1.communeId', $commune['communeId'])->or->equalTo('r2.communeId',
            $commune['communeId'])->unnest();
        $paginator = $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->paginatorScolaritesR2(
            $where, [
                'nom',
                'prenom'
            ]);
        return new ViewModel(
            [
                'categorie' => 130,
                'commune' => $commune['nom'],
                'paginator' => $paginator,
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
                'criteres_form' => $criteres_form
            ]);
    }

    /**
     * Renvoie un fichier de la liste des élèves de la commune
     */
    public function comElevesDownloadAction()
    {
        $commune = $this->initCommune();
        if ($commune instanceof \Zend\Http\Response) {
            return $commune;
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $columns = [
            'Nom' => 'nom',
            'Prénom' => 'prenom',
            'Établissement' => 'etablissement',
            'Commune de l\'établissement' => 'lacommuneEtablissement',
            'Classe' => 'classe',
            'R1 Identité' => 'responsable1NomPrenom',
            'R1 Adresse ligne 1' => 'adresseL1R1',
            'R1 Adresse ligne 2' => 'adresseL2R1',
            'R1 Adresse ligne 3' => 'adresseL3R1',
            'R1 Commune' => 'lacommuneR1',
            'R1 Téléphone 1' => 'telephoneFR1',
            'R1 Téléphone 2' => 'telephonePR1',
            'R1 Téléphone 3' => 'telephoneTR1',
            'R1 email' => 'emailR1',
            'R2 Identité' => 'responsable2NomPrenom',
            'R2 Adresse ligne 1' => 'adresseL1R2',
            'R2 Adresse ligne 2' => 'adresseL2R2',
            'R2 Adresse ligne 3' => 'adresseL3R2',
            'R2 Commune' => 'lacommuneR2',
            'R2 Téléphone 1' => 'telephoneFR2',
            'R2 Téléphone 2' => 'telephonePR2',
            'R2 Téléphone 3' => 'telephoneTR2',
            'R2 email' => 'emailR2'
        ];
        // index du tableau $columns correspondant à des n° de téléphones
        $aTelephoneIndexes = [];
        $idx = 0;
        foreach ($columns as $column_field) {
            if (substr($column_field, 0, 9) == 'telephone') {
                $aTelephoneIndexes[] = $idx;
            }
            $idx ++;
        }
        // reprise des critères
        $criteres = Session::get('post', [], $this->getSessionNamespace('com-eleves'));
        // formulaire des critères de recherche
        $criteres_form = new \SbmPortail\Form\CriteresCommuneForm();
        // initialiser le form pour les select ...
        $criteres_form->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis())
            ->setValueOptions('classeId',
            $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout());
        // créer un objectData qui contient la méthode getWhere() adhoc
        $criteres_obj = new \SbmPortail\Model\Db\ObjectData\CriteresCommune(
            $criteres_form->getElementNames(), $this->commune_sanspreinscrits);
        $criteres_form->setData($criteres);
        if ($criteres_form->isValid()) {
            $criteres_obj->exchangeArray($criteres_form->getData());
        }
        // lancement de la requête selon la catégorie de l'utilisateur
        $where = $criteres_obj->getWhereForEleves();
        $where = $criteres_obj->getWhere()
            ->nest()
            ->equalTo('r1.communeId', $commune['communeId'])->or->equalTo('r2.communeId',
            $commune['communeId'])->unnest();
        try {
            $result = $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->withScolaritesR2(
                $where, [
                    'nom',
                    'prenom'
                ]);
        } catch (\Exception $e) {
            die('Erreur dans ' . __METHOD__);
        }
        // et construction d'un tabeau des datas
        $data = [];
        foreach ($result as $eleve) {
            $aEleve = $eleve->getArrayCopy(); // var_dump($aEleve);
            $ligne = [];
            foreach ($columns as $value) {
                $ligne[] = $aEleve[$value];
            }
            $data[] = $ligne;
        }
        // exportation en formatant les n° de téléphones pour qu'ils soient encadrés par
        // le caractère d'enclosure
        $viewhelper = new \SbmCommun\Model\View\Helper\Telephone();
        return $this->csvExport('eleves.csv', array_keys($columns), $data,
            function ($item) use ($aTelephoneIndexes, $viewhelper) {
                foreach ($aTelephoneIndexes as $idx) {
                    $item[$idx] = $viewhelper($item[$idx]);
                }
                return $item;
            });
    }

    /**
     * Renvoie la liste au format pdf
     */
    public function comPdfAction()
    {
        $commune = $this->initCommune();
        if ($commune instanceof \Zend\Http\Response) {
            return $commune;
        }
        $criteres_form = new \SbmPortail\Form\CriteresCommuneForm();
        $criteres_form->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis())
            ->setValueOptions('classeId',
            $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout());
        $criteres_obj = new \SbmPortail\Model\Db\ObjectData\CriteresCommune(
            $criteres_form->getElementNames(), $this->commune_sanspreinscrits);
        $criteres = Session::get('post', [], $this->getSessionNamespace('com-eleves'));
        $criteres_form->setData($criteres);
        if ($criteres_form->isValid()) {
            $criteres_obj->exchangeArray($criteres_form->getData());
        }
        $documentId = 'List élèves portail commune';
        $where = $criteres_obj->getWhereForEleves();
        $where = $criteres_obj->getWherePdf()
            ->nest()
            ->equalTo('communeIdR1', $commune['communeId'])->or->equalTo('communeIdR2',
            $commune['communeId'])->unnest();
        $call_pdf = $this->RenderPdfService;
        if ($docaffectationId = $this->params('id', false)) {
            $call_pdf->setParam('docaffectationId', $docaffectationId);
        }
        $call_pdf->setParam('documentId', $documentId)
            ->setParam('where', $where)
            ->setEndOfScriptFunction(
            function () {
                $this->flashMessenger()
                    ->addSuccessMessage("Création d'un pdf.");
            })
            ->renderPdf();
    }

    /**
     * Présente la carte des établissements fréquentés par les élèves inscrits pour le
     * millesime en cours. On peut ouvrir la fiche de l'établissement.
     */
    public function comCarteEtablissementsAction()
    {
        $commune = $this->initCommune();
        if ($commune instanceof \Zend\Http\Response) {
            return $commune;
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
        $viewmodel = new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'ptEtablissements' => $this->db_manager->get('Sbm\Portail\Commune\Query')
                    ->setProjection($this->projection)
                    ->setCommuneId($commune['communeId'])
                    ->etablissementsPourCarte(),
                'config' => StdLib::getParam('etablissement', $this->config_cartes),
                'url_api' => $this->url_api
            ]);
        $viewmodel->setTemplate('sbm-cartographie/carte/etablissements.phtml');
        return $viewmodel;
    }

    /**
     * Présente la carte des stations de la commune, en indiquant pour chacune d'elles les
     * circuits qui la desservent. On peut ouvrir la fiche d'une station.
     */
    public function comCarteStationsAction()
    {
        $commune = $this->initCommune();
        if ($commune instanceof \Zend\Http\Response) {
            return $commune;
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }

        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
        $viewmodel = new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'ptStations' => $this->db_manager->get('Sbm\Portail\Commune\Query')
                    ->setProjection($this->projection)
                    ->setCommuneId($commune['communeId'])
                    ->stationsPourCarte(),
                'config' => StdLib::getParam('station', $this->config_cartes),
                'url_api' => $this->url_api
            ]);
        $viewmodel->setTemplate('sbm-cartographie/carte/stations.phtml');
        return $viewmodel;
    }

    /**
     * Présente les circuits passant sur la commune, avec possibilité d'éditer les
     * horaires, de consulter les élèves les fréquentant (par service, par station)
     */
    public function comCircuitsAction()
    {
        $commune = $this->initCommune();
        if ($commune instanceof \Zend\Http\Response) {
            return $commune;
        }
        $args = $this->initListe('lignes');
        if ($args instanceof Response) {
            return $args;
        } elseif (array_key_exists('cancel', $args)) {
            $this->redirectToOrigin()->reset();
            return $this->redirect()->toRoute('sbmgestion/transport');
        }
        $millesime = Session::get('millesime');
        $as = $millesime . '-' . ($millesime + 1);
        $critere_form = $args['form'];
        $critere_form->remove('lotId');
        $viewhelper = new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Portail\Commune\Query')
                    ->setCommuneId($commune['communeId'])
                    ->paginatorLignes($args['where'], [
                    'actif DESC',
                    'ligneId'
                ]),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_lignes', 15),
                'criteres_form' => $critere_form,
                'admin' => false,
                'millesime' => $millesime,
                'as' => $as,
                'commune' => $commune['nom']
            ]);
        $viewhelper->setTemplate('sbm-portail/index/com-ligne.phtml');
        return $viewhelper;
    }

    /**
     * Entrée des etablissements
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etIndexAction()
    {
        $auth = $this->authenticate->by('email');
        if (! $auth->hasIdentity()) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
        $userId = $auth->getUserId();
        try {
            $etablissementId = $this->db_manager->get('Sbm\Db\Table\UsersEtablissements')->getEtablissementId(
                $userId);
            $oetablissement = $this->db_manager->get('Sbm\Db\Vue\Etablissements')->getRecord(
                $etablissementId);
            $etablissement = "$oetablissement->nom - $oetablissement->commune";
            $services = $this->db_manager->get('Sbm\Db\Query\Services')->getServicesGivenEtablissement(
                $etablissementId);
            $effectifEtablissements = $this->db_manager->get(
                'Sbm\Db\Eleve\EffectifEtablissements');
            $effectifEtablissements->init($this->etablissement_sanspreinscrits);
            $elevesTransportes = $effectifEtablissements->transportes($etablissementId);
            $effectifEtablissementsServices = $this->db_manager->get(
                'Sbm\Db\Eleve\EffectifEtablissementsServices');
            $effectifEtablissementsServices->setCaractereConditionnel($etablissementId)->init(
                $this->etablissement_sanspreinscrits);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $etablissement = '';
            $elevesTransportes = '';
            $services = [];
            $effectifEtablissementsServices = null;
        }

        return new ViewModel(
            [
                'etablissement' => $etablissement,
                'elevesTransportes' => $elevesTransportes,
                'services' => $services,
                'effectifEtablissementsServices' => $effectifEtablissementsServices
            ]);
    }

    /**
     * Entrée des transporteurs
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function trIndexAction()
    {
        $auth = $this->authenticate->by('email');
        if (! $auth->hasIdentity()) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
        $userId = $auth->getUserId();
        try {
            $transporteurId = $this->db_manager->get('Sbm\Db\Table\UsersTransporteurs')->getTransporteurId(
                $userId);
            $transporteur = $this->db_manager->get('Sbm\Db\Table\Transporteurs')->getRecord(
                $transporteurId)->nom;
            $services = $this->db_manager->get('Sbm\Db\Table\Services')->fetchAll(
                [
                    'transporteurId' => $transporteurId
                ]);
            $effectifTransporteurs = $this->db_manager->get(
                'Sbm\Db\Eleve\EffectifTransporteurs');
            $effectifTransporteurs->init($this->transporteur_sanspreinscrits);
            $elevesATransporter = $effectifTransporteurs->transportes($transporteurId);
            $effectifTransporteursServices = $this->db_manager->get(
                'Sbm\Db\Eleve\EffectifTransporteursServices');
            $effectifTransporteursServices->setCaractereConditionnel($transporteurId)->init(
                $this->transporteur_sanspreinscrits);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $transporteur = '';
            $transporteurId = null;
            $elevesATransporter = '';
            $services = [];
            $effectifTransporteursServices = null;
        }
        return new ViewModel(
            [
                'transporteur' => $transporteur,
                'elevesATransporter' => $elevesATransporter,
                'services' => $services,
                'effectifTransporteursServices' => $effectifTransporteursServices
            ]);
    }

    public function trElevesAction()
    {
        $auth = $this->authenticate->by('email');
        if (! $auth->hasIdentity() || $auth->getCategorieId() < 100 ||
            $auth->getCategorieId() >= 130) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
        $userId = $auth->getUserId();
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $this->sbm_isPost = false;
            $args = Session::get('post', [], $this->getSessionNamespace('tr-eleves'));
        } else {
            $args = $prg;
            $retour = StdLib::getParam('op', $args, '') == 'retour';
            if ($retour) {
                // dans ce cas, il s'agit du retour d'une action de type suppr, ajout ou
                // edit. Comme pour un get, on récupère ce qui est en session.
                $this->sbm_isPost = false;
                $args = Session::get('post', [], $this->getSessionNamespace('tr-eleves'));
            } else {
                if (array_key_exists('cancel', $args)) {
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                        return $this->redirect()->toRoute('sbmportail',
                            [
                                'action' => 'index'
                            ]);
                    }
                } elseif (array_key_exists('origine', $args)) {
                    $this->redirectToOrigin()->setBack($args['origine']);
                    unset($args['origine']);
                }
                $this->sbm_isPost = true;
                Session::set('post', $args, $this->getSessionNamespace('tr-eleves'));
            }
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
        $categorie = $auth->getCategorieId();
        if ($categorie == 110) {
            $sanspreinscrits = $this->transporteur_sanspreinscrits;
        } else {
            $sanspreinscrits = $this->etablissement_sanspreinscrits;
        }
        $criteres_obj = new \SbmPortail\Model\Db\ObjectData\Criteres(
            $criteres_form->getElementNames(), $sanspreinscrits);
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
        switch ($categorie) {
            case 2:
                // Filtre les résultats pour n'afficher que ce qui concerne ce
                // transporteur
                try {
                    $right = $this->db_manager->get('Sbm\Db\Table\UsersTransporteurs')->getTransporteurId(
                        $userId);
                    $where = $criteres_obj->getWhere()
                        ->nest()
                        ->equalTo('ser1.transporteurId', $right)->or->equalTo(
                        'ser2.transporteurId', $right)->unnest();
                    $paginator = $this->db_manager->get(
                        'Sbm\Db\Query\AffectationsServicesStations')->paginatorScolaritesR(
                        $where, [
                            'nom',
                            'prenom'
                        ]);
                } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                    $this->flashMessenger()->addErrorMessage(
                        'Votre compte n\'est pas associé à un transporteur. Contactez le service des transports scolaires');
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                        return $this->redirect()->toRoute('sbmportail',
                            [
                                'action' => 'tr-index'
                            ]);
                    }
                }
                break;
            case 3:
                // Filtre les résultats pour n'afficher que ce qui concerne cet
                // établissement
                $right = $this->db_manager->get('Sbm\Db\Table\UsersEtablissements')->getEtablissementId(
                    $userId);
                $where = $criteres_obj->getWhere()->equalTo('sco.etablissementId', $right);
                $paginator = $this->db_manager->get(
                    'Sbm\Db\Query\AffectationsServicesStations')->paginatorScolaritesR(
                    $where, [
                        'nom',
                        'prenom'
                    ]);
                break;
            default:
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                    return $this->redirect()->toRoute('sbmportail',
                        [
                            'action' => 'index'
                        ]);
                }
                break;
        }
        return new ViewModel(
            [
                'categorie' => $categorie,
                'paginator' => $paginator,
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
                'criteres_form' => $criteres_form
            ]);
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf (le
     * listener SbmPdf\Listener\PdfListener lancera la création du pdf) Il n'y a pas de
     * vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function trPdfAction()
    {
        $auth = $this->authenticate->by('email');
        if (! $auth->hasIdentity()) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
        $userId = $auth->getUserId();

        $criteres_form = new \SbmPortail\Form\CriteresForm();
        $categorie = $auth->getCategorieId();
        if ($categorie == 110) {
            $sanspreinscrits = $this->transporteur_sanspreinscrits;
        } else {
            $sanspreinscrits = $this->etablissement_sanspreinscrits;
        }
        $criteres_obj = new \SbmPortail\Model\Db\ObjectData\Criteres(
            $criteres_form->getElementNames(), $sanspreinscrits);
        $criteres = Session::get('post', [], $this->getSessionNamespace('tr-eleves'));
        if (! empty($criteres)) {
            $criteres_obj->exchangeArray($criteres);
        }
        switch ($categorie) {
            case 2:
                $right = $this->db_manager->get('Sbm\Db\Table\UsersTransporteurs')->getTransporteurId(
                    $userId);
                $where = $criteres_obj->getWherePdf()
                    ->nest()
                    ->equalTo('transporteur1Id', $right)->or->equalTo('transporteur2Id',
                    $right)->unnest();
                $documentId = 'List élèves portail transporteur';
                break;
            case 3:
                $right = $this->db_manager->get('Sbm\Db\Table\UsersEtablissements')->getEtablissementId(
                    $userId);
                $where = $criteres_obj->getWherePdf()->equalTo('etablissementId', $right);
                $documentId = 'List élèves portail etab';
                break;
            default:
                return $this->redirect()->toRoute('login', [
                    'action' => 'home-page'
                ]);
        }
        $call_pdf = $this->RenderPdfService;
        if ($docaffectationId = $this->params('id', false)) {
            // $docaffectationId par get - $args['documentId'] contient le libellé du menu
            // dans docaffectations
            $call_pdf->setParam('docaffectationId', $docaffectationId);
        }
        $call_pdf->setParam('documentId', $documentId)
            ->setParam('where', $where)
            ->setEndOfScriptFunction(
            function () {
                $this->flashMessenger()
                    ->addSuccessMessage("Création d'un pdf.");
            })
            ->renderPdf();
    }

    public function trCircuitsAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false || ! array_key_exists('serviceId', $args)) {
                return $this->redirect()->toRoute('sbmportail', [
                    'action' => 'tr-index'
                ]);
            }
        } else {
            $args = $prg;
            if (array_key_exists('horaires', $args)) {
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        $serviceId = $args['serviceId'];
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))->equalTo('serviceId',
            $serviceId);
        $effectifCircuits = $this->db_manager->get('Sbm\Db\Eleve\EffectifCircuits');
        $effectifCircuits->init(true);
        return new ViewModel(
            [
                'service' => $this->db_manager->get('Sbm\Db\Table\Services')->getRecord(
                    $serviceId),
                'effectifCircuits' => $effectifCircuits,
                'paginator' => $this->db_manager->get('Sbm\Db\Vue\Circuits')->paginator(
                    $where, [
                        'm1'
                    ]),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_circuits', 10),
                'page' => $this->params('page', 1),
                'origine' => StdLib::getParam('origine', $args)
            ]);
    }

    public function trCircuitGroupAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false || ! array_key_exists('serviceId', $args) ||
                ! array_key_exists('circuitId', $args)) {
                return $this->redirect()->toRoute('sbmportail', [
                    'action' => 'tr-index'
                ]);
            }
        } else {
            $args = $prg;
            if (array_key_exists('eleves', $args)) {
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        $circuitId = $args['circuitId'];
        $circuit = $this->db_manager->get('Sbm\Db\Vue\Circuits')->getRecord($circuitId);
        return new ViewModel(
            [
                'data' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->queryGroup(
                    Session::get('millesime'),
                    FiltreEleve::byCircuit($circuit->serviceId, $circuit->stationId),
                    [
                        'nom',
                        'prenom'
                    ]),
                'circuit' => $circuit,
                'serviceId' => $args['serviceId'],
                'page' => $this->params('page', 1)
            ]);
    }

    public function trCarteEtablissementsAction()
    {
        $this->redirectToOrigin()->setBack('/portail/index');
        return $this->redirect()->toRoute('sbmcarte', [
            'action' => 'etablissements'
        ]);
    }

    public function trCarteStationsAction()
    {
        $this->redirectToOrigin()->setBack('/portail/index');
        return $this->redirect()->toRoute('sbmcarte', [
            'action' => 'stations'
        ]);
    }

    public function trExtractionTelephonesAction()
    {
        $auth = $this->authenticate->by('email');
        if (! $auth->hasIdentity()) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
        $userId = $auth->getUserId();
        $right = $this->db_manager->get('Sbm\Db\Table\UsersTransporteurs')->getTransporteurId(
            $userId);

        $criteres_form = new \SbmPortail\Form\CriteresForm();
        $categorie = $auth->getCategorieId();
        if ($categorie == 110) {
            $sanspreinscrits = $this->transporteur_sanspreinscrits;
        } else {
            $sanspreinscrits = $this->etablissement_sanspreinscrits;
        }
        $criteres_obj = new \SbmPortail\Model\Db\ObjectData\Criteres(
            $criteres_form->getElementNames(), $sanspreinscrits);
        $criteres = Session::get('post', [], $this->getSessionNamespace('tr-eleves'));
        if (! empty($criteres)) {
            $criteres_obj->exchangeArray($criteres);
        }
        $where = $criteres_obj->getWhere()
            ->nest()
            ->equalTo('ser1.transporteurId', $right)->or->equalTo('ser2.transporteurId',
            $right)->unnest();

        $resultset = $this->db_manager->get('Sbm\Db\Query\AffectationsServicesStations')->getTelephonesPortables(
            $where);
        $data = iterator_to_array($resultset);
        if (! empty($data)) {
            $fields = array_keys(
                is_array(current($data)) ? current($data) : current($data)->getArrayCopy());
            return $this->csvExport('telephones.csv', $fields, $data,
                function ($item) {
                    return is_array($item) ? $item : $item->getArrayCopy();
                });
        } else {
            $this->flashMessenger()->addInfoMessage(
                'Il n\'y a pas de données correspondant aux critères indiqués.');
            return $this->redirect()->toRoute('sbmportail',
                [
                    'action' => $auth->getCategorieId() < 200 ? 'tr-eleves' : 'org-eleves',
                    'page' => $this->params('page', 1)
                ]);
        }
    }

    public function trElevesDownloadAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $columns = [
            'Nom' => 'nom',
            'Prénom' => 'prenom',
            'R Identité' => 'responsable',
            'R Adresse ligne 1' => 'adresseL1',
            'R Adresse ligne 2' => 'adresseL2',
            'R Adresse ligne 3' => 'adresseL3',
            'R Commune' => 'laposte',
            'R Téléphone 1' => 'telephoneF',
            'R Téléphone 2' => 'telephoneP',
            'R Téléphone 3' => 'telephoneT',
            'Établissement' => 'etablissement',
            'Commune de l\'établissement' => 'lacommuneEtablissement',
            'Classe' => 'classe',
            'Service' => 'service1',
            'Station Montée' => 'station1',
            'Commune station montée' => 'communeStation1',
            'Station Descente' => 'station2',
            'Commune station descente' => 'communeStation2',
            'Correspondance' => 'ligne2Id'
        ];
        // index du tableau $columns correspondant à des n° de téléphones
        $aTelephoneIndexes = [];
        $idx = 0;
        foreach ($columns as $column_field) {
            if (substr($column_field, 0, 9) == 'telephone') {
                $aTelephoneIndexes[] = $idx;
            }
            $idx ++;
        }
        // contrôle de l'identité de l'utilisateur
        $auth = $this->authenticate->by('email');
        if (! $auth->hasIdentity()) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
        $userId = $auth->getUserId();
        // reprise des critères
        $criteres = Session::get('post', [], $this->getSessionNamespace('tr-eleves'));
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
        $categorie = $auth->getCategorieId();
        if ($categorie == 110) {
            $sanspreinscrits = $this->transporteur_sanspreinscrits;
        } else {
            $sanspreinscrits = $this->etablissement_sanspreinscrits;
        }
        $criteres_obj = new \SbmPortail\Model\Db\ObjectData\Criteres(
            $criteres_form->getElementNames(), $sanspreinscrits);

        $criteres_form->setData($criteres);
        if ($criteres_form->isValid()) {
            $criteres_obj->exchangeArray($criteres_form->getData());
        }
        // lancement de la requête selon la catégorie de l'utilisateur
        try {
            switch ($categorie) {
                case 2:
                    // Filtre les résultats pour n'afficher que ce qui concerne ce
                    // transporteur
                    $right = $this->db_manager->get('Sbm\Db\Table\UsersTransporteurs')->getTransporteurId(
                        $userId);
                    $where = $criteres_obj->getWhere()
                        ->nest()
                        ->equalTo('ser1.transporteurId', $right)->or->equalTo(
                        'ser2.transporteurId', $right)->unnest();
                    $result = $this->db_manager->get(
                        'Sbm\Db\Query\AffectationsServicesStations')->getScolaritesR(
                        $where, [
                            'nom',
                            'prenom'
                        ]);
                    break;
                case 3:
                    // Filtre les résultats pour n'afficher que ce qui concerne cet
                    // établissement
                    $right = $this->db_manager->get('Sbm\Db\Table\UsersEtablissements')->getEtablissementId(
                        $userId);
                    $where = $criteres_obj->getWhere()->equalTo('sco.etablissementId',
                        $right);
                    $result = $this->db_manager->get(
                        'Sbm\Db\Query\AffectationsServicesStations')->getScolaritesR(
                        $where, [
                            'nom',
                            'prenom'
                        ]);
                    break;
                default:
                    throw new \Exception('');
                    break;
            }
        } catch (\Exception $e) {
            die('Erreur dans ' . __METHOD__);
        }
        // et construction d'un tabeau des datas
        $data = [];
        foreach ($result as $eleve) {
            $aEleve = $eleve->getArrayCopy();
            $ligne = [];
            foreach ($columns as $value) {
                $ligne[] = $aEleve[$value];
            }
            $data[] = $ligne;
        }
        // exportation en formatant les n° de téléphones pour qu'ils soient encadrés par
        // le caractère d'enclosure
        $viewhelper = new \SbmCommun\Model\View\Helper\Telephone();
        return $this->csvExport('eleves.csv', array_keys($columns), $data,
            function ($item) use ($aTelephoneIndexes, $viewhelper) {
                foreach ($aTelephoneIndexes as $idx) {
                    $item[$idx] = $viewhelper($item[$idx]);
                }
                return $item;
            });
    }

    // ===========================================================================================================
    // méthodes du menu Bienvenue
    //
    public function modifCompteAction()
    {
        $retour = $this->url()->fromRoute('sbmportail');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', [
            'action' => 'modif-compte'
        ]);
    }

    public function localisationAction()
    {
        $this->flashMessenger()->addWarningMessage(
            'La localisation n\'est pas possible pour votre catégorie d\'utilisateurs.');
        return $this->redirect()->toRoute('sbmportail');
    }

    public function mdpChangeAction()
    {
        $retour = $this->url()->fromRoute('sbmportail');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', [
            'action' => 'mdp-change'
        ]);
    }

    public function emailChangeAction()
    {
        $retour = $this->url()->fromRoute('sbmportail');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', [
            'action' => 'email-change'
        ]);
    }

    public function messageAction()
    {
        $retour = $this->url()->fromRoute('sbmportail');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('SbmMail');
    }
}