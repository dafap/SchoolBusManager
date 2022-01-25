<?php
/**
 * Lance le création d'un document pdf
 *
 * @project sbm
 * @package SbmPdf/src/Mvc/Controller/Plugin
 * @filesource Pdf.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 nov. 2021
 * @version 2021-2.6.4
 */
namespace SbmPdf\Mvc\Controller\Plugin;

use SbmBase\Model\StdLib;
use SbmPdf\Mvc\Controller\Plugin\Exception\OutOfBoundsException;
use SbmPdf\Service\PdfManager;
use Zend\Http\Response;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Stdlib\Parameters;

class Pdf extends AbstractPlugin
{

    const PLUGINMANAGER_ID = 'documentPdf';

    const ERR_MSG = 'La demande est incorrecte';

    /**
     *
     * @var int
     */
    private $documentId;

    /**
     *
     * @var Parameters
     */
    private $params;

    /**
     *
     * @var PdfManager
     */
    private $pdf_manager;

    /**
     *
     * @var MvcEvent
     */
    private $event;

    /**
     *
     * @var Response
     */
    private $response;

    public function __construct(PdfManager $pdf_manager)
    {
        $this->pdf_manager = $pdf_manager;
    }

    /**
     * Renvoie le document spécifié par les paramètres indiqués.
     * Soit la classe du document est indiquée dans params, soit elle est obtenue à partir
     * de la fiche du document dans la table documents.
     *
     * @param \SbmPdf\Service\PdfManager $pdf_manager
     * @param array $params
     * @return \Zend\Http\Response
     */
    public function __invoke(array $params): Response
    {
        $this->params = new Parameters($params);
        try {
            $config = $this->pdf_manager->get('Sbm\DbManager')
                ->get('Sbm\Db\System\Documents')
                ->getConfig($this->getDocumentId());
            $classDocument = $this->params->get('classDocument',
                StdLib::getParam('classDocument', $config, false));
            if (! $classDocument) {
                throw new Exception\RuntimeException(
                    "Le modèle de document n'est pas indiqué.");
            } elseif (! $this->pdf_manager->has($classDocument)) {
                throw new Exception\RuntimeException(
                    "Ce modèle de document n'est pas programmé.");
            }
            $out_mode = $this->params->get('out_mode',
                StdLib::getParam('out_mode', $config, 'I'));
            $out_name = $this->params->get('out_name',
                StdLib::getParam('out_name', $config, 'resultat.pdf'));
            $response = $this->getResponse();
            if ($out_mode == 'I') {
                $response->getHeaders()->addHeaders($this->inlineHeaders($out_name));
            } else {
                $response->getHeaders()->addHeaders($this->attachmentHeaders($out_name));
            }
            $response->setContent(
                $this->pdf_manager->get($classDocument)
                    ->setPdfManager($this->pdf_manager)
                    ->setDocumentId($this->getDocumentId())
                    ->setConfig('document', $config)
                    ->setParams($this->params)
                    ->render());
            if (headers_sent() || ($complement = ob_get_contents())) {
                $msg = "Certaines données ont déjà été envoyées au navigateur.\n" .
                    "Il est donc impossible de transmettre le fichier PDF.";
                if ($complement) {
                    $msg .= "\n$complement";
                }
                throw new Exception\RuntimeException($msg);
            }
            return $response;
        } catch (\Exception $e) {
            if (getenv('APPLICATION_ENV') == 'development') {
                $msg = sprintf("%s\n%s\n%s", __METHOD__, $e->getMessage(),
                    $e->getTraceAsString());
            } else {
                $msg = "Impossible de définir le document.";
            }
            throw new Exception\RuntimeException($msg, $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * Si on a déjà recherché le documentId alors on le renvoie sans le recalculer
     * Sinon
     * params contient un 'documentId' qui est un scalaire (Id ou libellé) ou un tableau
     * params peut contenir un 'docaffectationId'.
     * Si c'est le cas, 'documentId' contient
     * le libellé du document dans le menu.
     * Il y a 4 cas :
     * - il existe un paramètre docaffectationId dans $params
     * - il existe un parametre documentId dans $params et ce n'est pas un nombre
     * - il existe un paramètre documentId dans $params et c'est un nombre entier
     * - sinon on lance une exception
     *
     * @throws \SbmPdf\Mvc\Controller\Plugin\Exception\OutOfBoundsException
     * @return int
     */
    private function getDocumentId(): int
    {
        if (! $this->documentId) {
            $docKey = current((array) $this->params->documentId);
            $docaffectationId = $this->params->docaffectationId;
            if ($docaffectationId) {
                $oDocaffectation = $this->pdf_manager->get('Sbm\DbManager')
                    ->get('Sbm\Db\System\DocAffectations')
                    ->getRecord($docaffectationId);
                // ici, $docKey doit contenir le libelle du menu
                if ($oDocaffectation->libelle != $docKey) {
                    throw new OutOfBoundsException(self::ERR_MSG);
                }
                $this->documentId = $oDocaffectation->documentId;
            } else {
                if (! is_numeric($docKey)) {
                    $this->documentId = $this->pdf_manager->get('Sbm\DbManager')
                        ->get('Sbm\Db\System\Documents')
                        ->getDocumentId($docKey);
                } elseif (ctype_digit($docKey)) {
                    $this->documentId = $docKey;
                } else {
                    throw new OutOfBoundsException(self::ERR_MSG);
                }
            }
        }
        return $this->documentId;
    }

    /**
     *
     * @param string $filename
     * @return string[]|string[][]
     */
    private function attachmentHeaders(string $filename)
    {
        $array = [
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => sprintf('attachment; filename="%s"',
                basename($filename)),
            'Tranfert-Encoding' => 'binary',
            'Cache-Control' => 'private, must-revalidate, post-check=0, pre-check=0, max-age=1',
            'Pragma' => 'public',
            'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
            'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT'
        ];
        // en cas de PHP en mode "non CGI"
        if (strpos(php_sapi_name(), 'cgi') === false) {
            $array['Content-Type'] = [
                'application/force-download',
                'application/octet-stream',
                'application/download',
                'application/pdf'
            ];
        }
        return $array;
    }

    /**
     *
     * @param string $filename
     * @return string[]
     */
    private function inlineHeaders(string $filename)
    {
        return [
            'Content-type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="%s"', basename($filename)),
            'Tranfert-Encoding' => 'chunked',
            'Cache-Control' => 'private, must-revalidate, post-check=0, pre-check=0, max-age=1',
            'Pragma' => 'public',
            'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
            'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT'
        ];
    }

    /**
     * Get the response
     *
     * @return Response
     * @throws Exception\DomainException if unable to find response
     */
    private function getResponse()
    {
        if ($this->response) {
            return $this->response;
        }

        $event = $this->getEvent();
        $response = $event->getResponse();
        if (! $response instanceof Response) {
            throw new Exception\DomainException(
                'Le plugin Pdf ne peut pas obtenir la réponse.');
        }
        $this->response = $response;
        return $this->response;
    }

    /**
     * Get the event
     *
     * @return MvcEvent
     * @throws Exception\DomainException if unable to find event
     */
    private function getEvent()
    {
        if ($this->event) {
            return $this->event;
        }

        $controller = $this->getController();
        if (! $controller instanceof InjectApplicationEventInterface) {
            throw new Exception\DomainException(
                'Redirect plugin requires a controller that implements InjectApplicationEventInterface');
        }

        $event = $controller->getEvent();
        if (! $event instanceof MvcEvent) {
            $params = $event->getParams();
            $event = new MvcEvent();
            $event->setParams($params);
        }
        $this->event = $event;

        return $this->event;
    }
}