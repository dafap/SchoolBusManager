<?php
/**
 * Validateur pour la plage Ip autorisée
 *
 * @project sbm
 * @package SbmCommun/Model/Validator
 * @filesource PlageIp.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
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
                } elseif (1 > (int) $parts[1] || 31 < (int) $parts[1]) {
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
        $intIp = $this->ipToInteger($ip);
        
        // contrôle le $range et le force en tableau
        $range = $this->getOption('range');
        if (! $this->isValidRange($range)) {
            return false;
        }
        $range = (array) $range;
        
        // pour chaque élément du tableau $range
        foreach ($range as $plageIp) {
            $parts = explode('/', $plageIp);
            $intBaseIp = $this->ipToInteger($parts[0]);
            if (count($parts) == 2) {
                $masque = (int) (0xffffff << (32 - $parts[1]));
                $valid = ($intBaseIp & $masque) == ($intIp & $masque);
            } else {
                $valid = $intBaseIp == $intIp;
            }
            if ($valid) {
                return true;
            }
        }
        $this->error(self::NOT_AUTHORIZED_ADDRESS);
        return false;
    }

    /**
     * Renvoie un entier représentant l'adresse IP
     * L'adresse ip est valide.
     *
     * @param string $ip            
     * @return integer
     */
    private function ipToInteger($ip)
    {
        $t4 = explode('.', $ip);
        $n = 0;
        foreach ($t4 as $i) {
            $n = (int) (256 * $n + $i);
        }
        return $n;
    }
}
