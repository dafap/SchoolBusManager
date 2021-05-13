<?php
/**
 * Méthodes nécessaires à différentes classes
 *
 *
 * @project sbm
 * @package SbmPdf/src/Model/Document
 * @filesource TableSimple.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 mars 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Document;

use SbmPdf\Model\Exception;
use SbmBase\Model\Session;

/**
 *
 * @author alain
 *
 */
trait DocumentTrait
{

    /**
     * Remplacement des variables éventuelles %millesime%, %date%, %heure% et %userId% et
     * des opérateurs %gt%, %gtOrEq%, %lt%, %ltOrEq%, %ltgt%, %notEq%
     *
     * La propriété $pdf_manager doit être accessible dans la classe d'appel.
     *
     * @param string $sql
     * @param int $authentifiedUserId
     * @return string
     */
    public function decodeSource(string $sql)
    {
        $authentifiedUserId = $this->pdf_manager->get('SbmAuthentification\Authentication')
        ->by()
        ->getUserId();
        return str_replace(
            [
                '%date%',
                '%heure%',
                '%millesime%',
                '%userId%',
                '%gt%',
                '%gtOrEq%',
                '%lt%',
                '%ltOrEq%',
                '%ltgt%',
                '%notEq%'
            ],
            [
                date('Y-m-d'),
                date('H:i:s'),
                Session::get('millesime'),
                $authentifiedUserId,
                '>',
                '>=',
                '<',
                '<=',
                '<>',
                '<>'
            ], $sql);
    }

    /**
     * Reçoit le nom d'enregistrement d'une classe dérivée de AbstractSbmTable (SbmCommun)
     * et renvoie le nom d'enregistrement d'un EffectifInterface (SbmGestion)
     *
     * @param string $stringSbmAbstractTable
     *
     * @return string
     *
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable
     * @see \SbmGestion\Model\Db\Service\EffectifInterface
     */
    public function getStringEffectifInterface($stringAbstractSbmTable)
    {
        $parts = explode('\\', $stringAbstractSbmTable);
        if (count($parts) < 4) {
            $msg = __METHOD__ . " - Argument invalide\n";
            if (getenv('APPLICATION_ENV') == 'development') {
                ob_start();
                var_dump($stringAbstractSbmTable);
                $msg .= ob_get_clean();
            }
            throw new Exception\InvalidArgumentException($msg);
        }
        $parts[2] = 'Eleve';
        $parts[3] = 'Effectif' . $parts[3];
        return implode('\\', $parts);
    }
}

