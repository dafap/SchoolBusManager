<?php
/**
 * Abstract controller action pour les controllers de ce module
 *
 * La méthode params() surcharge la méthode de Zend
 * 
 * @project sbm
 * @package SbmAjax/Controller
 * @filesource AbstractActionController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 avr. 2016
 * @version 2016-2
 */
namespace SbmAjax\Controller;

use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;

class AbstractActionController extends ZendAbstractActionController
{
    /**
     * Conteneur des paramètres passés dans args
     * @var array
     */
    private $args = null;
    
    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }
    
    /**
     * Cette méthode décompose le paramètre 'args' passé dans la route en un tableau.
     * - si $param == 'args' alors la méthode renvoie la tableau obtenu
     * - si $param est une clé de ce tableau, la méthode renvoie la valeur associée à cette clé
     * - sinon la méthode parent est appelée (et vraisemblablement renvoie $default)
     * 
     * (non-PHPdoc)
     * @see \Zend\Mvc\Controller\AbstractController::params($param, $default)
     */
    public function params($param, $default = null)
    {
        if (is_null($this->args)) {
            $this->args = array();
            $result = parent::params('args', array());
            if (! empty($result)) {
                $aResult = explode('/', $result);
                foreach ($aResult as $item) {
                    $aItem = explode(':', $item);
                    $this->args[$aItem[0]] = $aItem[1];
                }
            }
        }
        if ($param == 'args') {
            return $this->args;
        } elseif (array_key_exists($param, $this->args)) {
            return urldecode($this->args[$param]);
        } else {
            return parent::params($param, $default);
        }
    }
}