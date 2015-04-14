<?php
/**
 * Listener de l'évènement de paiement : 
 * - scolariteOK
 *
 * Indication de paiement dans la table scolarites
 * 
 * @project sbm
 * @package SbmPaiement/Listener
 * @filesource ScolariteOK.php
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

class ScolariteOK extends AbstractListener implements ListenerAggregateInterface
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
        $this->listeners[] = $sharedEvents->attach('SbmPaiement\Plugin\Plateforme', 'scolariteOK', array(
            $this,
            'onScolariteOK'
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
     * Traitement de l'évènement 'scolariteOK'
     * Le contexte de l'évènement est le ServiceManager.
     * Les paramètres sont les références à traiter.
     *
     * @param Event $e            
     */
    public function onScolariteOK(Event $e)
    {
        $this->setServiceLocator($e->getTarget());
        $params = $e->getParams();
        // indicateur utiliser pour la mise à jour du champ `paiement` de la table `scolarites`
        $indicateur = $params['type'] == 'CREDIT' ? 0 : 1;
        
        $table = $this->getServiceLocator()->get('Sbm\Db\Table\Scolarites');
        $objectData = $this->getServiceLocator()->get('Sbm\Db\ObjectData\Scolarite');
        foreach ($params['eleveIds'] as $eleveId) {
            try {
                $objectData->exchangeArray(array('millesime' => $params['millesime'], 'eleveId' => $eleveId, 'paiement' => $indicateur));
                $table->updateRecord($objectData);
            } catch (\Exception $e) {
                $msg = sprintf('Impossible de mettre à jour la scolarité de l\'élève n° %s pour l\'année %s', $eleveId, $params['millesime']);
                $this->log(Logger::CRIT, $msg, $params);
            }
        }
    }
}