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
 * @date 5 déc. 2017
 * @version 2017-2.3.14
 */
namespace SbmCommun\Model\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmBase\Model\Session;
use SbmCommun\Model\Strategy\Niveau;
use SbmCartographie\Model\Point;
use SbmCartographie\Model\Service\CartographieManager;
use SbmCartographie\Model\Exception;
use SbmCartographie\Model\SbmCartographie\Model;
use SbmCartographie;

class CalculDroits implements FactoryInterface
{

    /**
     * Millesime sur lequel on travaille
     *
     * @var int
     */
    private $millesime;

    /**
     * Table classes
     *
     * @var \SbmCommun\Model\Db\Service\Table\Classes
     */
    private $tClasses;

    /**
     * Table eleves
     *
     * @var \SbmCommun\Model\Db\Service\Table\Eleves
     */
    private $tEleves;

    /**
     * Table responsables
     *
     * @var \SbmCommun\Model\Db\Service\Table\Responsables
     */
    private $tResponsables;

    /**
     * Table scolarites
     *
     * @var \SbmCommun\Model\Db\Service\Table\Scolarites
     */
    private $tScolarites;

    /**
     * Table etablissements
     *
     * @var \SbmCommun\Model\Db\Service\Table\Etablissements
     */
    private $tEtablissements;

    /**
     * objet permettant d'obtenir les collèges ou les écoles prise en compte pour un niveau et un point géographique donnés
     *
     * @var \SbmCartographie\GoogleMaps\DistanceEtablissements
     */
    private $oDistanceEtablissement;

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
        if (! $serviceLocator->has('SbmCarto\DistanceEtablissements')) {
            throw new Exception(sprintf('CartographieManager attendu.'));
        }
        $this->millesime = Session::get('millesime');
        $db_manager = $serviceLocator->get('Sbm\DbManager');
        $this->tEleves = $db_manager->get('Sbm\Db\Table\Eleves');
        $this->tScolarites = $db_manager->get('Sbm\Db\Table\Scolarites');
        $this->tResponsables = $db_manager->get('Sbm\Db\Table\Responsables');
        $this->tEtablissements = $db_manager->get('Sbm\Db\Table\Etablissements');
        $this->tClasses = $db_manager->get('Sbm\Db\Table\Classes');
        $this->oDistanceEtablissement = $serviceLocator->get('SbmCarto\DistanceEtablissements');
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
        $scolarite = $this->tScolarites->getRecord([
            'millesime' => $this->millesime,
            'eleveId' => $eleveId
        ]);
        $etablissement = $this->tEtablissements->getRecord($scolarite->etablissementId);
        $destination = new Point($etablissement->x, $etablissement->y);
        $destination->setAttribute('etablissementId', $etablissement->etablissementId);
        $destination->setAttribute('communeId', $etablissement->communeId);
        $destination->setAttribute('statut', $etablissement->statut);
        $classe = $this->tClasses->getRecord($scolarite->classeId);
        if (empty($classe)) {
            $niveau = $etablissement->niveau;
        } else {
            $niveau = $classe->niveau;
        }
        $strategieNiveau = new Niveau();
        $niveau = $strategieNiveau->extract($niveau);
        
        // domiciles
        $elv = $this->tEleves->getRecord($eleveId);
        // résidence du 1er responsable
        $resp = $this->tResponsables->getRecord($elv->responsable1Id);
        $domiciles[0] = new Point($resp->x, $resp->y);
        $domiciles[0]->setAttribute('communeId', $resp->communeId);
        // résidence du 2e responsable
        $tmp = $elv->responsable2Id;
        if (! empty($tmp)) {
            $resp = $this->tResponsables->getRecord($elv->responsable2Id);
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
                    $distances = $this->oDistanceEtablissement->plusieursOriginesUneDestination($domiciles, $destination);
                    $j = 1;
                    foreach ($distances as $distance) {
                        $this->distance['R' . $j ++] = round($distance / 1000, 1);
                    }
                    // dans tous les cas, l'élève est dans le district du lycée
                    return true;
                    break;
                case 4:
                    // collège
                    $result = $this->oDistanceEtablissement->domicilesCollege($domiciles, $destination);
                    $j = 1;
                    foreach ($result['distances'] as $distance) {
                        $this->distance['R' . $j ++] = round($distance / 1000, 1);
                    }
                    return $result['droit'];
                    break;
                default:
                    // école
                    $result = $this->oDistanceEtablissement->domicilesEcole($niveau, $domiciles, $destination);
                    $j = 1;
                    foreach ($result['distances'] as $distance) {
                        $this->distance['R' . $j ++] = round($distance / 1000, 1);
                    }
                    return $result['droit'];
                    break;
            }
        } catch (\SbmCartographie\GoogleMaps\ExceptionNotAnswer $e) {
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
     * Méthode à utiliser en début d'année ou en cours d'année s'il n'y a pas de changement d'établissement scolaire.
     * Cette méthode permet de ne pas perdre les droits acquis en cours d'année.
     *
     * @param int $eleveId            
     */
    public function majDistancesDistrictSansPerte($eleveId)
    {
        if ($this->distancesDistrict($eleveId)) {
            $oData = $this->tScolarites->getObjData();
            $oData->exchangeArray([
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
        $oData->exchangeArray([
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
        return max($row['distanceR1'], $row['distanceR2']) < 1 || ($row['district'] == 0 && $row['derogation'] == 0);
    }
}