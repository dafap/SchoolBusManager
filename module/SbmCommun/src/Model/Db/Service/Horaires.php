<?php
/**
 * Classe regroupant les méthodes permettant d'obtenir les horaires d'un point d'arrêt sur
 * un circuit ou de trouver la nature des horaires d'un service.
 *
 * Un point d'arrêt d'un circuit a 3 horaires composés d'un passage à l'aller et de deux
 * passages au retour.
 * Chaque horaire est attribué a un ensemble de jours de la semaine, précisé dans la fiche
 * service.
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service
 * @filesource Horaires.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 juillet 2022
 * @version 2019-2.5.15
 */
namespace SbmCommun\Model\Db\Service;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Strategy\Semaine;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Horaires implements FactoryInterface
{

    /**
     *
     * @var DbManager
     */
    private $db_manager;

    /**
     *
     * @var Semaine
     */
    private $semaine;

    /**
     * Liste des jours de la semaine sous forme de tableau indexé :<ul> <li>La clé est le
     * code du jour.</li> <li>La valeur est le nom du jour.</li></ul>
     *
     * @var array
     */
    private $jours;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! $serviceLocator instanceof DbManager) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->semaine = new Semaine();
        $this->jours = Semaine::getJours();
        return $this;
    }

    public function getNatureHoraires(string $serviceId)
    {
        $jours = Semaine::getJours();
        $aHoraires = $this->db_manager->get('Sbm\Db\Table\Services')->getHoraires(
            $serviceId);
        $result = [];
        foreach ($aHoraires as $key => $codejours) {
            $array = [];
            foreach ($codejours as $value) {
                $array[] = $jours[$value];
            }
            if (! empty($array)) {
                $result[$key] = implode(' ', $array);
            }
        }
        return $result;
    }

    public function getTableHoraires($id)
    {
        $result = [];
        if (is_array($id)) {
            // il doit y avoir la clé 'serviceId' et il peut y avoir la clé 'stationId'
            // sinon, c'est pas bon et on lance une exception.
            if (! array_key_exists('serviceId', $id) ||
                (count($id) == 2 && ! array_key_exists('stationId', $id))) {
                $msg = 'Le tableau de paramètres reçu n\'est pas de la forme' .
                    ' [\'serviceId\' => serviceId, \'stationId\' => stationId]';
                throw new \Exception($msg);
            }
            if (array_key_exists('stationId', $id)) {
                $tCircuits = $this->db_manager->get('Sbm\Db\Table\Circuits');
                $resultset = $tCircuits->fetchAll($id);
                if ($resultset->count()) {
                    $circuit = $resultset->current();
                    $id = $circuit->circuitId;
                    $typeCircuitId = true;
                } else {
                    $id = $id['serviceId'];
                    $typeCircuitId = false;
                }
            } else {
                $id = $id['serviceId'];
                $typeCircuitId = false;
            }
        } else {
            $typeCircuitId = true;
        }
        if ($typeCircuitId) {
            // $id est un circuitId
            $aHoraires = $this->db_manager->get('Sbm\Db\Query\Circuits')->getHoraires(
                (int) $id);
            for ($i = 1; $i <= 3; $i ++) {
                if ($aHoraires["horaire$i"]) {
                    $result["horaire$i"] = $this->ligneTableHoraires($aHoraires, $i);
                }
            }
        } else {
            // $id est un serviceId
            $aHoraires = $this->db_manager->get('Sbm\Db\Table\Services')->getHoraires($id);
            foreach ($aHoraires as $key => $codejours) {
                if (! empty($codejours)) {
                    $result[$key] = [
                        'nature' => $this->natureHoraire($codejours),
                        'm' => '',
                        's' => '',
                        'z' => ''
                    ];
                }
            }
        }
        return $result;
    }

    /**
     *
     * @param array|\ArrayObject $aHoraires
     * @param int $i
     * @return array
     */
    private function ligneTableHoraires($aHoraires, int $i): array
    {
        $codejours = $this->semaine->hydrate($aHoraires["horaire$i"]);
        $m = new \DateTime($aHoraires["m$i"]);
        $s = new \DateTime($aHoraires["s$i"]);
        $z = new \DateTime($aHoraires["z$i"]);
        return [
            "nature" => $this->natureHoraire($codejours),
            "m" => $m->format('H:i'),
            "s" => $s->format('H:i'),
            "z" => $z->format('H:i')
        ];
    }

    private function natureHoraire(array $codejours): string
    {
        $array = [];
        foreach ($codejours as $value) {
            $array[] = $this->jours[$value];
        }
        return implode(' ', $array);
    }

    public function encodeHoraires($serviceId, array $horaires): array
    {
        $codes = [];
        $natures = $this->getNatureHoraires($serviceId);
        foreach ($horaires as $item) {
            for ($i = 1; $i <= count($natures); $i ++) {
                if ($item == $natures["horaire$i"]) {
                    $codes[] = 1 << ($i - 1);
                    break;
                }
            }
        }
        return $codes;
    }
}