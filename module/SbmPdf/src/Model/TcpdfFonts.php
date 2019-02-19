<?php
/**
 * Liste des polices installées dans TcPdf
 *
 *
 * @project sbm
 * @package SbmPdf/Model
 * @filesource TcpdfFonts.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPdf\Model;

class TcpdfFonts
{

    private $tcpdf_font_path;

    private $fonts = [];

    private $mono = [];

    public function __construct()
    {
        $tcpdf_fonts = new \TCPDF_FONTS();
        $this->tcpdf_font_path = $tcpdf_fonts->_getfontpath();
    }

    /**
     * Renvoie un tableau des polices disponibles pour TcPdf dont les clés sont les noms internes
     * des polices et les valeurs sont les noms visibles.
     * (directement disponible pour peupler un SELECT dans un formulaire)
     *
     * @param boolean $mono
     *            (si true, ne renvoie que les polices à espacement fixe)
     *            
     * @throws \Exception
     * @return array <p>Chaque ligne du tableau est de la forme file => name où :</p>
     *         <ul>
     *         <li>file est le nom interne de la police</li>
     *         <li>name est le nom de la police qui apparaitra dans la liste déroulante.</li>
     *         </ul>
     */
    public function getFonts($mono = false)
    {
        if (empty($this->fonts)) {
            $pwd = getcwd();
            if (! chdir($this->tcpdf_font_path)) {
                throw new \Exception(
                    sprintf(
                        'Le dossier %s n\'est pas le dossier des polices du module TcPdf.',
                        $this->tcpdf_font_path));
            }
            foreach (glob('*.php') as $php_file) {
                $features = $this->getFontFeatures($php_file);
                if ($features === false || $features->cidfont0)
                    continue;
                $this->fonts += $features->font;
                if ($features->mono) {
                    $this->mono += $features->font;
                }
            }
            foreach ($this->fonts as $key => $label) {
                $carfin = substr($key, - 1); // dernier caractère
                $racine = null;
                if (substr($key, - 2) == 'bi') {
                    $racine = substr($key, 0, mb_strlen($key) - 2);
                    $carfin = 'bi';
                } elseif ($carfin == 'i' || $carfin == 'b') {
                    $racine = substr($key, 0, mb_strlen($key) - 1);
                }
                if (isset($racine) && array_key_exists($racine, $this->fonts)) {
                    unset($this->fonts[$key]);
                    switch ($carfin) {
                        case 'bi':
                            $this->fonts[$racine] .= ' + Gras italique';
                            break;
                        case 'i':
                            $this->fonts[$racine] .= ' + Italique';
                            break;
                        case 'b':
                            $this->fonts[$racine] .= ' + Gras';
                            break;
                        default:
                            ;
                            break;
                    }
                }
            }
            unset($label);
            asort($this->fonts, SORT_STRING);
            asort($this->mono, SORT_STRING);
            chdir($pwd);
        }
        return $mono ? $this->mono : $this->fonts;
    }

    private function getFontFeatures($php_file)
    {
        $cw = $file = $name = $type = null; // ces variables peuvent être surchargées par l'include
        include $php_file;

        if (! isset($cw))
            return false;

        $result = new \stdClass();
        if (isset($file)) {
            $result->font = [
                basename($file, '.z') => isset($name) ? $name : basename($php_file, '.php')
            ];
        } else {
            $result->font = [
                basename($php_file, '.php') => isset($name) ? $name : basename($php_file,
                    '.php')
            ];
        }
        $result->mono = max($cw) == min($cw);
        $result->cidfont0 = $type == 'cidfont0'; // polices asiatiques à oublier
        return $result;
    }
}