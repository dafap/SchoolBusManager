<?php
/**
 * Classe d'utilisation de l'API geocoder de google
 *
 * @project sbm
 * @package SbmCartographie/GoogleMaps
 * @filesource Geocoder.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 déc. 2017
 * @version 2017-2.3.14
 */
namespace SbmCartographie\GoogleMaps;

use SbmBase\Model\StdLib;

class Geocoder
{

    /**
     *
     * @var SbmCartographie\Projection\ProjectionInterface
     */
    private $projection;

    /**
     * URL d'appel de l'API geocoder (service)
     *
     * @var string
     */
    private $google_geocoder_url;

    private $google_reversegeocoder_url;

    public function __construct($projection, $google_api)
    {
        $this->projection = $projection;
        $this->google_geocoder_url = StdLib::getParam('geocoder', $google_api);
        $this->google_reversegeocoder_url = StdLib::getParam('reversegeocoder', 
            $google_api);
    }

    /**
     * Demande la longitude et la latitude d'une adresse postale.
     * Renvoie un tableau contenant
     * la longitude, la latitude et l'adresse formatée trouvée (pour contrôle visuel).
     * Si l'adresse n'est pas trouvée, longitude et latitude sont zéro et l'adresse est : 'pas trouvé'
     *
     * @param string $adresse            
     * @param string $codePostal            
     * @param string $commune            
     * @return array Les clés du tableau résultat sont <b>lat</b>, <b>lng</b> et <b>adresse</b>
     */
    public function geocode($adresse, $codePostal, $commune)
    {
        $ligneAdresse = sprintf('%s,%05s %s', $adresse, $codePostal, $commune);
        $url = sprintf($this->google_geocoder_url, urlencode($ligneAdresse));
        $reponse = json_decode(@file_get_contents($url));
        $lat = 0;
        $lng = 0;
        $formatted_address = "pas trouvé";
        $commune = strtoupper($commune);
        if ($reponse) {
            if ($reponse->status == 'OK') {
                foreach ($reponse->results as $result) {
                    foreach ($result->address_components as $element) {
                        if (in_array('locality', $element->types)) {
                            $locality = strtoupper($element->long_name);
                            if ($locality == $commune) {
                                $lat = $result->geometry->location->lat;
                                $lng = $result->geometry->location->lng;
                                $formatted_address = $result->formatted_address;
                            }
                        }
                    }
                }
            }
        } else {
            throw new ExceptionNotAnswer('GoogleMaps API ne répond pas');
        }
        return array(
            'lat' => $lat,
            'lng' => $lng,
            'adresse' => $formatted_address
        );
    }

    /**
     * Renvoie l'adresse postale d'un lieu dans un tableau
     *
     * @param float $lat            
     * @param float $lng            
     *
     * @throws Exception
     *
     * @return array Tableau associatif de la forme array('numero' => , 'rue' => , 'lieu-dit' => ,'code_postal' => , 'commune' => , 'departement' => , 'region' => , 'pays' => )
     */
    public function reverseGeocoding($lat, $lng)
    {
        $url = sprintf($this->google_reversegeocoder_url, $lat, $lng);
        $reponse = json_decode(@file_get_contents($url), true);
        if ($reponse) {
            if (is_array($reponse) && $reponse['status'] == "OK") {
                $location = array(
                    'numero' => '',
                    'rue' => '',
                    'lieu-dit' => '',
                    'code_postal' => '',
                    'commune' => '',
                    'departement' => '',
                    'region' => '',
                    'pays' => ''
                );
                foreach ($reponse['results']['0']['address_components'] as $component) {
                    
                    switch ($component['types']) {
                        case in_array('street_number', $component['types']):
                            $location['numero'] = $component['long_name'];
                            break;
                        case in_array('route', $component['types']):
                            $location['rue'] = $component['long_name'];
                            break;
                        case in_array('sublocality', $component['types']):
                            $location['lieu-dit'] = $component['long_name'];
                            break;
                        case in_array('locality', $component['types']):
                            $location['commune'] = $component['long_name'];
                            break;
                        case in_array('administrative_area_level_2', $component['types']):
                            $location['departement'] = $component['long_name'];
                            break;
                        case in_array('administrative_area_level_1', $component['types']):
                            $location['region'] = $component['long_name'];
                            break;
                        case in_array('postal_code', $component['types']):
                            $location['code_postal'] = $component['long_name'];
                            break;
                        case in_array('country', $component['types']):
                            $location['pays'] = $component['long_name'];
                            break;
                    }
                }
                return $location;
            } else {
                throw new Exception(
                    'Impossible de trouver les l\'adresse postale de ce lieu.');
            }
        } else {
            throw new ExceptionNotAnswer('GoogleMaps API ne répond pas');
        }
    }
}