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
 * @date 18 mai 2020
 * @version 2020-2.6.0
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
     * Tableau des identifiants des sous-étiquettes
     *
     * @var int[]
     */
    protected $arraySublabels;

    /**
     * Ordonnées relatives à la zone d'écriture de l'étiquette ou de la sublabel
     *
     * @var float[]
     */
    protected $y;

    /**
     * Espacement entre les lignes d'écriture sur une étiquette
     *
     * @var float[]
     */
    protected $y_space;

    /**
     * Mémorise les calculs sur les sous-étiquettes pour optimisation
     *
     * @var array
     */
    protected $sublabelDescripteur;

    /**
     * Initialise la structure. Doit être suivi par : $label->setCurrentColumn($j); //
     * optionnel $label->setCurrentRow($k); // optionnel list($this->x, $this->y) =
     * $label->xyStart(); // nécessaire
     *
     * @param ServiceLocatorInterface $db_manager
     * @param int $documentId
     */
    public function __construct(ServiceLocatorInterface $db_manager, $documentId)
    {
        $this->db_manager = $db_manager;
        $this->documentId = $documentId;

        // Lecture des tables system document, doclabel et docfields pour initialiser les
        // paramètres de la structure
        /*
         * il ne semble pas être nécessaire d'utiliser la config du document dans cette
         * classe. (en réserve au cas où ...)
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
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $this->config['doclabel'] = require (__DIR__ . '/default/doclabels.inc.php');
        }
        $t = $this->db_manager->get('Sbm\Db\System\DocFields');
        try {
            $this->config['docfields'] = $t->getConfig($this->documentId);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $this->config['docfields'][0] = [];
        }
        $this->currentColumn = 1;
        $this->currentRow = 1;
        $this->y = [];
        $this->arraySublabels = $this->getSublabelIdx();
        $this->sublabelDescripteur = [];
        // préparation des lineCount. On ne compte pas les photos.
        $this->sublabelDescripteur['lineCount'] = array_combine($this->getSublabelIdx(),
            array_fill(0, count($this->config['doclabel']), 0));
        foreach ($this->sublabelDescripteur['lineCount'] as $sublabel => &$n) {
            foreach ($this->config['docfields'][$sublabel] as $docfield) {
                if ($docfield['nature'] < 2) {
                    $n ++;
                }
            }
        }
        $this->y_space = [];
        // Attention ! Ne pas utiliser arraySublabels à la place de la méthode
        // getSublabelIdx() afin de ne pas modifier son pointeur
        foreach ($this->getSublabelIdx() as $idx) {
            $lineCount = $this->sublabelDescripteur['lineCount'][$idx];
            if ($lineCount > 1) {
                $this->y_space[$idx] = ($this->writingAreaHeight($idx) -
                    $this->totalLineHeight($idx)) / ($lineCount - 1);
            } else {
                $this->y_space[$idx] = 0;
            }
        }
        $this->NewPage();
    }

    /**
     * Renvoie les index de sublabels. Il y a au moins l'index 0.
     *
     * @return array
     */
    public function getSublabelIdx()
    {
        return array_keys($this->config['doclabel']);
    }

    /**
     * Donne le nombre de lignes d'écriture dans la sous-étiquette courante. On n'a pas
     * compté pas les photos.
     *
     * @return int
     */
    public function lineCount(): int
    {
        $sublabel = current($this->arraySublabels);
        return $this->sublabelDescripteur['lineCount'][$sublabel];
    }

    /**
     * Hauteur de la sous-étiquette courante
     *
     * @return float
     */
    public function labelHeight(): float
    {
        $sublabel = current($this->arraySublabels);
        return (float) $this->config['doclabel'][$sublabel]['label_height'];
    }

    /**
     * Hauteur de la zone d'écriture dans la sous-étiquette $sublabel
     *
     * @param int $sublabel
     *            identifiant de la sous-étiquette
     * @return float
     */
    protected function writingAreaHeight(int $sublabel): float
    {
        $label_height = (float) $this->config['doclabel'][$sublabel]['label_height'];
        $padding_top = (float) $this->config['doclabel'][$sublabel]['padding_top'];
        $padding_bottom = (float) $this->config['doclabel'][$sublabel]['padding_bottom'];
        return $label_height - $padding_top - $padding_bottom;
    }

    /**
     * Renvoie la hauteur totale des lignes de la sous-étiquette $sublabel
     *
     * @param int $sublabel
     *            identifiant de la sous-étiquette
     * @return float
     */
    private function totalLineHeight(int $sublabel): float
    {
        $total = 0;
        foreach ($this->config['docfields'][$sublabel] as $field) {
            $total += $field['height'];
        }
        $writingAH = $this->writingAreaHeight($sublabel);
        if ($total > $writingAH) {
            $ratio = $writingAH / $total;
            foreach ($this->config['docfields'][$sublabel] as &$field) {
                $field['height'] *= $ratio;
            }
            return $writingAH;
        }
        return $total;
    }

    /**
     * Initialise la colonne courante de la planche d'étiquettes. Permet de fixer la
     * position de la première étiquette à tirer sur une planche partiellement utilisée.
     * Utile également pour tirer des duplicatas.
     *
     * @param int $n
     */
    public function setCurrentColumn(int $n): self
    {
        $this->currentColumn = $n;
        return $this;
    }

    /**
     * Initialise la ligne courante de la planche d'étiquettes. Permet de fixer la
     * position de la première étiquette à tirer sur une planche partiellement utilisée.
     * Utile également pour tirer des duplicatas.
     *
     * @param int $n
     */
    public function setCurrentRow(int $n): self
    {
        $this->currentRow = $n;
        return $this;
    }

    /**
     * Renvoie la position X origine pour la sous-étiquette courante
     *
     * @return float abcisse x en coordonnées absolue (page)
     */
    protected function xStart(): float
    {
        $sublabel = current($this->arraySublabels);
        $margin_left = (float) $this->config['doclabel'][$sublabel]['margin_left'];
        $label_width = (float) $this->config['doclabel'][$sublabel]['label_width'];
        $x_space = (float) $this->config['doclabel'][$sublabel]['x_space'];
        $padding_left = (float) $this->config['doclabel'][$sublabel]['padding_left'];
        $xa = $margin_left + ($this->currentColumn - 1) * ($label_width + $x_space);
        return $xa + $padding_left;
    }

    /**
     * Renvoie la position Y origine pour la sous-étiquette courante
     *
     * @return float ordonnée y en coorrdonnées absolue (page)
     */
    protected function yStart(): float
    {
        $sublabel = current($this->arraySublabels);
        $margin_top = (float) $this->config['doclabel'][$sublabel]['margin_top'];
        $label_height = (float) $this->config['doclabel'][$sublabel]['label_height'];
        $y_space = (float) $this->config['doclabel'][$sublabel]['y_space'];
        $padding_top = (float) $this->config['doclabel'][$sublabel]['padding_top'];
        $ya = $margin_top + ($this->currentRow - 1) * ($label_height + $y_space);
        return $ya + $padding_top;
    }

    /**
     * Renvoie la position (X, Y) d'origine de la sous-étiquette courante. Si la première
     * ligne est décalée (pas de label mais un label_width) alors X en tient compte et est
     * décalé d'autant que nécessaire.
     *
     * @formatter:off
     * Usage dans Tcpdf :
     * <pre>
     * list($this->x, $this->y) = $label->xyStart();
     * </pre>
     *
     * Pour un changement d'étiquette on fera :
     * <pre>
     * $label = new Etiquette($sm, $documentId);
     * list($this->x, $this->y) = $label->xyStart();
     * </pre>
     * @formatter:on
     *
     * @return float[] (x, y) en coordonnées absolue (page)
     */
    public function xyStart(): array
    {
        $sublabel = current($this->arraySublabels);
        if (empty($this->config['docfields'][$sublabel][0]['label'])) {
            $label_width = (float) $this->config['docfields'][$sublabel][0]['label_width'];
        } else {
            $label_width = 0;
        }
        $this->y[$sublabel] = 0;
        return [
            $this->xStart($sublabel) + $label_width,
            $this->yStart($sublabel)
        ];
    }

    /**
     * Renvoie la position (X, Y) d'une nouvelle ligne d'écriture dans la sous-étiquette
     * courante.
     *
     * @formatter:off
     * Usage dans Tcpdf :
     * <pre>
     * list($this->x, $this->y) = $label->Ln($i);
     * </pre>
     * où $i est le numéro de la ligne qu'on vient d'écrire
     * @formatter:on
     *
     * @param int $rang
     *            numéro de la donnée dans la sous-étiquette
     * @return float[] (x, y) en coordonnées absolue (page)
     */
    public function Ln(int $rang): array
    {
        $sublabel = current($this->arraySublabels);
        $height = (float) $this->config['docfields'][$sublabel][$rang]['height'];
        if (array_key_exists($rang + 1, $this->config['docfields'][$sublabel]) &&
            empty($this->config['docfields'][$sublabel][$rang + 1]['label'])) {
            $label_width = (float) $this->config['docfields'][$sublabel][$rang + 1]['label_width'];
        } else {
            $label_width = 0;
        }
        $this->y[$sublabel] += $this->y_space[$sublabel] + $height;
        return [
            $this->xStart() + $label_width,
            $this->yStart() + $this->y[$sublabel]
        ];
    }

    /**
     * Renvoie la position (X, Y) de la prochaine ligne d'écriture dans la sous-étiquette
     * courante. Changement de sous-étiquette si nécessaire. Changement d'étiquette si
     * nécessaire. Si la page est pleine, renvoie false.
     *
     * @formatter:off
     * Usage dans Tcpdf :
     * <pre>
     * if (($xy = $label->NextPosition($i)) == false) {
     *     $this->AddPage();
     *     list($this->x, $this->y) = $label->NewPage();
     * } else {
     *     list($this->x, $this->y) = $xy;
     * }
     * </pre>
     * @formatter:on
     *
     * @param int $rang
     *            numéro de la donnée dans la sous-étiquette
     * @return array|number[]|boolean
     */
    public function NextPosition(int $rang)
    {
        if ($rang < $this->lineCount()) {
            return $this->Ln($rang);
        }
        // changemant de sous-étiquette
        $sublabel = next($this->arraySublabels);
        // $rang = 0;
        if ($sublabel !== false) {
            return $this->xyStart();
        }
        // changement d'étiquette dans la planche d'étiquettes
        $sublabel = reset($this->arraySublabels);
        $this->currentColumn ++;
        if ($this->currentColumn > $this->config['doclabel'][$sublabel]['cols_number']) {
            $this->currentRow ++;
            $this->currentColumn = 1;
        }
        if ($this->currentRow <= $this->config['doclabel'][$sublabel]['rows_number']) {
            return $this->xyStart();
        }
        // nécessite un changement de page. On appellera NewPage() après la création de la
        // nouvelle page Pdf
        return false;
    }

    /**
     * Initialisation des propriétés lors du passage à une nouvelle page.
     *
     * @return array|number[]
     */
    public function NewPage(): array
    {
        $this->currentColumn = 1;
        $this->currentRow = 1;
        foreach ($this->arraySublabels as $sublabel) {
            $this->y[$sublabel] = 0;
        }
        return $this->xyStart();
    }

    /**
     * Donne la largeur de la cellule contenant la donnée de rang $rang de la
     * sous-étiquette courante
     *
     * @param int $rang
     *            numéro de la donnée dans la sous-étiquette
     * @return float
     */
    public function wCell(int $rang): float
    {
        $sublabel = current($this->arraySublabels);
        $label_width = (float) $this->config['doclabel'][$sublabel]['label_width'];
        $padding_left = (float) $this->config['doclabel'][$sublabel]['padding_left'];
        $padding_right = (float) $this->config['doclabel'][$sublabel]['padding_right'];
        if (empty($this->config['docfields'][$sublabel][$rang]['label'])) {
            $margin_left = (float) $this->config['docfields'][$sublabel][$rang]['label_width'];
        } else {
            $margin_left = 0;
        }
        return $label_width - $padding_left - $padding_right - $margin_left;
    }

    /**
     * Donne la hauteur de la cellule contenant la donnée de rang $rang de la
     * sous-étiquette courante
     *
     * @param int $rang
     *            numéro de la donnée dans la sous-étiquette
     * @return float
     */
    public function hCell(int $rang): float
    {
        $sublabel = current($this->arraySublabels);
        return (float) $this->config['docfields'][$sublabel][$rang]['height'];
    }

    /**
     * Donne l'alignement de la donnée de rang $rang de la sous-étiquette courante
     *
     * @param int $rang
     *            numéro de la donnée dans la sous-étiquette
     * @return string
     */
    public function alignCell(int $rang): string
    {
        $sublabel = current($this->arraySublabels);
        return $this->config['docfields'][$sublabel][$rang]['fieldname_align'];
    }

    /**
     * Donne le paramètre d'extension (stretch) de la donnée de rang $rang de la
     * sous-étiquette courante
     *
     * @param int $rang
     *            numéro de la donnée dans la sous-étiquette
     * @return int
     */
    public function stretchCell(int $rang): int
    {
        $sublabel = current($this->arraySublabels);
        return $this->config['docfields'][$sublabel][$rang]['fieldname_stretch'];
    }

    /**
     * Largeur de la sous-étiquette courante
     *
     * @return float
     */
    public function wLab(): float
    {
        $sublabel = current($this->arraySublabels);
        return (float) $this->config['doclabel'][$sublabel]['label_width'];
    }

    /**
     * Style du bord de la sous-étiquette courante
     *
     * @param callable $convertColor
     * @return array
     */
    public function borderStyle(callable $convertColor): array
    {
        $sublabel = current($this->arraySublabels);
        switch ($this->config['doclabel'][$sublabel]['border_dash']) {
            case 2:
                $dash = '1,3';
                break;
            case 1:
                $dash = '3,3';
                break;
            default:
                $dash = 0;
        }
        return [
            'all' => [
                'width' => $this->config['doclabel'][$sublabel]['border_width'],
                'dash' => $dash,
                'color' => $convertColor(
                    $this->config['doclabel'][$sublabel]['border_color'])
            ]
        ];
    }

    /**
     * Indique si la sous-étiquette courante a un bord (cadre rectangulaire)
     *
     * @return bool
     */
    public function hasBorder(): bool
    {
        $sublabel = current($this->arraySublabels);
        return $this->config['doclabel'][$sublabel]['border'];
    }

    /**
     * Donne l'alignement du label de la donnée de rang $rang de la sous-étiquette
     * courante
     *
     * @param int $rang
     *            rang de la donnée dans la sous-étiquette
     * @return string
     */
    public function alignLab(int $rang): string
    {
        $sublabel = current($this->arraySublabels);
        return $this->config['docfields'][$sublabel][$rang]['label_align'];
    }

    /**
     * Donne le paramètre d'extension (stretch) de l'étiquette de la donnée de rang $rang
     * dans la sous-étiquette courante
     *
     * @param int $rang
     *            rang de la donnée dans la sous-étiquette
     * @return int
     */
    public function stretchLab(int $rang): int
    {
        $sublabel = current($this->arraySublabels);
        return $this->config['docfields'][$sublabel][$rang]['label_stretch'];
    }

    /**
     * Espacement des caractères pour le label de la donnée de rang $rang de la
     * sous-étiquette courante
     *
     * @param int $rang
     *            rang de la donnée dans la sous-étiquette
     * @return float
     */
    public function labelSpace(int $rang): float
    {
        $sublabel = current($this->arraySublabels);
        return (float) $this->config['docfields'][$sublabel][$rang]['label_space'];
    }

    /**
     * Pour la photo de la sous-étiquette courante numéro $rang, renvoie les paramètres
     *
     * @param int $rang
     *            rang de la photo dans la sous-étiquette
     * @return array
     */
    public function parametresPhoto(int $rang): array
    {
        $sublabel = current($this->arraySublabels);
        return [
            'x' => $this->config['docfields'][$sublabel][$rang]['photo_x'],
            'y' => $this->config['docfields'][$sublabel][$rang]['photo_y'],
            'w' => $this->config['docfields'][$sublabel][$rang]['photo_w'],
            'h' => $this->config['docfields'][$sublabel][$rang]['photo_h'],
            'type' => $this->config['docfields'][$sublabel][$rang]['photo_type'],
            'align' => $this->config['docfields'][$sublabel][$rang]['photo_align'],
            'resize' => $this->config['docfields'][$sublabel][$rang]['photo_resize']
        ];
    }

    /**
     * Renvoie un tableau indexé sur les sublabels de tableau de descripteurs de champ.
     * Chaque descripteur de champ est un tableau associatif dont les clés sont celles du
     * tableau $cles.
     *
     * @return array
     */
    public function descripteurData(): array
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
        foreach ($this->config['docfields'] as $sublabel => $arrayDocfields) {
            $resultSublabel = [];
            foreach ($arrayDocfields as $field) {
                $descripteur = [];
                foreach ($cles as $key) {
                    $descripteur[$key] = $field[$key];
                }
                $resultSublabel[] = $descripteur;
            }
            $result[$sublabel] = $resultSublabel;
        }
        return $result;
    }

    /**
     * Renvoie la chaine à mettre en filigrane (ou chaine vide s'il n'y en a pas) dans la
     * sous-étiquette courante
     *
     * @return string
     */
    public function getFiligrane(): string
    {
        $sublabel = current($this->arraySublabels);
        return $this->config['doclabels'][$sublabel]['filignrane'];
    }
}