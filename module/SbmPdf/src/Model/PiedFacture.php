<?php
/**
 * Pied de page d'une facture
 *
 *
 * @project sbm
 * @package SbmPdf/src/Model
 * @filesource PiedFacture.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmPdf\Model;

use SbmBase\Model\StdLib;

class PiedFacture
{

    public function __invoke(&$pdf)
    {
        $pdf->SetXY(7, 269);
        $pdf->LinearGradient(7, 269, 192, 2, [
            255,
            255,
            255
        ], [
            255,
            0,
            0
        ]);
        $pdf->SetXY(15, 271);
        $pdf->SetFont('Times', 'B', 8);
        $pdf->Write(0, 'Transdev Albertville', '',false,'',true);
        $pdf->Write(0, 'Place de la Gare', '',false,'',true);
        $pdf->Write(0, '73200 ALBERTVILLE', '',false,'',true);
        $pdf->SetFont('Times', '', 6, '',false,'',true);
        $pdf->Write(0, 'SAS Transdev Albertville - RCS Nanterre 834 264 897', '',false,'',true);
        $pdf->Write(0, 'APE – 4931Z SIRET -834 264 897 00028');
    }
}