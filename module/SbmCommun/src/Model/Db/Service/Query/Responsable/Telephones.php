<?php
/**
 * Requêtes permettant d'obtenir les téléphones recevant des SMS pour des groupes de parents
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Query
 * @filesource Telephones.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 05 juin 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Responsable;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate;

class Telephones extends AbstractQuery
{
    use QueryTrait;

    protected function init()
    {
        $this->select = $this->sql->select()
            ->from(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns(
            [
                'responsableId' => 'responsableId',
                'to' => new Expression(
                    'CONCAT(res.titre, " ", res.nomSA, " ", res.prenomSA)'),
                'telephoneF' => new Expression(
                    'CASE WHEN smsF=1 THEN res.telephoneF ELSE NULL END'),
                'telephoneP' => new Expression(
                    ' CASE WHEN smsP=1 THEN res.telephoneP ELSE NULL END'),
                'telephoneT' => new Expression(
                    ' CASE WHEN smsT=1 THEN res.telephoneT ELSE NULL END')
            ])
            ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
            ->having(
            [
                new Predicate\IsNotNull('TelephoneF'),
                new Predicate\IsNotNull('TelephoneP'),
                new Predicate\IsNotNull('TelephoneT')
            ], Predicate\PredicateSet::OP_OR);
    }
}