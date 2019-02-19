<?php
/**
 * Ajoute les librairies JQuery nécessaires
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmBase/Model/View/Helper
 * @filesource JQuery.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 févr. 2019
 * @version 2019-2.5.0
 */
namespace SbmBase\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;

class JQuery extends AbstractHelper
{

    /**
     * Tableau de configuration de jQuery décrit dans /confi/autoload/sbm.local.php
     * Chaque nom de librairie est une clé du tableau.
     * Pour chaque librairie, le tableau indique :<ul>
     * <li>le mode ('append' ou 'prepend')</li>
     * <li>les fichiers js à installer par headScript()</li>
     * <li>les fichiers css à installer par headLink()</li>
     *
     * @var array
     */
    private $jquery_config;

    public function __construct($jquery_config)
    {
        if (! is_array($jquery_config) || ! array_key_exists('jquery', $jquery_config)) {
            throw new Exception("JQuery n'est pas configuré.");
        }
        $this->jquery_config = $jquery_config;
    }

    public function __invoke($options = [])
    {
        $basepath = $this->getView()->plugin('basepath');
        $headLink = $this->getView()->plugin('headlink');
        $headScript = $this->getView()->plugin('headscript');
        if (is_string($options)) {
            $options = (array) $options;
        }
        $array = [
            'jquery' => $this->jquery_config['jquery']
        ];
        foreach ($options as $key) {
            if (! array_key_exists($key, $this->jquery_config)) {
                throw new Exception("`$key` n'est pas configuré.");
            }
            $array[$key] = $this->jquery_config[$key];
        }
        foreach ($array as $config) {
            if (array_key_exists('mode', $config) && $config['mode'] == 'prepend') {
                if (array_key_exists('css', $config)) {
                    foreach ($config['css'] as $css) {
                        $headLink->prependStylesheet($basepath($css));
                    }
                }
                foreach ($config['js'] as $js) {
                    $headScript->prependFile($basepath($js));
                }
            } else {
                if (array_key_exists('css', $config)) {
                    foreach ($config['css'] as $css) {
                        $headLink->appendStylesheet($basepath($css));
                    }
                }
                foreach ($config['js'] as $js) {
                    $headScript->appendFile($basepath($js));
                }
            }
        }
    }
}