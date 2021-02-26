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
 * @date 28 août 2020
 * @version 2020-2.6.0
 */
namespace SbmPdf\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
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

    public function testAction()
    {
        return $this->documentPdf($this->pdf_manager,
            [
                'docaffectationId' => 31,
                'documentId'=>'Test plugin documentPdf query',
                'classDocument'=>'tableSimple', // peut être omis si présent dans table 'documents'
                //'where'=> (new \Zend\Db\Sql\Where())->literal('niveau = 4'),
                'pageheader_title'=>'Exemple',
                'pageheader_string'=>'Bla bla bla',
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifClasse'
            ]);
    }

    /**
     * Cette méthode est un TEST A SUPPRIMER
     */
    public function tableAction()
    {
        $pdf = new Tcpdf($this->pdf_manager);
        $pdf->SetTitle('Mon fichier PDF');
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetAutoPageBreak(true, 9);
        $pdf->SetFont('dejavusans', '', 10);

        $pdf->AddPage(); // add a new page to the document
        $table = new \SbmPdf\Model\Element\Table($pdf);
        $table->setLineHeight(3);
        for ($i = 0; $i < 2; $i ++) {
            $table->newRow()
            ->newCell('Exemple Multicell')
            ->setBorder(1)
            ->newCell('John')
            ->setBorder(1)
            ->newCell('1956-04-14')
            ->setBorder(1)
            ->newCell('johnny@example.com')
            ->setBorder(1)
                ->endRow()
                ->newRow()
                ->newCell('Last Name')
                ->setText('Override Text')
                ->setFontWeight('bold')
                ->setAlign('L')
                ->setVerticalAlign('bottom')
                ->setBorder(1)
                ->setRowspan(2)
                ->setColspan(2)
                ->setFontSize(10)
                ->setMinHeight(10)
                ->setPadding(2, 4)
                ->setPadding(2, 4, 5, 6)
                ->setWidth(125)
                ->newCell('bar')
                ->setBorder(1)
                ->endRow()
                ->newRow()
                ->newCell('toto')
                ->setBorder(1)
                ->newCell('Marius')
                ->setBorder(1)
                ->endRow();
        }

        $table()->Output('fichier.pdf', 'I');
    }

    private function init($sessionNameSpace)
    {
    }

    public function lesFacturesAction()
    {
        $responsableId = $this->getResponsableIdFromSession('nsArgsFacture');
        // factureset est un objet Iterator
        $factureset = $this->db_manager->get('Sbm\FactureSet')->init($responsableId);
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

    public function passTemporaireAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            die('bizare !');
            return $this->homePage();
        } elseif (! $prg || ! ($inviteId = StdLib::getParam('inviteId', $prg, false))) {
            return $this->homePage();
        }
        $imagePath = StdLib::findParentPath(__DIR__, 'SbmPdf/images');
        $qrcodeNiveau = 'QRCODE,Q';
        $qrcodeMessage = 'ABOARSCO00018';
        $imagePassJunior = file_get_contents(
            StdLib::concatPath($imagePath, 'passTemporaireJunior.svg'));
    }

    /**
     * Appel à la page du site de l'organisateur
     *
     * @return \Zend\Http\Response
     */
    public function horairesAction()
    {
        return $this->redirect()->toUrl(
            'https://www.tra-mobilite.com/fiches-horaires-tra-mobilite/');
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