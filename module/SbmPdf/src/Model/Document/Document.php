<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package
 * @filesource Document.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 oct. 2020
 * @version 2020-2.6.1
 */
namespace SbmPdf\Model\Document;

use SbmBase\Model\StdLib;
use SbmPdf\Model\Tcpdf;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class Document
{

    protected $config;

    protected $data;

    /**
     * Tableau associatif qui présente des données avec les clés suivantes :
     *
     * @formatter off
     * <ul>
     * <li>documentId (obligatoire)</li>
     * <li>where (obligatoire)</li>
     * <li>data (optionnel)</li>
     * <li>docaffectationId (optionnel)</li>
     * <li>pageheader_title (optionnel)</li>
     * <li>pageheader_string (optionnel)</li>
     * <li>criteres (optionnel)</li>
     * <li>strict (optionnel)</li>
     * <li>expression (optionnel)</li>
     * <li>caractereConditionnel (optionnel)</li>
     * <li>...</li></ul>
     *
     * @var array
     */
    protected $params;

    protected $pdf;

    protected $pdf_manager;

    /**
     * Identifiant du document dans la table système documents. A ne pas confondre avec la
     * valeur de la clé 'documentId' dans params. Voir getDocumentId() pour plus
     * d'informations.
     *
     * @var int
     */
    protected $documentId;

    protected $last_page;

    public function __construct(ServiceLocatorInterface $pdf_manager)
    {
        $this->pdf_manager = $pdf_manager;
        $this->pdf = new \TCPDF();
        $this->pdf->SetCreator('TCPDF');
        $this->pdf->SetAuthor();
        $this->last_page = 0;
        $this->documentId = 0;
        // set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        // set some language-dependent strings (optional)
        $fileLanguage = StdLib::concatPath(StdLib::findParentPath(__DIR__, 'lang'),
            PDF_LANG . '.php');
        if (@is_file($fileLanguage)) {
            $this->pdf->setLanguageArray(require $fileLanguage);
        }
    }

    /**
     * Permet de préciser la demande en passant l'identifiant du document demandé et
     * d'autres paramètres (voir la propriété $this->params pour une liste des clés).
     *
     * @param array $params
     * @return self
     */
    public function setParams(array $params): self
    {
        if (array_key_exists('data', $params)) {
            $this->setData($params['data']);
            unset($params['data']);
        }
        $this->params = $params;
        $this->configDocument();
        return $this;
    }

    /**
     * Renvoie la valeur du paramètre $key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed|array
     */
    protected function getParam(string $key, $default)
    {
        return StdLib::getParam($key, $this->params, $default);
    }

    /**
     * Le documentId s'obtient à partir des informations contenues dans params. Dans
     * params on trouvera une clé 'documentId' contenant soit un entier, le documentId,
     * soit une chaine de caractères, le nom du document ou le libellé dans le menu
     * d'impression. Dans ce dernier cas, la clé 'docaffectationId' est aussi renseignée.
     * Le paramètre 'documentId' est un scalaire ou un tableau à un élément. On le
     * transforme en scalaire.
     *
     * @return int : le documentId
     */
    protected function getDocumentId()
    {
        if (! $this->documentId) {
            // On s'assure que documentId est un scalaire
            $documentId = current((array) $this->getParam('documentId', 1));
            $docaffectationId = $this->getParam('docaffectationId', false);
            if ($docaffectationId) {
                $oDocaffectation = $this->pdf_manager->get('Sbm\DbManager')
                    ->get('Sbm\Db\System\DocAffectations')
                    ->getRecord($docaffectationId);
                // ici, $documentId doit contenir le libelle du menu
                if ($oDocaffectation->libelle != $documentId) {
                    throw new Exception('La demande est incorrecte.');
                }
                $this->documentId = $oDocaffectation->documentId;
            } elseif (! is_numeric($documentId)) {
                // ici, $documentId doit contenir le name du document
                $this->documentId = $this->pdf_manager->get('Sbm\DbManager')
                    ->get('Sbm\Db\System\Documents')
                    ->getDocumentId($documentId);
            } else {
                $this->documentId = $documentId;
            }
        }
        return $this->documentId;
    }

    private function configDocument()
    {
        $this->initSectionDocument();

    }

    /**
     * Initialise la section 'document' de $this->config à partir de l'enregistrement dans
     * la table documents.
     *
     * @throws Exception
     */
    private function initSectionDocument()
    {
        $table_documents = $this->pdf_manager->get('Sbm\DbManager')->get(
            'Sbm\Db\System\Documents');
        try {
            $this->config['document'] = $table_documents->getConfig(
                $this->getDocumentId());
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            if (getenv('APPLICATION_ENV') == 'development') {
                $msg = __METHOD__ . ' - ' . $e->getMessage();
            } else {
                $msg = "Impossible de définir le document.";
            }
            throw new Exception($msg, $e->getCode(), $e->getPrevious());
        }
    }

    private function initPdfDocument()
    {

    }

    /**
     * Lance la construction du document pdf
     */
    public function run()
    {
        // En-tête de document
        if ($this->getConfig('document', 'docheader', false)) {
            $this->sectionDocumentHeader();
        }

        // corps du document
        $this->sectionDocumentBody();

        // pied du document
        if ($this->getConfig('document', 'docfooter', false)) {
            $this->sectionDocumentFooter();
        }
        return $this->pdf->Output($this->getConfig('document', 'out_name', 'doc.pdf'),
            $this->getConfig('document', 'out_mode', 'I'));
    }

    /**
     * Renvoie le contenu de $this->config pour la section et la clé indiquées, ou default
     *
     * @param array|string $sections
     * @param string $key
     * @param mixed $default
     * @throws Exception
     * @return array
     */
    public function getConfig($sections, string $key, $default = null)
    {
        $index = (array) $sections;
        array_push($index, $key);
        return StdLib::getParamR($index, $this->config, $default);
    }

    public abstract function sectionDocumentHeader();

    public abstract function sectionDocumentBody();

    public abstract function sectionDocumentFooter();
}