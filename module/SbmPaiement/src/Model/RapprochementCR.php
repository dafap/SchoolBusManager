<?php
/**
 * Construction d'un document PDF donnant le compte-rendu d'un rapprochement
 *
 * @project sbm
 * @package SbmPaiement/Model
 * @filesource RapprochementCR.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 juil. 2019
 * @version 2019-2.5.0
 */
namespace SbmPaiement\Model;

class RapprochementCR extends \TCPDF
{

    private $cr_data;

    /**
     * Structure de type array dont les éléments sont des StandardClass avec les
     * propriétés suivantes :<ul><li>label (string)</li><li>align ('L', 'C' ou
     * 'R')</li><li>width (number)</li><li>format (fonction callback ou closure)</li></ul>
     *
     * @var array
     */
    private $cr_header;

    /**
     *
     * @param array $data
     *            le compte-rendu à afficher
     * @param array $header
     *            entêtes de première ligne du tableau résultat
     */
    public function __construct($data, $header = [])
    {
        $this->cr_data = $data;
        $this->cr_header = $header;
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->SetCreator('School Bus Manager');
        $this->SetTitle('Rapprochement des paiements en ligne');
        $this->SetHeaderData('sbm-logo.gif', 18, 'Rapprochement des paiements en ligne',
            'Liste des encaissements en ligne non enregistrés dans School Bus Manager');
        $this->SetMargins(15, 27, 15);
        $this->SetHeaderMargin(5);
        $this->SetFooterMargin(10);
        $this->SetAutoPageBreak(true, 25);
        $this->setImageScale(1.25);
    }

    public function setCrHeader($header)
    {
        $this->cr_header = $header;
    }

    public function render_table()
    {
        // Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');
        $this->SetFontSize(10);
        // Header
        $num_headers = count($this->cr_header);
        for ($i = 0; $i < $num_headers; ++ $i) {
            $this->Cell($this->cr_header[$i]->width, 7, $this->cr_header[$i]->label, 1, 0,
                'C', 1, '', 1);
        }
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        $this->SetFontSize(10);
        // Data
        $fill = 0;
        $nb_data = count($this->cr_data);
        $ligne = 1;
        foreach ($this->cr_data as $row) {
            if (count($row) != $num_headers) {
                throw new \Exception('Mauvais formatage des données.');
            }
            $bords = ($ligne ++ == $nb_data) ? 'BLR' : 'LR';
            for ($i = 0; $i < $num_headers; $i ++) {
                $fmt = $this->cr_header[$i]->format;
                $this->Cell($this->cr_header[$i]->width, 6,
                    $fmt($row[$i]), $bords, 0,
                    $this->cr_header[$i]->align, $fill, '', 1);
            }
            $this->Ln();
            $fill = ! $fill;
        }
    }

    public function render_cr()
    {
        $this->SetFont('helvetica', '', 12);
        // add a page
        $this->AddPage();
        // print table
        $this->render_table();
        // close and output PDF document
        return $this->Output('rapprochement.pdf', 'I');
    }
}