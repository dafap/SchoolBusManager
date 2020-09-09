<?php
/**
 * Requêtes utilisées pour le rôle Secretariat
 *
 *
 * @project sbm
 * @package SbmPortail/src/Model/Db/Service/Query
 * @filesource Secretariat.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 sept. 2020
 * @version 2020-2.6.0
 */
namespace SbmPortail\Model\Db\Service\Query;

use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Secretariat extends \SbmCommun\Model\Db\Service\Query\AbstractQuery
{
    use \SbmCommun\Model\Traits\ExpressionSqlTrait;

    protected function init()
    {
    }

    public function get(Where $where = null)
    {
        return $this->renderResult($this->mySelect($where));
    }

    private function mySelect(Where $where = null): Select
    {
        if (is_null($where)) {
            $where = new Where();
            $where->equalTo('sco.millesime', $this->millesime)->literal('sco.inscrit = 1');
        }
        $select = $this->sql->select()
            ->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->columns(
            [
                'eleveId' => 'eleveId',
                'mailchimp' => 'mailchimp',
                'dateCreation' => 'dateCreation',
                'dateModificationEleve' => 'dateModification',
                'nom' => 'nom',
                'nomSA' => 'nomSA',
                'prenom' => 'prenom',
                'prenomSA' => 'prenomSA',
                'dateN' => 'dateN',
                'sexe' => new Literal('IF(sexe = 1,\'masculin\',\'féminin\')'),
                'numero' => 'numero',
                'responsable1Id' => 'responsable1Id',
                'responsable2Id' => 'responsable2Id',
                'responsableFId' => 'responsableFId',
                'selectionEleve' => 'selection',
                'noteEleve' => 'note',
                'servicesR1' => new Literal($this->services(1)),
                'servicesR2' => new Literal($this->services(2))
            ])
            ->join([
            'r1' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'ele.responsable1Id=r1.responsableId',
            [
                'titreR1' => 'titre',
                'responsable1NomPrenom' => new Literal('CONCAT(r1.nom," ",r1.prenom)'),
                'adresseL1R1' => 'adresseL1',
                'adresseL2R1' => 'adresseL2',
                'adresseL3R1' => 'adresseL3',
                'codePostalR1' => 'codePostal',
                'communeIdR1' => 'communeId',
                'telephoneFR1' => 'telephoneF',
                'telephonePR1' => 'telephoneP',
                'telephoneTR1' => 'telephoneT',
                'emailR1' => 'email',
                'x1' => 'x',
                'y1' => 'y'
            ])
            ->join([
            'r1c' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r1.communeId=r1c.communeId',
            [
                'communeR1' => 'nom',
                'lacommuneR1' => 'alias',
                'laposteR1' => 'alias_laposte'
            ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId = sco.eleveId',
            [
                'millesime' => 'millesime',
                'selectionScolarite' => 'selection',
                'dateInscription' => 'dateInscription',
                'dateModificationScolarite' => 'dateModification',
                'etablissementId' => 'etablissementId',
                'classeId' => 'classeId',
                'chez' => 'chez',
                'adresseL1' => 'adresseL1',
                'adresseL2' => 'adresseL2',
                'codePostal' => 'codePostal',
                'communeId' => 'communeId',
                'x' => 'x',
                'y' => 'y',
                'distanceR1' => 'distanceR1',
                'distanceR2' => 'distanceR2',
                'dateEtiquetteR1' => 'dateEtiquetteR1',
                'dateEtiquetteR2' => 'dateEtiquetteR2',
                'dateCarteR1' => 'dateCarteR1',
                'dateCarteR2' => 'dateCarteR2',
                'inscrit' => 'inscrit',
                'gratuit' => 'gratuit',
                'paiementR1' => 'paiementR1',
                'paiementR2' => 'paiementR2',
                'duplicataR1' => 'duplicataR1',
                'duplicataR2' => 'duplicataR2',
                'fa' => 'fa',
                'anneeComplete' => 'anneeComplete',
                'subventionR1' => 'subventionR1',
                'subventionR2' => 'subventionR2',
                'demandeR1' => 'demandeR1',
                'demandeR2' => 'demandeR2',
                'dateDemandeR2' => 'dateDemandeR2',
                'accordR1' => 'accordR1',
                'accordR2' => 'accordR2',
                'internet' => 'internet',
                'district' => 'district',
                'derogation' => 'derogation',
                'dateDebut' => 'dateDebut',
                'dateFin' => 'dateFin',
                'joursTransportR1' => 'joursTransportR1',
                'joursTransportR2' => 'joursTransportR2',
                'subventionTaux' => 'subventionTaux',
                'grilleTarifR1' => 'grilleTarifR1',
                'grilleCodeR1' => 'grilleTarifR1',
                'reductionR1' => 'reductionR1',
                'grilleTarifR2' => 'grilleTarifR2',
                'grilleCodeR2' => 'grilleTarifR2',
                'reductionR2' => 'reductionR2',
                'tarifId' => 'tarifId',
                'regimeId' => new Literal('IF(regimeId = 1,\'interne\',\'DP\')'),
                'motifDerogation' => 'motifDerogation',
                'motifRefusR1' => 'motifRefusR1',
                'motifRefusR2' => 'motifRefusR2',
                'commentaire' => 'commentaire'
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'sco.etablissementId = eta.etablissementId',
            [
                'etablissement' => new Literal(
                    'CASE WHEN isnull(eta.alias) OR eta.alias = "" THEN eta.nom ELSE eta.alias END'),
                'xeta' => 'x',
                'yeta' => 'y',
                'niveauEtablissement' => 'niveau'
            ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'eta.communeId = com.communeId',
            [
                'communeEtablissement' => 'nom',
                'lacommuneEtablissement' => 'aliasCG',
                'laposteEtablissement' => 'alias_laposte'
            ])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'cla.classeId = sco.classeId', [
            'niveau' => 'niveau',
            'classe' => 'nom'
        ])
            ->join([
            'ori1' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'ori1.stationId = sco.stationIdR1', [
            'origine1' => 'nom'
        ])
            ->join([
            'cor1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cor1.communeId = ori1.communeId',
            [
                'communeOrigine1' => 'nom',
                'lacommuneOrigine1' => 'alias',
                'laposteOrigine1' => 'alias_laposte'
            ])
            ->join([
            'r2' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'ele.responsable2Id=r2.responsableId',
            [
                'titreR2' => 'titre',
                'responsable2NomPrenom' => new Literal(
                    'CASE WHEN isnull(r2.responsableId) THEN "" ELSE concat(r2.nom," ",r2.prenom) END'),
                'adresseL1R2' => 'adresseL1',
                'adresseL2R2' => 'adresseL2',
                'adresseL3R2' => 'adresseL3',
                'codePostalR2' => 'codePostal',
                'communeIdR2' => 'communeId',
                'telephoneFR2' => 'telephoneF',
                'telephonePR2' => 'telephoneP',
                'telephoneTR2' => 'telephoneT',
                'emailR2' => 'email',
                'x2' => 'x',
                'y2' => 'y'
            ], Select::JOIN_LEFT)
            ->join([
            'r2c' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r2.communeId=r2c.communeId',
            [
                'communeR2' => 'nom',
                'lacommuneR2' => 'alias',
                'laposteR2' => 'alias_laposte'
            ], Select::JOIN_LEFT)
            ->join([
            'ori2' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'ori2.stationId = sco.stationIdR2', [
            'origine2' => 'nom'
        ], Select::JOIN_LEFT)
            ->join([
            'cor2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cor2.communeId = ori2.communeId',
            [
                'communeOrigine2' => 'nom',
                'lacommuneOrigine2' => 'alias',
                'laposteOrigine2' => 'alias_laposte'
            ], Select::JOIN_LEFT);
        $select = $this->subAffectation($select, 1);
        $select = $this->subAffectation($select, 2);
        $select->where($where)->group('ele.eleveId');
        // die($this->getSqlString($select));
        return $select;
    }

    private function subAffectation(Select $select, int $trajet): Select
    {
        $subselect = $this->sql->select($this->db_manager->getCanonicName('affectations'))
            ->where((new Where())->equalTo('trajet', $trajet));
        return $select->join([
            "affR$trajet" => $subselect
        ], "affR$trajet.millesime = sco.millesime And affR$trajet.eleveId = sco.eleveId",
            [], Select::JOIN_LEFT)
            ->join([
            "cir1R$trajet" => $this->db_manager->getCanonicName('circuits')
        ], $this->jointureAffectationsCircuits($trajet, 1), [], Select::JOIN_LEFT)
            ->join([
            "sta1R$trajet" => $this->db_manager->getCanonicName('stations')
        ], "cir1R$trajet.stationId = sta1R$trajet.stationId",
            [
                "station1R$trajet" => 'nom'
            ], Select::JOIN_LEFT)
            ->join(
            [
                "comsta1R$trajet" => $this->db_manager->getCanonicName('communes', 'table')
            ], "comsta1R$trajet.communeId = sta1R$trajet.communeId",
            [
                "communeStation1R$trajet" => 'nom',
                "lacommuneStation1R$trajet" => 'alias',
                "laposteStation1R$trajet" => 'alias_laposte'
            ], Select::JOIN_LEFT)
            ->join([
            "cir2R$trajet" => $this->db_manager->getCanonicName('circuits')
        ], $this->jointureAffectationsCircuits($trajet, 2), [], Select::JOIN_LEFT)
            ->join([
            "sta2R$trajet" => $this->db_manager->getCanonicName('stations')
        ], "cir2R$trajet.stationId = sta2R$trajet.stationId",
            [
                "station2R$trajet" => 'nom'
            ], Select::JOIN_LEFT)
            ->join(
            [
                "comsta2R$trajet" => $this->db_manager->getCanonicName('communes', 'table')
            ], "comsta2R$trajet.communeId = sta2R$trajet.communeId",
            [
                "communeStation2R$trajet" => 'nom',
                "lacommuneStation2R$trajet" => 'alias',
                "laposteStation2R$trajet" => 'alias_laposte'
            ], Select::JOIN_LEFT);
    }

    private function jointureAffectationsCircuits(int $trajet, int $n)
    {
        return implode(' And ',
            [
                "affR$trajet.millesime = cir$n" . "R$trajet.millesime",
                "affR$trajet.ligne1Id = cir$n" . "R$trajet.ligneId",
                "affR$trajet.sensligne1 = cir$n" . "R$trajet.sens",
                "affR$trajet.moment = cir$n" . "R$trajet.moment",
                "affR$trajet.ordreligne1 = cir$n" . "R$trajet.ordre",
                "affR$trajet.station$n" . "Id = cir$n" . "R$trajet.stationId"
            ]);
    }

    private function services(int $trajet)
    {
        $descripteur = sprintf(
            "IF(ISNULL(%s), NULL, CONCAT_WS(' ',%s, %s, %s, %s, %s, %s, %s, %s))",
            "affR$trajet.eleveId", $this->getSqlSemaine("affR$trajet.jours"),
            "affR$trajet.ligne1Id", "DATE_FORMAT(cir1R$trajet.horaireD,'%H:%i')",
            "sta1R$trajet.nom", "comsta1R$trajet.alias",
            "DATE_FORMAT(cir2R$trajet.horaireD,'%H:%i')", "sta2R$trajet.nom",
            "comsta2R$trajet.alias");
        $services = sprintf(
            'GROUP_CONCAT(DISTINCT %2$s ORDER BY affR%1$d.trajet ASC, cir1R%1$d.horaireD ASC, affR%1$d.jours DESC SEPARATOR " | ")',
            $trajet, $descripteur);
        return $services;
    }
}