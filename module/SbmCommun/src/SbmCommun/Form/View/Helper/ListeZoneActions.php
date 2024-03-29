<?php
/**
 * ViewHelper permettant d'afficher une barre de menu.
 * (à déclarer dans module.config.php comme ceci : 'view_helpers' => array('invokables' => array('listeZoneActions' => 'SbmCommun\Form\View\Helper\ListeZoneActions',))
 *
 * Les liens sont envoyés en POST par un formulaire. Les paramètres d'appel sont passés par des inputs de type hidden.
 * Il y a 2 types d'items : 
 * - les inputs : cas général. 
 * - les labels
 * 
 * La mise en page d'une liste est la suivante :
 * <div class="liste-wrapper">
 *   <div class="menu clearfix">
 *     <?php echo $this->listeLigneActions($hiddens, $actions, $form_attributes); ?>
 *   </div>
 *   <table class="liste-inner paiements">
 *   </table>
 *   <div class="zone-pagination">
 *   </div>
 * </div>
 * Tous les attributs de la balise <form> sont acceptés, à l'exception de 'method' qui est fixée à POST.
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form/View/Helper
 * @filesource ListeZoneActions.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 09 août 2015
 * @version 2015-2
 */
namespace SbmCommun\Form\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Form\Annotation\Input;

class ListeZoneActions extends AbstractHelper
{

    private $form_name;

    /**
     * Crée le code d'un formulaire d'actions à utiliser au-dessus d'une liste.
     *
     * @param array $hiddens
     *            Tableau indexé array(name => value, ...). S'il n'y a pas de valeur mettre <b>null</b>.
     * @param array $buttons
     *            Tableau indexé array(name => array(), ...)
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
    public function __invoke($hiddens = array(), $buttons = array(), $attributes = array())
    {
        $result = $this->openForm($attributes);
        if (! array_key_exists('op', $hiddens)) {
            $hiddens['op'] = null;
        }
        $result .= $this->getHiddens($hiddens);
        $result .= $this->getMenuBar($buttons);
        return $result . $this->closeForm();
    }

    /**
     * Renvoie le code de la barre de menu
     *
     * @param array $buttons
     *            Ce tableau a la structure la suivante : array(name => attributes, ...) où attributes est un tableau
     *            (voir getMenuOnglet ou getButton pour la structure de attributes)
     *            
     * @return string
     */
    private function getMenuBar($buttons)
    {
        $result = '';
        foreach ($buttons as $name => $attributes) {
            if (empty($result)) {
                $result = "<ul class=\"menubar\">\n";
            }
            if (array_key_exists('label', $attributes) && $attributes['label']) {
                $result .= $this->getMenuOnglet($attributes);
            } else {
                $result .= '<li class="onglet">' . $this->getButton($name, $attributes) . "</li>\n";
            }
        }
        if (! empty($result)) {
            $result .= "</ul>\n";
        }
        return $result;
    }

    /**
     * Renvoie un onglet et le sous-menu éventuellement défini pour cet onglet.
     * L'onglet possède obligatoirement la classe css 'onglet' qui sera rajouté si besoin.
     *
     * @param array $attributes
     *            Ce tableau possède des clés parmi les suivantes :<ul>
     *            <li>label : booléen true</li>
     *            <li>class : classe css à appliquer</li>
     *            <li>value : le libellé de l'onglet à afficher dans la barre de menu</li>
     *            <li>menu : un tableau décrivant le sous-menu associé à cet onglet (voir getNavigation pour connaitre sa structure)</li></ul>
     *            
     * @return string
     */
    private function getMenuOnglet($attributes)
    {
        $result = '<li';
        if (array_key_exists('menu', $attributes) && empty($attributes['menu'] && array_key_exists('title', $attributes))) {
            $result .= ' title="' . $attributes['title'] . '"';
        }
        if (array_key_exists('class', $attributes)) {
            $class = $attributes['class'];
            if (strpos($class, 'onglet') === false && array_key_exists('menu', $attributes)) {
                $class .= ' onglet';
            }
        } else {
            $class = 'onglet';
        }
        if (strpos($class, 'fam-') !== false) {
            $result .= ' class="' . $class . '">';
        } else {
            if (array_key_exists('value', $attributes)) {
                $result .= ' class="' . $class . '">' . $attributes['value'];
            } else {
                $result .= ' class="' . $class . '">';
            }
        } 
        if (array_key_exists('menu', $attributes) && is_array($attributes['menu'])) {
            $result .= $this->getNavigation($attributes['menu']);
        }
        $result .= "</li>\n";
        return $result;
    }

    /**
     * Construit un menu à placer dans un onglet
     *
     * @param array $menu
     *            Ce tableau a la structure la suivante : array(name => attributes, ...) où attributes est un tableau
     *            (voir getButton pour la structure de attributes)
     *            
     * @return string
     */
    private function getNavigation($menu)
    {
        $result = '';
        foreach ($menu as $name => $attributes) {
            if (empty($result)) {
                $result = "<ul class=\"navigation\">\n";
            }
            $result .= '<li>' . $this->getButton($name, $attributes) . "</li>\n";
        }
        if (! empty($result)) {
            $result .= "</ul>\n";
        }
        return $result;
    }

    private function getButton($name, $attributes)
    {
        $result = '<input type="submit" name="' . $name . '" id="' . $name . '"';
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
        $result .= '>';
        return $result;
    }
    
    // ================= ancien ==============================
    private function getButtons($buttons)
    {
        $result = '';
        foreach ($buttons as $name => $attributes) {
            if (empty($result)) {
                $result = '<ul class="menubar">';
            }
            $result .= '<li class="onglet">' . $this->getItem($attributes) . "</li>\n";
            $result .= '<li class="onglet"><input type="submit" name="' . $name . '" id="' . $name . '"';
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
            $result .= "></li>\n";
        }
        if (! empty($result)) {
            $result .= '</ul>';
        }
        return $result;
    }

    private function getItem($attributes)
    {
        if (array_key_exists('label', $attributes)) {
            return '<li class="onglet">' . $this->getItem($attributes) . "</li>\n";
        } else {}
    }

    /**
     * Renvoie dans une chaine les balises &lt;input type="hidden" name="quelque_chose" id= &gt; indiqués dans le tableau $hiddens.
     * Chaque input a un id qui est identique à name. Il doit être unique dans la page.
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
        $result = '';
        foreach ($hiddens as $name => $value) {
            $result .= '<input type="hidden" name="' . $name . '" id="' . $name . '" value';
            if (! is_null($value)) {
                $result .= '="' . $value . '"';
            }
            $result .= ">\n";
        }
        return $result;
    }

    /**
     * Renvoie la balise <form .
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     * ..>
     *
     * @param array $attributes            
     * @return string
     */
    private function openForm($attributes)
    {
        // pour la compatibilité aves HTML 4, un attribut 'id' est toujours placé en même temps que l'attribut 'name' et prend la même valeur.
        if (array_key_exists('name', $attributes)) {
            $this->form_name = $attributes['name'];
        } elseif (array_key_exists('id', $attributes)) {
            $this->form_name = $attributes['id'];
        } else {
            $this->form_name = 'zoneactions';
        }
        $result = '<form id="' . $this->form_name . '" name="' . $this->form_name . '" method="post"';
        // ensuite on place les autres attributs s'ils sont précisés.
        // class (classe css)
        if (array_key_exists('class', $attributes)) {
            $result .= ' class="' . $attributes['class'] . '"';
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