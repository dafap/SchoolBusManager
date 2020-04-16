<?php
/**
 * Validateur pour la plage Ip autorisée
 *
 * @project sbm
 * @package SbmCommun/Model/Validator
 * @filesource PlageIp.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Digits;
use Zend\Validator\Ip;

class PlageIp extends AbstractValidator
{

    const INVALID = 'ipInvalid';

    const INVALID_RANGE = 'ipRangeInvalid';

    const NOT_IP_ADDRESS = 'notIpAddress';

    const NOT_AUTHORIZED_ADDRESS = 'notAuthorizedIpAddress';

    /**
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID => 'Invalid type given. String expected',
        self::INVALID_RANGE => 'Invalid range given. Integer between 1 and 31 expected',
        self::NOT_IP_ADDRESS => 'The input does not appear to be a valid IP address',
        self::NOT_AUTHORIZED_ADDRESS => 'This IP address is not authorized.'
    ];

    public function isValidRange($value)
    {
        if (is_array($value)) {
            foreach ($value as $ip) {
                if (! $this->isValidRange($ip))
                    return false;
            }
            return true;
        } elseif (! is_string($value)) {
            $this->error(self::INVALID);
            return false;
        } else {
            $parts = explode('/', $value);
            if (count($parts) == 2) {
                $digit_validator = new Digits();
                if (! $digit_validator->isValid($parts[1])) {
                    $this->error(self::INVALID_RANGE);
                    return false;
                } elseif (! $this->isValidSubnet($this->isIpv6($parts[0]), $parts[1])) {
                    $this->error(self::INVALID_RANGE);
                    return false;
                }
            }
            $ip_validator = new Ip();
            if (! $ip_validator->isValid($parts[0])) {
                $this->error(self::NOT_IP_ADDRESS);
                return false;
            }
            return true;
        }
    }

    /**
     * Indique si l'adresse est au format IPv6. On considère qu'elle est bien formée.
     *
     * @param string $value
     * @return boolean
     */
    private function isIpv6(string $value)
    {
        return strpos($value, ':') !== false;
    }

    /**
     * Vérifie la validité du sous-réseau demandé
     *
     * @param bool $ipv6
     * @param int $subnet
     * @return boolean
     */
    private function isValidSubnet(bool $ipv6, int $subnet)
    {
        if ($ipv6) {
            return (0 < $subnet) && ($subnet <= 128);
        } else {
            return (0 < $subnet) && ($subnet <= 32);
        }
    }

    public function isValid($ip)
    {
        // contrôle $ip et le code en integer
        if (! is_string($ip)) {
            $this->error(self::INVALID);
            return false;
        }
        $validator = new Ip();
        if (! $validator->isValid($ip)) {
            $this->error(self::NOT_IP_ADDRESS);
            return false;
        }
        $inet_addr = inet_pton($ip);

        // contrôle le $range et le force en tableau
        $range = $this->getOption('range');
        if (! $this->isValidRange($range)) {
            return false;
        }
        $range = (array) $range;

        // pour chaque élément du tableau $range
        foreach ($range as $plageIp) {
            $parts = explode('/', $plageIp);
            $inet_base = inet_pton($parts[0]);
            if (count($parts) == 2) {
                $masque = $this->getMask($this->isIpv6($parts[0]), $parts[1]);
                $valid = ($inet_base & $masque) == ($inet_addr & $masque);
            } else {
                $valid = $inet_base == $inet_addr;
            }
            if ($valid) {
                return true;
            }
        }
        $this->error(self::NOT_AUTHORIZED_ADDRESS);
        return false;
    }

    private function getMask(bool $ipv6, int $subnet)
    {
        if ($ipv6) {
            $len = 128;
        } else {
            $len = 32;
        }
        $mask = str_repeat('f', $subnet >> 2);
        switch ($subnet & 3) {
            case 3:
                $mask .= 'e';
                break;
            case 2:
                $mask .= 'c';
                break;
            case 1:
                $mask .= '8';
                break;
        }
        $mask = str_pad($mask, $len >> 2, '0');

        // Packed representation of netmask
        return pack('H*', $mask);
    }
}
