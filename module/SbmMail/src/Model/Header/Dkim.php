<?php
/**
 * Description d'un header DKIM pour signer un mail
 *
 * @project sbm
 * @package SbmMail/src/Model/Header
 * @filesource Dkim.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 juil. 2021
 * @version 2021-2.6.3
 */
namespace SbmMail\Model\Header;

use Zend\Mail\Header\HeaderInterface;
use Zend\Mail\Header\GenericHeader;
use Zend\Mail\Header\Exception\InvalidArgumentException;

class Dkim implements HeaderInterface
{

    protected $value;


    public function __construct(string $value)
    {
        $this->value = $value;
    }
    public function getEncoding()
    {
        return 'ASCII';
    }

    public function setEncoding($encoding)
    {
        return $this;
    }

    public function getFieldValue($format = HeaderInterface::FORMAT_RAW)
    {
        return $this->value;
    }

    public function toString()
    {
        return sprintf('%s: %s', $this->getFieldName(), $this->getFieldValue());
    }

    public static function fromString($headerLine)
    {
        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);
        if (strtolower($name) != 'dkimsignature') {
            throw new InvalidArgumentException('Ligne d’en-tête invalide pour la signature DKIM');
        }
        return new self($value);
    }

    public function getFieldName()
    {
        return 'DKIM-Signature';
    }
}