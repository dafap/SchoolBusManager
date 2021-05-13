<?php
/**
 * Permet d'enregistrer le formulaire UserRelation dans le form_manager
 *
 * Compatibilité ZF3
 * usage :
 * $form = $sm->get('SbmAdmin\Form\UserRelation')->getForm('etablissement');
 *
 * @project sbm
 * @package SbmAdmin/Form/Service
 * @filesource UserRelationFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmAdmin\Form\Service;

use SbmAdmin\Form\UserRelation;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserRelationFactory implements FactoryInterface
{

    private $name;

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
        $this->name = null;
        $this->form = null;
        return $this;
    }

    /**
     * Renvoie la classe du formulaire
     *
     * @param string $name
     *            etablissement|transporteur<br>
     *            Correspond à un rôle d'utilisateur
     *            
     * @throws \SbmAdmin\Form\DomainException (lancée par \SbmAdmin\Form\UserRelation)
     *        
     * @return \SbmAdmin\Form\Export
     */
    public function getForm($name)
    {
        if (is_null($this->form) || $name != $this->name) {
            $this->name = $name;
            $this->form = new UserRelation($name);
        }

        return $this->form;
    }
}