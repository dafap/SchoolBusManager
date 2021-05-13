<?php
/**
 * Objet permettant de générer un PASS PROVISOIRE
 *
 * @project sbm
 * @package SbmPdf/src/Model
 * @filesource PassProvisoire.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model;

use SbmBase\Model\StdLib;
use TCPDF;

class PassProvisoire
{

    const RECTO = 'pass-provisoire-recto-A4.svg';

    const VERSO = 'pass-provisoire-verso-A4.svg';

    /**
     * Nom du fichier image SVG, modèle du PASS PROVISOIRE coté recto.
     * Ce modèle contient des variables qui seront complétées à partir des data
     *
     * @var string
     */
    private $imagePassJuniorRecto;

    /**
     * Nom du fichier image SVG, contenu du PASS PROVISOIRE coté verso.
     * Pas de variable.
     *
     * @var string
     */
    private $imagePassJuniorVerso;

    /**
     * Niveau du QRCODE
     *
     * @var string
     */
    private $qrcodeNiveau;

    /**
     * QRCODE pour promouvoir le service PLAN TEMPS REEL
     *
     * @var string
     */
    private $qrcodeMessage1;

    /**
     * QRCODE à composter
     *
     * @var string
     */
    private $qrcodeMessage2;

    /**
     *
     * @var array
     */
    private $data;

    public function __construct()
    {
        $imagePath = StdLib::findParentPath(__DIR__, 'SbmPdf/images');
        $this->imagePassJuniorRecto = StdLib::concatPath($imagePath, self::RECTO);
        $this->imagePassJuniorVerso = StdLib::concatPath($imagePath, self::VERSO);
        $this->qrcodeNiveau = 'QRCODE,Q';
        $this->qrcodeMessage1 = 'https://www.tra-mobilite/plan-temps-reel/';
        $this->qrcodeMessage2 = 'ABOARSCO00018';
    }

    /**
     *
     * @param array $data
     * @throws \SbmPdf\Model\Exception\InvalidArgumentException
     * @return string
     */
    private function compileRecto(array $data): string
    {
        $imagePassJunior = file_get_contents($this->imagePassJuniorRecto);
        $du = StdLib::getParam('du', $data, '');
        $au = StdLib::getParam('au', $data, '');
        $eleve = StdLib::getParam('eleve', $data, '');
        $beneficiaire = StdLib::getParam('beneficiaire', $data, '');
        if (empty($beneficiaire)) {
            $beneficiaire = $eleve;
            $eleve = ''; // $eleve est facultatif par la suite
        } elseif (! empty($eleve)) {
            $eleve = 'chez ' . $eleve;
        }
        $adresseL1 = StdLib::getParam('adresseL1', $data, '');
        $adresseCommune = StdLib::getParam('adresseCommune', $data, '');
        $ecole = StdLib::getParam('ecole', $data, '');
        $station = StdLib::getParam('station', $data, '');
        $matin = StdLib::getParam('matin', $data, '');
        $soir = StdLib::getParam('soir', $data, '');
        $responsable = StdLib::getParam('responsable', $data, '');
        if (! $du || ! $au || ! $beneficiaire || ! $adresseL1 || ! $adresseCommune ||
            ! $ecole || ! $station || ! $matin || ! $soir || ! $responsable) {
            throw new Exception\InvalidArgumentException('Données incomplètes');
        }
        // facultatif
        $adresseL2 = StdLib::getParam('adresseL2', $data, '');
        if (empty($adresseL2)) {
            $adresseL2 = $adresseCommune;
            $adresseCommune = '';
        }
        $midi = StdLib::getParam('midi', $data, '');
        $numero = StdLib::getParam('numero', $data, 99991 + rand(10, 1000));
        return sprintf('@' . $imagePassJunior, $responsable, $adresseL1, $adresseL2,
            $adresseCommune, $du, $au, $numero, $beneficiaire, $eleve, $ecole, $station,
            $matin, $midi, $soir, '');
    }

    /**
     * Ne fonctionne pas. TCPDF n'interprète pas cette balise image.
     *
     * @param string $photo
     * @param string $type
     * @return string
     */
    private function ajoutGroupPhoto($photo, $type = 'jpeg')
    {
        if (empty($photo)) {
            return '';
        }
        $photo = base64_encode($photo);
        $type = strtolower($type);
        $tagG_debut = <<<EOT
        <g
             inkscape:groupmode="layer"
             id="photo1"
             inkscape:label="Photo d'identité"
             transform="translate(0,0)">
        EOT;
        $tagG_fin = '</g>';
        $tagImage = <<<EOT
        <image
               y="229.41333"
               x="73.76712"
               width="30"
               height="38.57143"
               preserveAspectRatio="xMidYMid slice"
               xlink:href="data:image/$type;base64,$photo"
               id="image728" />
        EOT;
        return $tagG_debut . "\n" . $tagImage . "\n" . $tagG_fin;
    }

    public function render(array $data)
    {
        $qrcodeStyle = [
            'border' => 0,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => [
                0,
                0,
                0
            ],
            'bgcolor' => false, // array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        ];

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8',
            false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TRANSDEV Albertville');
        $pdf->SetTitle('PASS TEMPORAIRE JUNIOR');
        $pdf->SetSubject('Transports Arlysère');
        $pdf->SetKeywords('TCPDF, PDF, PASS, ARLYSERE, School Bus Manager');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(0, 0, 0, true);
        $pdf->SetAutoPageBreak(TRUE, 0);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->AddPage();
        $pdf->ImageSVG($this->compileRecto($data), 0, 0, 210, 297);
        $pdf->write2DBarcode($this->qrcodeMessage1, $this->qrcodeNiveau, 181.3, 5.2, 24,
            24, $qrcodeStyle, 'N');
        $pdf->write2DBarcode($this->qrcodeMessage2, $this->qrcodeNiveau, 161.3, 217.2, 24,
            24, $qrcodeStyle, 'N');
        $pdf->AddPage();
        $pdf->ImageSVG($this->imagePassJuniorVerso, 0, 0, 210, 297);
        return $pdf->Output('passTemporaireJunior.pdf', 'D');
    }
}