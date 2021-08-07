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
 * @date 7 août 2021
 * @version 2021-2.6.3
 */
namespace SbmPdf\Controller;

use SbmBase\Model\Session;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmPdf\Model\Tcpdf;
use Zend\Http\PhpEnvironment\Response;

/**
 *
 * @property \SbmCommun\Model\Db\Service\DbManager $db_manager
 * @property \SbmPdf\Service\PdfManager $pdf_manager
 * @property \SbmAuthentification\Authentication\AuthenticationServiceFactory $authenticate
 * @property \SbmFront\Model\Responsable\Service\ResponsableManager $responsable_manager
 * @property array $organisateur (C'est le client ailleurs)
 *
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 *
 */
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
        $facture = $this->db_manager->get('Sbm\Facture')
            ->setMillesime(Session::get('millesime'))
            ->setResponsableId($responsableId);
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