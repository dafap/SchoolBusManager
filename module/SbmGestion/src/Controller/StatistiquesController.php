<?php
/**
 * Statistiques de gestion
 *
 *
 * @project sbm
 * @package SbmGestion/Controller
 * @filesource StatistiquesController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Resolver;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class StatistiquesController extends AbstractActionController
{

    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        return new ViewModel([]);
    }

    public function parClasseAction()
    {
        return new ViewModel(
            [
                'millesime' => Session::get('millesime'),
                'statistiques' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->statistiquesParClasse()
            ]);
    }

    public function pdfClasseAction()
    {
        $this->pdf('par-classe', 'statistiquesParClasse');
    }

    public function parCommuneAction()
    {
        return new ViewModel(
            [
                'millesime' => Session::get('millesime'),
                'statistiques' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->statistiquesParCommune()
            ]);
    }

    public function pdfCommuneAction()
    {
        $this->pdf('par-commune', 'statistiquesParCommune');
    }

    public function parCircuitAction()
    {
        return new ViewModel(
            [
                'millesime' => Session::get('millesime'),
                'statistiques' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->statistiquesParCircuit()
            ]);
    }

    public function pdfCircuitAction()
    {
        $this->pdf('par-circuit', 'statistiquesParCircuit');
    }

    public function parEtablissementAction()
    {
        return new ViewModel(
            [
                'millesime' => Session::get('millesime'),
                'statistiques' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->statistiquesParEtablissement()
            ]);
    }

    public function pdfEtablissementAction()
    {
        $this->pdf('par-etablissement', 'statistiquesParEtablissement');
    }

    public function parCircuitCommuneAction()
    {
        return new ViewModel(
            [
                'millesime' => Session::get('millesime'),
                'statistiques' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->statistiquesParCircuitCommune()
            ]);
    }

    public function pdfCircuitCommuneAction()
    {
        $this->pdf('par-circuit-commune', 'statistiquesParCircuitCommune');
    }

    public function parCommuneCircuitAction()
    {
        return new ViewModel(
            [
                'millesime' => Session::get('millesime'),
                'statistiques' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->statistiquesParCommuneCircuit()
            ]);
    }

    public function pdfCommuneCircuitAction()
    {
        $this->pdf('par-commune-circuit', 'statistiquesParCommuneCircuit');
    }

    public function parClasseEtablissementAction()
    {
        return new ViewModel(
            [
                'millesime' => Session::get('millesime'),
                'statistiques' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->statistiquesParClasseEtablissement()
            ]);
    }

    public function pdfClasseEtablissementAction()
    {
        $this->pdf('par-classe-etablissement', 'statistiquesParClasseEtablissement');
    }

    public function parEtablissementClasseAction()
    {
        return new ViewModel(
            [
                'millesime' => Session::get('millesime'),
                'statistiques' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->statistiquesParEtablissementClasse()
            ]);
    }

    public function pdfEtablissementClasseAction()
    {
        $this->pdf('par-etablissement-classe', 'statistiquesParEtablissementClasse');
    }

    private function pdf($action, $method)
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } else {
            $args = $prg ?: [];
            if (! array_key_exists('documentId', $args)) {
                $this->flashMessenger()->addErrorMessage(
                    'Le document à imprimer n\'a pas été indiqué.');
                return $this->redirect()->toRoute('sbmgestion/statistiques',
                    [
                        'action' => $action
                    ]);
            }
            $documentId = $args['documentId'];
        }
        $stack = new Resolver\TemplatePathStack(
            [
                'script_paths' => [
                    StdLib::findParentPath(__DIR__, 'view')
                ]
            ]);
        $resolver = new Resolver\AggregateResolver();
        $resolver->attach($stack);
        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver)
            ->plugin('basePath')
            ->setBasePath('/');
        ;
        $view = new ViewModel(
            [
                'millesime' => Session::get('millesime'),
                'statistiques' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->{$method}()
            ]);
        $view->setTemplate("sbm-gestion/statistiques/$action.phtml");
        // die($renderer->render($view));
        $call_pdf = $this->RenderPdfService;
        $call_pdf->setParam('documentId', $documentId)
            ->setParam('html', $renderer->render($view))
            ->setEndOfScriptFunction(
            function () {
                $this->flashMessenger()
                    ->addSuccessMessage("Création d'un pdf.");
            })
            ->renderPdf();
    }
}