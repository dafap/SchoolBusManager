<?php
/**
 * Listener de l'évènement de paiement :
 * - paiementOK
 *
 * Enregistrement dans la table paiements
 * 
 * @project sbm
 * @package SbmPaiement/Plugin
 * @filesource PaiementOK.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2015
 * @version 2015-1
 */
namespace SbmPaiement\Listener;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Log\Logger;
//use SbmCommun\Model\StdLib;

class PaiementOK extends AbstractListener implements ListenerAggregateInterface
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
        $this->listeners[] = $sharedEvents->attach('SbmPaiement\Plugin\Plateforme', 'paiementOK', array(
            $this,
            'onPaiementOK'
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

    /**
     * Traitement de l'évènement 'paiementOK'
     * Le contexte de l'évènement est le ServiceManager.
     * Les paramètres sont les données à enregistrer.
     * 
     * @param Event $e
     */
    public function onPaiementOK(Event $e)
    {
        $this->setServiceLocator($e->getTarget());
        $params = $e->getParams();
        if ($params['type'] == 'CREDIT') {
            $params['paiement']['montant'] *= -.01;
        } else {
            $params['paiement']['montant'] *= .01;
        }
        $datePaiement = $params['paiement']['datePaiement'];
        $responsableId = $params['paiement']['responsableId'];
        $reference = $params['paiement']['reference'];        
        $table = $this->getServiceLocator()->get('Sbm\Db\Table\Paiements');
        $params['paiement']['paiementId'] = $table->getPaiementId($responsableId, $datePaiement, $reference);
        // La référence paiementId doit être définie avant la création de l'objectData
        $objectData = $this->getServiceLocator()->get('Sbm\Db\ObjectData\Paiement');
        $objectData->exchangeArray($params['paiement']);
        // enregistrement du paiement
        try {
            $table->saveRecord($objectData);
        } catch (\Exception $e) {
            $this->log(Logger::CRIT, 'Impossible d\'enregistrer le paiement', $params);
        }       
    }
}