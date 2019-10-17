<?php
/**
 * Réponse d'une requête CURL
 *
 * @project sbm
 * @package SbmCleverSms/src/Model
 * @filesource Response.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 oct. 2019
 * @version 2019-2.5.2
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
        return $this->status['http_code'] >= 200 && $this->status['http_code'] < 205;
    }

    public function getCode()
    {
        return $this->status['http_code'];
    }

    public function debug_status()
    {
        ob_start();
        var_dump($this->status);
        $result = ob_get_clean();
        return strip_tags($result);
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

    /**
     * Renvoie la valeur de l'index ou le tableau si l'index n'est pas fourni. Déclenche
     * une exception si l'index n'existe pas.
     *
     * @param ?string $index
     * @return array|mixed
     */
    public function getResponse($index = null)
    {
        if (is_null($index)) {
            return $this->array_response;
        } elseif (array_key_exists($index, $this->array_response)) {
            return $this->array_response[$index];
        } else {
            ob_start();
            var_dump($this->array_response);
            $message = ob_get_clean();
            throw new Exception\OutOfBoundsException(
                "L'index $index n'est pas trouvé dans " . strip_tags($message));
        }
    }
}