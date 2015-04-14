<?php
/**
 * Listener qui appelle la plateforme pour obtenir le formulaire de paiement.
 *
 * L'appel est déclanché par un évènement 'appelPaiement' lancé par la méthode SbmUser\Controller\IndexController::payerAction().
 * On utilisera comme identifiant de l'évènement la chaine 'SbmPaiement\Plugin\Appel'.
 * 
 * Les paramètres de l'évènement contiennent les données suivantes :
 * - montant          : en euros (il faut le multiplier par 100 ici)
 * - count            : le nombre d'échéances (1 si paiement comptant, n si paiement en n fois)
 * - first            : montant en euros de la première échéance (à multiplier par 100 ici)
 * - period           : nombre de jours entre 2 paiements
 * - email            : email du responsable
 * - responsableId    : référence du responsable
 * - nom              : nom du responsable
 * - prénom           : prénom du responsable
 * - eleveIds         : tableau des eleveId, référence des élèves concernés par ce paiement
 * 
 * @project sbm
 * @package SbmPaiement/Plugin
 * @filesource AppelPlateforme.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2015
 * @version 2015-1
 */
namespace SbmPaiement\Plugin;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Log\Logger;

class AppelPlateforme implements ListenerAggregateInterface
{
    /**
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();
    
    /**
     * {@inheritDoc}
    */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach('SbmPaiement\Plugin\Appel', 'appelPaiement', array(
            $this,
            'onAppelPaiement'
        ), 1);
    }
    
    /**
     * (non-PHPdoc)
     *
     * @see \Zend\EventManager\ListenerAggregateInterface::detach()
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
    
    public function onAppelPaiement(Event $e)
    {
        $sm = $e->getTarget();
        $params = $e->getParams();
        // ouvre l'objet de la plateforme
        $objectPlateforme = $sm->get('SbmPaiement\Plugin\Plateforme');
        // appel en post
        $ch = curl_init($objectPlateforme->getUrl());
        $options = array(
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $objectPlateforme->getPostfields($params),
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
        );
        curl_setopt_array($ch, $options);
        if (!curl_exec($ch)) {
            // libcurl - Error codes à l'adresse http://curl.haxx.se/libcurl/c/libcurl-errors.html
            $mess = sprintf('Echec de l\'appel à la plateforme de paiement. (Code erreur n° %d)', curl_errno($ch));
            $objectPlateforme->logError(Logger::ALERT,$mess, $ch->getinfo());
        }
        curl_close($ch);
    }
}