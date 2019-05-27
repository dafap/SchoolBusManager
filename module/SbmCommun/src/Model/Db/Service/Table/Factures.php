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
 * @date 27 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\Db\Sql\Where;
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

    /**
     * Renvoie la dernière facture adressée à un responsable pour un millesime donné ou
     * false s'il n'y en a pas.
     *
     * @param int $millesime
     * @param int $responsableId
     *            return \SbmCommun\Model\Db\ObjectData\Facture | false
     */
    public function derniereFacture($millesime, $responsableId)
    {
        $where = new Where();
        $where->equalTo('millesime', $millesime)->equalTo('responsableId', $responsableId);
        $subselect = $this->getTableGateway()
            ->getSql()
            ->select();
        $subselect->columns([
            'numero' => new Literal('max(numero)')
        ])->where($where)->group('responsableId');
        // requête principale
        $where = new Where();
        $where->equalTo('millesime', $millesime)->equalTo('numero',$subselect);
        $select = $this->getTableGateway()
            ->getSql()
            ->select();
        $select->where($where);
        $resultset = $this->getTableGateway()->selectWith($select);
        if ($resultset->count()) {
            return $resultset->current();
        }
        return false;
    }
}