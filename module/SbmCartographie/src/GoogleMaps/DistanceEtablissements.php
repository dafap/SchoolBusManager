<?php
/**
 * Calcule la distance entre deux points
 *
 * Le constructeur précise s'il veut la distance à pied, à vélo, en voiture ou à vol d'oiseau.
 * 
 * Cette classe utilise l'API distanceMatrix de GoogleMaps. L'API direction de GoogleMaps pourrait convenir également (lire ce qui suit).
 * 
 * Le calcul de l'école la plus proche peut se faire en un seul appel par distanceMatrix alors que par directions il faut autant d'appel que d'écoles.
 * Compte tenu de la limitation des API de google en terme de nombre d'appel par seconde, il est préférable d'utiliser distanceMatrix.
 * Toutefois, distanceMatrix donne la distance du trajet le plus court en temps et non la plus courte distance.
 * 
 * Voici le résultat d'une requête simple par l'API distanceMatrix :
 * requête : 
 *   https://maps.googleapis.com/maps/api/distancematrix/json?origins=44.498275,2.280882&destinations=44.530225,2.241541&mode=car&language=fr-FR&sensor=false
 *   (à noter qu'on peut remplacer dans la requête les coordonnées origins ou destinations par l'adresse postale encodée html)
 *   (à noter également que dans une requête multiple, les couples de coordonnées des points origins ou destinations sont séparées par |)
 * réponse :
 * stdClass Object
 * (
 *   [destination_addresses] => Array
 *     (
 *          [0] => Chemin de Cantarane, 12110 Aubin, France
 *     )
 *   [origin_addresses] => Array
 *     (
 *          [0] => 4 Marion, 12390 Auzits, France
 *     )
 *   [rows] => Array
 *     (
 *          [0] => stdClass Object
 *              (
 *                  [elements] => Array
 *                      (
 *                          [0] => stdClass Object
 *                              (
 *                                  [distance] => stdClass Object
 *                                      (
 *                                          [text] => 6,5 km
 *                                          [value] => 6547
 *                                      )
 *
 *                                  [duration] => stdClass Object
 *                                      (
 *                                          [text] => 11 minutes
 *                                          [value] => 632
 *                                      )
 *
 *                                  [status] => OK
 *                              )
 *                      )
 *              )
 *      )
 *   [status] => OK
 * )
 * Les rows correspondent aux origines, les elements correspondent aux destinations.
 * 
 * Voici le résultat d'une requête par l'API directions :
 * requête :
 *   https://maps.googleapis.com/maps/api/directions/json?origin=44.498275,2.280882&amp;destination=44.530225,2.241541&amp;alternatives=true&amp;sensor=false
 * réponse :
 * object(stdClass)
 * (
 *   ['routes'] => Array
 *   (
 *      0 => object(stdClass)[552]
 *         public 'bounds' => 
 *           object(stdClass)[553]
 *             ...
 *         public 'copyrights' => string 'Map data ©2015 Google' (length=22)
 *         public 'legs' => 
 *           array (size=1)
 *             ...
 *         public 'overview_polyline' => 
 *           object(stdClass)[609]
 *             ...
 *         public 'summary' => string 'D148 and D5' (length=11)
 *         public 'warnings' => []
 *         public 'waypoint_order' => Array ()
 *    ),
 *    (
 *      1 => object(stdClass)[610]
 *         public 'bounds' => 
 *           object(stdClass)[611]
 *             ...
 *         public 'copyrights' => string 'Map data ©2015 Google' (length=22)
 *         public 'legs' => 
 *           array (size=1)
 *             ...
 *         public 'overview_polyline' => 
 *           object(stdClass)[681]
 *             ...
 *         public 'summary' => string 'D53 and D11' (length=11)
 *         public 'warnings' => []
 *         public 'waypoint_order' => Array ()
 *    )
 *    ['status'] => 'OK'
 * )    
 * avec par exemple :
 *    [bounds] => stdClass Object (
 *                 [northeast] => stdClass Object (
 *                    [lat] => 44.5304571
 *                    [lng] => 2.283027
 *                )
 *                 [southwest] => stdClass Object
 *                (
 *                    [lat] => 44.497752
 *                    [lng] => 2.240626
 *                )
 *             )
 * et
 *     [legs] => Array (
 *         [0] => stdClass Object (
 *               [distance] => stdClass Object
 *                  (
 *                     [text] => 6.5 km
 *                     [value] => 6545
 *                  )
 *               [duration] => stdClass Object
 *                  (
 *                     [text] => 11 mins
 *                     [value] => 632
 *                  )
 *               [end_address] => Chemin de Cantarane, 12110 Aubin, France
 *               [end_location] => stdClass Object
 *                  (
 *                     [lat] => 44.5304571
 *                     [lng] => 2.2408954
 *                  )
 *               [start_address] => 4 Marion, 12390 Auzits, France
 *               [start_location] => stdClass Object
 *                  (
 *                     [lat] => 44.498313
 *                     [lng] => 2.280858
 *                  )
 *               [steps] => Array
 *                  (
 *                     [0] => stdClass Object
 *                     (
 *                        [distance] => stdClass Object
 *                        (
 *                           [text] => 73 m
 *                           [value] => 73
 *                        ) 
 *                        [duration] => stdClass Object
 *                        (
 *                           [text] => 1 min
 *                           [value] => 6
 *                        )
 *                        [end_location] => stdClass Object
 *                        (
 *                           [lat] => 44.4981821
 *                           [lng] => 2.2817588
 *                        )
 *                        [html_instructions] => Head <b>east</b> on <b>Marion</b> toward <b>D53</b>
 *                        [polyline] => stdClass Object
 *                        (
 *                           [points] => marnGkn|LAQB[Bm@Fm@FYBO
 *                        )
 *                        [start_location] => stdClass Object
 *                        (
 *                           [lat] => 44.498313
 *                           [lng] => 2.280858
 *                        )
 *                        [travel_mode] => DRIVING
 *                    )
 *                    [1] => ... (autant de pas que de morceaux de chemin)
 *                  )
 *                [via_waypoint] => []
 *             )
 *        )       
 *     Legs correspond à étape. Ce voyage n'a qu'une étape donc il n'y a qu'un seul `legs` d'index 0. 
 *     Mais si on définit plusieurs étapes (waypoints) il y aura un `legs` par étape (en commençant par l'index 0).
 *     
 *     Chaque route décrit un itinéraire possible donc il faut les ordonner selon la distance.value pour obtenir la route la plus courte. 
 *     Pour cette API on peut également remplacer les couples de coordonnées par l'adresse postale encodée html.
 *     
 * @project sbm
 * @package SbmCartographie\GoogleMaps
 * @filesource DistanceEtablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCartographie\GoogleMaps;

use SbmBase\Model\StdLib;
use SbmCartographie\Model\Point;
use Zend\Db\Sql\Where;

class DistanceEtablissements
{

    /**
     * Db manager
     *
     * @var \Zend\ServiceManager\ServiceManager
     */
    private $db_manager;

    /**
     * URL d'appel de l'API distanceMatrix
     *
     * @var string
     */
    private $google_distancematrix_url;

    /**
     * URL d'appel de l'API directions
     *
     * @var string
     */
    private $google_directions_url;

    /**
     *
     * @var \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface
     */
    private $projection;

    /**
     *
     * @param \Zend\ServiceManager\ServiceManager $db_manager
     * @param \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface $projection
     * @param array $google_api
     */
    public function __construct($db_manager, $projection, $google_api)
    {
        $this->projection = $projection;
        $this->db_manager = $db_manager;
        $this->google_directions_url = StdLib::getParam('directions', $google_api);
        $this->google_distancematrix_url = StdLib::getParam('distancematrix', $google_api);
    }

    /**
     * Renvoie l'objet Projection configuré
     *
     * @return \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface
     */
    public function getProjection()
    {
        return $this->projection;
    }

    /**
     * Renvoie une chaine formée par 'lat,lng' où lat et lng sont la latitude et la longitude en
     * degrés décimaux.
     *
     * @param \SbmCartographie\Model\Point $point
     *
     * @return string
     */
    public function getLatLngString(Point $point)
    {
        if (! in_array($point->getUnite(), [
            'degré',
            'grade',
            'radian'
        ])) {
            $p = $this->projection->xyzVersgRGF93($point);
        } else {
            $p = $point;
        }
        $p = $p->to('degré');
        return sprintf('%s,%s', number_format($p->getLatitude(), 6, '.', ''),
            number_format($p->getLongitude(), 6, '.', ''));
    }

    /**
     * Renvoie la distance en mètres
     *
     * @param Point $origine
     * @param Point $destination
     * @return number
     */
    public function calculDistance(Point $origine, Point $destination)
    {
        $url = sprintf($this->google_distancematrix_url, $this->getLatLngString($origine),
            $this->getLatLngString($destination));
        $obj = json_decode(file_get_contents($url));
        if ($obj->status == 'OK') {
            if ($obj->rows[0]->elements[0]->status == 'OK') {
                $d = $obj->rows[0]->elements[0]->distance->value;
            } else {
                $d = null;
            }
        } else {
            $d = null;
        }
        return $d;
    }

    /**
     * Il y a plusieurs origines et une seule destination.
     * Renvoie un tableau de distances des origines à la destination.
     *
     * @param array $origines
     *            tableau de Point
     * @param Point $destination
     *
     * @throws Exception
     * @return array tableau de distances
     */
    public function plusieursOriginesUneDestination(array $origines, Point $destination)
    {
        if (! is_array($origines)) {
            throw new Exception(
                __METHOD__ .
                ' - Le paramètre 1 de cette méthode doit être un tableau. On a reçu ' .
                gettype($origines));
        }
        $tLatLng = [];
        foreach ($origines as $origine) {
            if (! $origine instanceof Point) {
                throw new Exception(
                    __METHOD__ .
                    ' - Le tableau reçu en paramètre 1 doit contenir des SbmCartographie\Model\Point. On a reçu un ' .
                    gettype($origine));
            }
            $tLatLng[] = $this->getLatLngString($origine);
        }
        $url = sprintf($this->google_distancematrix_url, implode('|', $tLatLng),
            $this->getLatLngString($destination));
        $obj = json_decode(file_get_contents($url));
        if ($obj->status == 'OK') {
            $result = [];
            for ($j = 0; $j < count($obj->rows); $j ++) {
                if ($obj->rows[$j]->elements[0]->status == 'OK') {
                    $result[] = $obj->rows[$j]->elements[0]->distance->value;
                }
            }
        } else {
            $result = array_fill(0, count($origines), 0);
        }
        return $result;
    }

    /**
     * Il y a une seule origine et plusieurs destinations.
     * La réponse de l'API donne donc un seul rows et plusieurs elements dans ce rows.
     * Renvoie un tableau de distances de l'origine aux destinations
     *
     * @param Point $origine
     * @param array $destinations
     *            tableau de Point
     * @throws Exception
     * @return array tableau de distances
     */
    public function uneOriginePlusieursDestinations(Point $origine, array $destinations)
    {
        if (! is_array($destinations)) {
            throw new Exception(
                __METHOD__ .
                ' - Le paramètre 2 de cette méthode doit être un tableau. On a reçu ' .
                gettype($destinations));
        }
        $tLatLng = [];
        foreach ($destinations as $destination) {
            if (! $destination instanceof Point) {
                throw new Exception(
                    __METHOD__ .
                    ' - Le tableau reçu en paramètre 2 doit contenir des SbmCartographie\Model\Point. On a reçu un ' .
                    gettype($destination));
            }
            $tLatLng[] = $this->getLatLngString($destination);
        }
        $url = sprintf($this->google_distancematrix_url, $this->getLatLngString($origine),
            implode('|', $tLatLng));
        $obj = json_decode(file_get_contents($url));
        if ($obj->status == 'OK') {
            $result = [];
            for ($j = 0; $j < count($obj->rows[0]->elements); $j ++) {
                if ($obj->rows[0]->elements[$j]->status == 'OK') {
                    $result[] = $obj->rows[0]->elements[$j]->distance->value;
                }
            }
        } else {
            $result = array_fill(0, count($destinations), 0);
        }
        return $result;
    }

    /**
     * Renvoie la plus courte distance en mètres
     *
     * @param Point $domicile
     * @param Point $destination
     */
    public function calculDistanceByDirections(Point $origine, Point $destination)
    {
        $url = sprintf($this->google_directions_url, $this->getLatLngString($origine),
            $this->getLatLngString($destination));
        $obj = json_decode(file_get_contents($url));
        if ($obj->status == 'OK') {
            usort($obj->routes,
                function ($a, $b) {
                    return intval($a->legs[0]->distance->value) -
                    intval($b->legs[0]->distance->value);
                });
            $d = $obj->routes[0]->legs[0]->distance->value;
        } else {
            $d = 0;
        }
        return $d;
    }

    /**
     * Cette méthode construit une structure donnant d'une part les coordonnées LatLng des
     * établissements et
     * d'autre part l'identité des établissements (etablissementId, communeId, statut) rangés dans
     * le même
     * ordre que les LatLng
     *
     * @param int $niveau
     * @param int $statut
     * @param string $communeId
     *
     * @return array tableau associatif dont les clés sont 'tEtablissements' et 'tLatLng'
     */
    private function prepareListeEcolesZone($niveau, $statut = null, $communeId = null)
    {
        $tableEtablissements = $this->db_manager->get('Sbm\Db\Table\Etablissements');
        $result = [
            'tEtablissements' => [],
            'tLatLng' => []
        ];
        foreach ($tableEtablissements->getEcoles($niveau, $statut, $communeId) as $ecole) {
            $p = new Point($ecole->x, $ecole->y);
            $p->setAttribute('communeId', $ecole->communeId);
            $result['tEtablissements'][] = [
                'etablissementId' => $ecole->etablissementId,
                'communeId' => $ecole->communeId,
                'statut' => $ecole->statut
            ];
            $result['tLatLng'][] = $this->getLatLngString($p);
        }
        return $result;
    }

    /**
     * Renvoie un tableau simple des etablissementId pour lesquels il y a un droit au transport
     * scolaire
     *
     * @param Point $domicile
     *            coordonnées du domicile dans le système utilisé ou en degré, grade ou radian (il
     *            y aura conversion)
     *            
     * @throws Exception
     * @return array tableau simple de tableaux associatifs ['etablissementId' => ...,
     *         'distance' => ...)
     */
    public function ecolesPrisesEnCompte($niveau, Point $domicile)
    {
        $structure = $this->prepareListeEcolesZone($niveau);
        $position = $this->getLatLngString($domicile);
        $url = sprintf($this->google_distancematrix_url, $position,
            implode('|', $structure['tLatLng']));
        $obj = json_decode(file_get_contents($url));
        if ($obj->status == 'OK') {
            $etablissementIdPublic = '00000000';
            $dMinPriveCommune = $dMinPriveZone = $dMinPublicCommune = $dMinPublicZone = 1e+11; // force
                                                                                               // la
                                                                                               // première
                                                                                               // affectation
            for ($j = 0; $j < count($obj->rows[0]->elements); $j ++) {
                if ($obj->rows[0]->elements[$j]->status == 'OK') {
                    $elementDistance = $obj->rows[0]->elements[$j]->distance->value;
                    $elementEtablissementId = $structure['tEtablissements'][$j]['etablissementId'];
                    $elementCommuneId = $structure['tEtablissements'][$j]['communeId'];
                    $elementStatut = $structure['tEtablissements'][$j]['statut'];
                    if ($elementStatut) {
                        // école publique
                        if ($elementDistance < $dMinPublicZone) {
                            $dMinPublicZone = $elementDistance;
                            $etablissementIdPublic = $elementEtablissementId;
                        }
                        if ($elementDistance < $dMinPublicCommune &&
                            $elementCommuneId == $domicile->getAttribute('communeId')) {
                            $dMinPublicCommune = $elementDistance;
                            $etablissementIdPublicCommune = $elementEtablissementId;
                        }
                    } else {
                        // école privée
                        if ($elementDistance < $dMinPriveZone) {
                            $dMinPriveZone = $elementDistance;
                            $etablissementIdPrive = $elementEtablissementId;
                        }
                        if ($elementDistance < $dMinPriveCommune &&
                            $elementCommuneId == $domicile->getAttribute('communeId')) {
                            $dMinPriveCommune = $elementDistance;
                            $etablissementIdPriveCommune = $elementEtablissementId;
                        }
                    }
                }
            }
            $result = [];
            $result[] = [
                'etablissementId' => $etablissementIdPublic,
                'distance' => $dMinPublicZone
            ];
            if (isset($etablissementIdPublicCommune) &&
                $etablissementIdPublic != $etablissementIdPublicCommune) {
                $result[] = [
                    'etablissementId' => $etablissementIdPublicCommune,
                    'distance' => $dMinPublicCommune
                ];
            }
            if (isset($etablissementIdPrive)) {
                $result[] = [
                    'etablissementId' => $etablissementIdPrive,
                    'distance' => $dMinPriveZone
                ];
            }
            if (isset($etablissementIdPriveCommune) &&
                $etablissementIdPrive != $etablissementIdPriveCommune) {
                $result[] = [
                    'etablissementId' => $etablissementIdPriveCommune,
                    'distance' => $dMinPriveCommune
                ];
            }
            return $result;
        } else {
            throw new Exception(
                'La requête sur GoogleMaps n\'a pas permis de calculer les distances.');
        }
    }

    /**
     * Renvoie un tableau simple des etablissementId pour lesquels il y a un droit au transport
     * scolaire
     *
     * @param Point $domicile
     *            coordonnées du domicile dans le système utilisé ou en degré, grade ou radian (il
     *            y aura conversion)
     *            
     * @throws Exception
     * @return array tableau simple de tableaux associatifs ['etablissementId' => ...,
     *         'distance' => ...)
     */
    public function collegesPrisEnCompte(Point $domicile)
    {
        $tSecteurScolaireClgPu = $this->db_manager->get(
            'Sbm\Db\Table\SecteursScolairesClgPu');
        $rowset = $tSecteurScolaireClgPu->fetchAll(
            [
                'communeId' => $domicile->getAttribute('communeId')
            ]);
        $listePublic = [];
        foreach ($rowset as $clg) {
            $listePublic[] = $clg->etablissementId;
        }
        $tClg = $this->db_manager->get('Sbm\Db\Table\Etablissements');
        $rowset = $tClg->fetchAll([
            'niveau' => 4
        ]);
        $structure = [
            'tEtablissements' => [],
            'tLatLng' => []
        ];
        foreach ($rowset as $clg) {
            if (in_array($clg->etablissementId, $listePublic) || ! $clg->statut) {
                $p = new Point($clg->x, $clg->y);
                $p->setAttribute('communeId', $clg->communeId);
                $structure['tEtablissements'][] = [
                    'etablissementId' => $clg->etablissementId,
                    'communeId' => $clg->communeId,
                    'statut' => $clg->statut
                ];
                $structure['tLatLng'][] = $this->getLatLngString($p);
            }
        }
        $position = $this->getLatLngString($domicile);
        $url = sprintf($this->google_distancematrix_url, $position,
            implode('|', $structure['tLatLng']));
        $obj = json_decode(file_get_contents($url));
        if ($obj->status == 'OK') {
            $result = [];
            $dMinPrive = 1e+11;
            for ($j = 0; $j < count($obj->rows[0]->elements); $j ++) {
                if ($obj->rows[0]->elements[$j]->status == 'OK') {
                    $elementDistance = $obj->rows[0]->elements[$j]->distance->value;
                    $elementEtablissementId = $structure['tEtablissements'][$j]['etablissementId'];
                    // $elementCommuneId = $structure['tEtablissements'][$j]['communeId'];
                    $elementStatut = $structure['tEtablissements'][$j]['statut'];
                    if ($elementStatut) {
                        // clg public du secteur scolaire
                        $result[] = [
                            'etablissementId' => $elementEtablissementId,
                            'distance' => $elementDistance
                        ];
                    } else {
                        // clg privé : prendre le plus proche
                        if ($elementDistance < $dMinPrive) {
                            $dMinPrive = $elementDistance;
                            $etablissementIdPrive = $elementEtablissementId;
                        }
                    }
                }
            }
            if (isset($etablissementIdPrive)) {
                $result[] = [
                    'etablissementId' => $etablissementIdPrive,
                    'distance' => $dMinPrive
                ];
            }
            return $result;
        } else {
            throw new Exception(
                'La requête sur GoogleMaps n\'a pas permis de calculer les distances.');
        }
    }

    /**
     * Les Point $domiciles ont pour attribut communeId
     * Le Point $college a pour attribut son etablissementId, sa communeId et son statut.
     *
     *
     * @param array $domiciles
     * @param Point $college
     *
     * @return array tableau associatif de la forme ['droit' => boolean, 'distances' => [])
     */
    public function domicilesCollege($domiciles, $college)
    {
        $droit = false;
        $where = new Where();
        if ($college->getAttribute('statut')) {
            // collège public : l'élève est-il du secteur scolaire ?
            $tSecteurScolaireClgPu = $this->db_manager->get(
                'Sbm\Db\Table\SecteursScolairesClgPu');
            $where->equalTo('communeId', $domiciles[0]->getAttribute('communeId'));
            if (count($domiciles) == 2) {
                $where->OR->equalTo('communeId', $domiciles[1]->getAttribute('communeId'));
            }
            $rowset = $tSecteurScolaireClgPu->fetchAll($where);
            foreach ($rowset as $clg) {
                if ($clg->etablissementId == $college->getAttribute('etablissementId')) {
                    $droit = true;
                    break;
                }
            }
            return [
                'droit' => $droit,
                'distances' => $this->plusieursOriginesUneDestination($domiciles, $college)
            ];
        } else {
            // collège privé : est-il d'une commune de résidence de l'élève ?
            $estDeLaCommune = false;
            $origines = [];
            $j = 0;
            foreach ($domiciles as $pt) {
                $estDeLaCommune |= $pt->getAttribute('communeId') ==
                    $college->getAttribute('communeId');
                $origines[$j ++] = $this->getLatLngString($pt);
            }
            // quels sont les établissements privés ?
            $tClg = $this->db_manager->get('Sbm\Db\Table\Etablissements');
            $where->equalTo('statut', 0)->equalTo('niveau', 4);
            if ($estDeLaCommune) {
                $where->equalTo('communeId', $college->getAttribute('communeId'));
            }
            $rowset = $tClg->fetchAll($where);
            if ($rowset->count() == 1) {
                // s'il n'y a qu'un établissement c'est fini
                return [
                    'droit' => true,
                    'distances' => $this->plusieursOriginesUneDestination($domiciles,
                        $college)
                ];
            } else {
                // il y en a plusieurs, recherche du collège privé le plus proche
                $tLatLng = [];
                $tEtablissementId = [];
                foreach ($rowset as $clg) {
                    $pt = new Point($clg->x, $clg->y);
                    $tLatLng[] = $this->getLatLngString($pt);
                    $tEtablissementId[] = $clg->etablissementId;
                }
                $url = sprintf($this->google_distancematrix_url, implode('|', $origines),
                    implode('|', $tLatLng));
                $obj = json_decode(file_get_contents($url));
                if ($obj->status == 'OK') {
                    // $distances = [];
                    $i = 0;
                    foreach ($obj->rows as $row) {
                        $dmin = 1e+11;
                        $procheEtablissementId = '';
                        $j = 0;
                        foreach ($row->elements as $element) {
                            if ($element->status == 'OK') {
                                if ($tEtablissementId[$j] ==
                                    $college->getAttribute('etablissementId')) {
                                    // $distances[$i] = $element->distance->value;
                                }
                                if ($dmin > $element->distance->value) {
                                    $dmin = $element->distance->value;
                                    $procheEtablissementId = $tEtablissementId[$j];
                                }
                            }
                            $j ++;
                        }
                        $droit |= $college->getAttribute('etablissementId') ==
                            $procheEtablissementId;
                        $i ++;
                    }
                    return [
                        'droit' => $droit,
                        'distances' => $this->plusieursOriginesUneDestination($domiciles,
                            $college)
                    ];
                }
            }
        }
    }

    /**
     * Les Point $domiciles ont pour attribut communeId
     * Le Point $ecole a pour attribut son etablissementId, sa communeId et son statut.
     *
     * Pour le public, école publique de la commune la plus proche ou école publique la plus proche
     * Pour le privé, école privée de la commune la plus proche ou école privée la plus proche
     *
     * @param int $niveau
     * @param array $domiciles
     * @param Point $ecole
     *
     * @return array tableau associatif de la forme ['droit' => boolean, 'distances' => [])
     */
    public function domicilesEcole($niveau, $domiciles, $ecole)
    {
        $droit = false;
        // $distances = [];
        // l'école est-elle de la commune d'un domicile. En même temps, construction du tableau
        // $origines contenant les LatLng des domiciles.
        $estDeLaCommune = false;
        $origines = [];
        $j = 0;
        foreach ($domiciles as $pt) {
            if ($pt->getAttribute('communeId') == $ecole->getAttribute('communeId')) {
                $estDeLaCommune = true;
                // $domicile = $j; // le domicile à prendre en compte pour le calcul de la plus
                // courte
                // distance
            }
            $origines[$j ++] = $this->getLatLngString($pt);
        }
        // liste des écoles ayant le statut de l'$ecole
        if ($estDeLaCommune) {
            $structure = $this->prepareListeEcolesZone($niveau,
                $ecole->getAttribute('statut'), $ecole->getAttribute('communeId'));
        } else {
            $structure = $this->prepareListeEcolesZone($niveau,
                $ecole->getAttribute('statut'));
        }
        // calcul des distances (plusieurs origines, plusieurs destinations)
        $url = sprintf($this->google_distancematrix_url, implode('|', $origines),
            implode('|', $structure['tLatLng']));
        $obj = json_decode(file_get_contents($url));
        if ($obj->status == 'OK') {
            $i = 0;
            foreach ($obj->rows as $row) {
                $dmin = 1e+11;
                $procheEtablissementId = '';
                $j = 0;
                foreach ($row->elements as $element) {
                    if ($element->status == 'OK') {
                        if ($structure['tEtablissements'][$j]['etablissementId'] ==
                            $ecole->getAttribute('etablissementId')) {
                            // $distances[$i] = $element->distance->value;
                        }
                        if ($dmin > $element->distance->value) {
                            $dmin = $element->distance->value;
                            $procheEtablissementId = $structure['tEtablissements'][$j]['etablissementId'];
                        }
                    }
                    $j ++;
                }
                $droit |= $ecole->getAttribute('etablissementId') == $procheEtablissementId;
                $i ++;
            }
            return [
                'droit' => $droit,
                'distances' => $this->plusieursOriginesUneDestination($domiciles, $ecole)
            ];
        } else {
            throw new Exception(
                'La requête sur GoogleMaps n\'a pas permis de calculer les distances.');
        }
    }
}