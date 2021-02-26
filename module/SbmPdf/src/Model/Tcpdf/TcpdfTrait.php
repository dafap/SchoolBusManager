<?php
/**
 * Méthodes communes à diverses classes
 *
 * @project sbm
 * @package SbmPdf/src/Model/Tcpdf
 * @filesource TcpdfTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 fév. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Tcpdf;

trait TcpdfTrait
{

    /**
     * Convertit une couleur hex RGB en un tableau decimal de couloeurs RGB.
     * Si la valeur est vide ou si la chaine contient 'transparent' retourne null.
     * Ne sait pas convertir les autres chaines de nom de couleur.
     *
     * @example convertColor('#ff00ff') => [255, 0, 255]
     * @example convertColor('#aaa') => [170, 170, 170]
     * @example convertColor([120, 50, 20]) => [120, 50, 20]
     * @example convertColor('transparent') => null
     *
     * @param string|array $color
     *
     * @return array|null
     */
    public function convertColor($color)
    {
        if (empty($color) || 'transparent' === $color) {
            return null;
        }
        if (is_string($color)) {
            if (array_key_exists($color, \TCPDF_COLORS::$webcolor)) {
                $color = \TCPDF_COLORS::$webcolor[$color];
            }
            $color = ltrim($color, '#');
            while (strlen($color) < 6) {
                $color .= substr($color, - 1);
            }
            return array_map('hexdec', str_split($color, 2));
        }
        return $color;
    }

    /**
     * La taille donnée en user units est convertie en pixels
     *
     * @param number $widthInUserUnit
     * @param string $unit
     * @param number $dpi
     * @throws \InvalidArgumentException
     * @return number
     */
    public function getSizeInPixel($widthInUserUnit, $unit, $dpi)
    {
        $unit = strtolower($unit);
        switch ($unit) {
            case 'px':
            case 'pt':
                {
                    return $widthInUserUnit;
                }
            case 'mm':
                {
                    $inch = $widthInUserUnit / 25.4;
                    break;
                }
            case 'cm':
                {
                    $inch = $widthInUserUnit / 2.54;
                    break;
                }
            case 'in':
                {
                    $inch = $widthInUserUnit;
                    break;
                }
            default:
                {
                    throw new \SbmPdf\Model\Exception\InvalidArgumentException(
                        "Unité \"$unit\" invalide.");
                }
        }
        return $inch * $dpi;
    }

    /**
     * Calcule et renvoie l'espace restant sur la page pour un Y donné.
     *
     * @param \TCPDF $pdf
     * @param int $page
     * @param float $y
     * @return float Espace restant sur la page en user unit.
     */
    public function getRemainingYPageSpace(\TCPDF $pdf, $page, $y)
    {
        $totalHeight = $pdf->getPageHeight($page) / $pdf->getScaleFactor();
        $margin = $pdf->getMargins();
        return $totalHeight - $margin['bottom'] - $y;
    }

    /**
     * Retourne la hauteur utilisable sur la page une fois enlevées les marges du haut et
     * du bas.
     *
     * @param \TCPDF $pdf
     * @param int $page
     *            si absent, on prend la page courante
     * @return float Hauteur en user units
     */
    public function getPageContentHeight(\TCPDF $pdf, $page = null)
    {
        if (is_null($page)) {
            $page = $pdf->getPage();
        }
        $totalHeight = $pdf->getPageHeight($page) / $pdf->getScaleFactor();
        $margin = $pdf->getMargins();

        return $totalHeight - $margin['bottom'] - $margin['top'];
    }
}