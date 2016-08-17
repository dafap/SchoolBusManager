<?php
/**
 * Classe qui contient des méthodes générales au projet
 *
 * @project sbm
 * @package module/SbmBase/src/SbmBase/Model
 * @filesource StdLib.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
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
        if ($entityType == 'table') {
            $tpe = $prefix == '' ? 't_' : '_t_';
        } elseif ($entityType == 'system') {
            $tpe = $prefix == '' ? 's_' : '_s_';
        } else {
            $tpe = $prefix == '' ? 'v_' : '_v_';
        }
        return $prefix . $tpe . $entityName;
    }

    /**
     * Renvoie true si toutes les clés contenues dans le tableau $keys existent de façon emboitée dans $search.
     * (cad si $keys possède n élements, $search[$key1][$key2]...[$keyn] est défini)
     * Renvoie false si $search n'est pas un tableau ou si une clé n'existe pas au rang prévu.
     *
     * @param array $keys            
     * @param array $search            
     * @return boolean
     */
    public static function array_keys_exists($keys, $search)
    {
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
     * Reçoit un tableau multidimensionnel et renvoie un objet
     *
     * @param array $array            
     * @return StdClass|unknown
     */
    public static function arrayToObject($array)
    {
        if (is_array($array)) {
            foreach ($array as &$item) {
                $item = self::arrayToObject($item);
                unset($item);
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
        if (! \is_array($array)) {
            ob_start();
            var_dump($array);
            $mess = sprintf("%s : Mauvaise configuration des paramètres. Un tableau est attendu. On a reçu %s", __METHOD__, html_entity_decode(strip_tags(ob_get_clean())));
            throw new Exception($mess);
        }
        if (! \is_string($index) && ! \is_integer($index)) {
            ob_start();
            print_r($index);
            $mess = sprintf("Le paramètre demandé doit être une chaîne de caractères ou un entier. On a reçu %s", html_entity_decode(strip_tags(ob_get_clean())));
            throw new Exception($mess);
        }
        if (\array_key_exists($index, $array)) {
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
        if (! \is_array($array)) {
            ob_start();
            var_dump($array);
            $mess = sprintf("%s : Mauvaise configuration des paramètres. Un tableau est attendu. On a reçu %s", __METHOD__, html_entity_decode(strip_tags(ob_get_clean())));
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
     * Si $file commence par un double-slash, c'est une adresse absolue.
     * La renvoyer sans concaténer.
     * Sinon, concaténer le $path et le $file en vérifiant les séparateurs de chemin.
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
            throw new Exception(__METHOD__ . 'Des chaînes de caractères sont attendues comme paramètres.');
        }
        if (substr($file, 0, 2) == '//') {
            return $file;
        }
        $result = rtrim(str_replace('\\', '/', $path), '/');
        $result .= '/';
        $result .= \ltrim($file, '/');
        return $result;
    }

    /**
     * Renvoie la valeur $val si ce n'est pas une chaine.
     * Evalue la chaine si c'est une valeur numérique
     * Evalue la chaine si c'est une valeur booléenne (true|false, vrai|faux, yes|no, oui|non)
     * Renvoie la chaine encadrée par des simples quotes, en échappant les apostrophes présentes si nécessaire et en supprimant les espaces de début et de fin
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
     * Renvoie un tableau associatif à partir d'une chaine qui décrit le tableau de la façon suivante :
     * clé1 => valeur1, clé2 => valeur2, etc.
     * Attention :
     * clé1, clé2, clé3 ... sont numériques, booléen ou string
     * valeur1, valeur2, valeur3 ... sont numériques ou string
     * Il n'est pas nécessaire d'encadrer les chaines par des guillemets ou des apostrophes ; ce sera fait par la méthode
     * Le séparateur de lignes du tableau est la virgule
     *
     * @param string $str            
     *
     * @return array
     */
    public static function getArrayFromString($str)
    {
        // on analyse la chaine reçue et on la formate correctement, avec quotes et échappement
        $trows = explode(',', $str); // tableau de lignes
        foreach ($trows as &$row) {
            $tkeyvalue = explode('=>', $row);
            foreach ($tkeyvalue as &$element) {
                $element = self::addQuotesToString($element);
                unset($element);
            }
            $row = implode('=>', $tkeyvalue);
            unset($row);
        }
        $str = implode(',', $trows);
        
        // construction du tableau résultat
        $tableau = array();
        eval("\$tableau=array($str);");
        return $tableau;
    }

    /**
     * Traduit la donnée $data si c'est une clé du tableau $array
     *
     * @param mixed $data            
     * @param array $array            
     * @return mixed
     */
    public static function translateData($data, $array)
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $item) {
                $result[] = array_key_exists($item, $array) ? $array[$item] : $item;
            }
            return implode('+', $result);
        }
        if (is_null($data))
            return '';
        return array_key_exists($data, $array) ? $array[$data] : $data;
    }

    /**
     *
     * @param numeric|string $data            
     * @param int $precision            
     * @param int $completion            
     * @return numeric|string
     */
    public static function formatData($data, $precision, $completion)
    {
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
}