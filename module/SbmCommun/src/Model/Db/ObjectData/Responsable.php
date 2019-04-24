<?php
/**
 * Objet contenant les données à manipuler pour la table Responsables
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Db/ObjectData
 * @filesource Responsable.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\ObjectData;

class Responsable extends AbstractObjectData
{

    private $hassbmservicesms;

    public function __construct(bool $hassbmservicesms)
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('responsableId');
        $this->hassbmservicesms = $hassbmservicesms;
    }

    public function accepteSms()
    {
        $ok = false;
        if ($this->hassbmservicesms) {
            $ok = ! empty($this->telephoneF) && $this->smsF == 1;
            $ok |= ! empty($this->telephoneP) && $this->smsP == 1;
            $ok |= ! empty($this->telephoneT) && $this->smsT == 1;
        }
        return $ok;
    }

    public function telephonesPourSms()
    {
        $telephones = [];
        foreach ([
            'F',
            'P',
            'T'
        ] as $value) {
            if (! empty($this->{"telephone$value"}) && $this->{"sms$value"} == 1) {
                $telephones[] = $this->{"telephone$value"};
            }
        }
        return $telephones;
    }
}