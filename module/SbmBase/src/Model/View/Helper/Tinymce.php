<?php
/**
 * Ajoute le service Tinymce défini dans le thème
 *
 *
 * @project sbm
 * @package SbmBase/Model/View/Helper
 * @filesource Tinymce.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmBase\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Tinymce extends AbstractHelper
{

    /**
     * Nom du thème
     *
     * @var string
     */
    private $tinymce_url;

    private $attrs;

    public function __construct($tinymce_url, $attrs = [])
    {
        if (substr($tinymce_url, 0, 8) != 'https://') {
            $basepath = $this->getView()->plugin('basepath');
            $this->tinymce_url = $basepath($tinymce_url);
        } else {
            $this->tinymce_url = $tinymce_url;
        }
        $this->attrs = $attrs;
    }

    /**
     * Met le service dans la pile headScript
     *
     * @param string $position
     *            'append' ou 'prepend'
     */
    public function __invoke(string $position = 'append')
    {
        if ($position != 'prepend') {
            $position = 'append';
        }
        $headScript = $this->getView()->plugin('headscript');
        if (empty($this->attrs)) {
            $headScript->{$position . 'File'}($this->tinymce_url);
        } else {
            $headScript->{$position . 'File'}($this->tinymce_url, 'text/javascript',
                $this->attrs);
        }
    }
}