<?php
/**
 * Réponse d'une requête CURL
 *
 * @project sbm
 * @package SbmCleverSms/src/Model
 * @filesource Response.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCleverSms\Model;

use SbmBase\Model\DateLib;

class Response
{

    private $array_response;

    private $status;

    public function __construct($status, $json_string)
    {
        $this->status = $status;
        if (! empty($json_string)) {
            $this->array_response = json_decode($json_string, true);
        } else {
            $this->array_response = [];
        }
    }

    public function isValid()
    {
        return $this->status['http_code'] < 205;
    }

    public function getCode()
    {
        return $this->status['http_code'];
    }

    public function getMessage()
    {
        switch ($this->status['http_code']) {
            case 200:
                return 'La requête s\'est correctement exécutée.';
            case 201:
                return 'La campagne d\'envoi est créée sur le serveur.';
            case 204:
                return 'La ressource est supprimée sur le serveur.';
            case 400:
                return 'Les paramètres de la requête sont incorrects.';
            case 401:
                return 'L\'authentification est incorrecte.';
            case 404:
                return 'La ressource n\'a pas été trouvée sur le serveur.';
            case 405:
                return 'On tente de supprimer un envoi non différé.';
            default:
                return sprintf('Erreur inconnue #%d', $this->status['http_code']);
        }
    }

    public function translate($key, $value)
    {
        if (array_key_exists('account', $this->array_response)) {
            if ($key == 'date_expiration') {
                return DateLib::formatDateTimeFromMysql($value);
            }
        } elseif (array_key_exists('mos', $this->array_response)) {
            if ($key == 'DateHour-Entry') {
                return DateLib::formatDateTimeFromMysql($value);
            }
        } elseif (array_key_exists('deliveries', $this->array_response)) {
            $nommenclature = [
                2 => 'Le SMS est transmis à l\'opérateur',
                3 => 'Le SMS est délivré sur le téléphone',
                4 => 'Le numéro du destinataire n\'a pas pu être traité',
                6 => 'Échec d\'envoi d\'un SMS',
                8 => 'Le numéro est en double et l\'intervalle d\'envoi est inférieur à 1 seconde'
            ];
            if (in_array($key, [
                'DateHour-Send',
                'DateHour-Delivery'
            ])) {
                return DateLib::formatDateTimeFromMysql($value);
            } elseif ($key == 'Statut' && array_key_exists($value, $nommenclature)) {
                return $nommenclature[$value];
            }
        }
        return $value;
    }

    public function getResponse()
    {
        return $this->array_response;
    }
}