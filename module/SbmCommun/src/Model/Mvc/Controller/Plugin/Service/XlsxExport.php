<?php
/**
 * Export par fichier xlsx téléchargeable
 * (Service déclaré dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/src/Model/Mvc/Controller/Plugin/Service
 * @filesource XlsxExport.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)require_once __DIR__ . '/../../../../../../../../vendor/autoload.php';require_once __DIR__ . '/../../../../../../../../vendor/autoload.php';
 * @date 5 déc. 2020
 * @version 2020-2.6.1
 */
namespace SbmCommun\Model\Mvc\Controller\Plugin\Service;

use Zend\Http\PhpEnvironment\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use SbmCommun\Model\Mvc\Controller\Plugin\Exception;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class XlsxExport extends AbstractPlugin
{

    /**
     *
     * @var Spreadsheet
     */
    private $spreadsheet;

    /**
     *
     * @var boolean
     */
    private $has_column_headers;

    /**
     * Taille maxi de mémoire avant d'utiliser un fichier temporaire pour la sortie
     *
     * @var int
     */
    private $tailleMBs;

    /**
     * Nom du fichier proposé lors du téléchargement
     *
     * @var string
     */
    private $file_name;

    public function __invoke(string $file_name = '', array $column_headers = [],
        $data = null, callable $func = null, array $params = [], string $sheet_title = '')
    {
        $this->tailleMBs = 2 * 1024 * 1024; // 2 Mo
        $this->spreadsheet = new Spreadsheet();
        $this->has_column_headers = false;
        if (func_num_args() == 0) {
            return $this;
        } elseif (func_num_args() == 1) {
            return $this->setFileName($file_name);
        }
        return $this->setFileName($file_name)
            ->setColumnHeaders($column_headers)
            ->setData($data, $func)
            ->setParams($params)
            ->setSheetTitle($sheet_title)
            ->getResponse();
    }

    public function setFileName(string $file_name)
    {
        $this->file_name = $file_name;
        return $this;
    }

    public function setParams(array $params = [])
    {
        if ($params) {
            $properties = &$this->spreadsheet->getProperties();
            if (array_key_exists('category', $params)) {
                $properties->setCategory($params['category']);
            }
            if (array_key_exists('company', $params)) {
                $properties->setCompany($params['company']);
            }
            if (array_key_exists('created', $params)) {
                $properties->setCreated($params['created']);
            }
            if (array_key_exists('creator', $params)) {
                $properties->setCreator($params['creator']);
            }
            if (array_key_exists('description', $params)) {
                $properties->setDescription($params['description']);
            }
            if (array_key_exists('keywords', $params)) {
                $properties->setKeywords($params['keywords']);
            }
            if (array_key_exists('lastModifiedBy', $params)) {
                $properties->setLastModifiedBy($params['lastModifiedBy']);
            }
            if (array_key_exists('manager', $params)) {
                $properties->setManager($params['manager']);
            }
            if (array_key_exists('modified', $params)) {
                $properties->setModified($params['modified']);
            }
            if (array_key_exists('subject', $params)) {
                $properties->set($params['subject']);
            }
            if (array_key_exists('title', $params)) {
                $properties->set($params['title']);
            }
        }
        return $this;
    }

    private function setRow(int $rang, $record, $func)
    {
        if (is_array($record) || $record instanceof \Traversable) {
            $activeSheet = $this->spreadsheet->setActiveSheetIndex(0);
            if ($this->has_column_headers) {
                $rang ++;
            }
            $columnIndex = 0;
            foreach ($record as $key => $value) {
                $columnIndex ++;
                if (is_callable($func)) {
                    $value = $func($key, $value);
                }
                if (! is_null($value)) {
                    $activeSheet->setCellValueByColumnAndRow($columnIndex, $rang, $value);
                }
            }
        } else {
            throw new Exception('La ligne est de type incorrect.');
        }
    }

    /**
     * Place les entêtes en ligne 1 de la feuille active d'index 0
     *
     * @param array $array
     * @return self
     */
    public function setColumnHeaders(array $array = []): self
    {
        if ($array) {
            $this->has_column_headers = true;
            $this->setRow(0, $array, null);
        }
        return $this;
    }

    /**
     *
     * @param array|\Traversable $data
     * @param callable $func
     * @throws \Exception
     * @return self
     */
    public function setData($data, callable $func = null): self
    {
        if (is_array($data) || $data instanceof \Traversable) {
            $rang = 0;
            foreach ($data as $record) {
                $rang ++;
                $this->setRow($rang, $record, $func);
            }
        } else {
            throw new Exception('Les données sont de type incorrect.');
        }
        return $this;
    }

    /**
     * Fixe le nom de la feuille active
     *
     * @param string $title
     * @return self
     */
    public function setSheetTitle(string $title = ''): self
    {
        if ($title) {
            $this->spreadsheet->getActiveSheet()->setTitle($title);
        }
        return $this;
    }

    /**
     * Renvoie la Response pour télécharger le fichier
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function getResponse()
    {
        if (method_exists($this->controller, 'getResponse')) {
            $response = $this->controller->getResponse();
        } else {
            $response = new Response();
        }
        $response->getHeaders()->addHeaders(
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment;filename="' .
                str_replace('"', '\\"', $this->file_name) . '.xlsx"'
            ]);
        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $fp = fopen("php://temp/maxmemory:$this->tailleMBs", 'r+');
        $writer->save($fp);
        rewind($fp);
        $response->setContent(stream_get_contents($fp));
        return $response;
    }
}