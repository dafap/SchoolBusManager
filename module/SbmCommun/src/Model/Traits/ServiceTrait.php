<?php
/**
 * Formulaire de saisie et modification d'un lien etablissement-service
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Traits
 * @filesource ServiceTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Traits;

trait ServiceTrait
{

    /**
     * Tableau des clés servant à identifier un service pour un millesime donné. Ce
     * tableau est ordonné dans l'ordre logique souhaité.
     *
     * @return string[]
     */
    public static function getServiceKeys()
    {
        return [
            'ligneId',
            'sens',
            'moment',
            'ordre'
        ];
    }

    /**
     * Renvoie le sens du service, en clair. L'argument est un tableau identifant le
     * service ou la valeur numérique de la clé 'sens' de ce service. S'il n'y a pas
     * d'argument, le tableau des sens est renvoyé.
     *
     * @param mixed $data
     */
    public static function getSens($data = 0)
    {
        $ref = [
            1 => 'Aller',
            2 => 'Retour'
        ];
        if ($data) {
            if (is_array($data)) {
                return $ref[$data['sens']];
            } elseif (is_int($data)) {
                return $ref[$data];
            } else {
                $msg = sprintf('Argument invalide : %s', $data);
                throw new \SbmCommun\Model\Exception\InvalidArgumentException($msg);
            }
        }
        return $ref;
    }

    /**
     * Renvoie le moment du service, en clair. L'argument est un tableau identifant le
     * service ou la valeur numérique de la clé 'moment' de ce service. S'il n'y a pas
     * d'argument, le tableau des moments est renvoyé.
     *
     * @param mixed $data
     */
    public static function getMoment($data = 0)
    {
        $ref = [
            1 => 'Matin',
            2 => 'Midi',
            3 => 'Soir'
        ];
        if ($data) {
            if (is_array($data)) {
                return $ref[$data['moment']];
            } elseif (is_int($data)) {
                return $ref[$data];
            } else {
                $msg = sprintf('Argument invalide : %s', $data);
                throw new \SbmCommun\Model\Exception\InvalidArgumentException($msg);
            }
        }
        return $ref;
    }

    /**
     * Vérifie que les clés sont présentent dans le data fourni, peu import l'ordre
     *
     * @param array $data
     * @return boolean
     */
    public function validServiceKeys(array $data)
    {
        $ref = self::getServiceKeys();
        sort($ref);
        $trikeys = array_values(array_intersect(array_keys($data), $ref));
        sort($trikeys);
        return $trikeys == $ref;
    }

    /**
     * Retourne une chaine de la forme ligneId|sens|moment|ordre
     *
     * @param array $data
     * @return string
     */
    public function encodeServiceId(array $data)
    {
        $ref = self::getServiceKeys();
        $serviceKeys = array_intersect_key($data, array_combine($ref, $ref));
        $serviceKeys = array_merge(array_combine($ref, $ref), $serviceKeys);
        return implode('|', $serviceKeys);
    }

    /**
     * Renvoie un tableau associatif de la forme ['ligneId'=>..., 'sens'=>...,
     * 'moment'=>..., 'ordre'=>...]
     *
     * @param string $serviceId
     * @throws \SbmCommun\Model\Exception\InvalidArgumentException
     * @return array
     */
    public function decodeServiceId(string $serviceId = '')
    {
        if ($serviceId) {
            $ref = self::getServiceKeys();
            $array = explode('|', $serviceId);
            $nbref = count($ref);
            $nbarray = count($array);
            if ($nbarray == $nbref + 1) {
                unset($array[0]);
            } elseif ($nbarray != $nbref) {
                $msg = sprintf('Argument invalide : %s', $serviceId);
                throw new \SbmCommun\Model\Exception\InvalidArgumentException($msg);
            }
            return array_combine($ref, $array);
        } else {
            return [];
        }
    }

    /**
     * On considère que le data a été vérifié et est bien formé
     *
     * @param array $data
     */
    public function identifiantService(array $data)
    {
        return sprintf('%s - %s - %s - Numéro %d', $data['ligneId'], self::getSens($data),
            self::getMoment($data), $data['ordre']);
    }

    /**
     * Renvoi une condition de jointure entre les table1 et table2 sur des services
     *
     * @param string $table1
     * @param string $table2
     * @return string
     */
    public function jointureService(string $table1, string $table2)
    {
        $items = $this->getServiceKeys();
        $items[] = 'millesime';
        $cond = [];
        foreach ($items as $item) {
            $cond[] = sprintf('%1$s.%3$s=%2$s.%3$s', $table1, $table2, $item);
        }
        return implode(' AND ', $cond);
    }
}

