<?php
/**
 * Service calculant les droits au transport pour un élève
 *
 * Par défaut, lors de la création d'une scolarité, il n'y a pas de droit au transport (scolarites.district = 0).
 * 
 * Lors de l'inscription d'un élève, ou du déménagement des parents :
 * Il faut calculer les droits et enregistrer leur acquisition dans scolarites.district.
 * - On distingue les règles selon le niveau de scolarité (maternelle, primaire, collège, lycée).
 * - Les droits acquis pour un établissement en début d'année sont conservés. Par contre, les droits peuvent être acquis en cours d'année.
 * - On calcule les droits en regardant successivement le domicile du responsable1, le domicile du responsable2, le domicile de l'élève.
 *   Dès qu'un des domiciles donne le droit, on arrête le calcul et on enregistre le droit dans la table scolarites.
 *   
 * Par contre, lors d'un changement d'établissement scolaire en cours d'année :
 * Les droits pouvaient être acquis sur l'ancien établissement et peuvent ne pas l'être sur le nouveau car en général on change de service. 
 * Aussi, on enregistrera tout, que ce soit l'acquisition ou la perte des droits.
 * 
 * @project sbm
 * @package SbmCommun\Model\Service
 * @filesource CalculDroits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Where;
use SbmBase\Model\Session;
use SbmCartographie\GoogleMaps;
use SbmCartographie\Model\Exception;
use SbmCartographie\Model\Point;
use SbmCartographie\Model\Service\CartographieManager;
use SbmCommun\Model\Strategy\Niveau;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCartographie\Model\SbmCartographie\Model;

class CalculDroits implements FactoryInterface
{

    /**
     * Millesime sur lequel on travaille
     *
     * @var int
     */
    private $millesime;

    /**
     * Table scolarites
     *
     * @var \SbmCommun\Model\Db\Service\Table\Scolarites
     */
    private $tScolarites;

    /**
     * objet permettant d'obtenir les collèges ou les écoles prise en compte pour un niveau et un point géographique donnés
     *
     * @var \SbmCartographie\GoogleMaps\DistanceMatrix
     */
    private $oDistanceMatrix;

    /**
     *
     * @var DbManager
     */
    private $db_manager;

    /**
     * Les distances en km des domiciles de l'élève à son établissement scolaire.
     * Lorsque l'élève a une résidence différente de celles de ses responsable, elle remplace la résidence du responsable n°1.
     * Cette propriété mise à jour dans la méthode district et est reprise dans les méthodes saveAcquisition() et saveAcquisitionPerte()
     *
     * @var array of float
     */
    private $distance = [];

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! $serviceLocator->has(GoogleMaps\DistanceMatrix::class)) {
            throw new Exception(
                sprintf(_("CartographieManager attendu, doit contenir %s."), 
                    GoogleMaps\DistanceMatrix::class));
        }
        $this->millesime = Session::get('millesime');
        $this->db_manager = $db_manager = $serviceLocator->get('Sbm\DbManager');
        $this->tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $this->oDistanceMatrix = $serviceLocator->get(GoogleMaps\DistanceMatrix::class);
        $this->distance = [
            'R1' => 0.0,
            'R2' => 0.0
        ];
        return $this;
    }

    /**
     * Retourne un booléen représentant le droit au transport pour l'établissement scolaire.
     *
     * Le droit est acquis si :
     * 1/ l'élève est scolarisé en lycée (niveau 8)
     * 2/ l'élève est scolarisé dans un établissement autorisé pour l'un de ses responsable
     * 3/ l'élève est scolarisé dans un établissement autorisé pour son adresse personnelle
     *
     * @param int $eleveId            
     *
     * @return boolean
     */
    private function distancesDistrict($eleveId)
    {
        // scolarisation
        $scolarite = $this->tScolarites->getRecord(
            [
                'millesime' => $this->millesime,
                'eleveId' => $eleveId
            ]);
        $etablissement = $this->db_manager->get('Sbm\Db\Table\Etablissements')->getRecord(
            $scolarite->etablissementId);
        $destination = new Point($etablissement->x, $etablissement->y);
        $destination->setAttribute('etablissementId', $etablissement->etablissementId);
        $destination->setAttribute('communeId', $etablissement->communeId);
        $destination->setAttribute('statut', $etablissement->statut);
        $classe = $this->db_manager->get('Sbm\Db\Table\Classes')->getRecord(
            $scolarite->classeId);
        if (empty($classe)) {
            $niveau = $etablissement->niveau;
        } else {
            $niveau = $classe->niveau;
        }
        $strategieNiveau = new Niveau();
        $niveau = $strategieNiveau->extract($niveau);
        
        // domiciles
        $elv = $this->db_manager->get('Sbm\Db\Table\Eleves')->getRecord($eleveId);
        // résidence du 1er responsable
        $tResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        $resp = $tResponsables->getRecord($elv->responsable1Id);
        $domiciles[0] = new Point($resp->x, $resp->y);
        $domiciles[0]->setAttribute('communeId', $resp->communeId);
        // résidence du 2e responsable
        $tmp = $elv->responsable2Id;
        if (! empty($tmp)) {
            $resp = $tResponsables->getRecord($elv->responsable2Id);
            $domiciles[1] = new Point($resp->x, $resp->y);
            $domiciles[1]->setAttribute('communeId', $resp->communeId);
        }
        // résidence de l'élève. Cette résidence remplace la résidence du 1er responsable
        $tmp1 = $scolarite->chez;
        $tmp2 = $scolarite->adresseL1;
        $tmp3 = $scolarite->codePostal;
        $tmp4 = $scolarite->communeId;
        if (! empty($tmp1) && ! empty($tmp2) && ! empty($tmp3) && ! empty($tmp4)) {
            $domiciles[0] = new Point($scolarite->x, $scolarite->y);
            $domiciles[0]->setAttribute('communeId', $scolarite->communeId);
        }
        try {
            switch ($niveau) {
                case 8:
                    // lycée
                    $distances = $this->oDistanceMatrix->plusieursOriginesUneDestination(
                        $domiciles, $destination);
                    $j = 1;
                    foreach ($distances as $distance) {
                        $this->distance['R' . $j ++] = round($distance / 1000, 1);
                    }
                    // dans tous les cas, l'élève est dans le district du lycée
                    return true;
                    break;
                case 4:
                    // collège
                    $result = $this->domicilesCollege($domiciles, $destination);
                    $j = 1;
                    foreach ($result['distances'] as $distance) {
                        $this->distance['R' . $j ++] = round($distance / 1000, 1);
                    }
                    return $result['droit'];
                    break;
                default:
                    // école
                    $result = $this->domicilesEcole($niveau, $domiciles, $destination);
                    $j = 1;
                    foreach ($result['distances'] as $distance) {
                        $this->distance['R' . $j ++] = round($distance / 1000, 1);
                    }
                    return $result['droit'];
                    break;
            }
        } catch (GoogleMaps\ExceptionNoAnswer $e) {
            // GoogleMaps API ne répond pas : on fait confiance et on met les distances à 99
            $this->distance = [
                'R1' => 99.0,
                'R2' => 99.0
            ];
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Les Point $domiciles ont pour attribut communeId
     * Le Point $college a pour attribut son etablissementId, sa communeId et son statut.
     *
     *
     * @param array(Point) $domiciles            
     * @param Point $college            
     *
     * @return array tableau associatif de la forme ['droit' => boolean, 'distances' => []]
     */
    private function domicilesCollege($dominciles, $college)
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
                'distances' => $this->oDistanceMatrix->plusieursOriginesUneDestination(
                    $domiciles, $college)
            ];
        } else {
            // collège privé : est-il d'une commune de résidence de l'élève ?
            $estDeLaCommune = false;
            $origines = [];
            $j = 0;
            foreach ($domiciles as $pt) {
                $estDeLaCommune |= $pt->getAttribute('communeId') ==
                     $college->getAttribute('communeId');
                $origines[$j ++] = $this->oDistanceMatrix->getLatLngFromParams($pt);
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
                    'distances' => $this->oDistanceMatrix->plusieursOriginesUneDestination(
                        $domiciles, $college)
                ];
            } else {
                // il y en a plusieurs, recherche du collège privé le plus proche
                foreach ($rowset as $clg) {
                    $pt = new Point($clg->x, $clg->y);
                    $aDestinations[] = $p;
                    $aEtablissementId[] = $clg->etablissementId;
                }
                $url = $this->oDistanceMatrix->getUrlGoogleApiDistanceMatrix($origines, 
                    $aDestinations);
                $obj = json_decode(@file_get_contents($url));
                if ($obj) {
                    if ($obj->status == 'OK') {
                        $distances = [];
                        $i = 0;
                        foreach ($obj->rows as $row) {
                            $dmin = 1e+11;
                            $procheEtablissementId = '';
                            $j = 0;
                            foreach ($row->elements as $element) {
                                if ($element->status == 'OK') {
                                    if ($aEtablissementId[$j] ==
                                         $college->getAttribute('etablissementId')) {
                                        $distances[$i] = $element->distance->value;
                                    }
                                    if ($dmin > $element->distance->value) {
                                        $dmin = $element->distance->value;
                                        $procheEtablissementId = $aEtablissementId[$j];
                                    }
                                }
                                $j ++;
                            }
                            $droit |= $college->getAttribute('etablissementId') ==
                                 $procheEtablissementId;
                        }
                        return [
                            'droit' => $droit,
                            'distances' => $this->oDistanceMatrix->plusieursOriginesUneDestination(
                                $domiciles, $college)
                        ];
                    }
                } else {
                    throw new GoogleMaps\ExceptionNoAnswer('GoogleMaps API ne répond pas.');
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
     * @param array(Point) $domiciles            
     * @param Point $ecole            
     *
     * @return array tableau associatif de la forme ['droit' => boolean, 'distances' => []]
     */
    private function domicilesEcole($niveau, $domiciles, $ecole)
    {
        $droit = false;
        $distances = [];
        $origines = [];
        // L'école est-elle de la commune d'un domicile ?
        $estDeLaCommune = false;
        $j = 0;
        foreach ($domiciles as $pt) {
            $estDeLaCommune |= $pt->getAttribute('communeId') ==
                 $ecole->getAttribute('communeId');
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
        try {
            $url = $this->oDistanceMatrix->getUrlGoogleApiDistanceMatrix($domiciles, 
                $structure['aDestinations']);
            $obj = json_decode(@file_get_contents($url));
            if ($obj) {
                if ($obj->status == 'OK') {
                    $i = 0;
                    foreach ($obj->rows as $row) {
                        $dmin = 1e+11;
                        $procheEtablissementId = '';
                        $j = 0;
                        foreach ($row->elements as $element) {
                            if ($element->status == 'OK') {
                                if ($structure['aEtablissements'][$j]['etablissementId'] ==
                                     $ecole->getAttribute('etablissementId')) {
                                    $distances[$i] = $element->distance->value;
                                }
                                if ($dmin > $element->distance->value) {
                                    $dmin = $element->distance->value;
                                    $procheEtablissementId = $structure['aEtablissements'][$j]['etablissementId'];
                                }
                            }
                            $j ++;
                        }
                        $droit |= $ecole->getAttribute('etablissementId') ==
                             $procheEtablissementId;
                    }
                    return [
                        'droit' => $droit,
                        'distances' => $this->oDistanceMatrix->plusieursOriginesUneDestination(
                            $domiciles, $ecole)
                    ];
                } else {
                    throw new Exception(
                        'La requête sur GoogleMaps n\'a pas permis de calculer les distances.');
                }
            } else {
                throw new GoogleMaps\ExceptionNoAnswer('GoogleMaps API ne répond pas.');
            }
        } catch (\Exception $e) {
            throw new \Exception('Calcule de distances impossible dans ' . __METHOD__, 0, 
                $e);
        }
    }

    /**
     * Cette méthode construit une structure donnant un tableau 'aDestinations' des Points
     * des établissements et d'autre part un tableau 'aEtablissements' des établissements
     * [etablissementId, communeId, statut] rangés dans le même ordre que les 'aDestinations'
     *
     * @param int $niveau            
     * @param int $statut            
     * @param string $communeId            
     *
     * @return array tableau associatif dont les clés sont 'aEtablissements' et ''
     */
    private function prepareListeEcolesZone($niveau, $statut = null, $communeId = null)
    {
        $tEtablissements = $this->db_manager->get('Sbm\Db\Table\Etablissements');
        $result = [
            'aEtablissements' => [],
            'aDestinations' => []
        ];
        foreach ($tEtablissements->getEcoles($niveau, $statut, $communeId) as $ecole) {
            $p = new Point($ecole->x, $ecole->y);
            $p->setAttribute('communeId', $ecole->communeId);
            $result['aEtablissements'][] = [
                'etablissementId' => $ecole->etablissementId,
                'communeId' => $ecole->communeId,
                'statut' => $ecole->statut
            ];
            $result['aDestinations'][] = $p;
        }
        return $result;
    }

    /**
     * Méthode à utiliser en début d'année ou en cours d'année s'il n'y a pas de changement d'établissement scolaire.
     * Cette méthode permet de ne pas perdre les droits acquis en cours d'année.
     *
     * @param int $eleveId            
     */
    public function majDistancesDistrictSansPerte($eleveId)
    {
        if ($this->distancesDistrict($eleveId)) {
            $oData = $this->tScolarites->getObjData();
            $oData->exchangeArray(
                [
                    'millesime' => $this->millesime,
                    'eleveId' => $eleveId,
                    'distanceR1' => round($this->distance['R1'], 1),
                    'distanceR2' => round($this->distance['R2'], 1),
                    'district' => 1
                ]);
            $this->tScolarites->saveRecord($oData);
        }
    }

    /**
     * Méthode à utiliser s'il y a changement d'établissement scolaire en cours d'année.
     *
     * @param int $eleveId            
     */
    public function majDistancesDistrict($eleveId)
    {
        // à faire au début puisque la méthode met aussi à jour la propriété 'distance'
        $district = $this->distancesDistrict($eleveId);
        $oData = $this->tScolarites->getObjData();
        $oData->exchangeArray(
            [
                'millesime' => $this->millesime,
                'eleveId' => $eleveId,
                'distanceR1' => round($this->distance['R1'], 1),
                'distanceR2' => round($this->distance['R2'], 1),
                'district' => $district ? 1 : 0
            ]);
        $this->tScolarites->saveRecord($oData);
    }

    /**
     * Renvoie un boolean indiquant si une préincription est en attente
     *
     * @param array $row
     *            tableau décrivant la scolarité d'un élève avec au moins les champs suivants : distanceR1, distanceR2, district, derogation, selectionScolarite
     *            
     * @return boolean
     */
    public function estEnAttente($row)
    {
        $maxDistance = max($row['distanceR1'], $row['distanceR2']);
        return max($row['distanceR1'], $row['distanceR2']) < 1 ||
             ($row['district'] == 0 && $row['derogation'] == 0);
    }
}