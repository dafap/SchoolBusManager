<?php
/**
 * ViewHelper permettant d'afficher le formulaire d'actions en bout de ligne dans une liste
 * (à déclarer dans module.config.php comme ceci : 'view_helpers' => array('invokables' => array('listeLigneActions' =>'SbmCommun\Form\View\Helper\ListeLigneActions',))
 * 
 * La mise en page d'une liste est la suivante :
 * <div class="liste-wrapper">
 *   <div class="zone-actions">
 *   </div>
 *   <table class="liste-inner paiements">
 *     <tbody>
 *       <tr><td>...</td><td><?php echo $this->listeLigneActions($id, $hiddens, $actions, $form_attributes); ?></td></tr>
 *       où $id est un id unique de la ligne, $hiddens, $actions et $arguments des tableaux comme décrit plus bas.
 *       ...
 *     </tbody>
 *   </table>
 *   <div class="zone-pagination">
 *   </div>
 * </div>
 * Tous les attributs de la balise <form> sont acceptés, à l'exception de 'method' qui est fixée à POST.
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form/View/Helper
 * @filesource ListeLigneActions.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 janv. 2015
 * @version 2015-1
 */
namespace SbmCommun\Form\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Form\Annotation\Input;

class ListeLigneActions extends AbstractHelper
{

    private $form_name;

    /**
     * Crée le code d'un formulaire d'actions à utiliser en bout de ligne dans une liste.
     *
     * @param int|string $id
     *            La référence unique des données de la ligne (composé par exemple à partir de la <i>primary_key</i> ou d'une <i>key unique</i>).
     * @param array $hiddens
     *            Tableau indexé array(name => value, ...). S'il n'y a pas de valeur mettre <b>null</b>.
     * @param array $attributes
     *            Ce sont les attributs du formulaires. En voici la liste (HTML5):<br><ul>
     *            <li><b>accept-charset</b> : Une liste des ensembles de caractères que le serveur accepte.
     *            Cette liste est délimitée par des espaces. Le navigateur les utilise dans l'ordre dans lequel ils ont été définis.
     *            La valeur par défaut est la chaîne de caractères réservée "UNKNOWN" ; dans ce cas, l'ensemble de caractères utilisé
     *            correspond à celui du document contenant l'élément &lt;form&gt;.</li>
     *            <li><b>action</b> : L'URI du programme qui traitera les informations soumises par le formulaire.
     *            Cette valeur peut être surchargée par un attribut <i>formaction</i> sur un élément &lt;button&gt; ou &lt;input&gt;.</li>
     *            <li><b>autocomplete</b> : Cet attribut énuméré peut prendre deux valeurs <i>on</i> ou <i>off</i>. La valeur par défaut est <i>on</i>.<ul>
     *            <li><i>on</i> : Le navigateur peut remplir automatiquement les valeurs d'après les précédentes informations qu'a entrées
     *            l'utilisateur lors d'usages antérieurs du formulaire.</li>
     *            <li><i>off</i> : L'utilisateur doit remplir lui-même la valeur de chaque champ, à chaque utilisation du formulaire, ou
     *            le formulaire utilise son propre système d'auto-complétion ; le navigateur ne doit pas remplir automatiquement les valeurs.</li></ul>
     *            Les éléments du formulaire peuvent bien sûr outrepasser cette valeur via leur propre attribut <i>autocomplete</i>.</li>
     *            <li><b>class</b> : Cet attribut permet d'affecter des classes css à la balise qui se comportera alors comme un conteneur.</li>
     *            <li><b>enctype</b> : Cet attribut définit le type MIME qui sera utilisé pour encoder les données envoyées au serveur.
     *            C'est un attribut énuméré qui peut prendre les valeurs suivantes :<ul>
     *            <li>application/x-www-form-urlencoded,</li>
     *            <li>multipart/form-data,</li>
     *            <li>text/plain HTML5, correspondant au type MIME éponyme.</li></ul>
     *            La valeur par défaut utilisée est <i>application/x-www-form-urlencoded</i>.
     *            Pour l'envoi de fichier, il vous faut utiliser la valeur <i>multipart/form-data</i>.
     *            Cette valeur peut être surchargée par l'attribut <i>formenctype</i> des éléments &lt;button&gt; ou &lt;button&gt;.</li>
     *            <li><b>method</b> : Cet attribut définit la méthode HTTP qui sera utilisée pour envoyer les données au serveur.
     *            C'est un attribut énuméré qui peut prendre les valeurs suivantes :<ul>
     *            <li><i>get</i> : correspondant à la méthode GET du protocole HTTP ;</li>
     *            <li><i>post</i> : correspondant à la méthode POST du protocole HTTP ;</li></ul>
     *            Si cet attribut n'est pas défini, la valeur par défaut utilisée est GET.
     *            Cette valeur peut-être surchargée par l'attribut <i>formmethod</i> des éléments &lt;button&gt; ou &lt;input&gt;.
     *            <li><b>name</b> : Le nom du formulaire. Il doit être unique parmi tous les formulaires d'un document, et ne doit pas être
     *            une chaîne de caractères vide.</li>
     *            <li><b>id</b> : <i>pour la compatibilité avec HTML4<i>, où l'attribut <i>name</i> est déprécié et remplacé par celui-ci.</li>
     *            <li><b>novalidate</b> : Cet attribut booléen indique si le formulaire doit être validé au moment de sa soumission.
     *            Par défaut, le formulaire sera validé lors de sa soumission.
     *            Il peut être surchargé par l'attribut <i>formnovalidate</i> des éléments &lt;button&gt; ou &lt;input&gt; appartenant au formulaire.</li>
     *            <li><b>target</b> : Un nom ou un mot-clé indiquant où afficher la réponse après avoir envoyé le formulaire.
     *            Les mots-clés suivants ont un sens particulier :<ul>
     *            <li><i>_self</i> : charge la réponse dans le même contexte de navigation. Valeur par défaut.</li>
     *            <li><i>_parent</i> : charge la réponse dans le contexte de navigation parent de l'actuel.</li>
     *            <li><i>_top</i> : charge la réponse dans le contexte de navigation le plus haut (cad celui qui n'a aucun parent).
     *            S'il n'y a pas de parent, se comporte comme <i>_self</i>.</li>
     *            <li><i>_blank</i> : charge la réponse dans un nouveau contexte de navigation.</li>
     *            Cette valeur peut être surchargée par l'attribut <i>formtarget</i> des éléments &lt;button&gt; ou &lt;input&gt;.</li>
     */
    public function __invoke($id, $hiddens = array(), $buttons = array(), $attributes = array())
    {
        $result = $this->openForm($id, $attributes);
        if (! array_key_exists('op', $hiddens)) {
            $hiddens['op'] = null;
        }
        $result .= $this->getHiddens($id, $hiddens);
        $result .= $this->getButtons($id, $buttons);
        return $result . $this->closeForm();
    }

    private function getButtons($id, $buttons)
    {
        $result = '';
        foreach ($buttons as $name => $attributes) {
            $result .= '<input type="submit" name="' . $name . '" id="' . $name . $id . '"';
            if (array_key_exists('class', $attributes)) {
                if (strpos($attributes['class'], 'fam-') !== false) {
                    $result .= ' class="' . $attributes['class'] . '" value';
                } elseif (strpos($attributes['class'], 'default') != false) {
                    if (array_key_exists('value', $attributes)) {
                        $result .= ' class="' . $attributes['class'] . '" value="' . $attributes['value'] . '"';
                    } else {
                        $result .= ' class="' . $attributes['class'] . '" value';
                    }                    
                } else {
                    if (array_key_exists('value', $attributes)) {
                        $result .= ' class="default ' . $attributes['class'] . '" value="' . $attributes['value'] . '"';
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
        }
        return $result;
    }

    /**
     * Renvoie dans une chaine les balises &lt;input type="hidden" name="quelque_chose" id=  &gt; indiqués dans le tableau $hiddens.
     * Chaque input a un id qui est la concaténation du name avec l'id de la ligne.
     *
     * @param int|string $id
     *            id de la ligne de données
     * @param array $hiddens            
     * @return string
     */
    private function getHiddens($id, $hiddens)
    {
        if (! is_array($hiddens)) {
            return '';
        }
        $result = '';
        foreach ($hiddens as $name => $value) {
            $result .= '<input type="hidden" name="' . $name . '" value';
            if (! is_null($value)) {
                $result .= '="' . $value . '"';
            }
            $result .= ' id="' . $name . $id . '"';
            $result .= ">\n";
        }
        return $result;
    }

    /**
     * Renvoie la balise <form .
     *
     *
     * ..>
     *
     * @param int|string $id            
     * @param array $attributes            
     * @return string
     */
    private function openForm($id, $attributes)
    {
        // pour la compatibilité aves HTML 4, un attribut 'id' est toujours placé en même temps que l'attribut 'name' et prend la même valeur.
        if (array_key_exists('name', $attributes)) {
            $this->form_name = $attributes['name'] . $id;
        } elseif (array_key_exists('id', $attributes)) {
            $this->form_name = $attributes['id'] . $id;
        } else {
            $this->form_name = 'ligneactions' . $id;
        }
        $result = '<form id="' . $this->form_name . '" name="' . $this->form_name . '" method="post"';
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