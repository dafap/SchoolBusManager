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
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmAjax\Controller;

use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;

class AbstractActionController extends ZendAbstractActionController
{

    /**
     * Conteneur des paramètres passés dans args
     *
     * @var array
     */
    private $args = null;

    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * Renvoie la valeur associée à la clé $param de la propriété $config
     *
     * @param string $param            
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function __get($param)
    {
        if (array_key_exists($param, $this->config)) {
            return $this->config[$param];
        }
        $message = sprintf(
            'Le paramètre %s n\'est pas une propriété définie par le ControllerFactory.', 
            $param);
        throw new Exception($message);
    }

    /**
     * Cette méthode décompose le paramètre 'args' passé dans la route en un tableau.
     * - si $param == 'args' alors la méthode renvoie la tableau obtenu
     * - si $param est une clé de ce tableau, la méthode renvoie la valeur associée à cette clé
     * - sinon la méthode parent est appelée (et vraisemblablement renvoie $default)
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractController::params($param, $default)
     */
    public function params($param, $default = null)
    {
        if (is_null($this->args)) {
            $this->args = [];
            $result = parent::params('args', []);
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