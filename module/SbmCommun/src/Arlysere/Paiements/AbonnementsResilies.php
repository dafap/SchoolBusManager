<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package
 * @filesource AbonnementsResilies.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 août 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere\Paiements;

use SbmBase\Model\StdLib;
use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Traits\DebugTrait;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;

class AbonnementsResilies extends AbstractQuery
{

    protected function init()
    {
    }

    public function setMillesime(int $millesime)
    {
        $this->millesime = $millesime;
        return $this;
    }

    public function run()
    {
        return $this->renderResult($this->selectAbonnementsResilies());
    }

    /**
     * Requête des abonnements résiliés pour l'année scolaire en cours. Lorsque les 3
     * paiement sont rayés (mouvement = 0) cela veut dire que le paiement a été annulé et
     * remboursé alors que pour un abonnement résilié le nombre de paiements est 1 ou 2.
     *
     * @return \Zend\Db\Sql\Select
     */
    private function selectAbonnementsResilies()
    {
        $where = new Where();
        $where->isNotNull('dateRefus')
            ->literal('mouvement = 0')
            ->literal('CodeModeDePaiement = 3')
            ->like('anneeScolaire', $this->millesime . '-%');
        return $this->sql->select()
            ->columns(
            [
                'responsableId',
                'datePaiement' => new Literal('DATE_FORMAT(datePaiement,"%d/%m/%Y")'),
                'dateRefus' => new Literal('DATE_FORMAT(dateRefus,"%d/%m/%Y")'),
                'nbEcheances' => new Literal('count(dateValeur)'),
                'datesEcheances' => new Literal(
                    'GROUP_CONCAT(DATE_FORMAT(dateValeur,"%d/%m/%Y") ORDER BY dateValeur SEPARATOR " et ")'),
                'montantTotal' => new Literal('SUM(montant)')
            ])
            ->from([
            'pai' => $this->db_manager->getCanonicName('paiements')
        ])
            ->where($where)
            ->group([
            'responsableId',
            'datePaiement'
        ])
            ->having('nbEcheances < 3'); // annulation et remboursement si 3
    }
}