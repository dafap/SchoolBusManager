<?php
/**
 * Renvoie une ligne de menu dans lequel le lien est un formulaire POST
 *
 * @project sbm
 * @package SbmCommun/src/Form/View/Helper
 * @filesource LigneMenuAction.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\View\Helper;

use SbmBase\Model\StdLib;
use Zend\View\Helper\AbstractHelper;

class LigneMenuAction extends AbstractHelper
{

    private $form_name;

    public function __invoke($bullet, $hiddens = [], $button_attributes = [], $form_attributes = [])
    {
        $result = '<li class="ligne-menu">';
        $result .= $this->getBullet($bullet);
        $result .= $this->openForm($form_attributes);
        if (! array_key_exists('op', $hiddens)) {
            $hiddens['op'] = null;
        }
        $result .= $this->getHiddens($hiddens);
        $result .= $this->getButton($button_attributes);
        return $result . $this->closeForm() . "</li>\n";
    }

    /**
     *
     * @param null|string $bullet
     *            une classe css
     * @return string
     */
    private function getBullet($bullet)
    {
        if ($bullet) {
            return "<i class=\"$bullet\"></i>";
        }
        return '';
    }

    private function getButton($attributes)
    {
        $name = StdLib::getParam('name', $attributes, 'run');
        $id = StdLib::getParam('id', $attributes, false);
        $result = '<input type="submit" name="' . $name . '"';
        if ($id) {
            $result .= ' id="' . $id . '"';
        }
        if (array_key_exists('class', $attributes)) {
            if (strpos($attributes['class'], 'fam-') !== false) {
                $result .= ' class="' . $attributes['class'] . '" value';
            } else {
                if (array_key_exists('value', $attributes)) {
                    $result .= ' class="' . $attributes['class'] . '" value="' .
                        $attributes['value'] . '"';
                } else {
                    $result .= ' class="' . $attributes['class'] . '" value';
                }
            }
        } else {
            if (array_key_exists('value', $attributes)) {
                $result .= ' value="' . $attributes['value'] . '"';
            } else {
                $result .= ' value';
            }
        }
        if (array_key_exists('accesskey', $attributes)) {
            $result .= ' accesskey="' . $attributes['accesskey'] . '"';
        }
        if (array_key_exists('autofocus', $attributes)) {
            $result .= ' autofocus';
        }
        if (array_key_exists('disabled', $attributes)) {
            $result .= ' disabled';
        }
        if (array_key_exists('formaction', $attributes)) {
            $result .= ' formaction="' . $attributes['formaction'] . '"';
        }
        if (array_key_exists('formenctype', $attributes)) {
            $result .= ' formenctype="' . $attributes['formenctype'] . '"';
        }
        if (array_key_exists('formnovalidate', $attributes)) {
            $result .= ' formnovalidate';
        }
        if (array_key_exists('formtarget', $attributes)) {
            $result .= ' formtarget="' . $attributes['formtarget'] . '"';
        }
        if (array_key_exists('hidden', $attributes)) {
            $result .= ' hidden';
        }
        if (array_key_exists('style', $attributes)) {
            $result .= ' style="' . $attributes['style'] . '"';
        }
        if (array_key_exists('tabindex', $attributes)) {
            $result .= ' tabindex="' . $attributes['tabindex'] . '"';
        }
        if (array_key_exists('title', $attributes)) {
            $result .= ' title="' . $attributes['title'] . '"';
        }
        if (array_key_exists('onclick', $attributes)) {
            $result .= ' onclick="' . $attributes['onclick'] . '"';
        }
        $result .= ">\n";
        return $result;
    }

    /**
     * Renvoie dans une chaine les balises &lt;input type="hidden"&gt; indiqués dans le
     * tableau $hiddens. Ces input n'ont pas de id.
     *
     * @param array $hiddens
     *
     * @return string
     */
    private function getHiddens($hiddens)
    {
        if (! is_array($hiddens)) {
            return '';
        }
        $format = "<input type=\"hidden\" name=\"%s\" value%s>\n";
        $result = '';
        foreach ($hiddens as $name => $value) {
            $result .= sprintf($format, $name, $value ? "=\"$value\"" : "");
        }
        return $result;
    }

    /**
     * Renvoie la balise <form>
     *
     * @param array $attributes
     * @return string
     */
    private function openForm($attributes)
    {
        // pour la compatibilité aves HTML 4, un attribut 'id' est toujours placé en même
        // temps que l'attribut 'name' et prend la même valeur.
        if (array_key_exists('name', $attributes)) {
            $this->form_name = $attributes['name'];
        } elseif (array_key_exists('id', $attributes)) {
            $this->form_name = $attributes['id'];
        } else {
            $this->form_name = 'lignemenuactions';
        }
        $result = '<form id="' . $this->form_name . '" name="' . $this->form_name .
            '" method="post"';
        // ensuite on place les autres attributs s'ils sont précisés.
        // class (classe css)
        if (array_key_exists('class', $attributes)) {
            $result .= ' class="' . $attributes['class'] . '"';
        }
        // style
        if (array_key_exists('style', $attributes)) {
            $result .= ' style="' . $attributes['style'] . '"';
        }
        // accept-charset
        if (array_key_exists('accept-charset', $attributes)) {
            $result .= ' accept-charset="' . $attributes['accept-charset'] . '"';
        }
        // action
        if (array_key_exists('form_action', $attributes)) {
            $result .= ' action="' . $attributes['form_action'] . '"';
        } else {
            $result .= ' action';
        }
        // autocomplete
        if (array_key_exists('autocomplete', $attributes)) {
            $result .= ' autocomplete="' . $attributes['autocomplete'] . '"';
        }
        // enctype
        if (array_key_exists('enctype', $attributes)) {
            $result .= ' enctype="' . $attributes['enctype'] . '"';
        }
        // novalidate
        if (array_key_exists('novalidate', $attributes)) {
            $result .= ' novalidate="' . $attributes['novalidate'] . '"';
        }
        // target
        if (array_key_exists('target', $attributes)) {
            $result .= ' target="' . $attributes['target'] . '"';
        }
        return $result . ">\n";
    }

    /**
     * Renvoie la balise </form>
     *
     * @return string
     */
    private function closeForm()
    {
        return "</form>\n";
    }
}