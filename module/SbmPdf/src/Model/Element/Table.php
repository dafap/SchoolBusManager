<?php
/**
 * Table à imprimer en pdf
 *
 * Cette table est composée de lignes Row créées par newRow().
 * Elle possède quelques attributs qui s'appliquent par défaut à toutes les lignes
 * du tableau rendu : borderWidth, fontFamily, fontSize, fontWeight, width,
 * widthPercentage
 *
 * Exemple d'utilisation :
 * $pdf->addPage();
 * $table = new Table($pdf);
 * $table->newRow()
 * ->newCell()
 * ->setText('Nom')
 * ->setFontWeight('bold')
 * ->setWidth(200)
 * ->end()
 * ->newCell()
 * ->setText('Prénom')
 * ->setFontWeight('bold')
 * ->setWidth(200)
 * ->end()
 * ->newCell()
 * ->setText('DateN')
 * ->setFontWeight('bold')
 * ->setWidth(200)
 * ->end()
 * ->newCell()
 * ->setText('Email')
 * ->setFontWeight('bold')
 * ->setWidth(200)
 * ->end()
 * ->end()
 * ->newRow() -> ...;
 * $table(); // pour écrire la table dans la page pdf
 *
 *
 * @project sbm
 * @package SbmPdf/src/Model/Element
 * @filesource Table.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol <dafap@free.fr>
 * @date 19 fév. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Element;

use SbmBase\Model\StdLib;
use SbmPdf\Model\Tcpdf;
use Zend\Stdlib\ArrayObject;

class Table
{
    use \SbmPdf\Model\Tcpdf\TcpdfTrait;

    const FONT_WEIGHT_NORMAL = 'normal';

    const FONT_WEIGHT_BOLD = 'bold';

    /**
     *
     * @var Tcpdf
     */
    private $pdf;

    /**
     *
     * @var callable
     */
    private $pageBreakCallBack;

    /**
     * Tableau de Row
     *
     * @var array;
     */
    private $rows;

    /**
     *
     * @var number
     */
    private $borderWidth;

    /**
     *
     * @var string
     */
    private $fontFamily;

    /**
     * in points (not in user units)
     *
     * @var float
     */
    private $fontSize;

    /**
     * une constante FONT_WEIGHT_NORMAL ou FONT_WEIGNT_BOLD de cette classe
     *
     * @var string
     */
    private $fontWeight;

    /**
     * Sauvegarde de la configuration courante à l'entrée
     *
     * @var array
     */
    private $fontSettings;

    /**
     * in user units
     *
     * @var float
     */
    private $lineHeight;

    /**
     * Largeur du tableau
     *
     * @var number
     */
    private $width;

    /**
     * Indique si la largeur est en pourcentage de la largeur de la page
     *
     * @var bool
     */
    private $widthPercentage;

    /**
     * Valeur interne pour prepare()
     *
     * @var bool
     */
    private $prepared;

    /**
     * Valeur interne pour getWidth()
     *
     * @var string
     */
    private $xPosition;

    /**
     *
     * @var ArrayObject
     */
    private $structure;

    public function __construct(Tcpdf $pdf)
    {
        $this->pdf = $pdf;
        $this->rows = [];
        $this->xPosition = $pdf->GetX();
        $this->prepared = false;
        $this->setBorderWidth($pdf->GetLineWidth())
            ->setFontFamily($pdf->getFontFamily())
            ->setFontSize($pdf->getFontSizePt())
            ->setFontWeight(
            strpos($pdf->getFontStyle(), 'B') !== false ? self::FONT_WEIGHT_BOLD : self::FONT_WEIGHT_NORMAL);
    }

    /**
     * Ecrit la table dans la page pdf.
     * Change de page si nécessaire.
     *
     * @return \SbmPdf\Model\Tcpdf
     */
    public function __invoke()
    {
        $this->prepare()->saveFontSettings();
        foreach ($this->structure->data as $r => $row) {
            // $needHeight = $this->structure->breakingHeights[$r];
            $y = $this->pdf->GetY();
            $x = $this->xPosition;
            $page = $this->pdf->getPage();
            // $remainingHeight = $this->getRemainingYPageSpace($this->pdf, $page, $y);
            if ($this->structure->breakingHeights[$r] >
                $this->getRemainingYPageSpace($this->pdf, $page, $y)) {
                $this->pdf->AddPage();
                $this->execPageBreakCallback($r);
                $y = $this->pdf->GetY();
                $page = $this->pdf->getPage();
            }
            foreach ($row as $c => $cell) {
                if ($cell instanceof Cell) {
                    $this->printCell($cell, $page, $x, $y);
                    // $this->pdf->MultiCell(16.3464, 13.7, 'Test MultiCel', 0, 'J',
                    // false, 1, 20, 20);
                }
                $x += $this->structure->colWidths[$c];
            }
            $this->pdf->setXY($this->xPosition, $y + $this->structure->rowHeights[$r]);
        }
        $this->restoreFontSettings();
        return $this->pdf;
    }

    /**
     * Renvoie un nouvel objet Row et l'archive dans la liste
     *
     * @return \SbmPdf\Model\Element\Row
     */
    public function newRow()
    {
        $row = new Row($this);
        $row->setBorderWidth($this->getBorderWidth())
            ->setFontFamily($this->getFontFamily())
            ->setFontSize($this->getFontSize())
            ->setFontWeight($this->getFontWeight())
            ->setLineHeight($this->getLineHeight());
        return $this->rows[] = $row;
    }

    /**
     *
     * @return \SbmPdf\Model\Tcpdf
     */
    public function getPdf()
    {
        return $this->pdf;
    }

    /**
     * Tableau de Row
     *
     * @return \SbmPdf\Model\Element\Row[];
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * ArrayObject défini dans la méthode computeTable()
     *
     * @return \Zend\Stdlib\ArrayObject
     */
    public function getStructure(): ArrayObject
    {
        return $this->structure;
    }

    /**
     *
     * @return mixed
     */
    public function getBorderWidth()
    {
        return $this->borderWidth;
    }

    /**
     * Renvoie un tableau de largeurs de colonnes en tenant compte de la largeur du
     * tableau et de la page.
     *
     * @throws \SbmPdf\Model\Exception\UnderflowException
     * @return array|number
     */
    private function getColumnWidths(): array
    {
        $margins = $this->pdf->getMargins();
        $maxWidth = $this->pdf->getPageWidth() - $margins['left'] - $margins['right'];
        $tableWidth = $this->getWidth() ?: $maxWidth;
        if ($tableWidth > $maxWidth) {
            throw new \SbmPdf\Model\Exception\UnderflowException(
                'Le tableau ne rentre pas ; il est trop large.');
        }
        $colWidths = $this->getRawColumnWidths($tableWidth);
        return $colWidths;
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
    public function getFontWeight(): string
    {
        return $this->fontWeight;
    }

    /**
     *
     * @return mixed
     */
    public function getLineHeight()
    {
        return $this->lineHeight ?: 0;
    }

    /**
     *
     * @return callable
     */
    public function getPageBreakCallBack()
    {
        return $this->pageBreakCallBack;
    }

    /**
     * in user units
     *
     * @return number|null
     */
    public function getWidth()
    {
        if (is_null($this->width)) {
            return null;
        } elseif ($this->isWidthPercentage()) {
            $margins = $this->getPdf()->getMargins();
            $maxWidth = $this->getPdf()->getPageWidth() - $margins['right'] -
                $this->xPosition;
            return $this->width / 100 * $maxWidth;
        } else {
            return $this->width;
        }
    }

    /**
     *
     * @return bool
     */
    public function isWidthPercentage(): bool
    {
        return $this->widthPercentage;
    }

    /**
     *
     * @param callable $pageBreakCallBack
     */
    public function setPageBreakCallBack($callBack): self
    {
        $this->pageBreakCallBack = $callBack;
        return $this;
    }

    /**
     *
     * @param \SbmPdf\Model\Element\Row[]; $rows
     */
    public function setRows($rows): self
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     *
     * @param string $fontFamily
     */
    public function setFontFamily($fontFamily): self
    {
        $this->fontFamily = $fontFamily;
        return $this;
    }

    /**
     *
     * @param number $fontSize
     */
    public function setFontSize($fontSize): self
    {
        $this->fontSize = $fontSize;
        return $this;
    }

    /**
     *
     * @param string $fontWeight
     */
    public function setFontWeight(string $fontWeight): self
    {
        $this->fontWeight = $fontWeight;
        return $this;
    }

    /**
     *
     * @param mixed $lineHeight
     */
    public function setLineHeight($lineHeight): self
    {
        $this->lineHeight = $lineHeight;
        return $this;
    }

    /**
     *
     * @param number $width
     * @param bool $widthPercentage
     */
    public function setWidth($width, $widthPercentage = false): self
    {
        if (! is_numeric($width)) {
            throw \SbmPdf\Model\Exception\InvalidArgumentException(
                'La largeur doit être numérique.');
        }
        $this->width = $width;
        $this->widthPercentage = (bool) $widthPercentage;
        return $this;
    }

    /**
     *
     * @param mixed $borderWidth
     */
    public function setBorderWidth($borderWidth): self
    {
        $this->borderWidth = $borderWidth;
        return $this;
    }

    /**
     * Vide les lignes d'une table et tous les résultats des calculs internes.
     * Utile dans execPageBreakCallback.
     */
    public function resetTable()
    {
        $this->rows = [];
        $this->structure = [];
        $this->prepared = false;
        $this->pageBreakCallBack = null;
        return $this;
    }

    public function prepare()
    {
        if (! $this->prepared) {
            $this->computeTable();
            $this->prepared = true;
        }
        return $this;
    }

    /**
     * Sauvegarde de la configuration de la police de caractères courante
     *
     * @return \SbmPdf\Model\Element\Table
     */
    private function saveFontSettings()
    {
        $this->fontSettings = [
            'family' => $this->getPdf()->getFontFamily(),
            'style' => $this->getPdf()->getFontStyle(),
            'size' => $this->getPdf()->getFontSize(),
            'size_pt' => $this->getPdf()->getFontSizePt(),
            'cell_height_ratio' => $this->getPdf()->getCellHeightRatio(),
            'cell_padding' => $this->getPdf()->getCellPaddings()
        ];
        return $this;
    }

    private function restoreFontSettings()
    {
        if (! $this->fontSettings) {
            throw new \SbmPdf\Model\Exception\RuntimeException(
                'Pas de configuration de police de caractères sauvegardée pour le moment.');
        }
        $this->getPdf()->SetFont($this->fontSettings['family'],
            $this->fontSettings['style'], $this->fontSettings['size_pt']);
        $this->getPdf()->setCellHeightRatio($this->fontSettings['cell_height_ratio']);
        $this->getPdf()->setCellPaddings($this->fontSettings['cell_padding']['L'],
            $this->fontSettings['cell_padding']['T'],
            $this->fontSettings['cell_padding']['R'],
            $this->fontSettings['cell_padding']['B']);
        return $this;
    }

    /**
     * Prépare un tableau virtuel à 2 dimensions où les cellules sont positionnées à leur
     * bonne place, même lorsqu'elles sont fusionnées.
     * Lorsqu'une cellule n'est pas fusionnée, elle contient l'objet Cell la décrivant.
     * Lorsque des cellules sont fusionnées, l'objet Cell est inscrit dans la case
     * supérieure gauche de la zone, les autres cellules contiennent les coordonnées de
     * cette cellule dans ArrayObject.
     */
    private function computeTable()
    {
        $this->saveFontSettings();
        $this->structure = new ArrayObject(
            [
                'data' => [],
                'nbCols' => 0,
                'nbRows' => 0,
                'colWidths' => [],
                'rowHeights' => [],
                'breakingHeights' => []
            ], ArrayObject::ARRAY_AS_PROPS);
        $this->createStructureTable();
        $this->restoreFontSettings();
    }

    /**
     * La largeur d'une colonne doit être spécifiée dans la première cellule non fusionnée
     * (colspan == 1) de la colonne (ligne de rang le plus petit), sinon on prendra la
     * largeur moyenne nécessaire à l'écriture du texte.
     */
    private function createStructureTable()
    {
        $nc = 0;
        $nr = count($this->rows);
        $array = array_fill(0, $nr, []);
        foreach ($this->rows as $r => $row) {
            foreach ($row->getCells() as $c => $cell) {
                $idx_c = 0;
                while (array_key_exists($idx_c, $array[$r])) {
                    $idx_c ++;
                }
                for ($i = 0; $i < $cell->getColspan(); $i ++) {
                    for ($j = 0; $j < $cell->getRowspan(); $j ++)
                        if ($i == 0 && $j == 0) {
                            $array[$r + $j][$idx_c] = $cell;
                        } else {
                            $array[$r + $j][$idx_c] = new ArrayObject(
                                [
                                    'row' => $r,
                                    'column' => $c
                                ], ArrayObject::ARRAY_AS_PROPS);
                        }
                    $idx_c ++;
                }
            }
            $n = count($array[$r]);
            if ($nc < $n) {
                $nc = $n;
            }
            ksort($array[$r]);
        }
        // complète la table si nécessaire
        for ($i = 0; $i < $nr; $i ++) {
            for ($j = 0; $j < $nc; $j ++) {
                if (! StdLib::array_keys_exists([
                    $i,
                    $j
                ], $array)) {
                    $array[$i][$j] = null;
                }
            }
        }
        $this->structure->data = $array;
        $this->structure->nbCols = $nc;
        $this->structure->nbRows = $nr;
        $this->structure->colWidths = $this->getColumnWidths();
        $this->structure->rowHeights = $this->getRowHeights();
        $this->correctWidths();
        $this->correctHeights();
    }

    private function insereTable(Table $t, int $r)
    {
        // insère le tableau table dans la structure de cette Table
    }

    /**
     * Corrige les largeurs des cellules de la table dans la structure.
     */
    private function correctWidths()
    {
        for ($r = 0; $r < $this->structure->nbRows; $r ++) {
            for ($c = 0; $c < $this->structure->nbCols; $c ++) {
                $cell = $this->structure->data[$r][$c];
                if (! $cell instanceof Cell)
                    continue;
                $width = 0;
                for ($i = 0; $i < $cell->getColspan(); $i ++) {
                    $width += $this->structure->colWidths[$c + $i];
                }
                $this->structure->data[$r][$c] = $cell->setWidth($width);
            }
        }
    }

    private function correctHeights()
    {
        $boundRows = array_combine(array_keys($this->structure->data),
            array_keys($this->structure->data));
        for ($r = 0; $r < $this->structure->nbRows; $r ++) {
            for ($c = 0; $c < $this->structure->nbCols; $c ++) {
                $cell = $this->structure->data[$r][$c];
                if ($cell instanceof Cell) {
                    $height = 0;
                    for ($i = 0; $i < $cell->getRowspan(); $i ++) {
                        $height += $this->structure->rowHeights[$r + $i];
                    }
                    $this->structure->data[$r][$c] = $cell->setLineHeight($height);
                } elseif ($cell instanceof ArrayObject) {
                    if ($cell->row < $boundRows[$r]) {
                        $boundRows[$r] = $boundRows[$cell->row];
                    }
                }
            }
        }
        $breakingHeights = array_fill(0, $this->structure->nbRows, 0);
        for ($r = $this->structure->nbRows - 1; $r >= 0; $r --) {
            $breakingHeights[$boundRows[$r]] += $this->structure->rowHeights[$r];
            if ($boundRows[$r] < $r) {
                $breakingHeights[$r] = 0;
            }
        }
        $this->structure->breakingHeights = $breakingHeights;
    }

    /**
     * Renvoie un tableau contenant les largeurs brutes des colonnes.
     * On ne prend pas en compte les cellules fusionnées sur plusieurs colonnes :
     * (colspan >1).
     *
     * @param float $maxWidth
     * @return array
     */
    private function getRawColumnWidths(float $maxWidth): array
    {
        $specWidths = array_fill(0, $this->structure->nbCols, 0);
        $strLenColWiths = [];
        for ($r = 0; $r < $this->structure->nbRows; $r ++) {
            for ($c = 0; $c < $this->structure->nbCols; $c ++) {
                $cell = $this->structure->data[$r][$c];
                if (! $cell instanceof Cell)
                    continue;
                if ($cell->getColspan() > 1)
                    continue;
                if ($cell->getWidth() && ! $specWidths[$c]) {
                    $specWidths[$c] = $cell->getWidth();
                } elseif (! $specWidths[$c]) {
                    $paddings = $cell->getPadding();
                    $fontName = $cell->getFontFamily();
                    $fontStyle = $cell->getFontWeight() == self::FONT_WEIGHT_BOLD ? 'B' : '';
                    $fontSize = $cell->getFontSize();
                    $strLenColWiths[$c][] = $this->pdf->GetStringWidth($cell->getText(),
                        $fontName, $fontStyle, $fontSize) + $paddings['L'] + $paddings['R'] +
                        2 * $cell->getBorderWidth();
                }
            }
        }
        // maxiWidth est la largeur permettant de placer la chaîne la plus large
        // averageWidth est la largeur moyenne des cellules non vides
        $maxiWidths = $averageWidths = array_fill(0, $this->structure->nbCols, 0);
        for ($c = 0; $c < $this->structure->nbCols; $c ++) {
            if ($specWidths[$c]) {
                // largeur spécifiée
                $maxiWidths[$c] = $averageWidths[$c] = $specWidths[$c];
            } elseif (array_key_exists($c, $strLenColWiths)) {
                // largeur moyenne des cellules non vides
                $tmpArray = array_filter($strLenColWiths[$c]);
                if (count($tmpArray)) {
                    $averageWidths[$c] = array_sum($tmpArray) / count($tmpArray);
                }
                foreach ($tmpArray as $value) {
                    if ($value > $maxiWidths[$c]) {
                        $maxiWidths[$c] = $value;
                    }
                }
            }
        }
        // corriger proportionellement les largeurs de colonnes si nécessaire
        $maxWidthSum = array_sum($maxiWidths);
        if ($maxWidthSum > $maxWidth) {
            $averageWidthSum = array_sum($averageWidths);
            if ($averageWidthSum > $maxWidth) {
                foreach ($averageWidths as &$width) {
                    $width = ($width / $maxWidthSum) * $maxWidth;
                }
            }
            return $averageWidths;
        } else {
            return $maxiWidths;
        }
    }

    /**
     * Renvoie un tableau de hauteurs de lignes en tenant compte de la hauteur de la page.
     *
     * @throws \SbmPdf\Model\Exception\LengthException
     * @return array
     */
    private function getRowHeights(): array
    {
        $colWidths = $this->structure->colWidths;
        $rowHeights = array_fill(0, $this->structure->nbRows, 0);
        for ($r = 0; $r < $this->structure->nbRows; $r ++) {
            for ($c = 0; $c < $this->structure->nbCols; $c ++) {
                $cell = $this->structure->data[$r][$c];
                if (! $cell instanceof Cell)
                    continue;
                if ($this->structure->data[$r][$c]->getRowspan() > 1)
                    continue;
                $padding = $cell->getPadding();
                $lines = $this->pdf->getNumLines($cell->getText(), $colWidths[$r][$c],
                    false, false,
                    [
                        'T' => 0,
                        'R' => $padding['R'],
                        'B' => 0,
                        'L' => $padding['L']
                    ], $cell->getBorder());
                $height = $lines * $cell->getLineHeight() *
                    ($cell->getFontSize() / $this->pdf->getScaleFactor() *
                    $this->pdf->getCellHeightRatio());
                $height += $padding['T'] + $padding['B'];
            }
            if ($height > $this->getPageContentHeight($this->pdf)) {
                $msg = "La hauteur nécessaire pour inscrire le contenu d'une cellule " .
                    "dépasse la hauteur de la page. Ce cas n'est pas pris en charge " .
                    "; vous devez diviser votre texte en plusieurs morceaux. " .
                    "Le contenu de cette cellule est \"%s\".";
                $content = mb_substr($cell->getText(), 0, 250);
                if (mb_strlen($cell->getText()) > 250) {
                    $content .= '[...]';
                }
                throw new \SbmPdf\Model\Exception\LengthException(sprintf($msg, $content));
            }
            $rowHeights[$r] = $height;
        }
        return $rowHeights;
    }

    /**
     * Ecrit la cellule dans la page du pdf
     *
     * @param Cell $cell
     * @param int $page
     * @param float $x
     * @param float $y
     * @param float $width
     * @param float $height
     */
    private function printCell(Cell $cell, $page, $x, $y)
    {
        if ($this->pdf->getPage() != $page) {
            $this->pdf->setPage($page);
        }
        $this->pdf->SetFont($cell->getFontFamily(),
            $cell->getFontWeight() == self::FONT_WEIGHT_BOLD ? 'B' : '',
            $cell->getFontSize());
        $padding = $cell->getPadding();
        $this->pdf->setCellPaddings($padding['L'], $padding['T'], $padding['R'],
            $padding['B']);
        $backgroundColor = $this->convertColor($cell->getBackgroundColor());
        if ($backgroundColor) {
            $this->pdf->SetFillColorArray($backgroundColor);
        }
        // Il faut recalculer la hauteur de ligne parce qu'il y a un bug dans
        // TCPDF qui réinitialise la hauteur des lignes (lors de l'écriture des lignes)
        // avant de noter la hauteur de la ligne courante dans MultiCell
        $this->pdf->setLastH(
            $this->pdf->getCellHeight(
                $cell->getLineHeight() *
                ($cell->getFontSize() / $this->pdf->getScaleFactor()), false));
        // écrire la cell dans le pdf par la méthode MultiCell(). A noter que maxh doit
        // être défini pour que 'bottom' et 'middle' fonctionnent. A noter qu'il faut
        // aussi que maxh soit légèrement supérieur à lineHeight sinon ça buggue.
        $this->pdf->MultiCell($cell->getWidth(), $cell->getLineHeight(), $cell->getText(),
            $cell->getBorder(), $cell->getAlign(), /*$backgroundColor !== null*/false, 1,
            $x, $y, true, false, false, true, $cell->getLineHeight() + 0.001,
            strtoupper(substr($cell->getVerticalAlign(), 0, 1)), $cell->isFitCell());
    }

    /**
     * Exécute une fonction callback au changement de page
     *
     * @param int $rowIndex
     * @param number $cellWidths
     * @param number $rowHeights
     * @param array $rowspanInfos
     * @param array $rows
     */
    private function execPageBreakCallback($rowIndex)
    {
        if (! $callback = $this->getPageBreakCallback()) {
            return;
        }
        // lance la méthode de rappel sur une table vidée ayant les mêmes attributs que
        // cette table.
        $table = clone $this;
        $callback($table->resetTable());
        $numberOfTableRows = count($table->getRows());
        if ($numberOfTableRows > 0) {
            $table();
        }
    }
}