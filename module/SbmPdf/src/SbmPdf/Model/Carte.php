<?php
/**
 * Objet de description de la carte de transport
 *
 * Utilisé dans Tcpdf pour le templateDocBodyMethod3
 * N'est pas enregistré dans service manager afin de passer aisément le documentId.
 *   $label = new Carte($this->pdf_manager->get('Sbm\DbManager'), $this->getDocumentId());
 * 
 * @project sbm
 * @package SbmPdf/Model
 * @filesource Carte.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 juin 2016
 * @version 2016-2.1.5
 */
namespace SbmPdf\Model;

class Carte extends Etiquette
{

    private $positions;

    public function __construct($db_manager, $documentId)
    {
        parent::__construct($db_manager, $documentId);
        $this->initPositions(3.3, $this->writingAreaHeight());
        $this->NewPage();
    }

    /**
     * version 2015 sur étiquettes
     * 
     * @param unknown $delta
     * @param unknown $hauteur_utile
     */
    private function initPositions_version2015($delta, $hauteur_utile)
    {
        // zone 1
        for ($y = $delta, $i = 0; $i < 3; $i ++) {
            $this->positions[$i]['x'] = 12;
            $this->positions[$i]['y'] = $y;
            $this->positions[$i]['data'] = true;
            $this->positions[$i]['style'] = 'main';
            $y += $delta;
        }
        $this->positions[0]['data'] = false; // c'est du texte - prendre uniquement le label du docfield
        $y = $this->positions[2]['y'] = 15; // en mm
        // zone 2 
        for ($y += $delta; $i < 14; $i ++) {
            $this->positions[$i]['x'] = 1;
            $this->positions[$i]['y'] = $y;
            $this->positions[$i]['data'] = true;
            $this->positions[$i]['style'] = 'data';
            $y += $delta;
        }
        $this->positions[12]['data'] = false;
        $this->positions[12]['style'] = 'titre4';
        $this->positions[13]['style'] = 'titre4';
        // optimisation des 11 lignes du bas
        $d = $hauteur_utile - $y;
        if (abs($d) > 0.11) {
            $delta += $d / 11;
            $this->initPositions($delta, $hauteur_utile);
        }
    }

    /**
     * version 2016 sur page A4
     * 
     * @param unknown $delta
     * @param unknown $hauteur_utile
     */
    private function initPositions($delta, $hauteur_utile)
    {
        // zone 1
        for ($y = $delta, $i = 0; $i < 10; $i ++) {
            /*$this->positions[$i]['x'] = 12;
            $this->positions[$i]['y'] = $y;
            $this->positions[$i]['data'] = true;
            $this->positions[$i]['style'] = 'main';
            $y += $delta;
        }
        $this->positions[0]['data'] = false; // c'est du texte - prendre uniquement le label du docfield
        $y = $this->positions[2]['y'] = 15; // en mm
        // zone 2
        for ($y += $delta; $i < 14; $i ++) {*/
            $this->positions[$i]['x'] = 1;
            $this->positions[$i]['y'] = $y;
            $this->positions[$i]['data'] = true;
            $this->positions[$i]['style'] = 'data';
            $y += $delta;
        }
        // optimisation des lignes
        $d = $hauteur_utile - $y;
        if (abs($d) > 0.11) {
            $delta += $d / 11;
            $this->initPositions($delta, $hauteur_utile);
        }
    }
    
    public function Ln($rang)
    {
        $result = parent::Ln($rang);
        $result[1] = $this->yStart() + $this->positions[$rang]['y'];
        return $result;
    }
    
    public function descripteurData()
    {
        $descripteur = parent::descripteurData();
        for ($i = 0; $i < count($this->positions); $i++) {
            $descripteur[$i]['data'] = $this->positions[$i]['data'];
        }
        return $descripteur;
    }
    
    public function X($rang)
    {
        return $this->xStart($rang) + $this->positions[$rang]['x'];
    }
}