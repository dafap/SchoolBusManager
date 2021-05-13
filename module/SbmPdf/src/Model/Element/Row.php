<?php
/**
 * Description d'une ligne d'un tableau pdf à imprimer
 *
 * @project sbm
 * @package SbmPdf/src/Model/Element
 * @filesource Row.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 jan. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Element;

use SbmPdf\Model\Element\Attribute\BackgroundAttribute;

/**
 *
 * @author alain from naitsirch (naitsirch@e.mail.de)
 * @see https://github.com/naitsirch/tcpdf-extension
 */
class Row implements ElementInterface
{
    use \SbmPdf\Model\Element\Attribute\BackgroundTrait;

    /**
     *
     * @var Table
     */
    private $table;

    /**
     * tableau de Cell
     *
     * @var array
     */
    private $cells;

    /**
     * Attributs s'appliquant par défaut à toutes les cellules de la ligne
     *
     * @var array
     */
    private $cellsAttributes;

    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->cells = [];
        $this->cellsAttributes = [];
    }

    /**
     * Renvoie la Table contenant la Row
     *
     * @return \SbmPdf\Model\Element\Table
     */
    public function endRow(): Table
    {
        return $this->table;
    }

    /**
     * Renvoie la Table (idem endRow() )
     *
     * @return \SbmPdf\Model\Element\Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Renvoie la liste des Cell dans un array
     *
     * @return array
     */
    public function getCells()
    {
        return $this->cells;
    }

    /**
     * Crée et renvoie une nouvelle cellule après l'avoir archivée.
     * Prend en compte les attributs définis au niveau de cette classe dans la propriété
     * $cellsAttributes
     *
     * @param string $text
     * @return \SbmPdf\Model\Element\Cell
     */
    public function newCell(string $text = '')
    {
        $cell = new Cell($this, $text);
        foreach ($this->cellsAttributes as $attr => $value) {
            $method = 'set' . ucwords($attr);
            $cell->{$method}($value);
        }
        return $this->cells[] = $cell;
    }

    /**
     *
     * @return \SbmPdf\Model\Element\Attribute\BackgroundAttribute
     */
    public function getBackground()
    {
        if (! $this->background) {
            $this->cellsAttributes['background'] = new BackgroundAttribute($this);
        }
        return $this->cellsAttributes['background'];
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
        $this->cellsAttributes['align'] = $align;
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
        $this->cellsAttributes['border'] = $border;
        return $this;
    }

    /**
     *
     * @param number $borderWidth
     */
    public function setBorderWidth($borderWidth): self
    {
        $this->cellsAttributes['borderWidth'] = $borderWidth;
        return $this;
    }

    /**
     *
     * @param number $fill
     * @return self
     */
    public function setFill($fill): self
    {
        $this->cellsAttributes['fill'] = $fill;
        return $this;
    }

    /**
     *
     * @param string $fontFamily
     * @return self
     */
    public function setFontFamily(string $fontFamily): self
    {
        $this->cellsAttributes['fontFamily'] = $fontFamily;
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
        $this->cellsAttributes['fontSize'] = $fontSize;
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
        $this->cellsAttributes['fontWeight'] = $fontWeight;
        return $this;
    }

    /**
     *
     * @param float $lineHeight
     * @return self
     */
    public function setLineHeight($lineHeight): self
    {
        $this->cellsAttributes['lineHeight'] = $lineHeight;
        return $this;
    }

    /**
     *
     * @param number $minHeight
     * @return self
     */
    public function setMinHeight($minHeight): self
    {
        $this->cellsAttributes['minHeight'] = $minHeight;
        return $this;
    }

    /**
     * Pas de contrôle à ce niveau.
     * Tout ce fera dans Cell.
     *
     * @param number ...$args
     * @return \SbmPdf\Model\Element\Row
     */
    public function setPadding(...$args)
    {
        if (count($args) == 1) {
            $args = current($args);
        }
        $this->cellsAttributes['padding'] = $args;
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
        $this->cellsAttributes['verticalAlign'] = $verticalAlign;
        return $this;
    }

    /**
     *
     * @param number $width
     * @return self
     */
    public function setWidth($width): self
    {
        $this->cellsAttributes['width'] = $width;
        return $this;
    }
}