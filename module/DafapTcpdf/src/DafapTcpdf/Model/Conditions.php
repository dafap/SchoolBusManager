<?php
/**
 * Gestion des conditions
 *
 * Une condition est une chaîne où la variable à tester est représentée par ?.
 * L'égalité peut être notée = ou ==
 * La non égalité est noté <> ou !=
 * Les opérateurs de comparaison <, <=, >, >= sont acceptés
 * Les opérateurs et, and, ou, or, &&, ||, xor sont acceptés
 * 
 * Les structures sont interprétées dans les méthodes commençant par `struc_`. 
 * Pour rajouter une nouvelle structure, il suffit de définir sa méthode.
 * Penser à mettre l'expression php à évaluer entre parenthèses afin de pouvoir enchainer les conditions.
 * 
 * Tous les mots sont mis entre quotes avant l'évaluation, à l'exception des mots autorisés par leur propriété.
 * Par exemple, in_array n'est évalué que si la propriété `enable['in_array']` existe et est vraie.
 * 
 * Si la condition est incohérente (syntaxe incorrecte), son évaluation renvoie false et il n'y a pas d'alerte.
 * 
 * @project sbm
 * @package DafapTcpdf
 * @filesource Conditions.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 oct. 2014
 * @version 2014-1
 */
namespace DafapTcpdf\Model;

class Conditions
{
    /**
     * Nom de la variable dans la condition. Cette variable sera définie dans la méthode value()
     * @var string
     */
    const VAR_NAME = 'valeur_a_tester_pour_la_condition';

    /**
     * Condition formée dans la méthode parse() à partir d'une analyse de la chaine fournie par la méthode setCondition()
     * @var string
     */
    private $condition;

    /**
     * Liste des mots clés autorisés
     * @var array
     */
    private $enable;

    /**
     * Construit un object Conditions
     *
     * @param string $condition
     *            condition à tester. L'égalité est notée = ou ==, la variable est notée ?.
     *            Les structures acceptées sont décrites dans les méthodes commençant par `struct_`.
     * @throws Exception si la condition n'est pas une chaine
     */
    public function __construct($condition)
    {
        if (! is_string($condition)) {
            ob_start();
            var_dump($condition);
            $dump = html_entity_decode(strip_tags(ob_get_clean()));
            throw new Exception("La condition indiquée devrait être une chaîne. On a reçu :\n$dump");
        }
        
        $this->enable = array();
        $this->setCondition($condition);
    }

    /**
     * Reçoit la valeur à tester et renvoie la valeur de la condition
     * 
     * @param mixed $value
     *      valeur à tester
     * @return boolean
     */
    public function value($value)
    {
        ${self::VAR_NAME} = $value;
        return (bool) @eval("return $this->condition;");
    }

    /**
     * Affecte la propriété `condition`
     * par la transformation de la condition reçue pour appliquer les structures et les règles de sécurité
     * 
     * @param string $condition
     */
    public function setCondition($condition)
    {
        // applique les méthodes des strucures définies
        foreach ($this->liste_structures() as $funct) {
            $condition = $this->{$funct}($condition);
        }
        // parse l'expression pour appliquer les règles de sécurité
        $this->parse($condition);
    }
    
    /**
     * Renvoie la liste des structures définies comme méthodes dont le nom commence par "struct_"
     * 
     * @return array
     */
    private function liste_structures()
    {
        $array = get_class_methods($this);
        $result = array();
        foreach ($array as $item) {
            if (substr($item, 0, strlen('struct_')) == 'struct_') {
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * Analyse la chaine condition et affecte la propriété condition
     * 
     * @param string $condition
     */
    private function parse($condition)
    {
        $this->condition = '';
        $token = token_get_all('<?php eval(' . $condition . '); ?>');
        foreach ($token as $element) {
            if (is_array($element)) {
                switch ($element[0]) {
                    case T_ARRAY:
                        if ($this->enable_in_array)
                            $this->condition .= $element[1];
                        break;
                    case T_BOOLEAN_AND:
                    case T_BOOLEAN_OR:
                    case T_CONSTANT_ENCAPSED_STRING:
                    case T_DNUMBER:
                    case T_IS_EQUAL:
                    case T_IS_GREATER_OR_EQUAL:
                    case T_IS_IDENTICAL:
                    case T_IS_NOT_EQUAL:
                    case T_IS_NOT_IDENTICAL:
                    case T_IS_SMALLER_OR_EQUAL:
                    case T_LOGICAL_AND:
                    case T_LOGICAL_OR:
                    case T_LOGICAL_XOR:
                    case T_LNUMBER:
                    case T_WHITESPACE:
                        $this->condition .= $element[1];
                        break;
                    case T_VARIABLE:
                        if ($element[1] == '$' . self::VAR_NAME)
                            $this->condition .= $element[1];
                        break;
                    case T_STRING:
                        switch ($element[1]) {
                            case 'et':
                                $this->condition .= '&&';
                                break;
                            case 'ou':
                                $this->condition .= '||';
                                break;
                            default:
                                if (array_key_exists($element[1], $this->enable) && $this->enable[$element[1]]) {
                                    $this->condition .= $element[1];
                                } else {
                                    $this->condition .= "'" . str_replace("'", "\'", trim(trim(trim($element[1]), '"'), "'")) . "'";
                                }
                        }
                        break;
                    default:
                        break;
                }
            } else {
                if ($element == '=')
                    $element = '==';
                if ($element == '?')
                    $element = '$' . self::VAR_NAME;
                if ($element == ';')
                    $element = '';
                $this->condition .= $element;
            }
        }
    }

    /**
     * Transforme la structure `? dans (...)` en une structure `in_array(?, array(...))` correcte pour le langage PHP
     * et met à jour la propriété `enable_in_array`
     *
     * @param string $condition            
     * @return string
     */
    private function struct_dans($condition)
    {
        $pattern = '/\?\s*dans\s*\((.*)\)/i';
        if (preg_match($pattern, $condition, $matches)) {
            $this->enable['in_array'] = true;
            $replace = 'in_array(?, array(' . $matches[1] . '))';
            return str_replace($matches[0], $replace, $condition);
        } else {
            return $condition;
        }
    }

    /**
     * Transforme la structure `? sauf (...)` en une structure `!in_array(?, array(...))` correcte pour le langage PHP
     * et met à jour la propriété `enable_in_array`
     *
     * @param string $condition            
     * @return string
     */
    private function struct_sauf($condition)
    {
        $pattern = '/\?\s*sauf\s*\((.*)\)/i';
        if (preg_match($pattern, $condition, $matches)) {
            $this->enable['in_array'] = true;
            $replace = '!in_array(?, array(' . $matches[1] . '))';
            return str_replace($matches[0], $replace, $condition);
        } else {
            return $condition;
        }
    }

    /**
     * Transforme la structure `? est null` en `is_null(?)`
     * et met à jour la propriété `enable_is_null`
     *
     * @param string $condition            
     * @return string
     */
    private function struct_est_null($condition)
    {
        $pattern = '/\?\s*est\s*null/i';
        if (preg_match($pattern, $condition, $matches)) {
            $this->enable['is_null'] = true;
            return str_replace($matches[0], 'is_null(?)', $condition);
        } else {
            return $condition;
        }
    }

    /**
     * Transforme la structure `? pas null` en `!is_null(?)`
     * et met à jour la propriété `enable_is_null`
     *
     * @param string $condition            
     * @return string
     */
    private function struct_pas_null($condition)
    {
        $pattern = '/\?\s*pas\s*null/i';
        if (preg_match($pattern, $condition, $matches)) {
            $this->enable['is_null'] = true;
            return str_replace($matches[0], '!is_null(?)', $condition);
        } else {
            return $condition;
        }
    }

    /**
     * Transforme la structure `? est vide` en `((is_null(?) || (? === "")))`
     *
     * @param string $condition            
     * @return string
     */
    private function struct_est_vide($condition)
    {
        $pattern = '/\?\s*est\s*vide/i';
        if (preg_match($pattern, $condition, $matches)) {
            $this->enable['is_null'] = true;
            return str_replace($matches[0], '(is_null(?) || (? === ""))', $condition);
        } else {
            return $condition;
        }
    }

    /**
     * Transforme la structure `? pas vide` en `(!is_null(?) && (? !== ""))`
     *
     * @param string $condition            
     * @return string
     */
    private function struct_pas_vide($condition)
    {
        $pattern = '/\?\s*pas\s*vide/i';
        if (preg_match($pattern, $condition, $matches)) {
            $this->enable['is_null'] = true;
            return str_replace($matches[0], '(!is_null(?) && (? !== ""))', $condition);
        } else {
            return $condition;
        }
    }

    /**
     * Transforme la structure `? commence par xxx` en `(mb_substr(?, 0, mb_strlen('xxx')) == 'xxx')`
     * et met à jour les propriétés `enable_mb_strlen` et `enable_mb_substr`
     *
     * @param string $condition            
     * @return string
     */
    private function struct_commence_par($condition)
    {
        $pattern = '/\?\s*commence\s*par\s+[\'"]?([\w+-]*)[\'"]?/i';
        if (preg_match($pattern, $condition, $matches)) {
            $this->enable['mb_strlen'] = $this->enable['mb_substr'] = true;
            $replace = '(mb_substr(?, 0, mb_strlen(\'' . $matches[1] . '\')) == \'' . $matches[1] . '\')';
            return str_replace($matches[0], $replace, $condition);
        } else {
            return $condition;
        }
    }

    /**
     * Transforme la structure `? ne commence pas par xxx` en `(mb_substr(?, 0, mb_strlen('xxx')) != 'xxx')`
     * et met à jour les propriétés `enable_mb_strlen` et `enable_mb_substr`
     *
     * @param string $condition            
     * @return string
     */
    private function struct_ne_commence_pas_par($condition)
    {
        $pattern = '/\?\s*ne\s*commence\s*pas\s*par\s+[\'"]?([\w+-]*)[\'"]?/i';
        if (preg_match($pattern, $condition, $matches)) {
            $this->enable['mb_strlen'] = $this->enable['mb_substr'] = true;
            $replace = '(mb_substr(?, 0, mb_strlen(\'' . $matches[1] . '\')) != \'' . $matches[1] . '\')';
            return str_replace($matches[0], $replace, $condition);
        } else {
            return $condition;
        }
    }

    /**
     * Transforme la structure `? finit par xxx` en `(mb_substr(?, -mb_strlen('xxx')) == 'xxx')`
     * et met à jour les propriétés `enable_mb_strlen` et `enable_mb_substr`
     *
     * @param string $condition            
     * @return string
     */
    private function struct_finit_par($condition)
    {
        $pattern = '/\?\s*finit\s*par\s+[\'"]?([\w+-]*)[\'"]?/i';
        if (preg_match($pattern, $condition, $matches)) {
            $this->enable['mb_strlen'] = $this->enable['mb_substr'] = true;
            $replace = '(mb_substr(?, -mb_strlen(\'' . $matches[1] . '\')) == \'' . $matches[1] . '\')';
            return str_replace($matches[0], $replace, $condition);
        } else {
            return $condition;
        }
    }

    /**
     * Transforme la structure `? finit par xxx` en `(mb_substr(?, -mb_strlen('xxx')) != 'xxx')`
     * et met à jour les propriétés `enable_mb_strlen` et `enable_mb_substr`
     *
     * @param string $condition            
     * @return string
     */
    private function struct_ne_finit_pas_par($condition)
    {
        $pattern = '/\?\s*ne\s*finit\s*pas\s*par\s+[\'"]?([\w+-]*)[\'"]?/i';
        if (preg_match($pattern, $condition, $matches)) {
            $this->enable['mb_strlen'] = $this->enable['mb_substr'] = true;
            $replace = '(mb_substr(?, -mb_strlen(\'' . $matches[1] . '\')) != \'' . $matches[1] . '\')';
            return str_replace($matches[0], $replace, $condition);
        } else {
            return $condition;
        }
    }

    /**
     * Transforme la structure `? contient xxx`par `(mb_strpos(?, 'xxx') !== false) `
     * et met à jour la propriété `enable_mb_strpos`
     *
     * @param string $condition            
     * @return string
     */
    private function struct_contient($condition)
    {
        $pattern = '/\?\s*contient\s+[\'"]?([\w+-]*)[\'"]?/i';
        if (preg_match($pattern, $condition, $matches)) {
            $this->enable['mb_strpos'] = true;
            $replace = '(mb_strpos(?, \'' . $matches[1] . '\') !== (0==1))';
            return str_replace($matches[0], $replace, $condition);
        } else {
            return $condition;
        }
    }

    /**
     * Transforme la structure `? ne contient pas xxx`par `(mb_strpos(?, 'xxx') === false) `
     * et met à jour la propriété `enable_mb_strpos`
     *
     * @param string $condition            
     * @return string
     */
    private function struct_ne_contient_pas($condition)
    {
        $pattern = '/\?\s*ne\s*contient\s*pas\s+[\'"]?([\w+-]*)[\'"]?/i';
        if (preg_match($pattern, $condition, $matches)) {
            $this->enable['mb_strpos'] = true;
            $replace = '(mb_strpos(?, \'' . $matches[1] . '\') === (0==1))';
            return str_replace($matches[0], $replace, $condition);
        } else {
            return $condition;
        }
    }
} 