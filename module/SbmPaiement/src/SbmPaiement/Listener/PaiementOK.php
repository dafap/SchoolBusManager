<?php
/**
 * Listener de l'évènement de paiement :
 * - paiementOK
 *
 * Enregistrement dans la table paiements
 * Compaibilité ZF3
 * 
 * @project sbm
 * @package SbmPaiement/Plugin
 * @filesource PaiementOK.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 avr. 2016
 * @version 2016-2
 */
namespace SbmPaiement\Listener;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Log\Logger;

class PaiementOK extends AbstractListener implements ListenerAggregateInterface
{

    /**
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = [];

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach('SbmPaiement\Plugin\Plateforme', 'paiementOK', [
            $this,
            'onPaiementOK'
        ], 1);
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
     * Le contexte de l'évènement n'est pas utilisé.
     * Les paramètres sont les données à enregistrer.
     *
     * @param Event $e            
     */
    public function onPaiementOK(Event $e)
    {
        $params = $e->getParams();
        if ($params['type'] == 'CREDIT') {
            $params['paiement']['montant'] *= - .01;
        } else {
            $params['paiement']['montant'] *= .01;
        }
        $datePaiement = $params['paiement']['datePaiement'];
        $responsableId = $params['paiement']['responsableId'];
        $reference = $params['paiement']['reference'];
        $table_paiements = $this->db_manager->get('Sbm\Db\Table\Paiements');
        $params['paiement']['paiementId'] = $table_paiements->getPaiementId($responsableId, $datePaiement, $reference);
        // La référence paiementId doit être définie avant la création de l'objectData
        $objectData_paiement = $this->db_manager->get('Sbm\Db\ObjectData\Paiement');
        $objectData_paiement->exchangeArray($params['paiement']);
        // enregistrement du paiement
        try {
            $table_paiements->saveRecord($objectData_paiement);
        } catch (\Exception $e) {
            $this->log(Logger::CRIT, 'Impossible d\'enregistrer le paiement', $params);
        }
    }
}