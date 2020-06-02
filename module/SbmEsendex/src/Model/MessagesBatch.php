<?php
/**
 * Objet de données des informations renvoyées par le service 'messagebatches' de l'API
 *
 * @project sbm
 * @package SbmEsendex/src/Model
 * @filesource MessagesBatch.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model;

class MessagesBatch
{

    public static $nomenclature = [
        'messagebatch' => 'lot de messages',
        'createdat' => 'date de création',
        'batchsize' => 'taille du lot',
        'persistedbatchsize' => 'taille de lot persistante',
        'acknowledged' => 'reconnu',
        'authorisationfailed' => 'autorisation échouée',
        'connecting' => 'connexion',
        'delivered' => 'livré',
        'failed' => 'échoué',
        'partiallydelivered' => 'partiellement livré',
        'rejected' => 'rejeté',
        'scheduled' => 'prévu',
        'sent' => 'envoyé',
        'submitted' => 'soumis',
        'validityperiodexpired' => 'période de validité expirée',
        'cancelled' => 'annulé',
        'accountreference' => 'référence du compte',
        'createdby' => 'créé par',
        'name' => 'nom'
    ];

    private $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function messagebatch($value = null)
    {
        if ($value != null) {
            $this->data['messagebatch'] = (string) $value;
        }
        return $this->data['messagebatch'];
    }

    public function createdat($value = null)
    {
        if ($value != null) {
            $date = \DateTime::createFromFormat(\DateTime::RFC3339_EXTENDED,
                (string) $value);
            if ($date) {
                $this->data['createdat'] = $date->format('d/m/Y H:i:s');
            } else {
                $this->data['createdat'] = (string) $value;
            }
        }
        return $this->data['createdat'];
    }

    public function batchsize($value = null)
    {
        if ($value != null) {
            $this->data['batchsize'] = (string) $value;
        }
        return $this->data['batchsize'];
    }

    public function persistedbatchsize($value = null)
    {
        if ($value != null) {
            $this->data['persistedbatchsize'] = (string) $value;
        }
        return $this->data['persistedbatchsize'];
    }

    public function acknowledged($value = null)
    {
        if ($value != null) {
            $this->data['acknowledged'] = (string) $value;
        }
        return $this->data['acknowledged'];
    }

    public function authorisationfailed($value = null)
    {
        if ($value != null) {
            $this->data['authorisationfailed'] = (string) $value;
        }
        return $this->data['authorisationfailed'];
    }

    public function connecting($value = null)
    {
        if ($value != null) {
            $this->data['connecting'] = (string) $value;
        }
        return $this->data['connecting'];
    }

    public function delivered($value = null)
    {
        if ($value != null) {
            $this->data['delivered'] = (string) $value;
        }
        return $this->data['delivered'];
    }

    public function failed($value = null)
    {
        if ($value != null) {
            $this->data['failed'] = (string) $value;
        }
        return $this->data['failed'];
    }

    public function partiallydelivered($value = null)
    {
        if ($value != null) {
            $this->data['partiallydelivered'] = (string) $value;
        }
        return $this->data['partiallydelivered'];
    }

    public function rejected($value = null)
    {
        if ($value != null) {
            $this->data['rejected'] = (string) $value;
        }
        return $this->data['rejected'];
    }

    public function scheduled($value = null)
    {
        if ($value != null) {
            $this->data['scheduled'] = (string) $value;
        }
        return $this->data['scheduled'];
    }

    public function sent($value = null)
    {
        if ($value != null) {
            $this->data['sent'] = (string) $value;
        }
        return $this->data['sent'];
    }

    public function submitted($value = null)
    {
        if ($value != null) {
            $this->data['submitted'] = (string) $value;
        }
        return $this->data['submitted'];
    }

    public function validityperiodexpired($value = null)
    {
        if ($value != null) {
            $this->data['validityperiodexpired'] = (string) $value;
        }
        return $this->data['validityperiodexpired'];
    }

    public function cancelled($value = null)
    {
        if ($value != null) {
            $this->data['cancelled'] = (string) $value;
        }
        return $this->data['cancelled'];
    }

    public function accountreference($value = null)
    {
        if ($value != null) {
            $this->data['accountreference'] = (string) $value;
        }
        return $this->data['accountreference'];
    }

    public function createdby($value = null)
    {
        if ($value != null) {
            $this->data['createdby'] = (string) $value;
        }
        return $this->data['createdby'];
    }

    public function name($value = null)
    {
        if ($value != null) {
            $this->data['name'] = (string) $value;
        }
        return $this->data['name'];
    }

    public function toArray()
    {
        $array = [];
        foreach ($this->data as $key => $value) {
            $array[self::$nomenclature[$key]] = $value;
        }
        return $array;
    }
}