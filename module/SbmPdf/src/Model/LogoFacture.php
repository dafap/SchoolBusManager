<?php
/**
 * Dessin du logo de la facture
 *
 * @project sbm
 * @package SbmPdf/src/Model
 * @filesource LogoFacture.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmPdf\Model;

use SbmBase\Model\StdLib;

class LogoFacture
{

    public function __invoke(&$pdf)
    {
        $file = StdLib::concatPath(StdLib::findParentPath(__DIR__, 'public/img'),
            'transdev.png');
        $pdf->Image($file, 7, 7, 68.44);
        $pdf->SetXY(26, 23);
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColorArray([
            255,
            0,
            0
        ]);
        $pdf->Write(0, 'ALBERTVILLE');
    }
}