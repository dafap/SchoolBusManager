<?php
/**
 * Recherche les abonnements résiliés non soldés
 *
 * @project sbm
 * @package SbmCommun\src\Arlysere\Paiements
 * @filesource AbonnementsResilies.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 juin 2021
 * @version 2021-2.6.2
 */
namespace SbmCommun\Arlysere\Paiements;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
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
        $resultset = $this->renderResult($this->selectAbonnementsResilies());
        $oCalculs = $this->db_manager->get('Sbm\Facture\Calculs');
        $array = [];
        foreach ($resultset as $row) {
            $solde = $oCalculs->getResultats($row['responsableId'], [], true)->getSolde();
            if ($solde > 0.02) {
                $array[$row['responsableId']] = $row;
            }
        }
        return array_values($array);
    }

    /**
     * Requête des abonnements résiliés pour l'année scolaire en cours.
     * Lorsque les 3
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
        // annulation et remboursement si nbEcheances == 3
        return $this->sql->select()
            ->columns(
            [
                'responsableId',
                'datePaiement' => new Literal('DATE_FORMAT(datePaiement,"%d/%m/%Y")'),
                'dateResiliation' => new Literal('DATE_FORMAT(dateRefus,"%d/%m/%Y")'),
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
            ->having('nbEcheances < 3')
            ->order([
            'dateRefus'
        ]);
    }
}