<?php
/**
 * Tous mes tests
 *
 * @project sbm
 * @package SbmFront/src/Controller
 * @filesource TestController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmFront\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use PhpOffice\PhpSpreadsheet\IOFactory;
use TCPDF;

class TestController extends AbstractActionController
{
    use \SbmCommun\Model\Traits\DebugTrait, \SbmCommun\Model\Traits\SqlStringTrait;

    /**
     *
     * @var int
     */
    private $millesime;

    /**
     *
     * @var \SbmAuthentification\Authentication\AuthenticationService
     */
    private $auth;

    private function initDebug()
    {
        $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/tmp'), 'debug-test.log');
        $this->millesime = Session::get('millesime');
        $this->auth = $this->authenticate->by('email');
    }

    public function indexAction()
    {
        $imagePath = StdLib::findParentPath(__DIR__, 'SbmPdf/images');
        $qrcodeNiveau = 'QRCODE,Q';
        $qrcodeMessage1 = 'https://www.tra-mobilite/plan-temps-reel/';
        $qrcodeMessage2 = 'ABOARSCO00018';
        $imagePassJunior = file_get_contents(
            StdLib::concatPath($imagePath, 'pass-provisoire-A4.svg'));
        $du = '14/12/2020';
        $au = '28/12/2020';
        $beneficiaire_nom = 'HAEYAERT';
        $beneficiaire_prenom = 'ELEA';
        // $chez = 'MASSON Juliette';
        $eleve_nom = "";
        $eleve_prenom = "";
        $eleve_numero = 75433; // stagiaire => supérieur à 99991
        $adresseL1 = '31 RUE AIMÉ ET EUGÉNIE COTTON';
        $adresseL2 = '';
        $codePostal = '73540';
        $commune = 'LA BATHIE';
        $etablissement = 'COLLÈGE PIERRE GRANGE - ALBERTVILLE';
        $origine = 'CIMETIÈRE (LA BATHIE)';
        $services_matin = '535';
        $services_midi = '535';
        $services_soir = '535';
        $responsable_titre = "Mme";
        $responsable_nom = "DELFORGE";
        $responsable_prenom = "LUCILE";
        if ($eleve_nom == '') {
            $imagePassJunior = str_replace('chez', '', $imagePassJunior);
        }
        $imagePathJunior = sprintf('@' . $imagePassJunior, $responsable_titre,
            $responsable_nom, $responsable_prenom, $adresseL1, $adresseL2, $codePostal,
            $commune, $du, $au, $eleve_numero, $beneficiaire_nom, $beneficiaire_prenom,
            $eleve_nom, $eleve_prenom, $etablissement, $origine, $services_matin,
            $services_midi, $services_soir);
        $qrcodeStyle = [
            'border' => 0,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => [
                0,
                0,
                0
            ],
            'bgcolor' => false, // array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        ];

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8',
            false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Alain POMIROL, DAFAP Informatique');
        $pdf->SetTitle('PASS TEMPORAIRE JUNIOR');
        $pdf->SetSubject('School Bus Manager');
        $pdf->SetKeywords('TCPDF, PDF, PASS, ARLYSERE, School Bus Manager');

        /*
         * $pdf->setHeaderFont(Array( PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN ));
         * $pdf->setFooterFont(Array( PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA ));
         */
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        // $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(0, 0, 0, true);
        $pdf->SetAutoPageBreak(TRUE, 0);
        // $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        // $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->AddPage();
        $pdf->ImageSVG($imagePathJunior, 0, 0, 210, 297);
        $pdf->write2DBarcode($qrcodeMessage1, $qrcodeNiveau, 181.3, 5.2, 24, 24,
            $qrcodeStyle, 'N');
        $pdf->write2DBarcode($qrcodeMessage2, $qrcodeNiveau, 161.3, 217.2, 24, 24,
            $qrcodeStyle, 'N');
        $pdf->AddPage();
        $pdf->ImageSVG(StdLib::concatPath($imagePath, 'pass-provisoire-verso-A4.svg'), 0,
            0, 210, 297);
        // $pdf->ImageSVG(StdLib::concatPath($imagePath, 'logo.svg'), 15, 20, '', 35);
        // $pdf->ImageSVG($imagePathJunior, 110, 20, '', 54);
        // $pdf->ImageSVG(StdLib::concatPath($imagePath, 'bas-de-page.svg'), 10, 266,
        // 190);
        // $pdf->write2DBarcode($qrcodeMessage, $qrcodeNiveau, 165.8, 21.8, 25, 25,
        // $qrcodeStyle, 'N');
        // =============
        // $pdf->setY(75);
        // $pdf->SetFont('helvetica', '', 16);
        // $pdf->Write(0, $qrcodeNiveau);
        // =============
        /*
         * $y = 95; $pdf->setY($y); $pdf->SetFont('helvetica', 'B', 24); $pdf->Write(0,
         * sprintf("PASS TEMPORAIRE JUNIOR\nValable du %s au %s", $du, $au), '', false,
         * 'C'); $y +=35; $pdf->setY($y); $pdf->SetFont('helvetica', '', 16);
         * $pdf->Write(0, 'Bénéficiaire'); $y += 10; $pdf->setY($y); $pdf->Write(0, 'Nom
         * :'); $pdf->setX(50); $pdf->SetFont('helvetica', 'B', 16); $pdf->Write(0,
         * $beneficiaire_nom); $y +=6; $pdf->setY($y); $pdf->SetFont('helvetica', '', 16);
         * $pdf->Write(0, 'Prénom :'); $pdf->setX(50); $pdf->SetFont('helvetica', 'B',
         * 16); $pdf->Write(0, $beneficiaire_prenom); $pdf->SetFont('helvetica', '', 12);
         * $y +=22; $pdf->setY($y); $pdf->Write(0, 'Hébergé chez :'); $pdf->setX(77);
         * $pdf->SetFont('helvetica', 'B', 12); $pdf->Write(0, $chez); $y +=6;
         * $pdf->setY($y); $pdf->SetFont('helvetica', '', 12); $pdf->Write(0, 'Adresse
         * :'); $pdf->SetFont('helvetica', 'B', 12); foreach (array_filter( [ $adresseL1,
         * $adresseL2, sprintf('%s %s', $codePostal, $commune) ]) as $value) {
         * $pdf->SetY($y); $pdf->setX(77); $pdf->Write(0, $value); $y += 6; } $y += 16;
         * $pdf->setY($y); $pdf->SetFont('helvetica', '', 13); $pdf->Write(0, 'Point de
         * montée :'); $pdf->setX(77); $pdf->SetFont('helvetica', 'B', 13); $pdf->Write(0,
         * $origine); $y += 6; $pdf->setY($y); $pdf->SetFont('helvetica', '', 13);
         * $pdf->Write(0, 'Établissement scolaire :'); $pdf->setX(77);
         * $pdf->SetFont('helvetica', 'B', 13); $pdf->Write(0, $etablissement); $y += 22;
         * $pdf->setY($y); $pdf->SetFont('helvetica', '', 13); $pdf->Write(0, 'Services de
         * transport :'); $y += 6; $pdf->setXY(40, $y); $pdf->SetFont('helvetica', '',
         * 13); $pdf->Write(0, 'Matin :'); $pdf->setX(77); $pdf->SetFont('helvetica', 'B',
         * 13); $pdf->Write(0, $services_matin); $y += 6; $pdf->setXY(40, $y);
         * $pdf->SetFont('helvetica', '', 13); $pdf->Write(0, 'Mercredi midi :');
         * $pdf->setX(77); $pdf->SetFont('helvetica', 'B', 13); $pdf->Write(0,
         * $services_midi); $y += 6; $pdf->setXY(40, $y); $pdf->SetFont('helvetica', '',
         * 13); $pdf->Write(0, 'Soir :'); $pdf->setX(77); $pdf->SetFont('helvetica', 'B',
         * 13); $pdf->Write(0, $services_soir);
         */
        return $pdf->Output('passTemporaireJunior.pdf', 'D');
        die();

        $this->initDebug();
        $error_msg[] = $this->auth->getIdentity();
        $error_msg[] = $this->auth->getCategorieId();
        $error_msg[] = $this->auth->getUserId();
        $error_msg[] = 'Terminé';
        // dump et print_r de l'objet 'obj'
        $viewmodel = new ViewModel([
            'obj' => $error_msg,
            'form' => null
        ]);
        $viewmodel->setTemplate('sbm-front/test/test.phtml');
        return $viewmodel;
    }

    /**
     * Permet de construire le tableau des acl à placer dans module.config.php dans la clé
     * 'actions' sans en oublier.
     * Il suffit ensuite d'ajouter les autorisations pour
     * chacune des actions
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function majAclPortailAction()
    {
        $this->initDebug();
        $translate = new \Zend\Filter\Word\CamelCaseToDash();
        $class_name = 'SbmPortail\Controller\IndexController';
        $f = new \ReflectionClass($class_name);
        $arrayActions = [];
        foreach ($f->getMethods() as $m) {
            if (strpos($m->name, 'Action') && $m->class == $class_name) {
                $action = strtolower(
                    $translate->filter(str_replace('Action', '', $m->name)));
                $arrayActions[] = sprintf("'%s'=>['allow'=>['roles'=>[]]]", $action);
            }
        }
        $error_msg = implode(",\n", $arrayActions);
        $viewmodel = new ViewModel([
            'obj' => $error_msg,
            'form' => null
        ]);
        $viewmodel->setTemplate('sbm-front/test/test.phtml');
        return $viewmodel;
    }

    public function phpOfficeAction()
    {
        $inputFileName = 'D:/dafap/Developpements Eclipse/arlysere/paiement en ligne/Export_transactions/Export_transactions_20200504-09450872566.xls';
        // $helper->log('Loading file ' . pathinfo($inputFileName, PATHINFO_BASENAME) . '
        // using IOFactory to identify the format');
        $spreadsheet = IOFactory::load($inputFileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        /**
         * Create a new Xls Reader *
         */
        // $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        // $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        // $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xml();
        // $reader = new \PhpOffice\PhpSpreadsheet\Reader\Ods();
        // $reader = new \PhpOffice\PhpSpreadsheet\Reader\Slk();
        // $reader = new \PhpOffice\PhpSpreadsheet\Reader\Gnumeric();
        // $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        /**
         * Load $inputFileName to a Spreadsheet Object *
         */
        // $spreadsheet = $reader->load($inputFileName);

        // $error_msg[] = 'Terminé';
        // dump de l'objet 'obj'
        $viewmodel = new ViewModel([
            'obj' => $sheetData,
            'form' => null
        ]);
        $viewmodel->setTemplate('sbm-front/test/test.phtml');
        return $viewmodel;
    }

    public function affectationsParEtablissementAction()
    {
        $this->initDebug();
        $etablissementId = '0730769P';
        $etablissement = 'Clg Beaufort';
        $this->debugLog("$etablissement : $etablissementId");
        $tscolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $taffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
        $compteur = 0;
        for ($r = 1; $r <= 2; $r ++) {
            $where = new Where();
            $where->literal(sprintf('demandeR%d = 1', $r))
                ->literal(sprintf('accordR%d = 1', $r))
                ->literal('millesime = 2020')
                ->equalTo('etablissementId', $etablissementId);
            $sco = $tscolarites->getTableGateway()->getTable();
            $stationId = sprintf('stationIdR%d', $r);
            $joursTransport = sprintf('joursTransportR%d', $r);
            $select = $tscolarites->getTableGateway()
                ->getSql()
                ->select()
                ->join([
                'ele' => $this->db_manager->getCanonicName('eleves')
            ], "$sco.eleveId=ele.eleveId",
                [
                    'responsableId' => sprintf('responsable%dId', $r)
                ])
                ->where($where);
            // ->limit(10)
            // ->offset(30);
            $resultset = $tscolarites->getTableGateway()->selectWith($select);
            foreach ($resultset as $row) {
                set_time_limit(90);
                if (0 == ++ $compteur % 10) {
                    $this->debugLog($compteur);
                }
                if ($row->responsableId) {
                    $taffectations->deleteResponsableId(2020, $row->eleveId,
                        $row->responsableId);
                    // $this->debugLog(
                    // 'EleveId:' . $row->eleveId . ' - ResponsaableId: ' .
                    // $row->responsableId);
                    $this->db_manager->get('Sbm\ChercheTrajet')
                        ->setEtablissementId($row->etablissementId)
                        ->setStationId($row->{$stationId})
                        ->setEleveId($row->eleveId)
                        ->setJours($row->{$joursTransport})
                        ->setTrajet($r)
                        ->setResponsableId($row->responsableId)
                        ->run();
                }
            }
        }

        $error_msg[] = 'Terminé';
        // dump et print_r de l'objet 'obj'
        $viewmodel = new ViewModel([
            'obj' => $error_msg,
            'form' => null
        ]);
        $viewmodel->setTemplate('sbm-front/test/test.phtml');
        return $viewmodel;
    }

    public function affectationsParOrigineAction()
    {
        $this->initDebug();
        $aCommunes = [
            "La Giettaz" => "73123",
            "Flumet" => "73114",
            "Notre-Dame-de-Bellecombe" => "73186",
            "Saint-Nicolas-la-Chapelle" => "73262",
            "Crest-Voland" => "73094",
            "Cohennoz" => "73088",
            "Aiton" => "73007",
            "Bonvillard" => "73048",
            "Sainte-Hélène-sur-Isère" => "73241",
            "Notre-Dame-des-Millières" => "73188",
            "Monthion" => "73170",
            "Grignon" => "73130",
            "Hauteluce" => "73132",
            "Villard-sur-Doron" => "73317",
            "Beaufort" => "73034",
            "Queige" => "73211",
            "Feissons-sur-Isère" => "73112",
            "Rognaix" => "73216",
            "Cevins" => "73063",
            "Saint-Paul-sur-Isère" => "73268",
            "Esserts-Blay" => "73110",
            "La Bâthie" => "73032",
            "Tours-en-Savoie" => "73298",
            "Grésy-sur-Isère" => "73129",
            "Montailleur" => "73162",
            "Saint-Vital" => "73283",
            "Frontenex" => "73121",
            "Tournon" => "73297",
            "Cléry" => "73086",
            "Verrens-Arvey" => "73312",
            "Plancherine" => "73202",
            "Mercury" => "73154",
            "Pallud" => "73196",
            "Allondaz" => "73014",
            "Thénésol" => "73292",
            "Césarches" => "73061",
            "Venthon" => "73308",
            "Marthod" => "73153",
            "Ugine" => "73303",
            "Gilly-sur-Isère" => "73124",
            "Albertville" => "73011",
            "La Léchère" => "73187",
            "Megève" => "74173"
        ];
        $tscolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $taffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
        $compteur = 0;
        foreach ([
            'Grignon' => '73130'
        ] as $key => $communeId) {
            $this->debugLog($key);
            for ($r = 1; $r <= 2; $r ++) {
                $where = new Where();
                $where->literal(sprintf('demandeR%d = 1', $r))
                    ->literal(sprintf('accordR%d = 1', $r))
                    ->literal('millesime = 2020')
                    ->equalTo('sta.communeId', $communeId);
                $sco = $tscolarites->getTableGateway()->getTable();
                $stationId = sprintf('stationIdR%d', $r);
                $joursTransport = sprintf('joursTransportR%d', $r);
                $select = $tscolarites->getTableGateway()
                    ->getSql()
                    ->select()
                    ->join([
                    'ele' => $this->db_manager->getCanonicName('eleves')
                ], "$sco.eleveId=ele.eleveId",
                    [
                        'responsableId' => sprintf('responsable%dId', $r)
                    ])
                    ->join([
                    'sta' => $this->db_manager->getCanonicName('stations')
                ], "$sco.$stationId = sta.stationId", [])
                    ->where($where);
                // ->limit(20)
                // ->offset(80)

                $resultset = $tscolarites->getTableGateway()->selectWith($select);
                foreach ($resultset as $row) {
                    set_time_limit(90);
                    if (0 == ++ $compteur % 10) {
                        $this->debugLog($compteur);
                    }
                    $taffectations->deleteResponsableId(2020, $row->eleveId,
                        $row->responsableId);
                    $this->db_manager->get('Sbm\ChercheTrajet')
                        ->setEtablissementId($row->etablissementId)
                        ->setStationId($row->{$stationId})
                        ->setEleveId($row->eleveId)
                        ->setJours($row->{$joursTransport})
                        ->setTrajet($r)
                        ->setResponsableId($row->responsableId)
                        ->run();
                }
            }
            // break;
        }

        $error_msg[] = 'Terminé';
        // dump et print_r de l'objet 'obj'
        $viewmodel = new ViewModel([
            'obj' => $error_msg,
            'form' => null
        ]);
        $viewmodel->setTemplate('sbm-front/test/test.phtml');
        return $viewmodel;
    }

    public function testAffectationAction()
    {
        $cr = [];
        $chercheTrajets = $this->db_manager->get('Sbm\ChercheTrajet');
        $televes = $this->db_manager->get('Sbm\Db\Table\Eleves');
        $tscolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $taffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
        $rowset = $tscolarites->fetchAll((new Where())->notEqualTo('stationIdR1', 0));
        foreach ($rowset as $row) {
            $responsableId = $televes->getRecord($row->eleveId)->responsable1Id;
            for ($moment = 1; $moment <= 3; $moment ++) {

                $chercheTrajets->setEtablissementId($row->etablissementId);
                $chercheTrajets->setStationId($row->stationIdR1);
                for ($i = 1, $trajetsPossibles = []; ! count($trajetsPossibles) && $i <= 4; $i ++) {
                    $trajetsPossibles = $chercheTrajets->getTrajets($moment, $i);
                }
                $i --;
                // DEBUG
                $cr[$row->eleveId][$moment]['nb_circuits'] = $i;
                $cr[$row->eleveId][$moment]['nb_trajets'] = count($trajetsPossibles);

                if (count($trajetsPossibles)) {
                    // @TODO: contrôle des places disponibles
                    $trajet = current($trajetsPossibles);
                    // DEBUG
                    $cr[$row->eleveId][$moment]['trajet'] = $trajet;
                    $oAffectation = $taffectations->getObjData();
                    $oAffectation->millesime = $row->millesime;
                    $oAffectation->eleveId = $row->eleveId;
                    $oAffectation->trajet = 1;
                    $oAffectation->moment = $moment;
                    $oAffectation->responsableId = $responsableId;
                    for ($j = 1; $j <= $i; $j ++) {
                        $oAffectation->jours = $trajet["semaine_$j"];
                        $oAffectation->correspondance = $j;
                        $oAffectation->station1Id = $trajet["station1Id_$j"];
                        $oAffectation->ligne1Id = $trajet["ligne1Id_$j"];
                        $oAffectation->sensligne1 = $trajet["sensligne1_$j"];
                        $oAffectation->ordreligne1 = $trajet["ordreligne1_$j"];
                        $oAffectation->station2Id = $trajet["station2Id_$j"];
                        // DEBUG
                        $cr[$row->eleveId][$moment]['affectation'][$j] = $oAffectation->getArrayCopy();
                        // @TODO: enregistre l'affectation
                        $taffectations->saveRecord($oAffectation);
                    }
                }
            }
        }
        $cr[] = 'Terminé';
        $viewmodel = new ViewModel([
            'obj' => $cr,
            'form' => null
        ]);
        $viewmodel->setTemplate('sbm-front/test/test.phtml');
        return $viewmodel;
    }

    public function insererDesUsersAction()
    {
        $adapter = $this->db_manager->getDbAdapter();
        $sql = new \Zend\Db\Sql\Sql($adapter);
        $select = $sql->select()
            ->from('communaux')
            ->columns([
            'nom' => 'correspondant',
            'email' => 'mail'
        ]);
        // die($sql->getSqlStringForSqlObject($select));
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $tUsers = $this->db_manager->get('Sbm\Db\Table\Users');
        $oUser = $tUsers->getObjData();
        // pour chaque enregistrement, préparer un objet zonage et l'enregistrer
        $error_msg = [];
        foreach ($rowset as $row) {
            $parts = explode(' ', $row['nom']);
            $oUser->exchangeArray(
                [
                    'nom' => $parts[1],
                    'email' => $row['email'],
                    'prenom' => $parts[0],
                    'titre' => 'Mme',
                    'categorieId' => 100
                ]);
            $oUser->completeToCreate();
            try {
                $tUsers->saveRecord($oUser);
            } catch (\Exception $e) {
                $error_msg[] = [
                    $row['nom'],
                    $e->getMessage()
                ];
            }
        }
        $error_msg[] = 'Terminé';
        $viewmodel = new ViewModel([
            'obj' => $error_msg,
            'form' => null
        ]);
        $viewmodel->setTemplate('sbm-front/test/test.phtml');
        return $viewmodel;
    }

    /**
     * Méthode prête pour donner un numéro aux élèves.
     * La renommer testAction pour qu'elle
     * fonctionne. Si elle est trop longue, rajouter des ->limit(xxx) au select. Au
     * préalable, vider la table sbm_t_eleves et ré-initialiser AUTO_INCREMENT par : ALTER
     * TABLE `sbm_t_responsables` AUTO_INCREMENT = 1;
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function numeroterLesElevesAction()
    {
        $adapter = $this->db_manager->getDbAdapter();
        $sql = new \Zend\Db\Sql\Sql($adapter);
        $select = $sql->select()
            ->from('eleves')
            ->columns(
            [
                'nom',
                'nomSA',
                'prenom',
                'prenomSA',
                'dateN',
                'sexe',
                'responsable1Id',
                'responsable2Id',
                'id_tra'
            ])
            ->where((new Where())->equalTo('responsable1Id', 2070))
            ->order('responsable1Id');
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $tEleves = $this->db_manager->get('Sbm\Db\Table\Eleves');
        $oEleve = $tEleves->getObjData();
        // pour chaque enregistrement, préparer un objet zonage et l'enregistrer
        $error_msg = [];
        foreach ($rowset as $row) {
            $oEleve->exchangeArray(
                [
                    'nom' => $row['nom'],
                    'nomSA' => $row['nomSA'],
                    'prenom' => $row['prenom'],
                    'prenomSA' => $row['prenomSA'],
                    'dateN' => $row['dateN'],
                    'sexe' => $row['sexe'],
                    'responsable1Id' => $row['responsable1Id'],
                    'responsable2Id' => $row['responsable2Id'],
                    'id_tra' => $row['id_tra']
                ]);
            try {
                $tEleves->saveRecord($oEleve);
            } catch (\Exception $e) {
                $error_msg[] = [
                    $row['nomSA'],
                    $e->getMessage()
                ];
            }
        }
        $error_msg[] = 'Terminé';
        $viewmodel = new ViewModel([
            'obj' => $error_msg,
            'form' => null
        ]);
        $viewmodel->setTemplate('sbm-front/test/test.phtml');
        return $viewmodel;
    }
}