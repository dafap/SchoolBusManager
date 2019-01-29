<?php
/**
 * Objet étiquette
 *
 * En version 2, cette classe est appelée dans Tcpdf et n'est pas considérée comme un service 
 * afin de pourvoir passer aisément le documentId
 *   $label = new Etiquette($this->pdf_manager->get('Sbm\DbManager'), $this->getDocumentId());
 * 
 * @project sbm
 * @package SbmPdf/Model
 * @filesource Etiquette.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 jan. 2019
 * @version 2019-2.4.6
 */
namespace SbmPdf\Model;

use Zend\ServiceManager\ServiceLocatorInterface;

class Etiquette
{

    /**
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $db_manager;

    /**
     *
     * @var int
     */
    protected $documentId;

    /**
     * Les clés de ce tableau sont document, doclabel et docfields
     *
     * @var array
     */
    protected $config;

    /**
     * Colonne courante
     *
     * @var int
     */
    protected $currentColumn;

    /**
     * Ligne courante
     *
     * @var int
     */
    protected $currentRow;

    /**
     * Ordonnée relative à la zone d'écriture de l'étiquette
     *
     * @var float
     */
    protected $y;

    /**
     * Espacement entre les lignes d'écriture sur une étiquette
     *
     * @var float
     */
    protected $y_space;

    /**
     * Initialise la structure.
     *
     * Doit être suivi par :
     * $label->setCurrentColumn($j); // optionnel
     * $label->setCurrentRow($k); // optionnel
     * list($this->x, $this->y) = $label->xyStart(); // nécessaire
     *
     * @param ServiceLocatorInterface $db_manager            
     * @param int $documentId            
     */
    public function __construct(ServiceLocatorInterface $db_manager, $documentId)
    {
        $this->db_manager = $db_manager;
        $this->documentId = $documentId;
        
        // Lecture des tables system document, doclabel et docfields pour initialiser les paramètres de la structure
        /*
         * il ne semble pas être nécessaire d'utiliser la config du document dans cette classe.
         * (en réserve au cas où ...)
         */
        // $t = $this->db_manager->get('Sbm\Db\System\Documents');
        // try {
        // $this->config['document'] = $t->getConfig($this->documentId);
        // } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
        // $this->config['document'] = require (__DIR__ . '/default/documents.inc.php');
        // }
        $t = $this->db_manager->get('Sbm\Db\System\DocLabels');
        try {
            $this->config['doclabel'] = $t->getConfig($this->documentId);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
            $this->config['doclabel'] = require (__DIR__ . '/default/doclabels.inc.php');
        }
        $t = $this->db_manager->get('Sbm\Db\System\DocFields');
        try {
            $this->config['docfields'] = $t->getConfig($this->documentId);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
            $this->config['docfields'] = [];
        }
        $this->y_space = ($this->writingAreaHeight() - $this->totalLineHeight()) /
             ($this->lineCount() - 1);
        $this->NewPage();
    }

    /**
     * Donne le nombre de lignes d'écriture dans une étiquette.
     * On ne compte pas les photos
     *
     * @return int
     */
    public function lineCount()
    {
        //return count($this->config['docfields']);
        $n = 0;
        foreach ($this->config['docfields'] as $docfield) {
            if ($docfield['nature'] < 2) {
                $n++;
            }
        }
        return $n;
    }

    public function labelHeight()
    {
        return (float) $this->config['doclabel']['label_height'];
    }

    /**
     * Hauteur de la zone d'écriture dans une étiquette
     *
     * @return number
     */
    protected function writingAreaHeight()
    {
        $label_height = (float) $this->config['doclabel']['label_height'];
        $padding_top = (float) $this->config['doclabel']['padding_top'];
        $padding_bottom = (float) $this->config['doclabel']['padding_bottom'];
        return $label_height - $padding_top - $padding_bottom;
    }

    /**
     * Renvoie la hauteur totale des lignes
     *
     * @return number
     */
    private function totalLineHeight()
    {
        $total = 0;
        foreach ($this->config['docfields'] as $field) {
            $total += $field['height'];
        }
        $writingAH = $this->writingAreaHeight();
        if ($total > $writingAH) {
            $ratio = $writingAH / $total;
            foreach ($this->config['docfields'] as &$field) {
                $field['height'] *= $ratio;
            }
            return $writingAH;
        }
        return $total;
    }

    /**
     * Initialise la colonne courante.
     * Permet de fixer la position de la première étiquette à tirer sur une planche partiellement utilisée.
     * Utile également pour tirer des duplicatas.
     *
     * @param int $n            
     */
    public function setCurrentColumn($n)
    {
        $this->currentColumn = $n;
    }

    /**
     * Initialise la ligne courante.
     * Permet de fixer la position de la première étiquette à tirer sur une planche partiellement utilisée.
     * Utile également pour tirer des duplicatas.
     *
     * @param int $n            
     */
    public function setCurrentRow($n)
    {
        $this->currentRow = $n;
    }

    /**
     * Renvoie la position X origine pour l'étiquette courante
     *
     * @return float abcisse x en coordonnées absolue (page)
     */
    protected function xStart()
    {
        $margin_left = (float) $this->config['doclabel']['margin_left'];
        $label_width = (float) $this->config['doclabel']['label_width'];
        $x_space = (float) $this->config['doclabel']['x_space'];
        $padding_left = (float) $this->config['doclabel']['padding_left'];
        $xa = $margin_left + ($this->currentColumn - 1) * ($label_width + $x_space);
        return $xa + $padding_left;
    }

    /**
     * Renvoie la position Y origine pour l'étiquette courante
     *
     * @return float ordonnée y en coordonnées absolue (page)
     */
    protected function yStart()
    {
        $margin_top = (float) $this->config['doclabel']['margin_top'];
        $label_height = (float) $this->config['doclabel']['label_height'];
        $y_space = (float) $this->config['doclabel']['y_space'];
        $padding_top = (float) $this->config['doclabel']['padding_top'];
        $ya = $margin_top + ($this->currentRow - 1) * ($label_height + $y_space);
        return $ya + $padding_top;
    }

    /**
     * Renvoie la position (X, Y) d'origine de l'étiquette courante.
     * Si la première ligne est décalée (pas de label mais un label_width)
     * alors X en tient compte et est décalé d'autant que nécessaire.
     *
     * L'affectation dans la classe Tcpdf se fera de la façon suivante :
     * list($this->x, $this->y) = $label->xyStart();
     *
     * Pour un changement d'étiquette on fera :
     * $label = new Etiquette($sm, $documentId);
     * list($this->x, $this->y) = $label->xyStart();
     *
     * @return array of float (x, y) en coordonnées absolue (page)
     */
    public function xyStart()
    {
        if (empty($this->config['docfields'][0]['label'])) {
            $label_width = (float) $this->config['docfields'][0]['label_width'];
        } else {
            $label_width = 0;
        }
        $this->y = 0;
        return [
            $this->xStart() + $label_width,
            $this->yStart()
        ];
    }

    /**
     * Renvoie la position (X, Y) d'une nouvelle ligne d'écriture dans l'étiquette.
     *
     * On fera :
     * list($this->x, $this->y) = $label->Ln($i); // ou $i est le numéro de la ligne qu'on vient d'écrire
     *
     * @param int $rang            
     *
     * @return array of float (x, y) en coordonnées absolue (page)
     */
    public function Ln($rang)
    {
        $height = (float) $this->config['docfields'][$rang]['height'];
        if (array_key_exists($rang + 1, $this->config['docfields']) &&
             empty($this->config['docfields'][$rang + 1]['label'])) {
            $label_width = (float) $this->config['docfields'][$rang + 1]['label_width'];
        } else {
            $label_width = 0;
        }
        $this->y += $this->y_space + $height;
        return [
            $this->xStart() + $label_width,
            $this->yStart() + $this->y
        ];
    }

    /**
     * Renvoie la position (X, Y) de la prochaine ligne d'écriture dans une étiquette.
     * Changement d'étiquette si nécessaire.
     * Si la page est pleine, renvoie false.
     *
     * Usage dans Tcpdf:<code>
     * if (($xy = $label->NextPosition($i)) == false) {
     * $this->AddPage();
     * list($this->x, $this->y) = $label->NewPage();
     * } else {
     * list($this->x, $this->y) = $xy;
     * }</code>
     *
     * @param int $rang            
     * @return array|boolean
     */
    public function NextPosition($rang)
    {
        if ($rang < $this->lineCount()) {
            return $this->Ln($rang);
        }
        $this->currentColumn ++;
        if ($this->currentColumn > $this->config['doclabel']['cols_number']) {
            $this->currentRow ++;
            $this->currentColumn = 1;
        }
        if ($this->currentRow <= $this->config['doclabel']['rows_number']) {
            return $this->xyStart();
        }
        // nécessite un changement de page
        return false;
    }

    public function NewPage()
    {
        $this->currentColumn = 1;
        $this->currentRow = 1;
        $this->y = 0;
        return $this->xyStart();
    }

    public function wCell($rang)
    {
        $label_width = (float) $this->config['doclabel']['label_width'];
        $padding_left = (float) $this->config['doclabel']['padding_left'];
        $padding_right = (float) $this->config['doclabel']['padding_right'];
        if (empty($this->config['docfields'][$rang]['label'])) {
            $margin_left = (float) $this->config['docfields'][$rang]['label_width'];
        } else {
            $margin_left = 0;
        }
        return $label_width - $padding_left - $padding_right - $margin_left;
    }

    public function hCell($rang)
    {
        return (float) $this->config['docfields'][$rang]['height'];
    }

    public function alignCell($rang)
    {
        return $this->config['docfields'][$rang]['fieldname_align'];
    }

    public function stretchCell($rang)
    {
        return $this->config['docfields'][$rang]['fieldname_stretch'];
    }

    public function wLab($rang)
    {
        return (float) $this->config['doclabel']['label_width'];
    }

    public function txtLab($rang)
    {
        return (float) $this->config['doclabel']['label'];
    }

    public function alignLab($rang)
    {
        return $this->config['docfields'][$rang]['label_align'];
    }

    public function stretchLab($rang)
    {
        return $this->config['docfields'][$rang]['label_stretch'];
    }

    public function labelSpace($rang)
    {
        return (float) $this->config['docfields'][$rang]['label_space'];
    }

    public function parametresPhoto($rang)
    {
        return [
            'x' => $this->config['docfields'][$rang]['photo_x'],
            'y' => $this->config['docfields'][$rang]['photo_y'],
            'w' => $this->config['docfields'][$rang]['photo_w'],
            'h' => $this->config['docfields'][$rang]['photo_h'],
            'type' => $this->config['docfields'][$rang]['photo_type'],
            'align' => $this->config['docfields'][$rang]['photo_align'],
            'resize' => $this->config['docfields'][$rang]['photo_resize']
        ];
    }

    /**
     * Renvoie un tableau indexé à partir de 0
     *
     * @return array
     */
    public function descripteurData()
    {
        $cles = [
            'fieldname',
            'filter',
            'format',
            'label',
            'nature',
            'style'
        ];
        $result = [];
        foreach ($this->config['docfields'] as $field) {
            $descripteur = [];
            foreach ($cles as $key) {
                $descripteur[$key] = $field[$key];
            }
            $result[] = $descripteur;
        }
        return $result;
    }
}