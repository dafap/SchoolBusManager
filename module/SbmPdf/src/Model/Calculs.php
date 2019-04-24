<?php
/**
 * Traitement des formules dans les pieds de page et les pieds de document
 *
 * Les fonctions évaluées sont : 
 *   %anneescolaire%
 *   %date%
 *   %heure%
 *   %millesime%
 *   %compte%
 *   %compte(condition)% où condition est une chaine compatible à la classe Conditions
 *   %somme(num_colonne) où num_colonne est le numéro de colonne en partant de 1 pour la première
 *   %moyenne(num_colonne)%
 *   %max(num_colonne)%
 *   %min(num_colonne)%
 * 
 * Pour rajouter une fonction de calcul, il suffit de construire une méthode qui porte son nom (minuscules sans les % délimitant son nom dans la chaine à évaluer)
 *   
 * Exemple d'utilisation :
 * echo '<h1>Functions</h1><pre>';
 * $data = [
 *  [ 1,  2, 12],
 *  [12,  6,  7)]
 *  [14, 15, 12],
 *  [20,  7, 18],
 *  [17, 16, 18]
 * ];
 * $f1 = 'Nombre: %compte% lignes - Total col1: %moyenne(1)% col2: %moyenne(2)% col3: %moyenne(3)%';
 * $oCalculs = new Calculs($data);
 * $oCalculs->range(0, 2);
 * echo $oCalculs->getResultat($f1) . "\n";
 * echo $oCalculs->getResultat($f1) . "\n"; // pas de ré-évaluation des calculs qui sont bufferisés dans la propriété resultats
 * $oCalculs->range(3, 4);
 * echo $oCalculs->getResultat($f1) . "\n"; // ré-évaluation puisque range() a changé les propriétés debut et fin
 * $oCalculs->range(0, 4);
 * echo $oCalculs->getResultat($f1) . "\n"; // ré-évaluation puisque range() a changé les propriétés debut et fin
 * echo $oCalculs->getResultat($f1) . "\n"; // pas de ré-évaluation des calculs qui sont bufferisés dans la propriété resultats
 * echo $oCalculs->getResultat($f1) . "\n"; // pas de ré-évaluation des calculs qui sont bufferisés dans la propriété resultats
 * echo "\n</pre>";
 * 
 * @project sbm
 * @package SbmPdf/Model
 * @filesource Calculs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPdf\Model;

use SbmBase\Model\Session;

class Calculs
{

    private $data;

    private $debut;

    private $fin;

    private $arg_default;

    private $expressions = [];

    private $resultats;

    /**
     * Constructeur
     *
     * @param array $data
     *            Le tableau data est constitué de lignes et de colonnes
     * @param int $arg_default
     *            Colonne sur laquelle porteront les calculs si elle n'est pas précisée
     *            dans la chaine
     */
    public function __construct($data, $arg_default = 1)
    {
        $this->data = (array) $data;
        $this->arg_default = $arg_default;
        $this->debut = PHP_INT_MAX; // force le range qui suit
        $this->range();
    }

    /**
     * Remise à zéro du buffer des résultats
     */
    public function reset()
    {
        $this->resultats = [];
    }

    /**
     * Initialise la première et la dernière ligne à prendre en compte dans le tableau des
     * data pour les calculs et vide le buffer des résultats. Sans paramètre, tout le
     * tableau des data sera pris en compte de la première à la dernière ligne. Si le
     * second paramètre est null ou s'il est supérieur à la taille du tableau des data il
     * sera initialisé à la dernière ligne du tableau des data
     * Si les paramètres debut et fin sont inchangés, il n'y a pas de reset du buffer des
     * résultats
     *
     * @param number $debut
     * @param number|null $fin
     */
    public function range($debut = 0, $fin = null)
    {
        if (! is_int($debut) || (! is_null($fin) && ! is_int($fin))) {
            throw new Exception(
                'Erreur de type dans les arguments de la méthode ' . __METHOD__);
        }
        if (! ($this->debut == $debut && $this->fin == $fin)) {
            $this->debut = $debut;
            if (is_null($fin) || $fin > count($this->data)) {
                $fin = count($this->data) - 1;
            }
            $this->fin = $fin;
            $this->reset();
        }
    }

    /**
     * Analyse une chaine pour construire la structure des expressions
     *
     * @param string $s
     *            Chaine à analyser.
     * @param string $idx
     *            Clé du tableau expression. En général, c'est le codage md5 de la chaine.
     */
    private function analyse($s, $idx)
    {
        $s = $this->getExpression($s);
        $pattern = '/%([a-z]+)(?:{([^};]*)})?%/i';
        $matches = null;
        if (preg_match_all($pattern, $s, $matches) !== false) {
            $this->expressions[$idx]['search'] = $matches[0];
            $this->expressions[$idx]['functions'] = isset($matches[1]) ? $matches[1] : [];
            $this->expressions[$idx]['args'] = isset($matches[2]) ? $matches[2] : [];
        }
        return $s;
    }

    /**
     * Contrôle si le nombre de parenthèses de l'expression est cohérent et renvoie
     * l'expression après avoir remplacé les parenthèses de premier niveau par des
     * accolades.
     *
     * @param string $expression
     * @throws \Exception si l'expression est mal parenthésée
     * @return string
     */
    private function getExpression($expression)
    {
        $parentheses = [];
        for ($np = 0, $i = 0; $i < mb_strlen($expression) && $np >= 0; $i ++) {
            $c = $expression[$i];
            if ($c == '(' || $c == ')') {
                if ($np == 0 && $c == '(')
                    $parentheses[$i] = '{';
                elseif ($np == 1 && $c == ')')
                    $parentheses[$i] = '}';
                if ($c == '(')
                    $np ++;
                else
                    $np --;
            }
        }
        if ($np != 0) {
            $msg = sprintf("L'expression est mal parenthésée : %s", $expression);
            throw new \Exception($msg);
        }
        foreach ($parentheses as $position => $accolade) {
            $expression = substr_replace($expression, $accolade, $position, 1);
        }
        return $expression;
    }

    /**
     * Renvoie la chaine après avoir remplacé les fonctions de calcul par leurs valeurs.
     *
     * @param string $s
     *            Chaine modèle contenant les mots clés
     * @return string
     */
    public function getResultat($s)
    {
        if ($s == '')
            return '';
        if (array_key_exists($key = md5($s), $this->resultats))
            return $this->resultats[$key];

        if (! array_key_exists($key, $this->expressions)) {
            $s = $this->analyse($s, $key);
        }
        $search = $this->expressions[$key]['search'];
        $replace = [];
        for ($j = 0; $j < count($search); $j ++) {
            if (! method_exists($this, $this->expressions[$key]['functions'][$j]))
                continue;
            $replace[] = $this->{$this->expressions[$key]['functions'][$j]}(
                $this->expressions[$key]['args'][$j]);
        }
        return $this->resultats[$key] = str_replace($search, $replace, $s);
    }

    public function dumpData()
    {
        die(var_dump($this->data));
    }

    /**
     * ***************************************************************************************************************
     * Définition des différentes fonctions de calcul
     * ***************************************************************************************************************
     */

    /**
     * Renvoie l'année scolaire courante
     *
     * @return string
     */
    public function anneescolaire()
    {
        $millesime = Session::get('millesime');
        return sprintf('%d-%d', $millesime, $millesime + 1);
    }

    /**
     * Renvoie la date du jour.
     *
     * @return string Date au format jj/mm/aaaa
     */
    public function date()
    {
        return date('d/m/Y');
    }

    /**
     * Renvoie l'heure actuelle.
     *
     * @return string Heure actuelle au format hh:mm:ss
     */
    public function heure()
    {
        return date('H:i:s');
    }

    /**
     * Renvoie le millesime courant
     *
     * @return int
     */
    public function millesime()
    {
        return Session::get('millesime');
    }

    /**
     * Renvoie le nombre de lignes du tableau dans l'intervalle fixé par la méthode range.
     *
     * @return number Nombre de lignes
     */
    public function compte($condition = null)
    {
        if (is_null($condition) || $condition == '') {
            return $this->fin - $this->debut + 1;
        }
        $oConditions = new Conditions($condition);
        $column = $this->arg_default;
        $colonne = $column - 1;
        $compte = 0;
        for ($ligne = $this->debut; $ligne <= $this->fin; $ligne ++) {
            if (! isset($this->data[$ligne][$colonne])) {
                ob_start();
                print_r($this->data);
                $dump = html_entity_decode(strip_tags(ob_get_clean()));
                throw new Exception(
                    "Ligne $ligne. Pas de colonne n° $column dans les données ($colonne).\n$dump\n");
            }
            $val = $this->data[$ligne][$colonne];
            if ($oConditions->value($val)) {
                $compte ++;
            }
        }
        return $compte;
    }

    private function nombre()
    {
        return $this->compte();
    }

    /**
     * Renvoie la somme de la colonne précisée dans l'intervalle fixé par la méthode
     * range.
     *
     * @param int $column
     *            Numéro de la colonne en partant de 1
     * @return number Somme
     */
    public function somme($column)
    {
        if (empty($column))
            $column = $this->arg_default;
        $colonne = $column - 1;
        $somme = 0;
        for ($ligne = $this->debut; $ligne <= $this->fin; $ligne ++) {
            if (! isset($this->data[$ligne][$colonne])) {
                ob_start();
                print_r($this->data);
                $dump = html_entity_decode(strip_tags(ob_get_clean()));
                throw new Exception(
                    "Ligne $ligne. Pas de colonne n° $column dans les données ($colonne).\n$dump\n");
            }
            $val = $this->data[$ligne][$colonne];
            $somme += is_numeric($val) ? $val : 0;
        }
        return $somme;
    }

    /**
     * Renvoie la moyenne de la colonne précisée dans l'intervalle fixé par la méthode
     * range.
     *
     * @param int $column
     *            Numéro de la colonne en partant de 1
     * @return number Moyenne
     */
    public function moyenne($column)
    {
        return $this->somme($column) / ($this->compte() ?: 1);
    }

    /**
     * Renvoie la plus grande valeur de la colonne précisée dans l'intervalle fixé par la
     * méthode range.
     *
     * @param int $column
     *            Numéro de la colonne en partant de 1
     * @return number Max
     */
    public function max($column)
    {
        if (empty($column))
            $column = $this->arg_default;
        $colonne = $column - 1;
        $max = - PHP_INT_MAX;
        for ($ligne = $this->debut; $ligne <= $this->fin; $ligne ++) {
            $val = $this->data[$ligne][$colonne];
            if (! is_numeric($val))
                $val = 0;
            if ($max < $val)
                $max = $val;
        }
        return $max;
    }

    /**
     * Renvoie la plus petite valeur de la colonne précisée dans l'intervalle fixé par la
     * méthode range.
     *
     * @param int $column
     *            Numéro de la colonne en partant de 1
     * @return number Min
     */
    public function min($column)
    {
        if (empty($column))
            $column = $this->arg_default;
        $colonne = $column - 1;
        $min = PHP_INT_MAX;
        for ($ligne = $this->debut; $ligne <= $this->fin; $ligne ++) {
            $val = $this->data[$ligne][$colonne];
            if (! is_numeric($val))
                $val = 0;
            if ($min > $val)
                $min = $val;
        }
        return $min;
    }
}
