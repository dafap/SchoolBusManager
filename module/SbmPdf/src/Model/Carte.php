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
 * @date 18 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmPdf\Model;

use SbmBase\Model\StdLib;

class Carte extends Etiquette
{

    private $positions;

    public function __construct($db_manager, $documentId)
    {
        parent::__construct($db_manager, $documentId);
        foreach ($this->getSublabelIdx() as $sublabel) {
            $this->initPositions($sublabel, 3.3, $this->writingAreaHeight($sublabel));
        }
        $this->NewPage();
    }

    /**
     * Version 2020 - Etiquette constituée de plusieurs sous-étiquettes L'appel à la
     * méthode LineCount() ne renvoie que le nombre de lignes de la sous-étiquette
     * courante. Ici il faut initialiser sans modifier la sous-étiquette courante. On
     * consulte donc directemment la propriété sublableDescripteur
     *
     * @param float $delta
     * @param float $hauteur_utile
     */
    private function initPositions(int $sublabel, float $delta, float $hauteur_utile)
    {
        $n = $this->sublabelDescripteur['lineCount'][$sublabel];
        if ($n) {
            for ($y = $delta, $i = 0; $i < $n; $i ++) {
                $this->positions[$sublabel][$i]['x'] = 1;
                $this->positions[$sublabel][$i]['y'] = $y;
                $this->positions[$sublabel][$i]['data'] = true;
                //$this->positions[$sublabel][$i]['style'] = 'data';
                $y += $delta;
            }
            // optimisation des lignes
            $d = $hauteur_utile - $y;
            if (abs($d) > 0.01 * $n) {
                $delta += $d / max($n, 2);
                $this->initPositions($sublabel, $delta, $hauteur_utile);
            }
        } else {
            $this->positions['default']['x'] = 0;
            $this->positions['default']['y'] = 0;
            $this->positions['default']['data'] = false;
            //$this->positions['default']['style'] = 'data';
        }
    }

    /**
     *
     * @param int $rang
     * @param string $key
     * @return mixed
     */
    private function getPosition(int $rang, string $key)
    {
        $sublabel = current($this->arraySublabels);
        return StdLib::getParamR([
            $sublabel,
            $rang,
            $key
        ], $this->positions, $this->positions['default'][$key]);
    }

    public function Ln(int $rang): array
    {
        $result = parent::Ln($rang);
        $result[1] = $this->yStart() + $this->getPosition($rang, 'y');
        return $result;
    }

    public function descripteurData(): array
    {
        $descripteur = parent::descripteurData();
        $keysSublabel = array_keys($descripteur);
        foreach ($keysSublabel as $sublabel) {
            $keysRang = array_keys($descripteur[$sublabel]);
            foreach ($keysRang as $rang) {
                $descripteur[$sublabel][$rang] = array_merge(
                    $descripteur[$sublabel][$rang],
                    StdLib::getParamR([
                        $sublabel,
                        $rang
                    ], $this->positions, $this->positions['default']));
            }
        }
        return $descripteur;
    }

    public function X($rang)
    {
        return $this->xStart($rang) + $this->getPosition($rang, 'x');
    }
}