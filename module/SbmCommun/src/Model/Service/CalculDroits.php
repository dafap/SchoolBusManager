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
 * @date 26 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Service;

use SbmBase\Model\Session;
use SbmCartographie\GoogleMaps;
use SbmCartographie\Model\Exception;
use SbmCartographie\Model\Point;
use SbmCommun\Model\Strategy\Niveau;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
     * objet permettant d'obtenir les collèges ou les écoles prise en compte pour un niveau et un
     * point géographique donnés
     *
     * @var \SbmCartographie\GoogleMaps\DistanceMatrix
     */
    private $oDistanceMatrix;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var array
     */
    private $compte_rendu = [];

    /**
     * Les distances en km des domiciles de l'élève à son établissement scolaire.
     * Lorsque l'élève a une résidence différente de celles de ses responsable, elle remplace la
     * résidence du responsable n°1.
     * Cette propriété mise à jour dans la méthode district et est reprise dans les méthodes
     * saveAcquisition() et saveAcquisitionPerte()
     *
     * @var array of float
     * 
     * @throws \SbmCartographie\Model\Exception\ExceptionNoCartographieManager
     * 
     * @return CalculDroits
     */
    private $distance = [];

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! $serviceLocator->has(GoogleMaps\DistanceMatrix::class)) {
            throw new Exception\ExceptionNoCartographieManager(
                sprintf(_("CartographieManager attendu, doit contenir %s."),
                    GoogleMaps\DistanceMatrix::class));
        }
        $this->setMillesime();
        $this->db_manager = $serviceLocator->get('Sbm\DbManager');
        $this->tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $this->oDistanceMatrix = $serviceLocator->get(GoogleMaps\DistanceMatrix::class);
        $this->distance = [
            'R1' => 0.0,
            'R2' => 0.0
        ];
        return $this;
    }

    /**
     * initialise la propriété
     *
     * @param string $millesime
     */
    public function setMillesime($millesime = null)
    {
        if (is_null($millesime)) {
            $this->millesime = Session::get('millesime');
        } else {
            $this->millesime = $millesime;
        }
    }

    /**
     * Retourne un booléen représentant le droit au transport pour l'établissement scolaire
     * sans tenir compte de la règle des distances.
     * On ne regarde que si l'établissement est
     * autorisé pour l'un des 2 domiciles.
     * Les distances de l'établissement aux domicilex sont calculées et placées dans la
     * propriété $distance de cette classe (tableau associatif ['R1' => ..., 'R2' => ...]
     * Même s'il n'y a pas de demande de transport, la distance est calculée.
     * Si GoogleMaps ne répond pas alors :
     * - la distance est nulle si pas de demande pour ce domicile
     * - la distance est reprise si $gardeDistance == true
     * - la distance est 99 sinon, ce qui donnera un droit au transport et permettra
     * le paiement en ligne par les parents.
     *
     * Le droit est acquis si :
     * 1/ l'élève est scolarisé en lycée (niveau 8)
     * 2/ l'élève est scolarisé dans un établissement autorisé pour l'un de ses responsable
     * 3/ l'élève est scolarisé dans un établissement autorisé pour son adresse personnelle
     * Le droit va permettre de mettre à jour le champ district de la table scolarites
     *
     * @param int $eleveId
     * @param bool $gardeDistance
     *
     * @return boolean
     */
    private function distancesDistrict($eleveId, $gardeDistance = true)
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
        $destination->setAttribute('classeId', $scolarite->classeId);
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
        $domiciles = [];
        $elv = $this->db_manager->get('Sbm\Db\Table\Eleves')->getRecord($eleveId);
        // résidence du 1er responsable
        $tResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        $resp = $tResponsables->getRecord($elv->responsable1Id);
        $domiciles[0] = new Point($resp->x, $resp->y);
        $domiciles[0]->setAttribute('communeId', $resp->communeId);
        $domiciles[0]->setDistance($scolarite->distanceR1);
        // résidence du 2e responsable
        $tmp = $elv->responsable2Id;
        $r2 = ! empty($tmp);
        if ($r2) {
            $resp = $tResponsables->getRecord($elv->responsable2Id);
            $domiciles[1] = new Point($resp->x, $resp->y);
            $domiciles[1]->setAttribute('communeId', $resp->communeId);
            $domiciles[1]->setDistance($scolarite->distanceR2);
        }
        // résidence de l'élève. Cette résidence remplace la résidence du 1er responsable
        $tmp1 = $scolarite->chez;
        $tmp2 = $scolarite->adresseL1;
        $tmp3 = $scolarite->codePostal;
        $tmp4 = $scolarite->communeId;
        if (! empty($tmp1) && ! empty($tmp2) && ! empty($tmp3) && ! empty($tmp4)) {
            $domiciles[0] = new Point($scolarite->x, $scolarite->y);
            $domiciles[0]->setAttribute('communeId', $scolarite->communeId);
            // la distance à cette résidence est indiquée dans $scolarite->distanceR1
            // et est déjà initialisée dans $domiciles[0]
        }
        // Les distances seront mises à jour si elles sont vides (ou 0) ou égales à 99
        // sinon, elles seront inchangées
        try {
            switch ($niveau) {
                case 8:
                    // lycée
                    $result = [
                        'distances' => $this->oDistanceMatrix->plusieursOriginesUneDestination(
                            $domiciles, $destination),
                        'droit' => true
                    ];
                    break;
                case 4:
                    // collège
                    $result = $this->domicilesCollege($domiciles, $destination);
                    break;
                default:
                    // école
                    $result = $this->domicilesEcole($niveau, $domiciles, $destination);
                    break;
            }
            // on récupère la distance donnée par distanceMatrix
            $j = 1;
            foreach ($result['distances'] as $distance) {
                $this->distance['R' . $j ++] = round($distance / 1000, 1);
            }
            if ($gardeDistance) {
                // on rétablit la distance indiquée si elle est significative
                $j = 1;
                foreach ($domiciles as $domicile) {
                    $distanceActuelle = (float) $domicile->getDistance();
                    if ($distanceActuelle != 99 && ! empty($distanceActuelle)) {
                        $this->distance['R' . $j ++] = $distanceActuelle;
                    }
                }
            }
            $this->setCompteRendu($result);
            return $result['droit'];
        } catch (GoogleMaps\Exception\ExceptionNoAnswer $e) {
            /**
             * GoogleMaps API ne répond pas.
             * Pour chaque domicile :
             * - si pas de demande de transport on met 0.0
             * - si demande et gardeDistance on rétablit la distance indiquée
             * - sinon 99
             */
            $j = 1;
            foreach ($domiciles as $domicile) {
                $this->distance["R$j"] = $scolarite->{"demandeR$j"} ? ($gardeDistance ? $domicile->getDistance() : 99) : 0.0;
                $j ++;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Prépare un compte_rendu du calcul des distances et des droits en conservant les
     * messages et recherchant le nom et la commune de l'établissement le plus proche
     * lorsque l'établissement demandé n'est pas celui donnant droit.
     * Lorsque l'établissement demandé donne lieu au transport, le compte_rendu est vide.
     *
     * Le compte-rendu est de la forme :
     * ['etablissements' => [['nom' => string, 'commune' => string], ...]]
     * ou ['message' => string] ou []
     *
     * @param array $array
     *            de la forme [
     *            'droit' => bool, // obligatoire
     *            'distances' => [], // non utilisé dans cette méthode
     *            'message' => string, // optionnel
     *            'etablissementsAyantDroit' => [int] // optionnel, des etablissementId
     *            ]
     */
    private function setCompteRendu($array)
    {
        $this->compte_rendu = [];
        if (! $array['droit']) {
            if (array_key_exists('etablissementsAyantDroit', $array)) {
                foreach ($array['etablissementsAyantDroit'] as $etablissementId) {
                    $etablissement = $this->db_manager->get('Sbm\Db\Table\Etablissements')->getRecord(
                        $etablissementId);
                    $commune = $this->db_manager->get('Sbm\Db\Table\Communes')->getRecord(
                        $etablissement->communeId);
                    $this->compte_rendu['etablissements'][] = [
                        'nom' => $etablissement->nom,
                        'commune' => $commune->nom
                    ];
                }
            }
            if (array_key_exists('message', $array)) {
                $this->compte_rendu['message'] = $array['message'];
            }
        }
    }

    /**
     * Le compte-rendu renvoyé est un tableau de la forme :
     * []
     * ou
     * ['message' => string]
     * ou
     * ['etablissements' => array] ou array est un tableau de ['nom' => string, 'commune'=>string]
     *
     * @return array
     */
    public function getCompteRendu()
    {
        return $this->compte_rendu;
    }

    /**
     * Les Point $domiciles ont pour attribut communeId
     * Le Point $college a pour attribut etablissementId, classeId, communeId et statut.
     *
     * Pour le public, collège du secteur scolaire de la commune du domicile
     * Pour le privé, collège de la commune le plus proche ou collège le plus proche
     *
     * @param array(Point) $domiciles
     * @param Point $college
     *
     * @return array tableau associatif de la forme
     *         ['droit' => boolean, 'distances' => [], 'message' => string]
     *         ou
     *         ['droit' => ..., 'distances' => ..., 'etablissementsAyantDroit' => [int]]
     */
    private function domicilesCollege($domiciles, $college)
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
                    $domiciles, $college),
                'message' => $droit ? '' : 'Vous n\'êtes pas dans le secteur scolaire du collège demandé.'
            ];
        } else {
            // Le collège privé est-il de la commune d'un domicile ?
            $estDeLaCommune = false;
            foreach ($domiciles as $pt) {
                $estDeLaCommune |= $pt->getAttribute('communeId') ==
                    $college->getAttribute('communeId');
            }
            // liste des collèges privés
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
                        $domiciles, $college),
                    'message' => ''
                ];
            } else {
                // tableau de Point
                $aDestinations = [];
                // il y en a plusieurs, recherche du collège privé le plus proche
                foreach ($rowset as $clg) {
                    $pt = new Point($clg->x, $clg->y);
                    $pt->setAttribute('etablissementId', $clg->etablissementId);
                    $aDestinations[] = $pt;
                }
                // calcul des distances (plusieurs origines, plusieurs destinations)
                return $this->fncDistanceMatrix($college, $domiciles, $aDestinations);
            }
        }
    }

    /**
     * Les Point $domiciles ont pour attribut communeId
     * Le Point $ecole a pour attribut etablissementId, classeId, communeId et statut.
     *
     * Pour le public, école publique de la commune la plus proche ou école publique la plus proche
     * Pour le privé, école privée de la commune la plus proche ou école privée la plus proche
     *
     * @param int $niveau
     * @param array(Point) $domiciles
     * @param Point $ecole
     *
     * @return array tableau associatif de la forme
     *         ['droit' => ..., 'distances' => ..., 'etablissementsAyantDroit' => [int]]
     */
    private function domicilesEcole($niveau, $domiciles, $ecole)
    {
        // L'école est-elle de la commune d'un domicile ?
        $estDeLaCommune = false;
        $tRpiCommunes = $this->db_manager->get('Sbm\Db\Table\RpiCommunes');
        $communesEcole = $tRpiCommunes->getCommuneIds($ecole->getAttribute('communeId'));
        foreach ($domiciles as $pt) {
            foreach ($communesEcole as $communeId) {
                $estDeLaCommune |= $pt->getAttribute('communeId') == $communeId;
            }
        }
        // liste des écoles ayant le statut de l'$ecole
        if ($estDeLaCommune) {
            $aDestinations = $this->prepareListeEcolesZone($niveau,
                $ecole->getAttribute('statut'), $communesEcole);
        } else {
            $aDestinations = $this->prepareListeEcolesZone($niveau,
                $ecole->getAttribute('statut'));
        }
        // calcul des distances (plusieurs origines, plusieurs destinations)
        return $this->fncDistanceMatrix($ecole, $domiciles, $aDestinations);
    }

    /**
     * Cette méthode construit une structure donnant un tableau de Points des établissements
     * avec en attribut : etablissementId, communeId, statut
     *
     * @param int $niveau
     * @param int $statut
     * @param array|string|null $communeId
     *
     * @return array tableau de Point
     */
    private function prepareListeEcolesZone($niveau, $statut = null, $communeId = null)
    {
        $tEtablissements = $this->db_manager->get('Sbm\Db\Table\Etablissements');
        $result = [];
        foreach ($tEtablissements->getEcoles($niveau, $statut, $communeId) as $ecole) {
            $pt = new Point($ecole->x, $ecole->y);
            $pt->setAttribute('communeId', $ecole->communeId);
            $pt->setAttribute('etablissementId', $ecole->etablissementId);
            $pt->setAttribute('statut', $ecole->statut);
            $result[] = $pt;
        }
        return $result;
    }

    /**
     * Renvoie $etablissement si l'établissement n'est pas dans un RPI ou si la classeId
     * est assurée dans cet établissement
     * sinon, renvoi l'identifiant de l'établissement du RPI qui assure cette classe
     *
     * @param int $etablissementId
     * @param int $classeId
     *
     * @throws \RuntimeException
     *
     * @return boolean|int
     */
    private function getEtablissementId($etablissementId, $classeId)
    {
        $tRpiEtablissements = $this->db_manager->get('Sbm\Db\Table\RpiEtablissements');
        try {
            $rpiId = $tRpiEtablissements->getRpiId($etablissementId);
            $tRpiClasses = $this->db_manager->get('Sbm\Db\Table\RpiClasses');
            try {
                $tRpiClasses->getRecord(
                    [
                        'classeId' => $classeId,
                        'etablissementId' => $etablissementId
                    ]);
                return $etablissementId;
            } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                // il faut chercher quel établissement du RPI assure cette classe
                $resultset = $tRpiEtablissements->fetchAll([
                    'rpiId' => $rpiId
                ]);
                foreach ($resultset as $row) {
                    try {
                        $tRpiClasses->getRecord(
                            [
                                'classeId' => $classeId,
                                'etablissementId' => $row->etablissementId
                            ]);
                        return $row->etablissementId;
                    } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {}
                }
                $msg = sprintf(
                    'Cette classe (classeId = %d) n\'est pas assurée dans ce RPI (rpiId = %d).',
                    $classeId, $rpiId);
                throw new \RuntimeException($msg, $e->getCode(), $e);
            }
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            return $etablissementId;
        }
    }

    /**
     * fncDistanceMatrix, plusieurs origines, plusieurs destinations
     *
     * @param Point $ptEtablissement
     *            établissement où l'élève est inscrit
     * @param array(Point) $aPtOrigines
     *            liste des domiciles de l'élève
     * @param array(Point) $aPtDestinations
     *            liste des établissements du niveau de l'élève (parfois restreints dans un RPI)
     *            
     * @return array Tableau de la forme
     *         ['droit' => ..., 'distances' => ..., 'etablissementsAyantDroit' => [int]]
     *        
     * @throws \SbmCartographie\GoogleMaps\Exception\Exception
     * @throws \SbmCartographie\GoogleMaps\Exception\ExceptionNoAnswer
     * @throws \RuntimeException
     */
    private function fncDistanceMatrix($ptEtablissement, $aPtOrigines, $aPtDestinations)
    {
        $droit = false;
        $aEtablissementsAyantDroit = [];
        // on initialise $distances afin d'éviter une décalage si la réponse de distanceMatrix
        // est invalide pour la première origine ($element->status != OK).
        $distances = [
            0
        ];
        try {
            $obj = $this->oDistanceMatrix->getJsonResult($aPtOrigines, $aPtDestinations);
            if ($obj) {
                if ($obj->status == 'OK') {
                    $i = 0;
                    foreach ($obj->rows as $row) {
                        $dmin = 1e+11;
                        $procheEtablissementId = '';
                        $j = 0;
                        foreach ($row->elements as $element) {
                            if ($element->status == 'OK') {
                                // on récupère la distance entre le domicile et l'ecole
                                if ($aPtDestinations[$j]->getAttribute('etablissementId') ==
                                    $ptEtablissement->getAttribute('etablissementId')) {
                                    $distances[$i] = $element->distance->value;
                                }
                                // on met à jour la distance minimale $dmin
                                // et on mémorise l'établissement le plus proche
                                if ($dmin > $element->distance->value) {
                                    $dmin = $element->distance->value;
                                    $procheEtablissementId = $aPtDestinations[$j]->getAttribute(
                                        'etablissementId');
                                }
                            }
                            $j ++;
                        }
                        // le droit est accordé pour l'établissement le plus proche
                        // ou pour l'établissement du même RPI s'il fait partie d'un RPI
                        // et si la classe n'est pas ouverte dans l'établissement le plus
                        // proche
                        $procheEtablissementId = $this->getEtablissementId(
                            $procheEtablissementId,
                            $ptEtablissement->getAttribute('classeId'));
                        $droit |= $ptEtablissement->getAttribute('etablissementId') ==
                            $procheEtablissementId;
                        $aEtablissementsAyantDroit[] = $procheEtablissementId;
                        $i ++;
                    }
                    return [
                        'droit' => $droit,
                        'distances' => $distances,
                        'etablissementsAyantDroit' => $aEtablissementsAyantDroit
                    ];
                } else {
                    throw new GoogleMaps\Exception\Exception(
                        'La requête sur GoogleMaps n\'a pas permis de calculer les distances.');
                }
            } else {
                throw new GoogleMaps\Exception\ExceptionNoAnswer(
                    'GoogleMaps API ne répond pas.');
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Calcul de distances impossible dans ' . __METHOD__, 0, $e);
        }
    }

    /**
     * Méthode à utiliser en début d'année ou en cours d'année s'il n'y a pas de changement
     * d'établissement scolaire.
     * Cette méthode permet de ne pas perdre les droits acquis en cours d'année.
     *
     * @param int $eleveId
     * @param bool $gardeDistance
     */
    public function majDistancesDistrictSansPerte($eleveId, $gardeDistance = true)
    {
        if ($this->distancesDistrict($eleveId, $gardeDistance)) {
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
     * @param bool $gardeDistance
     */
    public function majDistancesDistrict($eleveId, $gardeDistance = true)
    {
        // à faire au début puisque la méthode met aussi à jour la propriété 'distance'
        $droit = $this->distancesDistrict($eleveId, $gardeDistance);
        $oData = $this->tScolarites->getObjData();
        $oData->exchangeArray(
            [
                'millesime' => $this->millesime,
                'eleveId' => $eleveId,
                'distanceR1' => round($this->distance['R1'], 1),
                'distanceR2' => round($this->distance['R2'], 1),
                'district' => $droit ? 1 : 0
            ]);
        $this->tScolarites->saveRecord($oData);
    }

    /**
     * Renvoie un boolean indiquant si une préincription est en attente
     *
     * @param array $row
     *            tableau décrivant la scolarité d'un élève avec au moins les champs suivants :
     *            distanceR1, distanceR2, district, derogation, selectionScolarite
     *            
     * @return boolean
     */
    public function estEnAttente($row)
    {
        return max($row['distanceR1'], $row['distanceR2']) < 1 ||
            ($row['district'] == 0 && $row['derogation'] == 0);
    }
}