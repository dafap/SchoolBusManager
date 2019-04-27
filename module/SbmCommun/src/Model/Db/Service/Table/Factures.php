<?php
/**
 * Gestion de la table `factures`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Table
 * @filesource Factures.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\Db\Sql\Predicate\Literal;

class Factures extends AbstractSbmTable implements EffectifInterface
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'factures';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Factures';
        $this->id_name = [
            'exercice',
            'numero'
        ];
    }

    /**
     * La requête renvoie null si l'exercice n'est pas commencé. On renverra alors 0.
     *
     * @param int $exercice
     * @return int
     */
    public function dernierNumero($exercice)
    {
        $select = $this->getTableGateway()
            ->getSql()
            ->select();
        $select->columns([
            'numero' => new Literal('max(numero)')
        ])->where(new Literal("exercice = $exercice"));
        $resultset = $this->getTableGateway()->selectWith($select);
        return $resultset->current()->numero ?: 0;
    }
}