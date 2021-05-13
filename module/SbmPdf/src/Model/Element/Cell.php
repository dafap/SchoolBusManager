<?php
/**
 * Description d'une cellule d'un tableau pdf à imprimer
 *
 * @project sbm
 * @package SbmPdf/src/Model/Element
 * @filesource Cell.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 jan. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Element;

use SbmPdf\Model\Element\Attribute\BackgroundAttribute;

class Cell implements ElementInterface
{
    use \SbmPdf\Model\Element\Attribute\BackgroundTrait;

    const VERTICAL_ALIGN_BOTTOM = 'bottom';

    const VERTICAL_ALIGN_MIDDLE = 'middle';

    const VERTICAL_ALIGN_TOP = 'top';

    /**
     *
     * @var Row
     */
    private $row;

    /**
     *
     * @var string
     */
    private $text;

    /**
     *
     * @var integer
     */
    private $colspan = 1;

    /**
     *
     * @var integer
     */
    private $rowspan = 1;

    /**
     * en user units
     *
     * @var float
     */
    private $width = 0;

    /**
     *
     * @var float
     */
    private $minHeight = 0;

    /**
     * en user units
     *
     * @var float
     */
    private $lineHeight = 0;

    /**
     *
     * @var BackgroundAttribute
     */
    private $background;

    /**
     *
     * @var integer|string
     */
    private $border = 0;

    /**
     *
     * @var float
     */
    private $borderWidth = 0;

    /**
     *
     * @var string
     */
    private $align = 'L';

    /**
     * vertical alignment T, M or B
     *
     * @var string
     */
    private $verticalAlign = self::VERTICAL_ALIGN_TOP;

    /**
     *
     * @var boolean
     */
    private $fitCell = false;

    /**
     *
     * @var integer
     */
    private $fill = 0;

    /**
     *
     * @var string
     */
    private $fontFamily;

    /**
     *
     * @var float
     */
    private $fontSize;

    /**
     *
     * @var string
     */
    private $fontWeight;

    /**
     *
     * @var array
     */
    private $padding = [];

    /**
     *
     * @return number
     */
    public function getBorderWidth()
    {
        return $this->borderWidth;
    }

    /**
     *
     * @param number $borderWidth
     */
    public function setBorderWidth($borderWidth): self
    {
        $this->borderWidth = $borderWidth;
        return $this;
    }

    public function __construct(Row $row, string $text)
    {
        $this->row = $row;
        $this->setText($text)
            ->setBorderWidth($row->getTable()
            ->getBorderWidth())
            ->setFontFamily($row->getTable()
            ->getFontFamily())
            ->setFontSize($row->getTable()
            ->getFontSize())
            ->setFontWeight($row->getTable()
            ->getFontWeight())
            ->setMinHeight($row->getTable()
            ->getLineHeight())
            ->setPadding($row->getTable()
            ->getPdf()
            ->getCellPaddings());
    }

    /**
     *
     * @return \SbmPdf\Model\Element\Row
     */
    public function getTableRow()
    {
        return $this->row;
    }

    /**
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     *
     * @return number
     */
    public function getColspan()
    {
        return $this->colspan;
    }

    /**
     *
     * @return number
     */
    public function getRowspan()
    {
        return $this->rowspan;
    }

    /**
     *
     * @return number
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     *
     * @return number
     */
    public function getMinHeight()
    {
        return $this->minHeight;
    }

    /**
     *
     * @return float
     */
    public function getLineHeight(): float
    {
        return $this->lineHeight ?: 0;
    }

    /**
     *
     * @return \SbmPdf\Model\Element\Attribute\BackgroundAttribute
     */
    public function getBackground()
    {
        if (! $this->background) {
            $this->background = new BackgroundAttribute($this);
        }
        return $this->background;
    }

    public function getBackgroundColor()
    {
        return $this->getBackground()->getColor();
    }

    public function getBackgroundDpi()
    {
        return $this->getBackground()->getDpi();
    }

    public function getBackgroundImage()
    {
        return $this->getBackground()->getImage();
    }

    /**
     *
     * @return number|string
     */
    public function getBorder()
    {
        return $this->border;
    }

    /**
     *
     * @return string
     */
    public function getAlign(): string
    {
        return $this->align;
    }

    /**
     *
     * @return string
     */
    public function getVerticalAlign(): string
    {
        return $this->verticalAlign;
    }

    /**
     * Indique si le texte de la cellule doit être adapté à la taille de la cellule.
     *
     * @return boolean
     */
    public function isFitCell()
    {
        return $this->fitCell;
    }

    /**
     *
     * @return number
     */
    public function getFill()
    {
        return $this->fill;
    }

    /**
     *
     * @return string
     */
    public function getFontFamily(): string
    {
        return $this->fontFamily;
    }

    /**
     * en PT
     *
     * @return number
     */
    public function getFontSize()
    {
        return $this->fontSize;
    }

    /**
     *
     * @return string
     */
    public function getFontWeight()
    {
        return $this->fontWeight;
    }

    /**
     * Renvoie un tableau de marges intérieures à la cellule
     * Exemple : ['T' => 0, 'R' => 1.000125, 'B' => 0, 'L' => 1.000125]
     *
     * @return array
     */
    public function getPadding()
    {
        return $this->padding;
    }

    /**
     *
     * @param string $text
     * @return self
     */
    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    /**
     *
     * @param number $colspan
     * @return self
     */
    public function setColspan($colspan = 1): self
    {
        if ($colspan < 1) {
            throw new \SbmPdf\Model\Exception\InvalidArgumentException(
                'Le colspan ne doit pas être plus petit que "1".');
        }
        $this->colspan = $colspan;
        return $this;
    }

    /**
     *
     * @param number $rowspan
     * @return self
     */
    public function setRowspan($rowspan): self
    {
        if ($rowspan < 1) {
            throw new \SbmPdf\Model\Exception\InvalidArgumentException(
                'Le rowspan ne doit pas être plus petit que "1".');
        }
        $this->rowspan = $rowspan;
        return $this;
    }

    /**
     *
     * @param number $width
     * @return self
     */
    public function setWidth($width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     *
     * @param number $minHeight
     * @return self
     */
    public function setMinHeight($minHeight): self
    {
        $this->lineHeight = $minHeight;
        $this->minHeight = $minHeight;
        return $this;
    }

    /**
     *
     * @param float $lineHeight
     * @return self
     */
    public function setLineHeight($lineHeight): self
    {
        $this->lineHeight = $lineHeight < $this->minHeight ? $this->minHeight : $lineHeight;
        return $this;
    }

    /**
     *
     * @param \SbmPdf\Model\Element\Attribute\BackgroundAttribute $background
     * @return self
     */
    public function setBackground($background): self
    {
        $this->background = $background;
        return $this;
    }

    /**
     * Indique si des bordures doivent être tracées autour du bloc de cellules.
     * <ul>
     * <li>0 : pas de bordure</li><li>1 : cadre</li>
     * </ul>
     * ou une chaine de caractères en contenant un ou plusieurs parmi :
     * <ul>
     * <li>L : gauche</li><li>T : haut</li><li>R : droite</li><li>B : bas</li>
     * </ul>
     *
     * @param int|string $border
     * @return self
     */
    public function setBorder($border): self
    {
        $this->border = $border;
        return $this;
    }

    /**
     * Les valeurs possibles sont :
     * <ul>
     * <li>L : à gauche</li><li>C : centré</li><li>R : à droite</li><li>J : justifié</li>
     * </ul>
     *
     * @param string $align
     * @return self
     */
    public function setAlign($align): self
    {
        $this->align = $align;
        return $this;
    }

    /**
     * Les valeurs possibles sont :
     * <ul>
     * <li>'top'</li><li>'bottom'</li><li>'middle' ou 'center'</li>
     * </ul>
     * Utiliser de préférence les constantes de cette classe.
     *
     * @param string $verticalAlign
     * @return self
     */
    public function setVerticalAlign($verticalAlign): self
    {
        $this->verticalAlign = $verticalAlign;
        return $this;
    }

    /**
     * Indique si le texte de la cellule doit être adapté à la largeur de la cellule.
     *
     * @param boolean $fitCell
     * @return self
     */
    public function setFitCell(bool $fitCell): self
    {
        $this->fitCell = $fitCell;
        return $this;
    }

    /**
     *
     * @param number $fill
     * @return self
     */
    public function setFill($fill): self
    {
        $this->fill = $fill;
        return $this;
    }

    /**
     *
     * @param string $fontFamily
     * @return self
     */
    public function setFontFamily(string $fontFamily): self
    {
        $this->fontFamily = $fontFamily;
        return $this;
    }

    /**
     * en PT
     *
     * @param number $fontSize
     * @return self
     */
    public function setFontSize($fontSize): self
    {
        $this->fontSize = $fontSize;
        return $this;
    }

    /**
     * Les valeurs permises sont :
     * <ul>
     * <li><i>normal</i> : Table::FONT_WEIGHT_NORMAL</li>
     * <li><i>gras</i> : Table::FONT_WEIGHT_BOLD</li>
     * </ul>
     *
     * @param string $fontWeight
     * @return self
     */
    public function setFontWeight(string $fontWeight): self
    {
        $this->fontWeight = $fontWeight;
        return $this;
    }

    /**
     * Définition analogue à la définition en CSS.
     * <ul>
     * <li>Le premier paramètre peut être un tableau de la forme
     * ['T' => 2, 'R' => 3, 'B' => 1, 'L' => 3] ou [2, 3, 1, 3] ayant le même sens
     * ou un nombre.</li>
     * <li>Si c'est un tableau, les autres paramètres sont ignorés.</li>
     * <li>Les valeurs <b>null</b> sont ignorées.</li>
     * <li>Si on ne passe qu'un paramètre numérique, il est affecté à tous.</li>
     * <li>Si on ne passe que deux paramètres, le premier est affecté à top et bottom et
     * le
     * second à right et left.</li>
     * </ul>
     *
     * @param array|number $paddingOrTop
     * @param number $right
     * @param number $bottom
     * @param number $left
     * @return self
     */
    public function setPadding($paddingOrTop, $right = null, $bottom = null, $left = null): self
    {
        if (is_array($paddingOrTop)) {
            $params = array_values($paddingOrTop);
        } else {
            $params = array_filter([
                $paddingOrTop,
                $right,
                $bottom,
                $left
            ]);
        }
        $n = count($params);
        if ($n == 1) {
            $params = array_fill(0, 4, $params[0]);
        } elseif ($n == 2) {
            $params[] = $params[0];
            $params[] = $params[1];
        } elseif ($n != 4) {
            throw new \SbmPdf\Model\Exception\BadMethodCallException(
                "Le nombre de paramètres ne convient pas. On en attend 1, 2 ou 4. On en a reçu $n.");
        }

        $this->padding = array_combine([
            'T',
            'R',
            'B',
            'L'
        ], $params);
        return $this;
    }

    /**
     * Renvoie une nouvelle cellule de la même ligne
     *
     * @param string $text
     * @return \SbmPdf\Model\Element\Cell
     */
    public function newCell(string $text = ''): Cell
    {
        return $this->row->newCell($text);
    }

    /**
     * Renvoie la ligne
     *
     * @return \SbmPdf\Model\Element\Row
     */
    public function endRow()
    {
        return $this->row->endRow();
    }
}