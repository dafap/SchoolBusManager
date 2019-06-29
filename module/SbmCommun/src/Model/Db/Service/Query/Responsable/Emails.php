<?php
/**
 * Requêtes permettant d'obtenir les emails d'un groupe de responsables
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Query
 * @filesource Emails.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 05 juin 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Responsable;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\IsNotNull;

class Emails extends AbstractQuery
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
                'email' => 'email'
            ])
            ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
            ->having(new IsNotNull('email'));
    }
}