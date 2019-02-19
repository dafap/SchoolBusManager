<?php
/**
 * Méthodes permettant d'encoder et de décoder les requêtes Sql des documents.
 *
 * 
 * @project sbm
 * @package SbmPdf/Model
 * @filesource QuerySourceTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 févr. 2019
 * @version 2019-2.4.7
 */
namespace SbmPdf\Model;

use SbmBase\Model\Session;

trait QuerySourceTrait
{

    /**
     * Remplacement des variables éventuelles %millesime%, %date%, %heure% et %userId% et des
     * opérateurs %gt%, %gtOrEq%, %lt%, %ltOrEq%, %ltgt%, %notEq%
     *
     * @param string $sql
     * @param int $authentifiedUserId
     * @return string
     */
    public function decodeSource(string $sql, int $authentifiedUserId)
    {
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
}