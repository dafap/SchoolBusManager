<?php
/**
 * Listener de l'évènement de paiement :
 * - scolariteOK
 *
 * Indication de paiement dans la table scolarites sur le champ paiementR1
 * Compatibilité ZF3
 *
 * @project sbm
 * @package SbmPaiement/Listener
 * @filesource ScolariteOK.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmPaiement\Listener;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Log\Logger;

class ScolariteOK extends AbstractListener implements ListenerAggregateInterface
{

    /**
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = [];

    /**
     *
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach('SbmPaiement\Plugin\Plateforme',
            'scolariteOK', [
                $this,
                'onScolariteOK'
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
     * Traitement de l'évènement 'scolariteOK' Le contexte de l'évènement n'est pas
     * utilisé. Les paramètres sont les références à traiter.
     *
     * @param Event $e
     */
    public function onScolariteOK(Event $e)
    {
        $params = $e->getParams();
        // indicateur utiliser pour la mise à jour du champ `paiementR1` de la table
        // `scolarites`
        $indicateur = $params['type'] == 'CREDIT' ? 0 : 1;

        $table_scolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $objectData_scolarite = $this->db_manager->get('Sbm\Db\ObjectData\Scolarite');
        foreach ($params['eleveIds'] as $eleveId) {
            try {
                $objectData_scolarite->exchangeArray(
                    [
                        'millesime' => $params['millesime'],
                        'eleveId' => $eleveId,
                        'paiementR1' => $indicateur
                    ]);
                $table_scolarites->updateRecord($objectData_scolarite);
            } catch (\Exception $e) {
                $msg = sprintf(
                    'Impossible de mettre à jour la scolarité de l\'élève n° %s pour l\'année %s',
                    $eleveId, $params['millesime']);
                $this->log(Logger::CRIT, $msg, $params);
            }
        }
    }
}