<?php
/**
 * Permet d'enregistrer le formulaire Export dans le form_manager
 *
 * Compatibilité ZF3
 * usage :
 *   $form = $sm->get('SbmAdmin\Form\Export')->getForm('eleve');
 * 
 * @project sbm
 * @package SbmAdmin/Form/Service
 * @filesource ExportFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmAdmin\Form\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmAdmin\Form\Export;
use SbmAdmin\Form\Exception;

class ExportFactory implements FactoryInterface
{

    private $db_manager;

    private $source;

    private $form;

    /**
     * Crée le service en initialisant le db_manager
     *
     * (non-PHPdoc)
     * 
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db_manager = $serviceLocator->get('Sbm\DbManager');
        $this->source = null;
        $this->form = null;
        return $this;
    }

    /**
     * Renvoie la classe du formulaire
     *
     * @param string $source
     *            eleve|etablissement|responsable|station<br>
     *            Correspond à une méthode privée <i>formSource</i> de la classe Export
     *            
     * @throws \SbmAdmin\Form\Exception
     * @return \SbmAdmin\Form\Export
     */
    public function getForm($source = null)
    {
        if (is_null($this->form) || $source != $this->source) {
            $this->source = $source;
            $this->form = new Export($source, $this->db_manager);
        }
        
        return $this->form;
    }
}