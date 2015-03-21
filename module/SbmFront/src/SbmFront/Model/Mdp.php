<?php
/**
 * Les méthodes sur les mots de passe
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmFront\Model
 * @filesource Mdp.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 févr. 2015
 * @version 2015-1
 */
namespace SbmFront\Model;

use Zend\Crypt\Password\Bcrypt;
class Mdp
{
    private $chars;
    private $caps;
    private $nums;
    private $syms;
    
    /**
     * Indique les lettres, les chiffres et les caractères spéciaux autorisés
     * Par défaut, le i, le l et le o sont interdits pour éviter les confusions entre majuscules, minuscules et chiffres
     */
    public function __construct($chars = 'abcdefghjkmnpqrstuvwxyz', $nums = '0123456789', $syms = '!@#$%^&*()-+?')
    {
        $this->chars = strtolower($chars);
        $this->caps = strtoupper($chars);
        $this->nums = $nums;
        $this->syms = $syms;
    }
    
    public function getChars()
    {
        return $this->chars;
    }
    public function setChars($chars)
    {
        $this->chars = strtolower($chars);
    }
    public function getCaps()
    {
        return $this->caps;
    }
    public function setCaps($caps)
    {
        $this->caps = strtoupper($caps);
    }
    public function getNums()
    {
        return $this->nums;
    }
    public function setNums($nums)
    {
        $this->nums = $nums;
    }
    public function getSyms()
    {
        return $this->syms;
    }
    public function setSyms($syms)
    {
        $this->syms = $syms;
    }
    
    /**
     * Génère un mot de passe et le renvoie sous la forme d'une chaîne de caractères. Par défaut, le mot de passe est 
     * constitué de 8 lettres minuscules.
     * 
     * @param number $l
     * La longueur du mot de passe généré. Elle doit être supérieure ou égale à la somme des autres paramètres.
     * @param number $c
     * Le nombre de lettres majuscules
     * @param number $n
     * Le nombre de chiffres
     * @param number $s
     * Le nombre de caractères spéciaux
     * 
     * @throws Exception
     * @return string
     * Le mot de passe en clair dans une chaîne
     */
    public function genereMdp($l = 8, $c = 0, $n = 0, $s = 0)
    {
        $count = $c + $n + $s;
        $out = '';
        if(!is_int($l) || !is_int($c) || !is_int($n) || !is_int($s)) {
            throw  New Exception(__METHOD__ . ' : Les paramètres doivent être des entiers.');
        } else if($l < 0 || $l > 20 || $c < 0 || $n < 0 || $s < 0) {
            throw  New Exception(__METHOD__ . ' : Les paramètres sont en dehors de la plage acceptée.');
        } else if($c > $l) {
            throw  New Exception(__METHOD__ . ' : Le nombre de lettres majuscules ne doit pas excéder la longueur du mot de passe.');
        } else if($n > $l) {
            throw  New Exception(__METHOD__ . ' : Le nombre de chiffres ne doit pas excéder la longueur du mot de passe.');
        } else if($s > $l) {
            throw  New Exception(__METHOD__ . ' : Le nombre de caractères spéciaux ne doit pas excéder la longueur du mot de passe.');
        } else if($count > $l) {
            throw  New Exception(__METHOD__ . ' : La longueur demandée ne permet pas de remplir les contraintes.');
        }
    
        for ($i = 0; $i < $l; $i++) {
            $out .= substr($this->chars, mt_rand(0, strlen($this->chars) - 1), 1);
        }
    
        if($count) {
            $tmp1 = str_split($out);
            $tmp2 = array();
    
            for ($i = 0; $i < $c; $i++) {
                array_push($tmp2, substr($this->caps, mt_rand(0, strlen($this->caps) - 1), 1));
            }
    
            for ($i = 0; $i < $n; $i++) {
                array_push($tmp2, substr($this->nums, mt_rand(0, strlen($this->nums) - 1), 1));
            }
    
            for ($i = 0; $i < $s; $i++) {
                array_push($tmp2, substr($this->syms, mt_rand(0, strlen($this->syms) - 1), 1));
            }
    
            $tmp1 = array_slice($tmp1, 0, $l - $count);
            $tmp1 = array_merge($tmp1, $tmp2);
            shuffle($tmp1);
            $out = implode('', $tmp1);
        }
    
        return $out;
    }
    
    /**
     * Renvoie le mot de passe codé.
     * 
     * @param string $mdp
     * Mot de passe en clair
     * @param string $gds
     * Grain de sel (optionnel)
     * 
     * @return string
     * chaîne de longueur 60 caractères
     */
    public static function crypteMdp($mdp, $gds = '')
    {
        $bcrypt = new Bcrypt();
        return  $bcrypt->create($mdp . $gds);
    }
    
    public static function verify($mdp, $hash, $gds = '')
    {
        $bcrypt = new Bcrypt();
        return $bcrypt->verify($mdp . $gds, $hash);
    }
}