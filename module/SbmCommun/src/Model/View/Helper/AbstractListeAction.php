<?php
/**
 * Partie commune aux classes dérivées
 *
 * @project sbm
 * @package SbmCommun/src/Model/View/Helper
 * @filesource AbstractListeAction.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 août 2021
 * @version 2021-2.6.3
 */
namespace SbmCommun\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;

abstract class AbstractListeAction extends AbstractHelper
{

    protected $form_name;

    /**
     * Renvoie dans une chaine les balises &lt;input type="hidden" name="quelque_chose"
     * id= &gt; indiqués dans le tableau $hiddens.
     * Chaque input a un id qui est la
     * concaténation du name avec l'id de la ligne.
     *
     * @param array $hiddens
     * @param int|string $id
     *            id de la ligne de données pour ListeLigneAction et vide pour
     *            ListeZoneAction
     * @return string
     */
    protected function getHiddens($hiddens, $id = '')
    {
        if (! is_array($hiddens)) {
            return '';
        }
        $result = '';
        foreach ($hiddens as $name => $value) {
            $base = rtrim(rtrim($name, ']'), '[');
            if (is_array($value)) {
                foreach ($value as $key => $item) {
                    $hidden_name = $base . '[]';
                    $hidden_id = "$base$id-$key";
                    $result .= $this->renderHidden($hidden_name, $hidden_id, $item);
                }
            } else {
                $hidden_name = $base;
                $hidden_id = "$base$id";
                $result .= $this->renderHidden($hidden_name, $hidden_id, $value);
            }
        }
        return $result;
    }

    protected function renderHidden($hidden_name, $hidden_id, $value)
    {
        $result = '<input type="hidden" name="' . $hidden_name . '" value';
        if (! is_null($value)) {
            $result .= '="' . $value . '"';
        }
        $result .= ' id="' . $hidden_id . '"';
        $result .= ">\n";
        return $result;
    }

    protected function getButton($name, $attributes, $id = null)
    {
        if (! $id) {
            if (array_key_exists('id', $attributes)) {
                $id = $attributes['id'];
            } else {
                $id = $name;
            }
        }
        $result = '<input type="submit" name="' . $name . '" id="' . $id . '"';
        if (array_key_exists('class', $attributes)) {
            if (strpos($attributes['class'], 'fam-') !== false) {
                $result .= ' class="' . $attributes['class'] . '" value';
            } elseif (strpos($attributes['class'], 'default') != false) {
                if (array_key_exists('value', $attributes)) {
                    $result .= ' class="' . $attributes['class'] . '" value="' .
                        $attributes['value'] . '"';
                } else {
                    $result .= ' class="' . $attributes['class'] . '" value';
                }
            } else {
                if (array_key_exists('value', $attributes)) {
                    $result .= ' class="default ' . $attributes['class'] . '" value="' .
                        $attributes['value'] . '"';
                } else {
                    $result .= ' class="default ' . $attributes['class'] . '" value';
                }
            }
        } else {
            if (array_key_exists('value', $attributes)) {
                $result .= ' class="default" value="' . $attributes['value'] . '"';
            } else {
                $result .= ' class="default" value';
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
     * Renvoie la balise <form .
     * ..>
     *
     * @param int|string $id
     * @param array $attributes
     * @return string
     */
    protected function openForm($attributes, $form_name_default, $id = '')
    {
        // pour la compatibilité aves HTML 4, un attribut 'id' est toujours placé en même
        // temps que l'attribut 'name' et prend la même valeur.
        if (array_key_exists('name', $attributes)) {
            $this->form_name = $attributes['name'] . $id;
        } elseif (array_key_exists('id', $attributes)) {
            $this->form_name = $attributes['id'] . $id;
        } else {
            $this->form_name = $form_name_default . $id;
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
    protected function closeForm()
    {
        return "</form>\n";
    }
}
