<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package
 * @filesource ApiSmsInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model;

interface ApiSmsInterface
{
}

interface ApiSmsExceptionInterface
{
}

class OutOfBoundsException extends \OutOfBoundsException implements
    ApiSmsExceptionInterface
{
}

class ArgumentException extends \Esendex\Exceptions\ArgumentException implements
    ApiSmsExceptionInterface
{
}

class EsendexException extends \Esendex\Exceptions\EsendexException implements
    ApiSmsExceptionInterface
{
}

class XmlException extends \Esendex\Exceptions\XmlException implements
    ApiSmsExceptionInterface
{
}

class TelephoneException extends \OutOfBoundsException implements
    ApiSmsExceptionInterface
{
}