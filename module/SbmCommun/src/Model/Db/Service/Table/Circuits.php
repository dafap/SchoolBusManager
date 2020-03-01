<?php
/**
 * Gestion de la table `circuits`
 * (à déclarer dans module.config.php)
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Circuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 fév. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Strategy\Semaine as SemaineStrategy;
use Zend\Db\Sql\Expression;

class Circuits extends AbstractSbmTable implements EffectifInterface
{

    /**
     * Initialisation du circuit
     */
    protected function init()
    {
        $this->table_name = 'circuits';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Circuits';
        $this->id_name = 'circuitId';
        $this->strategies['semaine'] = new SemaineStrategy();
    }

    public function getSemaine()
    {
        return [
            SemaineStrategy::CODE_SEMAINE_LUNDI => 'lun',
            SemaineStrategy::CODE_SEMAINE_MARDI => 'mar',
            SemaineStrategy::CODE_SEMAINE_MERCREDI => 'mer',
            SemaineStrategy::CODE_SEMAINE_JEUDI => 'jeu',
            SemaineStrategy::CODE_SEMAINE_VENDREDI => 'ven',
            SemaineStrategy::CODE_SEMAINE_SAMEDI => 'sam',
            SemaineStrategy::CODE_SEMAINE_DIMANCHE => 'dim'
        ];
    }

    public function setSelection(int $circuitId, $selection)
    {
        $oData = clone $this->getObjData();
        $oData->exchangeArray([
            'circuitId' => $circuitId,
            'selection' => $selection
        ]);
        parent::saveRecord($oData);
    }

    /**
     * Charge un point d'arrêt d'un circuit à partir de ses caractéristiques
     *
     * @param int $millesime
     * @param string $ligneId
     * @param int $sens
     * @param int $moment
     * @param int $ordre
     * @param int $stationId
     * @return \SbmCommun\Model\Db\ObjectData\ObjectDataInterface
     */
    public function getCircuit(int $millesime, string $ligneId, int $sens, int $moment,
        int $ordre, int $stationId)
    {
        return $this->getRecord(
            [
                'millesime' => $millesime,
                'ligneId' => $ligneId,
                'sens' => $sens,
                'moment' => $moment,
                'ordre' => $ordre,
                'stationId' => $stationId
            ]);
    }

    /**
     * Renvoie vrai si la table ne contient pas de données pour ce millésime.
     *
     * @param int $millesime
     *
     * @return boolean
     */
    public function isEmptyMillesime($millesime)
    {
        $resultset = $this->fetchAll([
            'millesime' => $millesime
        ]);
        return $resultset->count() == 0;
    }

    /**
     * Supprime tous les enregistrements concernant le millesime indiqué.
     *
     * @param int $millesime
     *
     * @return int
     */
    public function viderMillesime($millesime)
    {
        return $this->table_gateway->delete([
            'millesime' => $millesime
        ]);
    }

    /**
     * Renvoie le dernier millesime utilisé dans la table des circuits
     *
     * @return int
     */
    public function getDernierMillesime()
    {
        $select = $this->getTableGateway()
            ->getSql()
            ->select();
        $select->columns([
            'millesime' => new Expression('max(millesime)')
        ]);
        $resultset = $this->getTableGateway()->selectWith($select);
        $row = $resultset->current();
        return $row->millesime;
    }

    /**
     * Vide les horaires du ou des circuits correspondants aux pramètres donnés en mettant
     * la valeur par défaut dans chaque colonne de l'horaire et met à jour la colonne
     * semaine. Pour vider plusieurs horaires sur une même ligne, mettre les paramètres
     * $sens, $moment ou $ordre à 0. Impossible de mettre $sens ou $moment à 0 sans mettre
     * $ordre à 0 aussi.
     *
     * @todo : Vérifier si semaine doit être codé en int ou sous forme de tableau de
     *       puissances de 2
     * @param int $millesime
     * @param string $ligneId
     * @param int $sens
     * @param int $moment
     * @param int $ordre
     * @param int $semaine
     */
    public function viderHoraire(int $millesime, string $ligneId, int $sens = 0,
        int $moment = 0, int $ordre = 0, int $semaine = 31)
    {
        $keys = [
            'millesime' => $millesime,
            'ligneId' => $ligneId,
            'sens' => $sens,
            'moment' => $moment,
            'ordre' => $ordre
        ];
        if ($ordre == 0) {
            unset($keys['ordre']);
            if ($moment == 0) {
                unset($keys['moment']);
            }
            if ($sens == 0) {
                unset($keys['sens']);
            }
        }
        $this->table_gateway->update(
            [
                'semaine' => $semaine,
                'horaireA' => $this->column_defaults['horaireA'],
                'horaireD' => $this->column_defaults['horaireD']
            ], $keys);
    }

    /**
     * Mise à jour de la semaine pour un service donné
     *
     * @param int $millesime
     * @param string $ligneId
     * @param int $sens
     * @param int $moment
     * @param int $ordre
     * @param int $semaine
     */
    public function majSemaine(int $millesime, string $ligneId, int $sens, int $moment,
        int $ordre, int $semaine)
    {
        $keys = [
            'millesime' => $millesime,
            'ligneId' => $ligneId,
            'sens' => $sens,
            'moment' => $moment,
            'ordre' => $ordre
        ];
        $this->table_gateway->update([
            'semaine' => $semaine
        ], $keys);
    }
}

