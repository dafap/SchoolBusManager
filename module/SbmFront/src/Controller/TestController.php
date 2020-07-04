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
                    //$this->debugLog(
                    //    'EleveId:' . $row->eleveId . ' - ResponsaableId: ' .
                    //    $row->responsableId);
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
     * Méthode prête pour donner un numéro aux élèves. La renommer testAction pour qu'elle
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