<?php
/**
 * Objet contenant les données à manipuler pour la table Circuits
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Circuit.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\ObjectData;

class Circuit extends AbstractObjectData
{
    use \SbmCommun\Model\Traits\ServiceTrait;

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('circuitId');
    }

    public function designationService()
    {
        return $this->identifiantService($this->getArrayCopy());
    }

    /**
     * Encodage d'un service
     *
     * @return string
     */
    public function getEncodeServiceId()
    {
        return $this->encodeServiceId(
            [
                'ligneId' => $this->ligneId,
                'sens' => $this->sens,
                'moment' => $this->moment,
                'ordre' => $this->ordre
            ]);
    }

    /**
     * Affectation d'un service encodé sous forme de chaine
     *
     * @param string $codeService
     */
    public function setServiceFromString(string $codeService)
    {
        $values = $this->getArrayCopy();
        $service = $this->decodeServiceId($codeService);
        $values = array_merge($values,
            [
                'ligneId' => $service->ligneId,
                'sens' => $service->sens,
                'moment' => $service->moment,
                'ordre' => $service->ordre
            ]);
        $this->exchangeArray($values);
    }
}