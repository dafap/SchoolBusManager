<?php
/**
 * Listener qui appelle la plateforme pour obtenir le formulaire de paiement.
 *
 * L'appel est déclanché par un évènement 'appelPaiement' lancé par 
 * la méthode SbmParent\Controller\IndexController::payerAction().
 * On utilisera comme identifiant de l'évènement la chaine 'appelPaiement'.
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
 * @package SbmPaiement/Listener
 * @filesource AppelPlateforme.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmPaiement\Listener;

use SbmBase\Model\StdLib;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;

class AppelPlateforme implements ListenerAggregateInterface
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
        $this->listeners[] = $sharedEvents->attach('SbmPaiement\AppelPlateforme',
            'appelPaiement', [
                $this,
                'onAppelPaiement'
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
     * Le contexte (target) de l'evenement $e donne la plate-forme configurée dans le
     * service manager sous la clé 'SbmPaiement\Plugin\Plateforme'
     *
     * @param Event $e
     */
    public function onAppelPaiement(Event $e)
    {
        $params = $e->getParams();
        // où sont mes certificats ?
        $cacert = StdLib::concatPath(realpath(StdLib::findParentPath(__DIR__, 'config')),
            'cacert.pem');
        // ouvre l'objet de la plateforme
        $objectPlateforme = $e->getTarget();

        $adapter = new Curl();
        $adapter->setOptions(
            [
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
        $adapter->setCurlOption(CURLOPT_CAINFO, $cacert);
        $client = new Client($objectPlateforme->getUrl());
        $client->setAdapter($adapter);
        $client->setMethod('POST');
        $client->setParameterPost($objectPlateforme->prepareAppel($params));
        $response = $client->send($client->getRequest());

        // output the response
        echo $response->getBody() . "<br/>";
        /*
         * // appel en post $ch = curl_init($objectPlateforme->getUrl()); $options = [
         * CURLOPT_POST => 1, CURLOPT_POSTFIELDS =>
         * $objectPlateforme->prepareAppel($params), CURLOPT_HEADER => 0,
         * CURLOPT_RETURNTRANSFER => 1, CURLOPT_CAINFO => $cacert, CURLOPT_SSL_VERIFYPEER
         * => true ]; curl_setopt_array($ch, $options); $fm = new
         * \Zend\Mvc\Controller\Plugin\FlashMessenger(); if (!curl_exec($ch)) { // libcurl
         * - Error codes à l'adresse https://curl.haxx.se/libcurl/c/libcurl-errors.html
         * $mess = sprintf('Echec de l\'appel à la plateforme de paiement. (Code erreur n°
         * %d)', curl_errno($ch)); $objectPlateforme->logError(Logger::ALERT,$mess,
         * curl_getinfo($ch)); $fm->addErrorMessage('Echec de l\'appel à la plateforme de
         * paiement.'); } else { $fm->addSuccessMessage('Accès au paiement en ligne.'); }
         * curl_close($ch);
         */
    }
}