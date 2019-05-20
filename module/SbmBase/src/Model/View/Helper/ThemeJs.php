<?php
/**
 * Ajoute le fichier js du theme
 *
 *
 * @project sbm
 * @package SbmBase/Model/View/Helper
 * @filesource ThemeJs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmBase\Model\View\Helper;

use SbmBase\Model\StdLib;
use Zend\View\Helper\AbstractHelper;

class ThemeJs extends AbstractHelper
{

    /**
     * Nom du thème
     *
     * @var string
     */
    private $theme_name;

    public function __construct($theme_name)
    {
        $this->theme_name = strtolower($theme_name);
        ;
    }

    /**
     * Met le fichier indiqué dans la pile headScript ou du inlineScript
     *
     * @param string $phtml_path
     *            chemin après le dossier theme, peut être ''
     * @param string $js_file
     *            nom du fichier, doit être non ''
     * @param string $position
     *            'append' ou 'prepend'
     * @param string $pile
     *            'head' ou 'inline'
     */
    public function __invoke(string $phtml_path, string $js_file,
        string $position = 'append', string $pile = 'head')
    {
        if ($position != 'prepend') {
            $position = 'append';
        }
        if ($pile != 'inline') {
            $pile = 'head';
        }
        $basepath = $this->getView()->plugin('basepath');
        $headScript = $this->getView()->plugin($pile . 'script');
        $path = sprintf('/js/%s/%s', $this->theme_name, $phtml_path);
        $js = StdLib::concatPath($path, $js_file);
        $headScript->{$position . 'File'}($basepath($js));
    }
}