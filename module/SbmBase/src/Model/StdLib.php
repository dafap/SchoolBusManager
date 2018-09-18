<?php
/**
 * Classe qui contient des méthodes générales au projet
 *
 * @project sbm
 * @package module/SbmBase/src/SbmBase/Model
 * @filesource StdLib.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmBase\Model;

abstract class StdLib
{

    /**
     * Renvoie le nom complet de l'entité (table ou view sql)
     * - s'il y a un préfixe, il va être appliqué
     * - puis le nom des tables est préfixé par `t_` et le nom des vues par `v_`
     *
     * @param string $entityName
     * @param string $entityType
     * @param string $prefix
     *
     * @return string
     */
    public static function entityName($entityName, $entityType, $prefix = '')
    {
        $t = empty($prefix) ? '' : $prefix . '_';
        return $t . substr($entityType, 0, 1) . "_$entityName";
    }

    /**
     * Renvoie true si toutes les clés contenues dans le tableau $keys existent de façon emboitée
     * dans $search.
     * (cad si $keys possède n élements, $search[$key1][$key2]...[$keyn] est défini)
     * Renvoie false si $search n'est pas un tableau ou si une clé n'existe pas au rang prévu.
     *
     * @param array $keys
     * @param array $search
     * @return boolean
     */
    public static function array_keys_exists($keys, $search)
    {
        if (! is_array($keys)) {
            throw new Exception('Argument invalide pour le tableau $keys.');
        }
        $s = $search;
        foreach ($keys as $key) {
            if (is_array($s)) {
                if (array_key_exists($key, $s)) {
                    $s = $s[$key];
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Reçoit un tableau multidimensionnel et renvoie un objet.
     * Pour les tableaux ayant des clés numériques :
     *
     * @see http://stackoverflow.com/questions/10333016/how-to-access-object-properties-with-names-like-integers
     *
     * @param array|mixed $array
     * @return \stdClass|mixed
     */
    public static function arrayToObject($array)
    {
        if (is_array($array)) {
            $numeric_key = false;
            foreach ($array as $key => &$item) {
                if (is_numeric($key)) {
                    $numeric_key = true;
                }
                $item = self::arrayToObject($item);
                unset($item);
            }
            if ($numeric_key) {
                return json_decode(json_encode((object) $array));
            }
            return (object) $array;
        }

        return $array;
    }

    /**
     * Renvoie la valeur associée à l'index dans le tableau.
     * La valeur renvoyée peut être de tout type.
     *
     * @param string|int $index
     * @param array $array
     * @param mixed $default
     *
     * @throws Exception
     *
     * @return mixed
     */
    public static function getParam($index, $array, $default = null)
    {
        if (! is_array($array)) {
            ob_start();
            var_dump($array);
            $mess = sprintf(
                "%s : Mauvaise configuration des paramètres. Un tableau est attendu. On a reçu %s",
                __METHOD__, html_entity_decode(strip_tags(ob_get_clean())));
            throw new Exception($mess);
        }
        if (! is_string($index) && ! is_integer($index)) {
            ob_start();
            print_r($index);
            $mess = sprintf(
                "Le paramètre demandé doit être une chaîne de caractères ou un entier. On a reçu %s",
                html_entity_decode(strip_tags(ob_get_clean())));
            throw new Exception($mess);
        }
        if (array_key_exists($index, $array)) {
            return $array[$index];
        } else {
            return $default;
        }
    }

    /**
     * Renvoie la valeur associée à un tableau d'index dans un tableau à multi-dimensions.
     * La valeur renvoyée peut être de tout type.
     *
     * @param array $index
     * @param array $array
     * @param mixed $default
     *
     * @throws Exception
     *
     * @return mixed
     */
    public static function getParamR($index, $array, $default = null)
    {
        if (! is_array($array)) {
            ob_start();
            var_dump($array);
            $mess = sprintf(
                "%s : Mauvaise configuration des paramètres. Un tableau est attendu. On a reçu %s",
                __METHOD__, html_entity_decode(strip_tags(ob_get_clean())));
            throw new Exception($mess);
        }
        if (is_array($index)) {
            if (self::array_keys_exists($index, $array)) {
                $s = $array;
                foreach ($index as $p) {
                    $s = self::getParam($p, $s);
                }
                return $s;
            } else {
                return $default;
            }
        } else {
            return self::getParam($index, $array, $default);
        }
    }

    /**
     * Renvoie le path absolu correspondant à $path dans la branche de l'arborescence
     * de fichiers antérieure à $dir.
     * Renvoie false si le $path n'est pas trouvé.
     *
     * @param string $dir
     * @param string $path
     *
     * @return boolean|string
     */
    public static function findParentPath($dir, $path)
    {
        $previousDir = '.';
        while (! is_dir($dir . DIRECTORY_SEPARATOR . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }
        return $dir . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Si $file commence par un double-slash, c'est une adresse absolue.
     * La renvoyer sans concaténer.
     * Sinon, concaténer le $path et le $file en vérifiant les séparateurs de chemin.
     * Dans le résultat, le séparateur est / quels que soient ceux utilisés dans $path ou $file.
     *
     * @see http://php.net/manual/fr/regexp.reference.escape.php
     *
     * @param string $path
     * @param string $file
     *
     * @throws Exception
     * @return string
     */
    public static function concatPath($path, $file)
    {
        if (! (is_string($path) && is_string($file))) {
            ob_start();
            var_dump($path, $file);
            throw new Exception(
                __METHOD__ . "Des chaînes de caractères sont attendues comme paramètres.\n" .
                html_entity_decode(strip_tags(ob_get_clean())));
        }
        if (substr($file, 0, 2) == '//') {
            return $file;
        } else {
            list ($path, $file) = preg_replace('/([\/\\\\]+)/', '/', [
                $path,
                $file
            ]);
            return rtrim($path, '/') . '/' . ltrim($file, '/');
        }
    }

    /**
     * Renvoie la valeur $val si ce n'est pas une chaine.
     * Evalue la chaine si c'est une valeur numérique
     * Evalue la chaine si c'est une valeur booléenne (true|false, vrai|faux, yes|no, oui|non)
     * Renvoie la chaine encadrée par des simples quotes, en échappant les apostrophes
     * présentes si nécessaire et en supprimant les espaces de début et de fin.
     *
     * @param mixed $val
     *
     * @return string
     */
    public static function addQuotesToString($val)
    {
        if (is_bool($val) || is_null($val) || is_array($val) || is_object($val)) {
            return $val;
        }

        $val = trim($val);

        if (is_numeric($val)) {
            return $val;
        }
        // gestion des booléens dans une chaine
        if ($val == 'true' || $val == 'false') {
            return $val == 'true' ? 1 : 0;
        }
        if ($val == 'vrai' || $val == 'faux') {
            return $val == 'vrai' ? 1 : 0;
        }
        if ($val == 'yes' || $val == 'no') {
            return $val == 'yes' ? 1 : 0;
        }
        if ($val == 'oui' || $val == 'non') {
            return $val == 'oui' ? 1 : 0;
        }
        // gestion des autres chaines
        return "'" . str_replace("'", "\'", trim(trim($val, '"'), "'")) . "'";
    }

    /**
     * Renvoie un tableau associatif à partir d'une chaine qui décrit le tableau de la
     * façon suivante :
     * clé1 => valeur1, clé2 => valeur2, etc.
     * Attention :
     * clé1, clé2, clé3 ... sont numériques, booléen ou string
     * valeur1, valeur2, valeur3 ... sont numériques ou string
     * Il n'est pas nécessaire d'encadrer les chaines par des guillemets ou des apostrophes ;
     * ce sera fait par la méthode
     * Le séparateur de lignes du tableau est la virgule
     *
     * @param string $str
     *
     * @return array
     */
    public static function getArrayFromString($str)
    {
        if (! is_string($str) && ! is_numeric($str) && ! is_null($str)) {
            throw new Exception(
                'Le paramètre doit être une chaine de caractère ou un nombre ou null.');
        }
        // on analyse la chaine reçue et on la formate correctement, avec quotes et échappement
        $trows = explode(',', $str); // tableau de lignes
        foreach ($trows as &$row) {
            $tkeyvalue = explode('=>', $row);
            foreach ($tkeyvalue as &$element) {
                $element = self::addQuotesToString($element);
                // unset($element);
            }
            $row = implode('=>', $tkeyvalue);
            // unset($row);
        }
        $str = implode(',', $trows);

        // construction du tableau résultat
        $tableau = [];
        eval("\$tableau=array($str);");
        return $tableau;
    }

    /**
     * Traduit la donnée $data si c'est une clé du tableau $array
     *
     * @param mixed $data
     * @param array $array
     * @throws Exception (lancée par la méthode traduire)
     * @return mixed
     */
    public static function translateData($data, $array)
    {
        if (is_array($data) && self::isIndexedArray($data)) {
            $result = [];
            foreach ($data as $item) {
                $result[] = self::traduire($item, $array);
            }
            return implode('+', $result);
        }
        return self::traduire($data, $array);
    }

    private static function traduire($data, $array)
    {
        if (! is_string($data) && ! is_numeric($data) && ! is_null($data)) {
            throw new Exception(
                'Le paramètre doit être une chaine de caractère ou un nombre ou null.');
        }
        if (is_null($data)) {
            return '';
        }
        return array_key_exists($data, $array) ? $array[$data] : $data;
    }

    /**
     * Construit un data formaté par sprintf en tenant compte du type de donnée
     * (digit, float, string) de la precision et de la completion.
     *
     * @param number|string|null $data
     *            Si $data est une chaine de digits alors elle est converti en float.
     *            null est considérée comme une chaine vide.
     * @param int $precision
     *            Ignoré si $data est un entier
     *            Indique le nombre de décimales si $data est un décimal ou
     *            une chaine de digits
     *            Indique une troncature de la chaine $data sinon. Par exemple :
     *            formatData('un bel exemple', 4, 9) donnera ' un b' (largeur 9 caractères).
     *            Mettre un nombre négatif pour pas de troncature.
     * @param int|string(0-9) $completion
     *            Indique la largeur minimale de la chaine
     *            Si c'est un entier, completion à gauche par un espace
     *            Si c'est une chaine de chiffres commençant par 0, le caractère de complétion est
     *            0
     *            
     * @return number|string
     */
    public static function formatData($data, $precision, $completion)
    {
        if (is_array($data) || is_object($data)) {
            throw new Exception(
                'La donnée est d\'un type incorrect : nombre, chaine ou null attendus.');
        }
        if ($completion != 0) {
            if ($precision > - 1) {
                $format = "%$completion.$precision";
            } else {
                $format = "%$completion";
            }
        } else {
            if ($precision > - 1) {
                $format = "%.$precision";
            } else {
                return $data; // pas de format (cas par défaut)
            }
        }
        if (is_numeric($data)) {
            if (is_int($data)) {
                $format .= 'd';
            } else {
                $format .= 'f';
            }
        } else {
            $format .= 's';
        }
        return sprintf($format, $data);
    }

    /**
     * Indique si un tableau est indexé (sinon, il est associatif).
     * Dans le cas de tableaux emboités on ne regarde que le premier niveau.
     *
     * @param array $array
     * @throws Exception
     * @return boolean
     */
    public static function isIndexedArray($array)
    {
        if (! is_array($array)) {
            throw new Exception('Le paramètre doit être un tableau.');
        }
        $keys = array_keys($array);
        return array_keys($keys) == array_values($keys);
    }
}