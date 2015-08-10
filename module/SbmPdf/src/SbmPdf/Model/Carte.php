<?php
/**
 * Objet de description de la carte de transport
 *
 * Utilisé dans Tcpdf pour le templateDocBodyMethod3
 * 
 * @project sbm
 * @package SbmPdf/Model
 * @filesource Carte.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 juil. 2015
 * @version 2015-1
 */
namespace SbmPdf\Model;

class Carte extends Label
{

    private $positions;

    public function __construct($sm, $documentId)
    {
        parent::__construct($sm, $documentId);
        $this->initPositions(3.3, $this->writingAreaHeight());
        $this->NewPage();
        //die(var_dump($this->positions));
    }

    private function initPositions($delta, $hauteur_utile)
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