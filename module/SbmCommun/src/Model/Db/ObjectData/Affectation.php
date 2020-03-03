<?php
/**
 * Objet contenant les données à manipuler pour la table Affectations
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Affectation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\ObjectData;

class Affectation extends AbstractObjectData
{
    use \SbmCommun\Model\Traits\ServiceTrait;

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'sens',
                'correspondance'
            ]);
    }

    public function designationService(int $n)
    {
        return $this->identifiantService(
            [
                'ligneId' => $this->{'ligne' . $n . 'Id'},
                'sens' => $this->{'sensligne' . $n},
                'moment' => $this->moment,
                'ordre' => $this->{'ordreligne' . $n}
            ]);
    }

    /**
     * Encodage d'un service
     *
     * @param int $n
     * @return string
     */
    public function getEncodeServiceId(int $n)
    {
        return $this->encodeServiceId(
            [
                'ligneId' => $this->{'ligne' . $n . 'Id'},
                'sens' => $this->{'sensligne' . $n},
                'moment' => $this->moment,
                'ordre' => $this->{'ordreligne' . $n}
            ]);
    }

    /**
     * Affectation d'un service encodé sous forme de chaine
     *
     * @param int $n
     * @param string $codeService
     */
    public function setServiceFromString(int $n, string $codeService)
    {
        $values = $this->getArrayCopy();
        $service = $this->decodeServiceId($codeService);
        $values = array_merge($values,
            [
                'ligne' . $n . 'Id' => $service->ligneId,
                'sensligne' . $n => $service->sens,
                'moment' => $service->moment,
                'ordreligne' . $n => $service->ordre
            ]);
        $this->exchangeArray($values);
    }
}