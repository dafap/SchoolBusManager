<?php
/**
 * Objet de manipulation des thèmes
 *
 *
 * @project sbm
 * @package SbmInstallation/src/Model
 * @filesource Theme.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2021
 * @version 2021-2.5.14
 */
namespace SbmInstallation\Model;

use SbmBase\Model\DateLib;
use SbmBase\Model\StdLib;

class Theme
{

    const PATERN_THEME = '/\'THEME\', \'(.*)\'/';

    /**
     * Chemin de config/autoload
     *
     * @var string
     */
    private $path_config_autoload;

    /**
     * Nom complet du fichier sbm.local.php
     *
     * @var string
     */
    private $sbm_local_php;

    /**
     * Contenu du fichier sbm.local.php décomposé en tableau
     *
     * @var array
     */
    private $sbm_local_content;

    /**
     * Nom du thème en cours. C'est aussi le nom des dossiers contenant les configurations
     * du thème (à partir de config/themes) et les css du thème (à partir de public/css)
     *
     * @var string
     */
    private $theme;

    public function __construct()
    {
        $this->path_config_autoload = StdLib::findParentPath(__DIR__, 'config/autoload');
        $this->sbm_local_php = $this->path_config_autoload . '/sbm.local.php';
        $this->sbm_local_content = file($this->sbm_local_php);
        $config_sbm = include ($this->sbm_local_php);
        if ($config_sbm['sbm']['client']['sigle'] == 'SBM') {
            // default
            $this->setTheme();
        } else {
            $this->setTheme($config_sbm['sbm']['client']['sigle']);
        }
    }

    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Met en place le thème dans config/autoload/sbm.local.php
     *
     * @param string $value
     */
    public function setTheme(string $value = 'default')
    {
        $pattern = [
            '/@date (.*)/',
            self::PATERN_THEME
        ];
        $replacement = [
            '@date ' . DateLib::today(),
            "'THEME', '$value'"
        ];
        file_put_contents($this->sbm_local_php,
            implode('', preg_replace($pattern, $replacement, $this->sbm_local_content)));
        $this->theme = $value;
    }

    /**
     * Enregistre une configuration sous forme d'un tableau associatif (partiellement) à 2
     * dimensions et dont les champs de dimension 2 on un tableau indexé en niveau 2 dans
     * un fichier $fileName du dossier de configuration du thème.
     *
     * @param string $fileName
     *            Nom du fichier (dans le dossier config du thème)
     * @param array $array
     *            Tableau de configuration reçu
     * @param array $args
     *            Champs de niveau 2 pour lesquels il faut filtrer les lignes vides
     * @return number
     */
    public function setConfigFileN2Idx(string $fileName, array $array, array $args = [])
    {
        unset($array['submit']);
        foreach ($args as $field) {
            $array[$field] = array_filter($array[$field],
                function ($v) {
                    return ! empty($v);
                });
        }
        $buffer = sprintf('%s %s;', $this->getCommentConfigFile($fileName),
            var_export($array, true));
        $target = StdLib::concatPath($this->getThemeConfigFolder(), $fileName);
        return file_put_contents($target, $buffer);
    }

    /**
     * Enregistre la configuration contenue dans $array dans le fichier $fileName sous la
     * forme d'un tableau associatif (partiellement) à 2 dimensions. Le tableau de
     * configuration peut avoir une dimension 2 sur certains champs. Dans ce cas, les
     * tableaux de niveau 2 sont soit des tableaux indexés, soit des tableaux associatifs.
     * Le tableau reçu est le retour d'un post. Il ne peut donc pas être de fourni sous la
     * même structure. Aussi, les champs de niveau 2 sont décomposés en 2 enregistrement,
     * l'index et la valeur. La closure reconstitue la bonne structure. Exemple:
     *
     * @formatter off
     *   Soit la configuration :
     *   [
     *      a => 3,
     *      b => [
     *          ba => x,
     *          bb => y
     *      ]
     *   ]
     *  Cette configuration est reçue dans $array sous la forme :
     *  [
     *    a => 3
     *    index-b1 => ba,
     *    value-b1 => x,
     *    index-b2 => bb,
     *    value-b2 => y
     *  ]
     *  La closure la reconstitue en supprimant éventuellement les lignes où index-?? serait vide.
     *  (car un index ne peut pas être vide - mais on garde l'index s'il vaut 0)
     * @ formatter on
     *
     * @param string $fileName
     *            Nom du fichier du thème dans /config/themes/[mon_theme]/config
     * @param array $array
     *            tableau php à enregistrer dans ce fichier
     * @param array $args
     *            champs de niveau 2 pour lesquels il faut filtrer les lignes vides ou
     *            sans index
     * @return number
     */
    public function setConfigFileN2Asso(string $fileName, array $array, array $args = [])
    {
        unset($array['submit']);
        foreach ($args as $field) {
            $array[$field] = (function () use ($array, $field) {
                $key1 = "index-$field";
                $value = "value-$field";
                if (array_key_exists($key1, $array)) {
                    $t = [];
                    for ($i = 0; $i < count($array[$key1]); $i ++) {
                        $key2 = trim($array[$key1][$i]);
                        if (! empty($key2) || $key2 === '0') {
                            $t[$key2] = $array[$value][$i];
                        }
                    }
                    return $t;
                } else {
                    return [];
                }
            })();
            unset($array["index-$field"], $array["value-$field"]);
        }
        $buffer = sprintf('%s %s;', $this->getCommentConfigFile($fileName),
            var_export($array, true));
        $target = StdLib::concatPath($this->getThemeConfigFolder(), $fileName);
        return file_put_contents($target, $buffer);
    }

    /**
     * Enregister dans le thème le tableau de configuration indiqué
     *
     * @param string $filename
     * @param array $array
     * @return number
     */
    public function setConfigFile(string $filename, array $array)
    {
        $buffer = sprintf('%s %s;', $this->getCommentConfigFile($filename),
            var_export($array, true));
        $target = StdLib::concatPath($this->getThemeConfigFolder(), $filename);
        return file_put_contents($target, $buffer);
    }

    /**
     * Renvoie le nombre d'octets écrits dans le fichier ou false en cas d'erreur
     *
     * @param string $filename
     * @param string $content
     * @return number
     */
    public function setCssFile(string $filename, string $content)
    {
        $css_file = $this->getThemeCssFolder() . '/' . $filename;
        return file_put_contents($css_file, $content);
    }

    /**
     * Renvoie le nombre d'octets écrits dans le fichier ou false en cas d'erreur
     *
     * @param string $filename
     * @param string $content
     * @return number
     */
    public function setHtmlFile(string $filename, string $content)
    {
        $html_file = $this->getThemeViewFolder() . '/' . $filename;
        return file_put_contents($html_file, $content);
    }

    /**
     * Renvoie un tableau décrit dans le fichier de configuration du thème sous le nom
     * indiqué
     *
     * @param string $fileName
     * @return array
     */
    public function getConfigFile(string $fileName)
    {
        $include_file = StdLib::concatPath($this->getThemeConfigFolder(), $fileName);
        if (file_exists($include_file)) {
            return include ($include_file);
        } else {
            return [];
        }
    }

    /**
     * Donne le contenu du fichier css indiqué
     *
     * @param string $filename
     * @return string
     */
    public function getCssFile(string $filename)
    {
        $filename = '/' . ltrim($filename, '/');
        $css_file = $this->getThemeCssFolder() . $filename;
        return file_get_contents($css_file);
    }

    public function getHtmlFile(string $filename)
    {
        $filename = '/' . ltrim($filename, '/');
        $html_file = $this->getThemeViewFolder() . $filename;
        return file_get_contents($html_file);
    }

    /**
     * Renvoie le tableau d'aide associé au fichier $filename
     *
     * @param string $filename
     * @return array
     */
    public function getHelp(string $filename)
    {
        $filename = '/' . ltrim($filename, '/');
        $html_file = $this->getThemeViewFolder() . $filename;
        $parts = explode('.', $html_file);
        $filehelp = $parts[0] . '.help.php';
        if (file_exists($filehelp)) {
            return include ($filehelp);
        }
        die(var_dump($filehelp));
        return [];
    }

    /**
     * Donne l'url relative du fichier indiqué, ou le chemin si le fichier n'est pas
     * indiqué usage dans une vue : $this->basePath($theme->getCssPath($fileName))
     *
     * @param string $filename
     * @return string
     */
    public function getCssPath(string $filename = '')
    {
        $filename = '/' . ltrim($filename, '/');
        return 'css/' . strtolower($this->theme) . $filename;
    }

    /**
     * Donne l'url relative du fichier indiqué, ou le chemin si le fichier n'est pas
     * indiqué usage dans une vue : $this->basePath($theme->getJsPath($fileName))
     *
     * @param string $filename
     * @return string
     */
    public function getJsPath(string $filename = '')
    {
        $filename = '/' . ltrim($filename, '/');
        return 'js/' . strtolower($this->theme) . $filename;
    }

    /**
     * Renvoie le chemin complet du fichier view indiqué par le nom relatif de sa vue. Par
     * exemple, pour la vue 'sbm-front/index/index-avant.phtmp', cela renverra :
     * '[quelquechose]/config/themes/[montheme]/view/sbm-front/index/index-avant.php'
     * L'extension .phtml n'est pas obligatoire.
     *
     * @param string $pathViewName
     * @return string
     */
    public function getIncludeView(string $pathViewName)
    {
        $pathViewName = preg_replace('/\.phtml$/', '', $pathViewName) . '.php';
        $pathViewName = '/' . ltrim($pathViewName, '/');
        return StdLib::findParentPath(__DIR__, 'config/themes/') .
            strtolower($this->theme) . '/view' . $pathViewName;
    }

    /**
     * Utile en public pour accéder à calendar.config.php qui contient le modèle d'année
     * scolaire pour le thème.
     *
     * @return string
     */
    public function getThemeConfigFolder()
    {
        return StdLib::findParentPath(__DIR__, 'config/themes/') .
            strtolower($this->theme) . '/config';
    }

    public function getConfigCalendar()
    {
        return include $this->getThemeConfigFolder() . '/calendar.config.php';
    }

    private function getThemeViewFolder()
    {
        return StdLib::findParentPath(__DIR__, 'config/themes/') .
            strtolower($this->theme) . '/view';
    }

    private function getThemeCssFolder()
    {
        return StdLib::findParentPath(__DIR__, 'public/css/') . strtolower($this->theme);
    }

    private function getCommentConfigFile(string $fileName)
    {
        $modele = <<<EOT
<?php
/**
 * Fichier de configuration
 *
 * Thème %s
 *
 * @project sbm
 * @package config/themes/%s/config
 * @filesource %s
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date %s
 * @version %s
 */

return
EOT;
        $version_inc = StdLib::concatPath(StdLib::findParentPath(__DIR__, 'config'),
            'version.inc.php');
        return sprintf($modele, $this->theme, strtolower($this->theme), $fileName,
            DateLib::today(), include ($version_inc));
    }
}
