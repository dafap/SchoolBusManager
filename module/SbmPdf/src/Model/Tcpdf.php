<?php
/**
 * Extension de la classe Tcpdf
 *
 * Les modèles d'en-tête de pages sont définis par les méthodes templateHeaderMethodx
 * où x est un entier à partir de 1
 * Le constructeur reçoit un PdfManager $pdf_manager et doit être suivi par l'appel de
 * la méthode setParams() qui installe le tableau de paramètres $params provenant de
 * l'évènement 'renderPdf'.
 *
 * @project sbm
 * @package SbmPdf/Model
 * @filesource Tcpdf.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmPdf\Model;

use SbmBase\Model\DateLib;
use SbmBase\Model\StdLib;
use SbmPdf\Model\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;

class Tcpdf extends \TCPDF
{
    use QuerySourceTrait;

    const HORS_SECTION = 0;

    const SECTION_DOCHEADER = 1;

    const SECTION_DOCBODY = 2;

    const SECTION_DOCFOOTER = 3;

    const SBM_DOCHEADER_DELTA_SMALL = 5;

    const SBM_DOCHEADER_DELTA_WIDE = 10;

    const SBM_DOCFOOTER_INSECABLE_DELTA_TITLE = 30;

    const SBM_DOCFOOTER_INSECABLE_DELTA_NBLIGNES = 3;

    const DEFAULT_SBM_DOCUMENT_TITLE = 'Liste';

    const DEFAULT_SBM_DOCHEADER_MARGIN = 20;

    const DEFAULT_SBM_DOCFOOTER_MARGIN = 20;

    /**
     * Tableau des paramètres passés par l'évènement
     * Les clés du tableau sont <ul>
     * <li>'documentId' de type integer</li>
     * <li>'where' de type Zend\Db\Sql\Where qui reprend la sélection obtenue par le formulaire de
     * critères</li></ul>
     *
     * @var array
     */
    protected $params = [];

    /**
     * Pdf Manager
     *
     * @var ServiceLocatorInterface
     */
    protected $pdf_manager;

    /**
     * Nom de l'enregistrement du recordSource dans le db_manager lorsqu'il s'agit d'une table ou
     * d'une vue.
     * Requête Sql sinon.
     *
     * @var string
     */
    private $recordSource;

    /**
     * Tableau des modèles de pages dont les clés sont (header, footer, page)
     *
     * @var array
     */
    private $templates = [
        'header' => null,
        'footer' => null,
        'page' => null
    ];

    /**
     * SECTION_DOCHEADER (en-tête de document) ; SECTION_DOCBODY (corps du document) ;
     * SECTION_DOCFOOTER (pied de document)
     *
     * @var int
     */
    private $section_document;

    /**
     * Numéro de la dernière page de l'en-tête de document
     *
     * @var int
     */
    private $last_page_docheader;

    /**
     * Tableau de configuration du document
     * Les clés sont <ul>
     * <li>'document' de type SbmCommun\Model\Db\ObjectDaya\Sys\Document</li>
     * <li>'docfields' array de SbmCommun\Model\Db\ObjectData\Sys\DocField</li>
     * <li>'doctable' de type SbmCommun\Model\Db\ObjectData\Sys\DocTable</li> (optionnel)
     * <li>'doccells' array de SbmCommun\Model\Db\ObjectData\Sys\DocCells</li> (optionnel)
     *
     * @var array
     */
    private $config = [];

    private $sbm_columns = [];

    /**
     * Buffer contenant les données à placer dans le document, initialisé par la méthode getData()
     * si nécessaire
     *
     * @var array
     */
    private $data = [];

    /**
     *
     * @param ServiceLocatorInterface $pdf_manager
     * @param array $params
     *            Tableau de paramètres passés par l'évènement renderPdf
     */
    public function __construct(ServiceLocatorInterface $pdf_manager)
    {
        $this->pdf_manager = $pdf_manager;

        $this->section_document = self::HORS_SECTION;
        $this->last_page_docheader = 0;
        parent::__construct(null, PDF_UNIT);
    }

    /**
     * Renvoie la version courante de TCPDF
     *
     * @return string
     */
    public static function getVersion()
    {
        return \TCPDF_STATIC::getTCPDFVersion();
    }

    /**
     * Initialise les paramètres
     *
     * @param array $params
     *            paramètres envoyés dans l'évènement 'renderPdf'
     *
     * @return \SbmPdf\Model\Tcpdf
     */
    public function setParams($params = [])
    {
        global $l;

        if (array_key_exists('data', $params)) {
            $this->setData($params['data']);
            unset($params['data']);
        }
        $this->params = $params;

        // document pdf
        $this->initConfigDocument();
        $this->setPageFormat($this->getConfig('document', 'page_format', PDF_PAGE_FORMAT),
            $this->getConfig('document', 'page_orientation', PDF_PAGE_ORIENTATION));
        $this->SetCreator($this->getConfig('document', 'creator', PDF_CREATOR));
        $this->SetAuthor($this->getConfig('document', 'author', PDF_AUTHOR));
        $this->SetTitle(
            $this->getConfig('document', 'title', self::DEFAULT_SBM_DOCUMENT_TITLE));
        $this->SetSubject($this->getConfig('document', 'subject', ''));
        $this->SetKeywords($this->getConfig('document', 'keywords', 'SBM, TS'));

        // en-tête de page
        $this->setPageHeader($this->getConfig('document', 'pageheader', false));

        // pied de page
        $this->setPageFooter($this->getConfig('document', 'pagefooter', false));

        // page
        $this->SetMargins(
            $this->getConfig('document', 'page_margin_left', PDF_MARGIN_LEFT),
            $this->getConfig('document', 'page_margin_top', PDF_MARGIN_TOP),
            $this->getConfig('document', 'page_margin_right', PDF_MARGIN_RIGHT));

        // set auto page breaks
        $this->SetAutoPageBreak(TRUE,
            $this->getConfig('document', 'page_margin_bottom', PDF_MARGIN_BOTTOM));

        // set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/' . PDF_LANG . '.php')) {
            require_once (dirname(__FILE__) . '/lang/' . PDF_LANG . '.php');
            $this->setLanguageArray($l);
        }

        // set default monospaced font
        $this->SetDefaultMonospacedFont(
            $this->getConfig('document', 'default_font_monospaced', PDF_FONT_MONOSPACED));

        // set font
        $this->SetFont(
            $this->getConfig('document', 'main_font_family', PDF_FONT_NAME_DATA),
            trim($this->getConfig('document', 'main_font_style', '')),
            $this->getConfig('document', 'main_font_size', PDF_FONT_SIZE_DATA));
        return $this;
    }

    /**
     * Configure la propriété $data de sorte que chaque ligne se comporte comme un tableau
     * associatif
     *
     * @param array|\Iterator $data
     */
    public function setData($data)
    {
        if ($data instanceof \Zend\Db\Adapter\Driver\Pdo\Result) {
            $data->setFetchMode(\PDO::FETCH_ASSOC);
            $this->data = $data;
        } elseif ($data instanceof \Iterator) {
            $this->data = iterator_to_array($data);
        } elseif (! is_array($data)) {
            throw new Exception('Mauvais type de données.');
        } else {
            $this->data = $data;
        }
        return $this;
    }

    /**
     * Renvoie le tableau des données ou un iterator se comportant comme tel
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Renvoie $this->params[$key] si la clé existe ou $default sinon
     *
     * @param string $key
     *            clé de la valeur recherchée
     * @param mixed $default
     *            valeur par défaut, renvoyée si la clé n'existe pas
     *
     * @return mixed valeur renvoyée
     */
    protected function getParam($key, $default)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        } else {
            return $default;
        }
    }

    /**
     * Renvoie $this->config[$section][$key] si les clés existent.
     *
     * @param string|array $sections
     *            document, doctable, docfields, doccells ou [doctable, thead | tbody | tfoot)
     * @param string $key
     *            clé de la valeur recherchée
     * @param mixed $default
     *            valeur par défaut renvoyée si la clé n'existe pas
     *
     * @throws Exception si la section n'est pas présente dans le tableau config ou cette section
     *         n'est pas un tableau
     *
     * @return mixed valeur renvoyée
     */
    public function getConfig($sections, $key, $default = null, $exception = false)
    {
        if (is_string($sections)) {
            $sections = [
                $sections
            ];
        }
        if (StdLib::array_keys_exists($sections, $this->config)) {
            $array = $this->config;
            reset($array);
            foreach ($sections as $k) {
                $array = $array[$k];
            }
            if (is_array($array)) {
                return array_key_exists($key, $array) ? $array[$key] : $default;
            }
        }
        if (! $exception) {
            return $default;
        }
        // on doit lancer une exception. Il faut savoir pourquoi.
        $config = 'config';
        $array = $this->config;
        foreach ($sections as $k) {
            if (! array_key_exists($k, $array)) {
                ob_start();
                var_dump($array);
                $dump_array = html_entity_decode(strip_tags(ob_get_clean()));
                $message = sprintf("La clé %s n'existe pas dans le tableau %s.\n%s", $k,
                    $config, $dump_array);
                break;
            } elseif (! is_array($array[$k])) {
                ob_start();
                var_dump($array);
                $dump_array = html_entity_decode(strip_tags(ob_get_clean()));
                $message = sprintf(
                    "La clé %s existe dans le tableau %s mais ne donne pas un tableau.\n%s",
                    $k, $config, $dump_array);
                break;
            } else {
                $array = $array[$k];
                $config .= '[' . $k . ']';
            }
        }
        if (getenv('APPLICATION_ENV') == 'development') {
            throw new Exception($message);
        } else {
            throw new Exception('Mauvaise configuration.');
        }
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
        return $this->Output($this->getConfig('document', 'out_name', 'doc.pdf'),
            $this->getConfig('document', 'out_mode', 'I'));
    }

    /**
     * Surcharge la méthode de tcpdf afin de placer les bon HTTP headers et arrêter le script après
     * un envoi inline ($dest = I ou FI)
     *
     * {@inheritdoc}
     * @see TCPDF::Output()
     */
    public function Output($name = 'doc.pdf', $dest = 'I')
    {
        // Output PDF to some destination
        // Finish document if necessary
        if ($this->state < 3) {
            $this->Close();
        }
        // Normalize parameters
        if (is_bool($dest)) {
            $dest = $dest ? 'D' : 'F';
        }
        $dest = strtoupper($dest);
        if ($dest[0] != 'F') {
            $name = preg_replace('/[\s]+/', '_', $name);
            $name = preg_replace('/[^a-zA-Z0-9_\.-]/', '', $name);
        }
        if ($this->sign) {
            // *** apply digital signature to the document ***
            // get the document content
            $pdfdoc = $this->getBuffer();
            // remove last newline
            $pdfdoc = substr($pdfdoc, 0, - 1);
            // remove filler space
            $byterange_string_len = strlen(\TCPDF_STATIC::$byterange_string);
            // define the ByteRange
            $byte_range = array();
            $byte_range[0] = 0;
            $byte_range[1] = strpos($pdfdoc, \TCPDF_STATIC::$byterange_string) +
                $byterange_string_len + 10;
            $byte_range[2] = $byte_range[1] + $this->signature_max_length + 2;
            $byte_range[3] = strlen($pdfdoc) - $byte_range[2];
            $pdfdoc = substr($pdfdoc, 0, $byte_range[1]) . substr($pdfdoc, $byte_range[2]);
            // replace the ByteRange
            $byterange = sprintf('/ByteRange[0 %u %u %u]', $byte_range[1], $byte_range[2],
                $byte_range[3]);
            $byterange .= str_repeat(' ', ($byterange_string_len - strlen($byterange)));
            $pdfdoc = str_replace(\TCPDF_STATIC::$byterange_string, $byterange, $pdfdoc);
            // write the document to a temporary folder
            $tempdoc = \TCPDF_STATIC::getObjFilename('doc', $this->file_id);
            $f = \TCPDF_STATIC::fopenLocal($tempdoc, 'wb');
            if (! $f) {
                $this->Error('Unable to create temporary file: ' . $tempdoc);
            }
            $pdfdoc_length = strlen($pdfdoc);
            fwrite($f, $pdfdoc, $pdfdoc_length);
            fclose($f);
            // get digital signature via openssl library
            $tempsign = \TCPDF_STATIC::getObjFilename('sig', $this->file_id);
            if (empty($this->signature_data['extracerts'])) {
                openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'],
                    array(
                        $this->signature_data['privkey'],
                        $this->signature_data['password']
                    ), array(), PKCS7_BINARY | PKCS7_DETACHED);
            } else {
                openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'],
                    array(
                        $this->signature_data['privkey'],
                        $this->signature_data['password']
                    ), array(), PKCS7_BINARY | PKCS7_DETACHED,
                    $this->signature_data['extracerts']);
            }
            // read signature
            $signature = file_get_contents($tempsign);
            // extract signature
            $signature = substr($signature, $pdfdoc_length);
            $signature = substr($signature, (strpos($signature, "%%EOF\n\n------") + 13));
            $tmparr = explode("\n\n", $signature);
            $signature = $tmparr[1];
            // decode signature
            $signature = base64_decode(trim($signature));
            // add TSA timestamp to signature
            $signature = $this->applyTSA($signature);
            // convert signature to hex
            $signature = current(unpack('H*', $signature));
            $signature = str_pad($signature, $this->signature_max_length, '0');
            // Add signature to the document
            $this->buffer = substr($pdfdoc, 0, $byte_range[1]) . '<' . $signature . '>' .
                substr($pdfdoc, $byte_range[1]);
            $this->bufferlen = strlen($this->buffer);
        }
        switch ($dest) {
            case 'I':
                {
                    // Send PDF to the standard output
                    $this->IsOutputEmpty();
                    if (php_sapi_name() != 'cli') {
                        $response = $this->prepareResponseInline($name);
                        $response->send();
                        die();
                    } else {
                        echo $this->getBuffer();
                    }
                    break;
                }
            case 'D':
                {
                    // download PDF as file
                    $this->IsOutputEmpty();
                    $response = $this->prepareResponseAttachment($name);
                    $response->send();
                    die();
                    break;
                }
            case 'F':
            case 'FI':
            case 'FD':
                {
                    // save PDF to a local file
                    $f = \TCPDF_STATIC::fopenLocal($name, 'wb');
                    if (! $f) {
                        $this->Error('Unable to create output file: ' . $name);
                    }
                    fwrite($f, $this->getBuffer(), $this->bufferlen);
                    fclose($f);
                    $this->IsOutputEmpty();
                    if ($dest == 'FI') {
                        $response = $this->prepareResponseInline($name);
                    } elseif ($dest == 'FD') {
                        $response = $this->prepareResponseAttachment($name);
                    }
                    $response->send();
                    die();
                    break;
                }
            case 'E':
                {
                    // return PDF as base64 mime multi-part email attachment (RFC 2045)
                    $retval = 'Content-Type: application/pdf;' . "\r\n";
                    $retval .= ' name="' . $name . '"' . "\r\n";
                    $retval .= 'Content-Transfer-Encoding: base64' . "\r\n";
                    $retval .= 'Content-Disposition: attachment;' . "\r\n";
                    $retval .= ' filename="' . $name . '"' . "\r\n\r\n";
                    $retval .= chunk_split(base64_encode($this->getBuffer()), 76, "\r\n");
                    return $retval;
                }
            case 'S':
                {
                    // returns PDF as a string
                    return $this->getBuffer();
                }
            default:
                {
                    $this->Error('Incorrect output destination: ' . $dest);
                }
        }
        return '';
    }

    private function IsOutputEmpty()
    {
        if (headers_sent() || ob_get_contents()) {
            $this->Error(
                'Certaines données ont déjà été envoyées au navigateur, impossible d’envoyer un fichier PDF');
        }
    }

    private function prepareResponseInline(string $name): Response
    {
        $response = new Response();
        // utilisation du header 'Transfert-Encoding' à la place de 'Content-Length' (HTTP/1.1)
        $response->getHeaders()
            ->addHeaderLine('Content-type', 'application/pdf')
            ->addHeaderLine('Content-Disposition', "inline; filename=\"$name\"")
            ->addHeaderLine('Tranfert-Encoding', 'chunked')
            ->addHeaderLine('Cache-Control',
            'private, must-revalidate, post-check=0, pre-check=0, max-age=1')
            ->addHeaderLine('Pragma', 'public')
            ->addHeaderLine('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
            ->addHeaderLine('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        $response->setContent($this->getPDFData());
        return $response;
    }

    private function prepareResponseAttachment(string $name): Response
    {
        $response = new Response();
        // utilisation du header 'Transfert-Encoding' à la place de 'Content-Length' (HTTP/1.1)
        $response->getHeaders()
            ->addHeaderLine('Content-Description', 'File Transfer')
            ->addHeaderLine('Cache-Control',
            'private, must-revalidate, post-check=0, pre-check=0, max-age=1')
            ->addHeaderLine('Pragma', 'public')
            ->addHeaderLine('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
            ->addHeaderLine('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        if (strpos(php_sapi_name(), 'cgi') === false) {
            $response->getHeaders()
                ->addHeaderLine('Content-Type', 'application/force-download')
                ->addHeaderLine('Content-Type', 'application/octet-stream')
                ->addHeaderLine('Content-Type', 'application/download')
                ->addHeaderLine('Content-Type', 'application/pdf');
        } else {
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/pdf');
        }
        $response->getHeaders()
            ->addHeaderLine('Content-Disposition', 'filename="' . basename($name) . '"')
            ->addHeaderLine('Content-Transfer-Encoding', 'binary');
        $response->setContent($this->getPDFData());
        return $response;
    }

    /**
     * En-tête de document
     */
    protected function sectionDocumentHeader()
    {
        $this->section_document = self::SECTION_DOCHEADER;
        $this->setPrintHeader($this->getConfig('document', 'docheader_pageheader', false));
        $this->setPrintFooter($this->getConfig('document', 'docheader_pagefooter', false));
        $this->AddPage();
        $templateDocHeaderMethod = 'templateDocHeaderMethod' .
            $this->getConfig('document', 'docheader_templateId', 1);
        if (method_exists($this, $templateDocHeaderMethod)) {
            $this->{$templateDocHeaderMethod}();
        } else {
            $this->templateDocHeaderMethod1(); // méthode par défaut
        }
        $this->last_page_docheader = $this->PageNo();
    }

    /**
     * Corps du document
     */
    protected function sectionDocumentBody()
    {
        $this->section_document = self::SECTION_DOCBODY;
        $current_font_family = $this->getFontFamily();
        $current_font_style = $this->getFontStyle();
        $current_font_size = $this->getFontSizePt();
        $current_text_color = $this->TextColor;
        $current_fill_color = $this->FillColor;
        $current_draw_color = $this->DrawColor;
        $current_color_flag = $this->ColorFlag;
        $current_fgcolor = $this->fgcolor;
        $current_bgcolor = $this->bgcolor;
        // gestion du header, du footer et du AddPage
        if ($this->getConfig('document', 'docheader', false)) {
            // il y avait une page d'en-tête
            if ($this->getConfig('document', 'pageheader', false) !=
                $this->getConfig('document', 'docheader_pageheader', false)) {
                // configuration différente de l'en-tête de page : on la chage
                $this->resetHeaderTemplate();
                $this->setPrintHeader($this->getConfig('document', 'pageheader', false));
            }
            if ($this->getConfig('document', 'docheader_page_distincte', false)) {
                // page distincte
                $this->AddPage();
            }
        } else {
            $this->setPrintHeader($this->getConfig('document', 'pageheader', false));
            $this->setPrintFooter($this->getConfig('document', 'pagefooter', false));
            $this->AddPage();
        }
        $templateDocBodyMethod = 'templateDocBodyMethod' .
            $this->getConfig('document', 'page_templateId', 1);
        if (method_exists($this, $templateDocBodyMethod)) {
            $this->{$templateDocBodyMethod}();
        } else {
            $this->templateDocBodyMethod1();
        }
        $this->SetFont($current_font_family, $current_font_style, $current_font_size);
        $this->DrawColor = $current_draw_color;
        $this->FillColor = $current_fill_color;
        $this->TextColor = $current_text_color;
        $this->ColorFlag = $current_color_flag;
        $this->bgcolor = $current_bgcolor;
        $this->fgcolor = $current_fgcolor;
    }

    /**
     * Si le pied de document n'est pas dans une page distincte, l'en-tête de page reste celui de
     * la section docbody (car la page est déjà commencée)
     * sinon, on configure l'en-tête de page de cette section
     *
     * Pour le pied de page, on configure toujours celui qui est prévu dans la section docfooter
     */
    protected function sectionDocumentFooter()
    {
        $this->section_document = self::SECTION_DOCFOOTER;
        // page distincte pour le pied de document
        if ($this->getConfig('document', 'docfooter_page_distincte', false)) {
            if ($this->getConfig('document', 'pageheader', false) !=
                $this->getConfig('document', 'docfooter_pageheader', false)) {
                $this->resetHeaderTemplate();
                $this->setPrintHeader(
                    $this->getConfig('document', 'docfooter_pageheader', false));
            }
            $this->AddPage();
        } else {
            $this->SetY($this->GetY(), true);
        }
        // dans tous les cas, on place le pied de page correctement
        $this->setPrintFooter($this->getConfig('document', 'docfooter_pagefooter', false));

        $templateDocFooterMethod = 'templateDocFooterMethod' .
            $this->getConfig('document', 'docfooter_templateId', 1);
        if (method_exists($this, $templateDocFooterMethod)) {
            $this->{$templateDocFooterMethod}();
        } else {
            $this->templateDocFooterMethod1(); // méthode par défaut
        }
    }

    /**
     * En-tête de page
     *
     * @param bool $has_pageheader
     */
    protected function setPageHeader($has_pageheader)
    {
        $oCalculs = new Calculs($this->data);
        $pageheader_title = $this->getParam('pageheader_title',
            $this->getConfig('document', 'pageheader_title', 'Etat'));
        $title = $oCalculs->getResultat($pageheader_title);
        $pageheader_string = $this->getParam('pageheader_string',
            $this->getConfig('document', 'pageheader_string',
                'éditée par School Bus Manager'));
        $subtitle = $oCalculs->getResultat($pageheader_string);
        $this->setPrintHeader($has_pageheader);
        $this->SetHeaderData(
            $this->getConfig('document', 'pageheader_logo', PDF_HEADER_LOGO),
            $this->getConfig('document', 'pageheader_logo_width', PDF_HEADER_LOGO_WIDTH),
            $title, $subtitle,
            $this->convertColor(
                $this->getConfig('document', 'pageheader_text_color', '000000')),
            $this->convertColor(
                $this->getConfig('document', 'pageheader_line_color', '000000')));
        $this->setHeaderFont(
            [
                $this->getConfig('document', 'pageheader_font_family', PDF_FONT_NAME_MAIN),
                trim($this->getConfig('document', 'pageheader_font_style', '')),
                $this->getConfig('document', 'pageheader_font_size', PDF_FONT_SIZE_MAIN)
            ]);
        $this->SetHeaderMargin(
            $this->getConfig('document', 'pageheader_margin', PDF_MARGIN_HEADER));
    }

    /**
     * Pied de page
     *
     * @param bool $has_pagefooter
     */
    protected function setPageFooter($has_pagefooter)
    {
        $this->setPrintFooter($has_pagefooter);
        $this->setFooterData(
            $this->convertColor(
                $this->getConfig('document', 'pagefooter_text_color', '000000')),
            $this->convertColor(
                $this->getConfig('document', 'pagefooter_line_color', '000000')));
        $this->setFooterFont(
            [
                $this->getConfig('document', 'pagefooter_font', PDF_FONT_NAME_DATA),
                trim($this->getConfig('document', 'pagefooter_font_style', '')),
                $this->getConfig('document', 'pagefooter_font_size', PDF_FONT_SIZE_DATA)
            ]);
        $this->setFooterMargin(
            $this->getConfig('document', 'pagefooter_margin', PDF_MARGIN_FOOTER));
    }

    /**
     * Renvoie le documentId, que l'on ait passé le documentId, le name ou le libellé du menu dans
     * l'appel de l'évènement.
     * Le paramètre 'documentId' est un scalaire ou un tableau à un élément. On le transforme en
     * scalaire.
     *
     * @return int
     */
    protected function getDocumentId()
    {
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
            return $oDocaffectation->documentId;
        }
        if (! is_numeric($documentId)) {
            // ici, $documentId doit contenir le name du document
            $table_documents = $this->pdf_manager->get('Sbm\DbManager')->get(
                'Sbm\Db\System\Documents');
            return $table_documents->getDocumentId($documentId);
        }
        return $documentId;
    }

    /**
     * Va chercher le where dans les paramètres de l'évènement (Where vide si ce paramètre n'y est
     * pas).
     * Rajoute le filtre indiqué dans le document comme un Literal
     *
     * @return \Zend\Db\Sql\Where
     */
    protected function getWhere()
    {
        $where = $this->getParam('where', new Where());
        $filter = $this->decodeSource((string) $this->getConfig('document', 'filter', ''),
            $this->pdf_manager->get('SbmAuthentification\Authentication')
                ->by()
                ->getUserId());
        if (! empty($filter)) {
            $where->literal($filter);
        }
        return $where;
    }

    /**
     * Renvoie une chaine ou un tableau
     *
     * @return string|array
     */
    protected function getOrderBy()
    {
        $orderBy = $this->getConfig('document', 'orderBy', null);
        $orderBy = empty($orderBy) ? $this->getParam('orderBy', null) : $orderBy;
        if (! empty($orderBy) && is_string($orderBy)) {
            // formater correctectement la chaine avec un espace après les virgules
            // $orderBy = str_replace(' ', ' ', str_replace(',', ', ', $orderBy));
            $parts = explode(',', $orderBy);
            if (count($parts) > 1) {
                $orderBy = [];
                foreach ($parts as $item) {
                    $orderBy[] = trim($item);
                }
            }
        }
        return $orderBy;
    }

    protected function getRecordSourceType()
    {
        if ($this->getParam('documentId', false) !== false) {
            return $this->getConfig('document', 'recordSourceType', 'T');
        } elseif ($this->getParam('recordSource', false) !== false) {
            return 'T';
        } else {
            throw new Exception(
                'La clé `recordSource` du document par défaut n\'est pas définie dans la propriété `params`. Revoir l\'appel de l\'event.');
        }
    }

    /**
     * Renvoie un AbstractSbmTable sur la table indiquée dans la clé recordSource du document
     * ou, si elle ne convient pas, dans la clé recordSource des paramètres reçus.
     *
     * @throws Exception si aucune des clés ne donne un recordSource valide pour le ServiceManager
     *         (vérifier éventellement les enregistrements des clés dans module.config.php)
     *
     * @return \SbmCommun\Model\Db\Service\Table\AbstractSbmTable
     */
    protected function getRecordSourceTable()
    {
        if ($this->pdf_manager->get('Sbm\DbManager')->has(
            $this->getConfig('document', 'recordSource', ''))) {
            $this->recordSource = $this->getConfig('document', 'recordSource', '');
            return $this->pdf_manager->get('Sbm\DbManager')->get($this->recordSource);
        } elseif ($this->pdf_manager->get('Sbm\DbManager')->has(
            $this->getParam('recordSource', ''))) {
            $this->recordSource = $this->getParam('recordSource', '');
            return $this->pdf_manager->get('Sbm\DbManager')->get($this->recordSource);
        } else {
            if (getenv('APPLICATION_ENV') == 'development') {
                $config_source = $this->getConfig('document', 'recordSource', 'absente');
                $param_source = $this->getParam('recordSource', 'absente');
                $msg = __METHOD__;
                $msg .= sprintf(
                    " - La clé `recordSource` est invalide ou n'est pas précisée pour ce document.\nClé dans la configuration du document : %s\nClé dans les paramètres d'appel: %s",
                    $config_source, $param_source);
            } else {
                $msg = 'Mauvaise définition de la source du document.';
            }
            throw new Exception($msg);
        }
    }

    /**
     * Lecture de la table système documents pour charger la fiche descriptive du document demandé
     */
    protected function initConfigDocument()
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

    /**
     *
     * @param string $nameStyle
     *            l'un des noms suivants : main, data, titre1, titre2, titre2, titre4
     * @param string $style
     *            combinaison des lettres BIUDO (B:gras, I:italic, U:souligné, D:barré, O:surligné)
     * @param string $taille
     *            en pt
     * @param array|string $color
     *            en RGB, la chaine en hexa avec ou sans le #, ou la chaine composée de 3 vakeurs
     *            séparées par virgule ou tableau de 3 cases
     */
    protected function setStyle($nameStyle, $style = null, $size = null, $color = null)
    {
        // $f = fopen('debug-style.txt', 'a');
        // fputs($f, $nameStyle . "\n");
        $key = $nameStyle . '_font_family';
        $family = $this->getConfig('document', $key, PDF_FONT_NAME_MAIN);
        if (is_null($style)) {
            $key = $nameStyle . '_font_style';
            $style = $this->getConfig('document', $key, '');
        }
        if (is_null($size)) {
            $key = $nameStyle . '_font_size';
            $size = $this->getConfig('document', $key, '');
        }
        // fputs($f, "$family - $style - $size\n");
        $this->SetFont($family, $style, $size);
        if (is_null($color)) {
            $key = $nameStyle . '_text_color';
            $color = $this->getConfig('document', $key, '000000');
        }
        // fputs($f, "$color\n");
        // fclose($f);
        $this->SetTextColorArray($this->convertColor($color));
    }

    /**
     * Surcharge de la méthode pour la gestion des sections du document (docheader, docbody,
     * docfooter)
     *
     * (non-PHPdoc)
     *
     * @see TCPDF::AddPage()
     */
    public function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false)
    {
        if ($this->inxobj) {
            // we are inside an XObject template
            return;
        }
        if (! isset($this->original_lMargin) or $keepmargins) {
            $this->original_lMargin = $this->lMargin;
        }
        if (! isset($this->original_rMargin) or $keepmargins) {
            $this->original_rMargin = $this->rMargin;
        }
        // terminate previous page
        $this->endPage();

        // détermine s'il faut changer de pied de page
        if ($this->last_page_docheader && $this->section_document == self::SECTION_DOCBODY &&
            $this->PageNo() == $this->last_page_docheader) {
            $this->last_page_docheader = 0;
            $this->setPrintFooter($this->config['document']['pagefooter']);
        }

        // met à jour les index previous
        if (array_key_exists('index', $this->data)) {
            foreach ($this->data['index'] as &$array) {
                $array['previous'] = $array['current'];
            }
            unset($array);
        }

        // start new page
        $this->startPage($orientation, $format, $tocpage);
    }

    /**
     * Surcharge de la méthode.
     * Pour définir un nouveau modèle d'en-tête, il suffit d'écrire une méthode
     * templateHeaderMethod2(), templateHeaderMethod3()...
     * en prenant modèle sur templateHeaderMethod1().
     *
     * (non-PHPdoc)
     *
     * @see TCPDF::Header()
     */
    public function Header()
    {
        if ($this->header_xobjid === false) {
            $templateHeaderMethod = 'templateHeaderMethod' .
                $this->getConfig('document', 'pageheader_templateId', 1);
            if (method_exists($this, $templateHeaderMethod)) {
                $this->{$templateHeaderMethod}();
            } else {
                $this->templateHeaderMethod1(); // méthode par défaut
            }
        }
        // print header template
        $x = 0;
        $dx = 0;
        if (! $this->header_xobj_autoreset and $this->booklet and (($this->page % 2) == 0)) {
            // adjust margins for booklet mode
            $dx = ($this->original_lMargin - $this->original_rMargin);
        }
        if ($this->rtl) {
            $x = $this->w + $dx;
        } else {
            $x = 0 + $dx;
        }
        $this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
        if ($this->header_xobj_autoreset) {
            // reset header xobject template at each page
            $this->header_xobjid = false;
        }
    }

    /**
     * Modèle d'en-tête de page par défaut
     */
    public function templateHeaderMethod1($param = null)
    {
        if (is_string($param) && $param == '?')
            return 'En-tête de page par défaut';

        // start a new XObject Template
        $this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
        $headerfont = $this->getHeaderFont();
        $headerdata = $this->getHeaderData();
        $this->y = $this->header_margin;
        if ($this->rtl) {
            $this->x = $this->w - $this->original_rMargin;
        } else {
            $this->x = $this->original_lMargin;
        }
        if ($this->getConfig('document', 'pageheader_logo_visible', true) &&
            $headerdata['logo'] &&
            ($headerdata['logo'] !=
            $this->getConfig('document', 'image_blank', K_BLANK_IMAGE))) {
            if (substr($headerdata['logo'], 0, 2) == '//') {
                $k_path_logo = $headerdata['logo']; // url absolue
            } else {
                $k_path_logo = rtrim(
                    $this->getConfig('document', 'url_path_images', K_PATH_IMAGES), '/') .
                    '/' . ltrim($headerdata['logo'], '/'); // url relative
            }
            $imgtype = \TCPDF_IMAGES::getImageFileType($k_path_logo);
            if (($imgtype == 'eps') or ($imgtype == 'ai')) {
                $this->ImageEps($k_path_logo, '', '', $headerdata['logo_width']);
            } elseif ($imgtype == 'svg') {
                $this->ImageSVG($k_path_logo, '', '', $headerdata['logo_width']);
            } else {
                // $file = rtrim(SBM_BASE_PATH, '/\\') . DIRECTORY_SEPARATOR . ltrim($k_path_logo,
                // '/\\');
                $file = StdLib::concatPath(SBM_BASE_PATH, $k_path_logo);
                $this->Image($file, '', '', $headerdata['logo_width']);
            }
            $imgy = $this->getImageRBY();
        } else {
            $imgy = $this->y;
        }
        $cell_height = $this->getCellHeight($headerfont[2] / $this->k);
        // set starting margin for text data cell
        if ($this->getRTL()) {
            $header_x = $this->original_rMargin + ($headerdata['logo_width'] * 1.1);
        } else {
            $header_x = $this->original_lMargin + ($headerdata['logo_width'] * 1.1);
        }
        $cw = $this->w - $this->original_lMargin - $this->original_rMargin -
            ($headerdata['logo_width'] * 1.1);
        $this->SetTextColorArray($this->header_text_color);
        // header title
        $this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
        $this->SetX($header_x);
        $this->Cell($cw, $cell_height, $headerdata['title'], 0, 1, '', 0, '', 0);
        // header string
        $this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
        $this->SetX($header_x);
        $this->MultiCell($cw, $cell_height, $headerdata['string'], 0, '', 0, 1, '', '',
            true, 0, false, true, 0, 'T', false);
        // print an ending header line
        $this->SetLineStyle(
            [
                'width' => 0.85 / $this->k,
                'cap' => 'butt',
                'join' => 'miter',
                'dash' => 0,
                'color' => $headerdata['line_color']
            ]);
        $this->SetY((2.835 / $this->k) + max($imgy, $this->y));
        if ($this->rtl) {
            $this->SetX($this->original_rMargin);
        } else {
            $this->SetX($this->original_lMargin);
        }
        $this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '',
            'T', 0, 'C');
        $this->endTemplate();
    }

    /**
     * Surcharge de la méthode
     * Cette méthode n'est appelée par la méthode setFooter() que si la propriété print_footer ==
     * true
     * Pour définir un nouveau modèle de pied de page, il suffit d'écrire une méthode
     * templateFooterMethod2(), templateFooterMethod3()...
     * en prenant modèle sur templateFooterMethod1().
     *
     * (non-PHPdoc)
     *
     * @see TCPDF::Footer()
     */
    public function Footer()
    {
        $templateFooterMethod = 'templateFooterMethod' .
            $this->getConfig('document', 'pagefooter_templateId', 1);
        if (method_exists($this, $templateFooterMethod)) {
            $this->{$templateFooterMethod}();
        } else {
            $this->templateFooterMethod1(); // méthode par défaut
        }
    }

    /**
     * Modèle de pied de page par défaut
     *
     * Il y aura toujours à droite le "numéro de page / nombre de pages" (exitant dans le modèle
     * par défaut de tcpdf)
     * On peut rajouter une chaine à gauche et une chaine au centre du pied de page.
     *
     * Le modèle de pied de page est se trouve dans $this->config['document']['pagefooter_string']
     * et s'obtient par $this->getConfig('document', 'pagefooter_string','')
     * Pour définir une chaine à gauche : @gauche{chaine}
     * Pour définir une chaine au centre : @centre{chaine}
     * Tout ce qui ne sera pas dans l'accolade de l'une de ces 2 structures sera ignoré.
     *
     * Les chaines peuvent contenir les variables suivantes :
     * %date% : date courante de création du document
     * %nombre% : nombre de lignes de données dans cette page
     * %somme(colonne)% où colonne est le rang de la colonne surlaquelle porte la somme (à partir
     * de 1)
     * %max(colonne)%
     * %min(colonne)%
     * %moyenne(colonne)%
     * Toute autre chaine restera inchangée.
     * Attention, les valeurs non numériques de la colonne sont considérées comme 0 pour les
     * fonctions somme, max, min et moyenne.
     *
     * @todo : Lorsqu'il y a plusieurs sources, les calculs se font sur chaque source et sont
     *       rendus s'ils ne sont pas nul
     *
     * @param string $param
     * @return string
     */
    public function templateFooterMethod1($param = null)
    {
        if (is_string($param) && $param == '?')
            return 'Pied de page par défaut';

        $cur_y = $this->y;
        $this->SetTextColorArray($this->footer_text_color);
        // set style for cell border
        $line_width = (0.85 / $this->k);
        $this->SetLineStyle(
            [
                'width' => $line_width,
                'cap' => 'butt',
                'join' => 'miter',
                'dash' => 0,
                'color' => $this->footer_line_color
            ]);
        // print document barcode
        $barcode = $this->getBarcode();
        if (! empty($barcode)) {
            $this->Ln($line_width);
            $barcode_width = round(
                ($this->w - $this->original_lMargin - $this->original_rMargin) / 3);
            $style = [
                'position' => $this->rtl ? 'R' : 'L',
                'align' => $this->rtl ? 'R' : 'L',
                'stretch' => false,
                'fitwidth' => true,
                'cellfitalign' => '',
                'border' => false,
                'padding' => 0,
                'fgcolor' => [
                    0,
                    0,
                    0
                ],
                'bgcolor' => false,
                'text' => false
            ];
            $this->write1DBarcode($barcode, 'C128', '', $cur_y + $line_width,
                $barcode_width, (($this->footer_margin / 3) - $line_width), 0.3, $style,
                '');
        }
        $w_page = isset($this->l['w_page']) ? $this->l['w_page'] . ' ' : '';
        if (empty($this->pagegroups)) {
            $pagenumtxt = $w_page . $this->getAliasNumPage() . ' / ' .
                $this->getAliasNbPages();
        } else {
            $pagenumtxt = $w_page . $this->getPageNumGroupAlias() . ' / ' .
                $this->getPageGroupAlias();
        }
        $this->SetY($cur_y);
        // Print page number
        if ($this->getRTL()) {
            $this->SetX($this->original_rMargin);
            $this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
        } else {
            $this->SetX($this->original_lMargin);
            $this->Cell(0, 0, $this->getAliasRightShift() . $pagenumtxt, 'T', 0, 'R');
        }
        // Print string
        $txt = $this->getConfig('document', 'pagefooter_string', '');
        if (! empty($txt) && ! empty($this->data[1])) {
            // remplacer les variables de la chaine
            $oCalculs = new Calculs($this->data[1]);
            $oCalculs->range($this->data['index'][1]['previous'],
                $this->data['index'][1]['current'] - 1);
            $txt = $oCalculs->getResultat($txt);

            // découpe en 2 parties
            $matches = null;
            preg_match("/@gauche{([^}]*)}/", $txt, $matches);
            $part_gauche = isset($matches[1]) ? $matches[1] : '';
            preg_match("/@centre{([^}]*)}/", $txt, $matches);
            $part_centre = isset($matches[1]) ? $matches[1] : '';

            // écrire le résultat
            $this->SetY($cur_y);
            if (! empty($part_gauche)) {
                if ($part_gauche == strip_tags($part_gauche)) {
                    $this->Write(0, $part_gauche, '', false, 'L');
                } else {
                    $this->writeHTML($part_gauche, true, false, true, false, 'L');
                }
            }
            if (! empty($part_centre)) {
                $this->SetX(0);
                if ($part_centre == strip_tags($part_centre)) {
                    $this->Write(0, $part_centre, '', false, 'C');
                } else {
                    $this->writeHTML($part_centre, true, false, true, false, 'C');
                }
            }
        }
    }

    /**
     * Conversion du codage de couleur.
     *
     * @param
     *            string|array
     *            Reçoit une chaine représentant le codage de la couleur au format html (codage
     *            hexa RGB)
     *            ou une chaine représentant le codage de la couleur au format rgb (chaine composée
     *            de 3 entiers de 0 à 255 séparés par des ,)
     *            ou un tableau de 3 entiers de 0 à 255 représentant le codage de la couleur au
     *            format rgb
     *
     * @return array string la couleur codée au format RGB sous la forme d'un tableau si l'entrée
     *         est au format HTML
     *         ou la couleur codée au format HTML sous la forme d'une chaine si l'entrée est au
     *         format RGB
     */
    private function convertColor($colorInput)
    {
        if (is_string($colorInput)) {
            $is_RGB = false;
            $array = explode(',', $colorInput);
            if (count($array) == 3) {
                $is_RGB = true;
                foreach ($array as $part) {
                    $is_RGB &= is_int($part) && ($part >= 0 && $part <= 255);
                }
            }
            if ($is_RGB)
                $colorInput = $array;
        }

        // convert hexadecimal to RGB
        if (is_string($colorInput)) {
            // si c'est le nom d'une couleur http
            if (array_key_exists($colorInput, \TCPDF_COLORS::$webcolor)) {
                $colorInput = \TCPDF_COLORS::$webcolor[$colorInput];
            }
            $colorInput = '#' . ltrim($colorInput, '#');
            if (preg_match("/^[#]([0-9a-fA-F]{6})$/", $colorInput)) {
                $hex_R = substr($colorInput, 1, 2);
                $hex_G = substr($colorInput, 3, 2);
                $hex_B = substr($colorInput, 5, 2);
                return [
                    hexdec($hex_R),
                    hexdec($hex_G),
                    hexdec($hex_B)
                ];
            } else {
                throw new Exception(
                    sprintf("%s (%s Ligne %d) %s n'est pas le codage HTML d'une couleur.",
                        __METHOD__, __FILE__, __LINE__, $colorInput));
            }
        } else {
            // convert RGB to hexadecimal
            if (! is_array($colorInput)) {
                ob_start();
                var_dump($colorInput);
                $dump = html_entity_decode(strip_tags(ob_get_clean()));
                throw new Exception(
                    sprintf(
                        "%s (%s Ligne %d) L'entrée n'est pas le codage RGB d'une couleur.\n%s",
                        __METHOD__, __FILE__, __LINE__, $dump));
            }

            foreach ($colorInput as $value) {
                $hex_value = dechex($value);
                if (strlen($hex_value) < 2) {
                    $hex_value = "0" . $hex_value;
                }
                $hex_RGB .= $hex_value;
            }

            return "#" . $hex_RGB;
        }
    }

    /**
     * Ecrit le texte donné en style Titre{n} où n est un entier de 1 à 4
     *
     * @param integer $n
     *            numéro de Titre
     * @param string $text
     *            texte à écrire
     * @param string $align
     *            'L' pour aligner à gauche, 'C' pour centrer, 'R' pour aligner à droite
     */
    protected function Titre($n, $text, $align)
    {
        $current_font_family = $this->getFontFamily();
        $current_font_style = $this->getFontStyle();
        $current_font_size = $this->getFontSizePt();
        $current_text_color = $this->TextColor;
        $current_draw_color = $this->DrawColor;

        $titre = "titre$n";
        // la bordure
        $t = $titre . '_line';
        if ($this->getConfig('document', $t, 0)) {
            $t .= '_color';
            $style = [
                'color' => $this->convertColor($this->getConfig('document', $t, 'black'))
            ];
            $this->SetLineStyle($style);
            $border = 1;
        } else {
            $border = 0;
        }
        // la police
        $t = $titre . '_font_';
        $this->SetFont($this->getConfig('document', $t . 'family', PDF_FONT_NAME_MAIN),
            trim($this->getConfig('document', $t . 'style', 'B')),
            $this->getConfig('document', $t . 'size', PDF_FONT_SIZE_MAIN));
        // la couleur
        $this->SetTextColorArray(
            $this->convertColor(
                $this->getConfig('document', $titre . '_text_color', 'black')));
        // le texte
        $this->Cell(0, 20, $text, $border, 1, $align);

        $this->setFont($current_font_family, $current_font_style, $current_font_size);
        $this->TextColor = $current_text_color;
        $this->DrawColor = $current_draw_color;
    }

    /**
     * Configuration graphique correspondant à cette section de table
     *
     * @param string $section
     *            'thead', 'tbody' ou 'tfoot'
     */
    protected function configGraphicSectionTable($section)
    {
        $this->SetFont(
            $this->getConfig('document', 'data_font_family', PDF_FONT_NAME_DATA),
            trim($this->getConfig([
                'doctable',
                $section
            ], 'font_style', '')),
            $this->getConfig('document', 'data_font_size', PDF_FONT_SIZE_DATA));
        $this->SetLineWidth($this->getConfig([
            'doctable',
            $section
        ], 'line_width', 0.2));
        $this->SetDrawColorArray(
            $this->convertColor(
                $this->getConfig([
                    'doctable',
                    $section
                ], 'draw_color', 'black')));
        $this->SetFillColorArray(
            $this->convertColor(
                $this->getConfig([
                    'doctable',
                    $section
                ], 'fill_color', 'white')));
        $this->SetTextColorArray(
            $this->convertColor(
                $this->getConfig([
                    'doctable',
                    $section
                ], 'text_color', 'black')));
    }

    // ============= Les en-tête de document ======================
    /**
     * Modèle d'en-tête de document par défaut
     */
    public function templateDocHeaderMethod1($param = null)
    {
        if (is_string($param) && $param == '?')
            return 'En-tête de document par défaut';

        if ($this->getConfig('document', 'docheader_page_distincte', true)) {
            // cas d'une page distincte
            $this->SetY(100, true, true);
            $this->Titre(1,
                $this->getConfig('document', 'title', self::DEFAULT_SBM_DOCUMENT_TITLE),
                'C');
            $this->SetY(140, true, true);
            $this->SetFont($this->getFontFamily(), 'I');
            $this->Write(0,
                trim($this->getConfig('document', 'docheader_subtitle', ''), "\n") . "\n",
                '', false, 'J');
        } else {
            // cas d'une section continue
            $y = $this->GetY() +
                $this->getConfig('document', 'docheader_margin',
                    self::DEFAULT_SBM_DOCHEADER_MARGIN);
            $this->SetY($y);
            $tmp = $this->getConfig('document', 'title', self::DEFAULT_SBM_DOCUMENT_TITLE);
            if (! empty($tmp)) {
                $this->Titre(1,
                    $this->getConfig('document', 'title', self::DEFAULT_SBM_DOCUMENT_TITLE),
                    'C');
                $delta = trim($this->getConfig('document', 'docheader_subtitle', ''), "\n") ==
                    '' ? self::SBM_DOCHEADER_DELTA_SMALL : self::SBM_DOCHEADER_DELTA_WIDE;
                $this->SetY($this->GetY() + $delta);
            }
            if (trim($this->getConfig('document', 'docheader_subtitle', ''), "\n") != '') {
                if ($this->getConfig('document', 'docheader_subtitle', '') ==
                    strip_tags($this->getConfig('document', 'docheader_subtitle', ''))) {
                    $font_style = $this->getFontStyle();
                    $this->SetFont($this->getFontFamily(), 'I');
                    $this->Write(0,
                        trim($this->getConfig('document', 'docheader_subtitle', ''), "\n") .
                        "\n", '', false, 'J');
                    $this->SetFont($this->getFontFamily(), $font_style);
                } else {
                    $this->writeHTML(
                        $this->getConfig('document', 'docheader_subtitle', ''), true,
                        false, true, false, '');
                }
            }
            /*
             * $this->SetLineStyle([
             * 'width' => 0.85 / $this->k,
             * 'cap' => 'butt',
             * 'join' => 'miter',
             * 'dash' => 0,
             * 'color' => $headerdata['line_color']
             * ));
             */
            $this->SetY((2.835 / $this->k) + $this->GetY());
        }
    }

    // ============= Les corps de document ======================
    /**
     * Modèle de corps de document par défaut.
     * Renvoie un identifiant du template si $param vaut '?'. Sinon, le paramètre est ignoré et le
     * template est exécuté.
     *
     * @param string $param
     *            s'il est renseigné il doit avoir la valeur '?' (sinon, il est ignoré)
     *
     * @return void|string Renvoie l'identifiant du template si $param == '?' sinon rien
     */
    public function templateDocBodyMethod1($param = null)
    {
        /**
         * Identifiant du template
         */
        if (is_string($param) && $param == '?') {
            return 'Document contenant un tableau de données';
        }

        /**
         * Initialisations et calculs
         */
        // lecture de la table 'doctables' pour obtenir $this->config['doctable'] = ['thead' =>
        // ..., 'tbody' => ..., 'tfoot' => ..., 'columns' => ...)
        $this->initConfigDoctable();

        // initialise les données
        $this->data['count'][1] = count($this->getDataForTable(1));
        $this->data['index'][1] = [
            'previous' => 0,
            'current' => 0
        ];
        $this->data['calculs'][1] = [];

        $this->sbm_columns = $this->getConfig('doctable', 'columns', []);
        // prend en compte le titre de la colonne pour la largeur à prévoir
        if ($this->getConfig([
            'doctable',
            'thead'
        ], 'visible', false)) {
            $this->configGraphicSectionTable('thead');
            foreach ($this->sbm_columns as &$column) {
                // vérifie si la largeur est suffisante pour écrire le titre
                $value_width = $this->GetStringWidth($column['thead'],
                    $this->getConfig('document', 'data_font_family', PDF_FONT_NAME_DATA),
                    trim($this->getConfig([
                        'doctable',
                        'thead'
                    ], 'font_style', '')),
                    $this->getConfig('document', 'data_font_size', PDF_FONT_SIZE_DATA));
                $value_width += $this->cell_padding['L'] + $this->cell_padding['R'];
                if ($value_width > $column['width']) {
                    $column['width'] = $value_width;
                }
                unset($column);
            }
        }

        // largeur de la zone d'écriture
        $pagedim = $this->getPageDimensions();
        $max_width = $pagedim['wk'] - $pagedim['lm'] - $pagedim['rm'];
        $sum_width = 0;
        foreach ($this->sbm_columns as $column) {
            $sum_width += $column['width'];
        }
        if (($table_width = $this->getConfig([
            'doctable',
            'tbody'
        ], 'width', 'auto')) == 'auto') {
            $ratio = $sum_width > $max_width ? $max_width / $sum_width : 1;
        } else {
            $ratio = $max_width * $table_width / 100 / $sum_width;
        }

        // largeur des colonnes
        foreach ($this->sbm_columns as &$column) {
            if ($ratio < 1) {
                $column['thead_stretch'] = 1;
                $column['tbody_stretch'] = 1;
            }
            $column['width'] *= $ratio;
            unset($column);
        }

        /**
         * Ecriture du document
         */
        // thead
        $this->templateDocBodyMethod1Thead();

        // tbody
        if ($this->getConfig([
            'doctable',
            'tbody'
        ], 'visible', false)) {
            $this->configGraphicSectionTable('tbody');
            $columns = $this->sbm_columns;
            $fill = 0;
            // index des sauts de page
            $idx_nl = $idx_page = [];
            for ($i = 0; $i < count($columns); $i ++) {
                if ($columns[$i]['nl']) {
                    $idx_nl[] = $i;
                }
                $idx_page[$i] = null;
                $this->data['index'][1]['nl']['debut'] = 0;
            }
            foreach ($this->getDataForTable() as $row) {
                // saut de page pour un changement de valeur d'une colonne
                $nl = false;
                foreach ($idx_nl as $i) {
                    if (! empty($idx_page[$i]) && $idx_page[$i] != $row[$i]) {
                        $nl = true;
                        $idx_page[$i] = $row[$i];
                        break;
                    }
                    $idx_page[$i] = $row[$i];
                }
                if ($nl) {
                    $this->templateDocBodyMethod1Tfoot(
                        $this->data['index'][1]['nl']['debut'],
                        $this->data['index'][1]['current'] - 1);
                    $this->data['index'][1]['nl']['debut'] = $this->data['index'][1]['current'];
                    $this->AddPage();
                    $this->configGraphicSectionTable('thead');
                    $this->templateDocBodyMethod1Thead();
                    $this->configGraphicSectionTable('tbody');
                }
                // tbody
                for ($j = 0; $j < count($row); $j ++) {
                    if (is_numeric($row[$j])) {
                        $align = $columns[$j]['tbody_align'] == 'standard' ? 'R' : $columns[$j]['tbody_align'];
                    } else {
                        $align = $columns[$j]['tbody_align'] == 'standard' ? 'L' : $columns[$j]['tbody_align'];
                    }
                    $this->Cell($columns[$j]['width'],
                        $this->getConfig([
                            'doctable',
                            'tbody'
                        ], 'row_height'),
                        StdLib::formatData($row[$j], $columns[$j]['tbody_precision'],
                            $columns[$j]['tbody_completion']),
                        $this->getConfig([
                            'doctable',
                            'tbody'
                        ], 'cell_border'), 0, $align, $fill,
                        $this->getConfig([
                            'doctable',
                            'tbody'
                        ], 'cell_link'), $columns[$j]['tbody_stretch'],
                        $this->getConfig([
                            'doctable',
                            'tbody'
                        ], 'cell_ignore_min_height'),
                        $this->getConfig([
                            'doctable',
                            'tbody'
                        ], 'cell_calign'),
                        $this->getConfig([
                            'doctable',
                            'tbody'
                        ], 'cell_valign'));
                }
                $this->data['index'][1]['current'] ++; // il faut mettre cette ligne après l'appel
                                                       // de Cell()
                $this->Ln();
                $fill = ! $fill;
            }
        }

        // tfoot
        $this->templateDocBodyMethod1Tfoot($this->data['index'][1]['nl']['debut']);

        $this->Cell($sum_width * $ratio, 0, '', 'T');
    }

    /**
     * Ecriture du thead pour ce template
     */
    private function templateDocBodyMethod1Thead()
    {
        if ($this->getConfig([
            'doctable',
            'thead'
        ], 'visible', false)) {
            foreach ($this->sbm_columns as $column) {
                if (is_numeric($column['thead'])) {
                    $align = $column['thead_align'] == 'standard' ? 'R' : $column['thead_align'];
                } else {
                    $align = $column['thead_align'] == 'standard' ? 'L' : $column['thead_align'];
                }
                $this->Cell($column['width'],
                    $this->getConfig([
                        'doctable',
                        'thead'
                    ], 'row_height'),
                    StdLib::formatData($column['thead'], $column['thead_precision'],
                        $column['thead_completion']),
                    $this->getConfig([
                        'doctable',
                        'thead'
                    ], 'cell_border'), 0, $align, 1,
                    $this->getConfig([
                        'doctable',
                        'thead'
                    ], 'cell_link'), $column['thead_stretch'],
                    $this->getConfig([
                        'doctable',
                        'thead'
                    ], 'cell_ignore_min_height'),
                    $this->getConfig([
                        'doctable',
                        'thead'
                    ], 'cell_calign'),
                    $this->getConfig([
                        'doctable',
                        'thead'
                    ], 'cell_valign'));
            }
            $this->Ln();
        }
    }

    /**
     * Ecriture du tfoot pour ce template
     */
    private function templateDocBodyMethod1Tfoot($debut = 0, $fin = null)
    {
        if ($this->getConfig([
            'doctable',
            'tfoot'
        ], 'visible', false)) {
            $this->configGraphicSectionTable('tfoot');
            $index = 0;
            foreach ($this->sbm_columns as $column) {
                // calcul sur la colonne $index
                $oCalculs = new Calculs($this->data[1], ++ $index);
                $oCalculs->range($debut, $fin);
                $value = $oCalculs->getResultat($column['tfoot']);
                //
                if (is_numeric($value)) {
                    $align = $column['tfoot_align'] == 'standard' ? 'R' : $column['tfoot_align'];
                } else {
                    $align = $column['tfoot_align'] == 'standard' ? 'L' : $column['tfoot_align'];
                }
                $this->Cell($column['width'],
                    $this->getConfig([
                        'doctable',
                        'tfoot'
                    ], 'row_height'),
                    StdLib::formatData($value, $column['tfoot_precision'],
                        $column['tfoot_completion']),
                    $this->getConfig([
                        'doctable',
                        'tfoot'
                    ], 'cell_border'), 0, $align, 1,
                    $this->getConfig([
                        'doctable',
                        'tfoot'
                    ], 'cell_link'), $column['tfoot_stretch'],
                    $this->getConfig([
                        'doctable',
                        'tfoot'
                    ], 'cell_ignore_min_height'),
                    $this->getConfig([
                        'doctable',
                        'tfoot'
                    ], 'cell_calign'),
                    $this->getConfig([
                        'doctable',
                        'tfoot'
                    ], 'cell_valign'));
            }
            $this->Ln();
        }
    }

    protected function initConfigDoctable($ordinal_table = 1)
    {
        $table_doctables = $this->pdf_manager->get('Sbm\DbManager')->get(
            'Sbm\Db\System\DocTables');
        try {
            $this->config['doctable'] = $table_doctables->getConfig(
                $this->getDocumentId(), $ordinal_table);
        } catch (\Exception $e) {
            $this->config['doctable'] = require (__DIR__ . '/default/doctables.inc.php');
        }

        $table_columns = $this->pdf_manager->get('Sbm\DbManager')->get(
            'Sbm\Db\System\DocTables\Columns');
        try {
            $this->config['doctable']['columns'] = $table_columns->getConfig(
                $this->getDocumentId(), $ordinal_table);
        } catch (\Exception $e) {
            // pas d'en-tête, pas de pied, colonnes proportionnelles à la taille du contenu
            $this->config['doctable']['thead']['visible'] = $this->config['doctable']['tfoot']['visible'] = false;
        }
    }

    /**
     * Renvoie le tableau des données pour la table $ordinal_table.
     * Initialise le tableau s'il est vide ou si $force.
     *
     * @param int $ordinal_table
     * @param boolean $force
     *            force une initialisation du tableau
     *
     * @throws Exception
     * @return array
     */
    protected function getDataForTable($ordinal_table = 1, $force = false)
    {
        if ($force || empty($this->data[$ordinal_table])) {
            $this->data[$ordinal_table] = [];
            // lecture de la description des colonnes
            $table_columns = $this->getConfig('doctable', 'columns', []);

            if ($this->getRecordSourceType() == 'T') {
                /**
                 * POUR LES SOURCES qui sont des TABLES ou des VUES
                 *
                 * La source doit être enregistrée dans le ServiceManager (table ou vue MySql)
                 * sinon exception
                 */
                $table = $this->getRecordSourceTable();

                // si la description des colonnes est vide, on configure toutes les colonnes de la
                // source
                if (empty($table_columns)) {
                    $ordinal_position = 1;
                    foreach ($table->getColumnsNames() as $column_name) {
                        $column = require (__DIR__ . '/default/doccolumns.inc.php');
                        $column['thead'] = $column['tbody'] = $column_name;
                        $column['ordinal_position'] = $ordinal_position ++;
                        $table_columns[] = $column;
                    }
                    $this->config['doctable']['columns'] = $table_columns;
                }
                // prépare les filtres pour le décodage des données (notamment booléennes)
                $columnEffectif = false;
                foreach ($table_columns as &$column) {
                    $column['filter'] = preg_replace([
                        '/^\s+/',
                        '/\s+$/'
                    ], '', $column['filter']);
                    if (! empty($column['filter']) && is_string($column['filter'])) {
                        $column['filter'] = StdLib::getArrayFromString(
                            stripslashes($column['filter']));
                    } else {
                        $column['filter'] = [];
                    }
                    // repère les colonnes d'effectifs
                    if (preg_match('/%(.*)%/', $column['tbody'])) {
                        $columnEffectif = true;
                    }
                    unset($column);
                }
                $effectifClass = null;
                if ($columnEffectif) {
                    $effectifClassName = $this->getParam('effectifClassName',
                        Columns::getStringEffectifInterface($this->recordSource));
                    if ($this->pdf_manager->get('Sbm\DbManager')->has($effectifClassName)) {
                        $effectifClass = $this->pdf_manager->get('Sbm\DbManager')->get(
                            $effectifClassName);
                        $id = $effectifClass->getIdColumn();
                        $sanspreinscrits = $this->getParam('sanspreinscrits', false);
                        if (method_exists($effectifClass, 'setCaractereConditionnel')) {
                            $caractere = $this->getParam('caractereConditionnel', false);
                            if ($caractere) {
                                $effectifClass->setCaractereConditionnel($caractere)->init(
                                    $sanspreinscrits);
                            } else {
                                // Mauvaise configuration
                                if (getenv('APPLICATION_ENV') == 'development') {
                                    throw new Exception(
                                        "Le paramètre `caractereConditionnel` n'a pas été défini avant l'appel.");
                                }
                                $effectifClass = null;
                            }
                        } else {
                            $effectifClass->init($sanspreinscrits);
                        }
                    }
                }
                // lecture des données et calcul des largeurs de colonnes
                foreach ($table->fetchAll($this->getWhere(), $this->getOrderBy()) as $row) {
                    $ligne = [];
                    foreach ($table_columns as &$column) {
                        try {
                            $ligne[] = $value = StdLib::translateData(
                                $row->{$column['tbody']}, $column['filter']);
                        } catch (\Exception $e) {
                            if ($effectifClass instanceof \SbmGestion\Model\Db\Service\Eleve\EffectifInterface) {
                                $columntbody = trim($column['tbody'], '%');
                                if (method_exists($effectifClass, $columntbody)) {
                                    $ligne[] = $effectifClass->{$columntbody}($row->{$id});
                                } else {
                                    $ligne[] = 0;
                                }
                            } else {
                                $ligne[] = 0;
                            }
                        }
                        // adapte la largeur de la colonne si nécessaire
                        $value_width = $this->GetStringWidth($value,
                            $this->getConfig('document', 'data_font_family',
                                PDF_FONT_NAME_DATA), '',
                            $this->getConfig('document', 'data_font_size',
                                PDF_FONT_SIZE_DATA));
                        $value_width += $this->cell_padding['L'] + $this->cell_padding['R'];
                        if ($value_width > $column['width']) {
                            $column['width'] = $value_width;
                        }
                        unset($column);
                    }
                    $this->data[$ordinal_table][] = $ligne;
                }
                $this->config['doctable']['columns'] = $table_columns;
            } else {
                /**
                 * POUR LES SOURCES qui sont des REQUETES SQL
                 *
                 * On essaiera de poser un effectif sur les colonnes %transportes% et %demandes% à
                 * condition qu'on ait fourni un paramètre 'effectifClassName' correct (cad qu'il
                 * existe une classe `effectifClass` implémentant `EffectifInterface` et possédant
                 * les methodes `tranportes()` et éventuellement `demandes()`.
                 * Pour obtenir des effectifs conditionnels, il faut qu'un paramètre
                 * 'caractereConditionnel' soit passé et que la classe `effectifClass`
                 * présente la méthode `setCaractereConditionnel`. Son appel se fera avant l'init.
                 */
                $columns = [];
                $effectifColumns = [];
                foreach ($table_columns as &$column) {
                    // on relève les colonnes d'effectifs et on met false à leur place dans
                    // $column['tbody'] pour ne pas rechercher la valeur dans la requête.
                    $matches = [];
                    if (preg_match('/^%(.*)%$/', $column['tbody'], $matches)) {
                        $effectifColumns[] = $matches[1];
                        $column['tbody'] = false;
                    } else {
                        $columns[] = $column['tbody'];
                    }
                }
                if ($effectifColumns) {
                    $effectifClassName = $this->getParam('effectifClassName', false);
                    $effectifClass = null;
                    if ($effectifClassName &&
                        $this->pdf_manager->get('Sbm\DbManager')->has($effectifClassName)) {
                        $effectifClass = $this->pdf_manager->get('Sbm\DbManager')->get(
                            $effectifClassName);
                        if ($effectifClass instanceof \SbmGestion\Model\Db\Service\Eleve\EffectifInterface) {
                            $id = $effectifClass->getIdColumn();
                            $sanspreinscrits = $this->getParam('sanspreinscrits', false);
                            if (method_exists($effectifClass, 'setCaractereConditionnel')) {
                                $caractere = $this->getParam('caractereConditionnel',
                                    false);
                                if ($caractere) {
                                    $effectifClass->setCaractereConditionnel($caractere)->init(
                                        $sanspreinscrits);
                                } else {
                                    // Mauvaise configuration
                                    if (getenv('APPLICATION_ENV') == 'development') {
                                        throw new Exception(
                                            "Le paramètre `caractereConditionnel` n'a pas été défini avant l'appel.");
                                    }
                                    $effectifClass = null;
                                }
                            } else {
                                $effectifClass->init($sanspreinscrits);
                            }
                        } else {
                            $effectifClass = null;
                        }
                    }
                }
                if (empty($columns)) {
                    $columns[] = Select::SQL_STAR;
                }
                $recordSource = $this->decodeSource(
                    $this->getConfig('document', 'recordSource', ''),
                    $this->pdf_manager->get('SbmAuthentification\Authentication')
                        ->by()
                        ->getUserId());
                $dbAdapter = $this->pdf_manager->get('Sbm\DbManager')->getDbAdapter();
                try {
                    $select = new Select($recordSource);
                    $select->columns($columns)
                        ->where($this->getWhere())
                        ->order($this->getOrderBy());
                    $sqlString = $select->getSqlString($dbAdapter->getPlatform());
                    $rowset = $dbAdapter->query($sqlString,
                        \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
                    if ($rowset->count()) {
                        // si la description des colonnes est vide, on configure toutes les
                        // colonnes de la source
                        if (empty($table_columns)) {
                            $ordinal_position = 1;
                            foreach (array_keys($rowset->current()->getArrayCopy()) as $column_name) {
                                $column = require (__DIR__ . '/default/doccolumns.inc.php');
                                $column['thead'] = $column['tbody'] = $column_name;
                                $column['ordinal_position'] = $ordinal_position ++;
                                $table_columns[] = $column;
                            }
                            $this->config['doctable']['columns'] = $table_columns;
                        }
                        foreach ($rowset as $row) {
                            // $row est un ArrayObject
                            $ligne = [];
                            $idEffectifColumns = 0;
                            for ($key = 0; $key < count($table_columns); $key ++) {
                                // on distingue les colonnes d'effectifs
                                if ($table_columns[$key]['tbody']) {
                                    // ce n'est pas une colonne d'effectif
                                    $value = $row[$table_columns[$key]['tbody']];
                                } elseif (array_key_exists($idEffectifColumns,
                                    $effectifColumns)) {
                                    // c'est une colonne d'effectif
                                    $method = $effectifColumns[$idEffectifColumns ++];
                                    if ($effectifClass instanceof \SbmGestion\Model\Db\Service\Eleve\EffectifInterface &&
                                        method_exists($effectifClass, $method)) {
                                        // la configuration est correcte
                                        $value = $effectifClass->{$method}($row->{$id});
                                    } else {
                                        // la configuration est incorrecte
                                        $value = 0;
                                    }
                                } else {
                                    // autres cas
                                    $value = 0;
                                }
                                // reprise du traitement
                                $ligne[] = $value;
                                // adapte la largeur de la colonne si nécessaire
                                $value_width = $this->GetStringWidth($value,
                                    $this->getConfig('document', 'data_font_family',
                                        PDF_FONT_NAME_DATA), '',
                                    $this->getConfig('document', 'data_font_size',
                                        PDF_FONT_SIZE_DATA));
                                $value_width += $this->cell_padding['L'] +
                                    $this->cell_padding['R'];
                                if ($value_width > $table_columns[$key]['width']) {
                                    $table_columns[$key]['width'] = $value_width;
                                }
                            }
                            $this->data[$ordinal_table][] = $ligne;
                            $this->config['doctable']['columns'] = $table_columns;
                        }
                    }
                } catch (\Exception $e) {
                    if (getenv('APPLICATION_ENV') == 'development') {
                        $msg = __METHOD__ . ' - ' . $e->getMessage() . "\n" . $recordSource .
                            "\n" . $e->getTraceAsString();
                    } else {
                        $msg = "Impossible d'exécuter la requête.\n" . $sqlString;
                    }
                    $errcode = $e->getCode();
                    if (! empty($errcode) && ! is_numeric($errcode)) {
                        $msg = sprintf('Erreur %s : %s', $errcode, $msg);
                        $errcode = null;
                    }
                    throw new Exception($msg, $errcode, $e->getPrevious());
                }
            }
        }
        return $this->data[$ordinal_table];
    }

    // ============= Les pieds de document ======================

    /**
     * Modèle de pied de document par défaut
     * Les variables de chaine traitées sont :
     * %nombre% : nombre de ligne de données
     * %numero% : numéro de la source (uniquement s'il y a plusieurs sources)
     */
    public function templateDocFooterMethod1($param = null)
    {
        if (is_string($param) && $param == '?') {
            return 'Pied de document par défaut';
        }

        // texte à écrire
        $oCalculs = new Calculs($this->data[1]);
        $txt = $oCalculs->getResultat(
            trim($this->getConfig('document', 'docfooter_string', ''), "\n") . "\n");

        // mise en page
        if ($this->getConfig('document', 'docfooter_page_distincte', false)) {
            // cas d'une page distincte
            $this->SetY(
                $this->GetY() +
                $this->getConfig('document', 'docfooter_margin',
                    self::DEFAULT_SBM_DOCFOOTER_MARGIN));
            $tmp = $this->getConfig('document', 'docfooter_title', '');
            if (! empty($tmp)) {
                $this->Titre(1, $this->getConfig('document', 'docfooter_title', ''), 'C');
                $this->SetY(140, true, true);
            }
            if (! empty($txt)) {
                $this->SetFont($this->getFontFamily(), 'I');
                // écrire le résultat
                if ($txt == strip_tags($txt)) {
                    $this->Write(0, trim($txt, "\n") . "\n");
                } else {
                    $this->writeHTML($txt, true, false, true, false, '');
                }
            }
        } else {
            // cas d'une section continue
            $current_page = $this->PageNo();
            $delta = $this->getConfig('document', 'docfooter_margin',
                self::DEFAULT_SBM_DOCFOOTER_MARGIN);
            if ($this->getConfig('document', 'docfooter_insecable', true)) {
                // on réserve la place pour la marge, le titre (si non vide) et 3 lignes (si
                // docfooter_string non vide)
                $tmp = $this->getConfig('document', 'docfooter_title', '');
                if (! empty($tmp)) {
                    $delta += self::SBM_DOCFOOTER_INSECABLE_DELTA_TITLE;
                }
                if (! empty($txt)) {
                    $delta += self::SBM_DOCFOOTER_INSECABLE_DELTA_NBLIGNES *
                        $this->getCellHeight($this->FontSize);
                }
            }
            $this->checkPageBreak($delta);
            if ($current_page == $this->PageNo()) {
                $this->SetY(
                    $this->GetY() +
                    $this->getConfig('document', 'docfooter_margin',
                        self::DEFAULT_SBM_DOCFOOTER_MARGIN));
            }
            $tmp = $this->getConfig('document', 'docfooter_title', '');
            if (! empty($tmp)) {
                $this->Titre(1, $this->getConfig('document', 'docfooter_title', ''), 'L');
            }
            if (! empty($txt)) {
                // écrire le résultat
                if ($txt == strip_tags($txt)) {
                    $this->Write(0, trim($txt, "\n") . "\n");
                } else {
                    $this->writeHTML($txt, true, false, true, false, '');
                }
            }
        }
    }

    private function getDataCalculTotal($key)
    {
        $function = $this->data['calculs'][1][$key]['function'];
        $column = $this->data['calculs'][1][$key]['column'];
        $column --; // indexation des colonnes à partir de 0
        switch ($function) {
            case 'somme':
                $result = 0;
                for ($j = 0; $j < $this->data['count'][1]; $j ++) {
                    $valeur = $this->data[1][$j][$column];
                    if (is_numeric($valeur)) {
                        $result += $valeur;
                    }
                }
                break;
            case 'max':
                $result = - PHP_INT_MAX;
                for ($j = 0; $j < $this->data['count'][1]; $j ++) {
                    $valeur = $this->data[1][$j][$column];
                    if (is_numeric($valeur) && $valeur > $result) {
                        $result = $valeur;
                    }
                }
                break;
            case 'min':
                $result = PHP_INT_MAX;
                for ($j = 0; $j < $this->data['count'][1]; $j ++) {
                    $valeur = $this->data[1][$j][$column];
                    if (is_numeric($valeur) && $valeur < $result) {
                        $result = $valeur;
                    }
                }
                break;
            case 'moyenne':
                $tmp_somme = 0;
                for ($j = 0, $tmp_nombre = 0; $j < $this->data['count'][1]; $j ++, $tmp_nombre ++) {
                    $valeur = $this->data[1][$j][$column];
                    if (is_numeric($valeur)) {
                        $tmp_somme += $valeur;
                    }
                }
                $result = $tmp_somme / $tmp_nombre;
                break;
            default:
                $result = - PHP_INT_MAX;
                break;
        }
        return $result;
    }

    // =======================================================================================================
    // Modèle pour imprimer des étiquettes
    //
    /**
     * Modèle pour imprimer des étiquettes.
     * Renvoie un identifiant du template si $param vaut '?'. Sinon, le paramètre est ignoré et le
     * template est exécuté.
     *
     * @param string $param
     *            s'il est renseigné il doit avoir la valeur '?' (sinon, il est ignoré)
     *
     * @return void|string Renvoie l'identifiant du template si $param == '?' sinon rien
     */
    public function templateDocBodyMethod2($param = null)
    {
        /**
         * Identifiant du template
         */
        if (is_string($param) && $param == '?') {
            return 'Etiquettes';
        }

        $label = new Etiquette($this->pdf_manager->get('Sbm\DbManager'),
            $this->getDocumentId());
        // pour le moment, planche entière
        // par la suite, pour commencer à l'étiquette colonne $j, rangée $k :
        // $label->setCurrentColumn($j); // optionnel
        // $label->setCurrentRow($k); // optionnel
        list ($this->x, $this->y) = $label->xyStart();
        $descripteur = $label->descripteurData();
        $page_vide = true;
        foreach ($this->getDataForEtiquettes($descripteur) as $etiquetteData) {
            $lignes = $etiquetteData['lignes'];
            $photos = $etiquetteData['photos'];
            $page_vide = false;
            // partie graphique : photos
            $origine = [
                $this->x,
                $this->y
            ];
            foreach ($photos as $rang => $img) {
                list ($x, $y, $w, $h, $type, $align, $resize) = array_values(
                    $label->parametresPhoto($rang));
                unset($resize); // TODO : ce paramètre n'est pas utilisé
                $x += $origine[0];
                $y += $origine[1];
                $this->Image($img, $x, $y, $w, $h, $type, '', $align, '', 150);
            }
            // partie texte - etiquetteData est indexé à partir de 0
            list ($x, $y) = $origine;
            $this->SetXY($x, $y);
            for ($j = 0; $j < count($lignes); $j ++) {
                $this->setStyle($descripteur[$j]['style']);
                $txt = $lignes[$j];
                $txtLabel = $descripteur[$j]['label'];
                if (! empty($txtLabel)) {
                    $this->Cell($label->wLab($j), $label->hCell($j), $txtLabel, 0, 0,
                        $label->alignLab($j), 0, '', $label->stretchLab($j));
                    $this->x += $label->labelSpace($j);
                }
                $this->Cell($label->wCell($j), $label->hCell($j), $txt, 0, 0,
                    $label->alignCell($j), 0, '', $label->stretchCell($j));
                list ($this->x, $this->y) = $label->Ln($j);
            }
            if (($xy = $label->NextPosition($j)) == false) {
                $page_vide = true;
                $this->AddPage();
                list ($this->x, $this->y) = $label->NewPage();
            } else {
                list ($this->x, $this->y) = $xy;
            }
        }
        if ($page_vide) {
            $this->deletePage($this->PageNo());
        }
    }

    /**
     * Renvoie un tableau indexé de données pour les étiquettes.
     * Chaque enregistrement du tableau correspond au contenu d'une étiquette sous la
     * forme d'un tableau associatif
     * <code>
     * [
     * 'lignes' => [tableau indexé des lignes de l'étiquette (1)],
     * 'photos' => false ou tableau indexé de tableaux associatifs de la photo et de ses
     * paramètres (2). Autant de photos que le descripteur l'indique
     * ]
     * </code>
     * <ol>
     * <li> Chaque ligne de ce tableau est une chaine de caractères correctement formatée
     * pour être directement "écrite" dans la page PDF.
     * <li> Si le descripteur indique qu'il s'agit d'une photo :
     * <code>
     * 'photos' =>[
     * [
     * img => @imagedata où imagedata est le décodage de la colonne photo
     * x => abscisse du coin supérieur gauche ('' par défaut)
     * y => ordonnée du coin supérieur gauche ('' par défaut)
     * w => largeur de l'image dans la page (0 par défaut)
     * h => hauteur de l'image dans la page (0 par défaut)
     * type => typephoto (JPEG ou PNG ou GIF - '' par défaut)
     * align => T ou M ou B ou N ('' par défaut)
     * resize => true (false par défaut)
     * dpi => résolution de l'image (300 par défaut)
     * ], ...
     * ]
     * </code>
     * Sinon, 'photo' => []
     * </li></ol>
     * Le filtrage des données se fait :<ul>
     * <li>pour les recordSources de type T par la méthode getWhere()</li>
     * <li>pour les recordSources de type R par l'exploitation du paramètre `criteres`
     * qui se présente sous la forme d'un tableau</li></ul>
     *
     * @param array $descripteur
     *            tableau de descripteurs des champs
     *            chaque champ est décrit dans un tableau avec les clés
     *            'fieldname', 'filter', 'format', 'label', 'nature', 'style', 'data'
     * @param bool $force
     *            force l'initialisation des données par lecture de la base
     *
     * @return array
     */
    protected function getDataForEtiquettes($descripteur, $force = false)
    {
        if ($force || empty($this->data)) {
            // prépare les filtres pour le décodage des données (notamment booléennes)
            foreach ($descripteur as &$column) {
                $column['filter'] = preg_replace([
                    '/^\s+/',
                    '/\s+$/'
                ], '', $column['filter']);
                if (! empty($column['filter'])) {
                    $column['filter'] = StdLib::getArrayFromString($column['filter']);
                } else {
                    $column['filter'] = [];
                }
                unset($column);
            }
            $this->data = [];
            if ($this->getRecordSourceType() == 'T') {
                // La source doit être enregistrée dans le ServiceManager (table ou vue MySql)
                // sinon exception
                $table = $this->getRecordSourceTable();
                // lecture des données et application du filtre et du format
                foreach ($table->fetchAll($this->getWhere(), $this->getOrderBy()) as $row) {
                    $lignes = [];
                    $photos = [];
                    foreach ($descripteur as $rang => $column) {
                        $value = StdLib::translateData($row->{$column['fieldname']},
                            $column['filter']);
                        switch ($column['nature']) {
                            case 2:
                                if ($value) {
                                    $photos[$rang] = '@' . stripslashes($value);
                                }
                                break;
                            case 1:
                                if (! empty($column['format']) &&
                                    stripos('h', $column['format']) !== false) {
                                    $value = DateLib::formatDateTimeFromMysql($value);
                                } else {
                                    $value = DateLib::formatDateFromMysql($value);
                                }
                                break;
                            default:
                                $value = sprintf($column['format'], $value);
                                break;
                        }
                        $lignes[] = $value;
                    }
                    if ($photos == []) {
                        $photos = false;
                    }
                    $this->data[] = [
                        'lignes' => $lignes,
                        'photos' => $photos
                    ];
                }
            } else {
                /**
                 * c'est une requête Sql.
                 *
                 * S'il n'y a pas de description des colonnes dans la table doccolumns
                 * alors on en crée une par défaut.
                 */
                // remplacement des variables %millesime%, %date%, %heure% et %userId%
                // et des opérateurs %gt%, %gtOrEq%, %lt%, %ltOrEq%, %ltgt%, %notEq%
                $sql = $this->decodeSource(
                    $this->getConfig('document', 'recordSource', ''),
                    $this->pdf_manager->get('SbmAuthentification\Authentication')
                        ->by()
                        ->getUserId());
                $dbAdapter = $this->pdf_manager->get('Sbm\DbManager')->getDbAdapter();

                try {
                    $criteres = $this->getParam('criteres', []);
                    $strict = $this->getParam('strict',
                        [
                            'empty' => [],
                            'not empty' => []
                        ]);
                    $expressions = $this->getParam('expression', []);
                    if (! empty($criteres) || ! empty($expressions)) {
                        $where = [];
                        foreach ($criteres as $key => $value) {
                            if (array_key_exists($key, $expressions))
                                continue;
                            if (in_array($key, $strict['empty'])) {
                                $where[] = "tmp.$key = \"$value\"";
                            } elseif (in_array($key, $strict['not empty'])) {
                                if (empty($value))
                                    continue;
                                $where[] = "tmp.$key = \"$value\"";
                            } else {
                                if (empty($value)) {
                                    continue;
                                } elseif (is_string($value)) {
                                    $where[] = "tmp.$key Like \"$value%\"";
                                } else {
                                    $where[] = $this->createExpression($value, 'tmp');
                                }
                            }
                        }
                        foreach ($expressions as $expression) {
                            $where[] = $expression;
                        }
                        $orderBy = $this->getOrderBy();
                        if (is_array($orderBy)) {
                            $orderBy = implode(', ', $orderBy);
                        }
                        if (! empty($where)) {
                            if (empty($orderBy)) {
                                $sql = "SELECT * FROM ($sql) tmp WHERE " .
                                    implode(' AND ', $where);
                            } else {
                                $sql = "SELECT * FROM ($sql) tmp WHERE " .
                                    implode(' AND ', $where) . " ORDER BY " . $orderBy;
                            }
                        } elseif (! empty($orderBy)) {
                            $sql = "SELECT * FROM ($sql) tmp ORDER BY " . $orderBy;
                        }
                    }
                    $rowset = $dbAdapter->query($sql,
                        \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
                    if ($rowset->count()) {
                        foreach ($rowset as $row) {
                            // $row est un ArrayObject
                            $lignes = [];
                            $photos = [];
                            for ($key = 0; $key < count($descripteur); $key ++) {
                                if (empty($descripteur[$key]['fieldname'])) {
                                    $value = '';
                                } else {
                                    $value = StdLib::translateData(
                                        $row[$descripteur[$key]['fieldname']],
                                        $descripteur[$key]['filter']);
                                    switch ($descripteur[$key]['nature']) {
                                        case 2:
                                            if ($value) {
                                                $photos[$key] = '@' . stripslashes($value);
                                            }
                                            break;
                                        case 1:
                                            if (! empty($descripteur[$key]['format']) &&
                                                stripos('h', $descripteur[$key]['format']) !==
                                                false) {
                                                $value = DateLib::formatDateTimeFromMysql(
                                                    $value);
                                            } else {
                                                $value = DateLib::formatDateFromMysql(
                                                    $value);
                                            }
                                            $lignes[] = $value;
                                            break;
                                        default:
                                            if (! empty($descripteur[$key]['format'])) {
                                                $value = sprintf(
                                                    $descripteur[$key]['format'], $value);
                                            }
                                            $lignes[] = $value;
                                            break;
                                    }
                                }
                            }
                            $this->data[] = [
                                'lignes' => $lignes,
                                'photos' => $photos
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    $message = sprintf(
                        "Impossible d\'exécuter la requête décrite dans ce document.\n%s",
                        $sql);
                    throw new Exception($message, 0, $e->getPrevious());
                }
            }
        }
        return $this->data;
    }

    /**
     * Récursivité qui s'arrête lorsque $array est une chaine de caractères.
     * Si cette chaine répond à la grammaire d'un nom de colonne, elle est préfixée.
     * Pour ne pas la préfixer il faut qu'elle soit quotée ('ALAIN' ou "ALAIN") ou
     * qu'elle contienne un caractère autre qu'une lettre, un chiffre ou le souligné.
     * Les colonnes déjà préfixées ne le sont pas à nouveau, même si le préfixe est
     * différent.
     *
     * @param array|string|number $array
     * @param string $prefix
     * @return string
     */
    private function createExpression($array, $prefix = '')
    {
        if (is_scalar($array)) {
            if (! empty($prefix) && preg_match('/^[A-Za-z_][A-Za-z0-9_]+$/', $array)) {
                return "$prefix.$array";
            } else {
                return $array;
            }
        }
        if (array_key_exists('operator', $array)) {
            $operator = $array['operator'];
            $parts = $array['parts'];
            switch ($operator) {
                case 'OR':
                case '||':
                case 'XOR':
                case 'AND':
                case '&&':
                    $pieces = [];
                    foreach ($parts as $partie) {
                        $pieces[] = $this->createExpression($partie, $prefix);
                    }
                    return '(' . implode(" $operator ", $pieces) . ')';
                    break;
                case 'NOT':
                case '!':
                    return $operator . ' (' . $this->createExpression($parts[0], $prefix) .
                        ')';
                    break;
                case 'IS NULL':
                case 'IS NOT NULL':
                case 'IS TRUE':
                case 'IS NOT TRUE':
                case 'IS FALSE':
                case 'IS NOT FALSE':
                case 'IS UNKNOWN':
                case 'IS NOT UNKNOWN':
                    return '(' . $this->createExpression($parts[0], $prefix) .
                        " $operator)";
                    break;
                case 'BETWEEN':
                    return '(' . $this->createExpression($parts[0], $prefix) . ' BETWEEN ' .
                        $this->createExpression($parts[1], $prefix) . ' AND ' .
                        $this->createExpression($parts[2], $prefix) . ')';
                    break;
                default:
                    return '(' . $this->createExpression($parts[0], $prefix) .
                        " $operator " . $this->createExpression($parts[1], $prefix) . ')';
                    break;
            }
        } else {
            throw new Exception('Syntaxe incorrecte.');
        }
    }

    // =======================================================================================================
    // Modèle pour imprimer des cartes
    //
    /**
     * Modèle pour imprimer les cartes de transport.
     * Renvoie un identifiant du template si $param vaut '?'. Sinon, le paramètre est ignoré et le
     * template est exécuté.
     *
     * @param string $param
     *            s'il est renseigné il doit avoir la valeur '?' (sinon, il est ignoré)
     *
     * @return void|string Renvoie l'identifiant du template si $param == '?' sinon rien
     */
    public function templateDocBodyMethod3($param = null)
    {
        /**
         * Identifiant du template
         */
        if (is_string($param) && $param == '?') {
            return 'Cartes de transport';
        }

        $label = new Carte($this->pdf_manager->get('Sbm\DbManager'),
            $this->getDocumentId());
        // position par défaut : planche entière
        if ($position = $this->getParam('position', false)) {
            $label->setCurrentColumn($position['column']);
            $label->setCurrentRow($position['row']);
        }
        list ($x, $y) = $label->xyStart();
        $this->SetXY($x, $y);
        // le descripteur est indexé à partir de 0
        $descripteur = $label->descripteurData();
        $duplicata = $this->getParam('duplicata', false);
        $page_vide = true;
        foreach ($this->getDataForEtiquettes($descripteur) as $etiquetteData) {
            $lignes = $etiquetteData['lignes'];
            $photos = (array) $etiquetteData['photos'];
            $page_vide = false;
            // partie graphique
            $origine = [
                $this->x,
                $this->y
            ];
            // $this->templateDocBodyMethod3Picture();
            // filigrane
            if ($duplicata) {
                list ($x, $y) = $origine;
                $y += $label->labelHeight() * 2 / 3;
                $this->StartTransform();
                $this->Rotate(45, $x, $y);
                $this->SetXY($x + 10, $y);
                $this->Titre(1, 'DUPLICATA', 'L');
                $this->StopTransform();
            }
            // partie photos
            foreach ($photos as $rang => $img) {
                list ($x, $y, $w, $h, $type, $align, $resize) = array_values(
                    $label->parametresPhoto($rang));
                unset($resize); // TODO : ce paramètre n'est pas utilisé
                $x += $origine[0];
                $y += $origine[1];
                $this->Image($img, $x, $y, $w, $h, $type, '', $align, '', 150);
            }
            // partie texte - etiquetteData est indexé à partir de 0
            list ($x, $y) = $origine;
            $this->SetXY($x, $y);
            for ($i = 0; $i < count($lignes); $i ++) {
                $this->setStyle($descripteur[$i]['style']);
                $txt = [];
                $txtLabel = $descripteur[$i]['label'];
                if (! empty($txtLabel)) {
                    $txt[] = $txtLabel;
                }
                if ($descripteur[$i]['data']) {
                    $txt[] = $lignes[$i];
                }
                $this->SetX($label->X($i));
                $this->Cell($label->wCell($i), $label->hCell($i), implode(' ', $txt), 0, 0,
                    $label->alignCell($i), 0, '', $label->stretchCell($i));
                unset($txt);
                list ($x, $y) = $label->Ln($i);
                $this->SetXY($x, $y);
            }
            if (($xy = $label->NextPosition($i)) == false) {
                $page_vide = true;
                $this->AddPage();
                list ($this->x, $this->y) = $label->NewPage();
            } else {
                list ($this->x, $this->y) = $xy;
            }
        }
        if ($page_vide) {
            $this->deletePage($this->PageNo());
        }
    }

    private function templateDocBodyMethod3Picture()
    {
        $path = $this->getConfig('document', 'url_path_images'); // se termine par /
        $x = $this->x;
        $y = $this->y;
        $file = $path . 'logocartegauche.png';
        $this->Image($file, $x, $y, 9.5, 13, '', '', '', true, 300);
        $file = $path . 'logocartedroite.png';
        $this->Image($file, $x + 56, $y, 23, 13, '', '', '', true, 300);
        $border_style = [
            'width' => 0.25,
            'cap' => 'round',
            'join' => 'round',
            'dash' => '1,2',
            'color' => [
                247,
                128,
                66
            ]
        ];
        $this->Rect($x + 56, $y + 17, 23, 29, 'D', [
            'all' => $border_style
        ]);
    }

    // =======================================================================================================
    // Modèle particulier pour les horaires avec élèves (2 tableaux)
    //
    /**
     * Modèle pour imprimer les horaires de circuits avec liste des élèves par point d'arrêt.
     * Le document est composé de deux tableaux, l'un pour l'aller, l'autre pour le retour.
     * Renvoie un identifiant du template si $param vaut '?'. Sinon, le paramètre est ignoré et le
     * template est exécuté.
     *
     * @param string $param
     *            s'il est renseigné il doit avoir la valeur '?' (sinon, il est ignoré)
     *
     * @return void|string Renvoie l'identifiant du template si $param == '?' sinon rien
     */
    public function templateDocBodyMethod4($param = null)
    {
        /**
         * Identifiant du template
         */
        if (is_string($param) && $param == '?') {
            return 'Horaires circuit avec élèves';
        }

        $fichier_phtml = $this->getParam('layout', null); // nom du fichier phtml (avec son chemin)
        if (empty($fichier_phtml)) {
            throw new Exception("Le modèle de ce document n'a pas été défini.");
        }
        $viewRender = $this->pdf_manager->get('ViewRenderer');
        $layout = new ViewModel();
        $layout->setTemplate($fichier_phtml);
        $saut_de_page = false;
        foreach ($this->getData() as $serviceId => $allerRetour) {
            $oservice = $this->pdf_manager->get('Sbm\DbManager')
                ->get('Sbm\Db\Table\Services')
                ->getRecord($serviceId);
            $otransporteur = $this->pdf_manager->get('Sbm\DbManager')
                ->get('Sbm\Db\Table\Transporteurs')
                ->getRecord($oservice->transporteurId);
            $transporteur = $otransporteur->nom;
            $nbPlaces = $oservice->nbPlaces;
            $telephone = $otransporteur->telephone;
            $part_gauche = "Circuit n° $serviceId - car $transporteur - $nbPlaces places";
            $part_droite = "Tél $transporteur : $telephone";
            if ($saut_de_page) {
                $this->AddPage();
            }
            $this->Write(0, $part_gauche, '', false, 'L');
            $this->Write(0, $part_droite, '', false, 'R', true);
            // die(var_dump($allerRetour));
            $layout->setVariables([
                'allerRetour' => $allerRetour
            ]);
            $codeHtml = $viewRender->render($layout);
            // echo($codeHtml);
            $this->writeHTML($codeHtml, true, false, false, false, '');
            $saut_de_page = true;
            ;
        }
    }

    public function templateFooterMethod4($param = null)
    {
        if (is_string($param) && $param == '?')
            return 'Pied de page pour les horaires';

        $cur_y = $this->y;
        $this->SetTextColorArray($this->footer_text_color);
        // set style for cell border
        $line_width = (0.85 / $this->k);
        $this->SetLineStyle(
            [
                'width' => $line_width,
                'cap' => 'butt',
                'join' => 'miter',
                'dash' => 0,
                'color' => $this->footer_line_color
            ]);
        // print document barcode
        $barcode = $this->getBarcode();
        if (! empty($barcode)) {
            $this->Ln($line_width);
            $barcode_width = round(
                ($this->w - $this->original_lMargin - $this->original_rMargin) / 3);
            $style = [
                'position' => $this->rtl ? 'R' : 'L',
                'align' => $this->rtl ? 'R' : 'L',
                'stretch' => false,
                'fitwidth' => true,
                'cellfitalign' => '',
                'border' => false,
                'padding' => 0,
                'fgcolor' => [
                    0,
                    0,
                    0
                ],
                'bgcolor' => false,
                'text' => false
            ];
            $this->write1DBarcode($barcode, 'C128', '', $cur_y + $line_width,
                $barcode_width, (($this->footer_margin / 3) - $line_width), 0.3, $style,
                '');
        }
        $w_page = isset($this->l['w_page']) ? $this->l['w_page'] . ' ' : '';
        if (empty($this->pagegroups)) {
            $pagenumtxt = $w_page . $this->getAliasNumPage() . ' / ' .
                $this->getAliasNbPages();
        } else {
            $pagenumtxt = $w_page . $this->getPageNumGroupAlias() . ' / ' .
                $this->getPageGroupAlias();
        }
        $this->SetY($cur_y);
        // Print page number
        if ($this->getRTL()) {
            $this->SetX($this->original_rMargin);
            $this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
        } else {
            $this->SetX($this->original_lMargin);
            $this->Cell(0, 0, $this->getAliasRightShift() . $pagenumtxt, 'T', 0, 'R');
        }
        // Print string
        $txt = $this->getConfig('document', 'pagefooter_string', '');
        if (! empty($txt) && ! empty($this->data)) {
            // remplacer les variables de la chaine
            $oCalculs = new Calculs($this->data);
            // $oCalculs->range($this->data['index']['previous'], $this->data['index']['current'] -
            // 1);
            $txt = $oCalculs->getResultat($txt);

            // découpe en 2 parties
            $matches = null;
            preg_match("/@gauche{([^}]*)}/", $txt, $matches);
            $part_gauche = isset($matches[1]) ? $matches[1] : '';
            preg_match("/@centre{([^}]*)}/", $txt, $matches);
            $part_centre = isset($matches[1]) ? $matches[1] : '';

            // écrire le résultat
            $this->SetY($cur_y);
            if (! empty($part_gauche)) {
                if ($part_gauche == strip_tags($part_gauche)) {
                    $this->Write(0, $part_gauche, '', false, 'L');
                } else {
                    $this->writeHTML($part_gauche, true, false, true, false, 'L');
                }
            }
            if (! empty($part_centre)) {
                $this->SetX(0);
                if ($part_centre == strip_tags($part_centre)) {
                    $this->Write(0, $part_centre, '', false, 'C');
                } else {
                    $this->writeHTML($part_centre, true, false, true, false, 'C');
                }
            }
        }
    }

    // =======================================================================================================
    // Modèle particulier pour les copies d'écran
    //
    /**
     * Doit recevoir un tableau de paramètres dont une clé est html.
     * Cette clé donne le texte html à placer dans le pdf.
     *
     * @param string $param
     * @return string
     */
    public function templateDocBodyMethod5($param = null)
    {
        /**
         * Identifiant du template
         */
        if (is_string($param) && $param == '?') {
            return 'Copie d\'écran';
        }
        $html = $this->getParam('html', 'Aucune donnée reçue.');
        // die('<pre>' . htmlentities($html) . '</pre>');
        $this->writeHTML($html, true, false, true, false, '');
    }

    // =======================================================================================================
    // Modèle particulier pour la liste des élèves dans le portail des organisateurs
    //
    /**
     * Liste associée à un layout et produisant un PDF à partir d'un modèle HTML
     * Les données sont passées par la méthode setData, que ce soit directement dans
     * cette classe ou par le service RenderPdfService.
     */
    public function templateDocBodyMethod6($param = null)
    {
        /**
         * Identifiant du template
         */
        if (is_string($param) && $param == '?') {
            return 'Liste associée à un modèle HTML (layout)';
        }
        $fichier_phtml = $this->getParam('layout', null); // nom du fichier phtml (avec son chemin)
        if (empty($fichier_phtml)) {
            throw new Exception("Le modèle de ce document n'a pas été défini.");
        }
        $viewRender = $this->pdf_manager->get('ViewRenderer');
        $layout = new ViewModel();
        $layout->setTemplate($fichier_phtml);
        $layout->setVariables([
            'eleves' => $this->getData()
        ]);
        $codeHtml = $viewRender->render($layout);
        set_time_limit(300);
        // echo($codeHtml);
        $this->writeHTML($codeHtml, true, false, false, false, '');
    }
}