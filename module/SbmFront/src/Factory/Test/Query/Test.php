<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package
 * @filesource Test.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 sept. 2020
 * @version 2020-2.6.0
 */
namespace SbmFront\Factory\Test\Query;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Expression;

class Test extends \SbmCommun\Model\Db\Service\Query\AbstractQuery
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
                'services' => new Literal($this->services())
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
            ], Select::JOIN_LEFT)
            ->join(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ], 'aff.millesime = sco.millesime And aff.eleveId = sco.eleveId', [],
            Select::JOIN_LEFT)
            ->join([
            'cir1' => $this->db_manager->getCanonicName('circuits')
        ], $this->jointureAffectationsCircuits(1), [], Select::JOIN_LEFT)
            ->join([
            'sta1' => $this->db_manager->getCanonicName('stations')
        ], 'cir1.stationId = sta1.stationId', [
            'station1' => 'nom'
        ], Select::JOIN_LEFT)
            ->join(
            [
                'comsta1' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'comsta1.communeId = sta1.communeId',
            [
                'communeStation1' => 'nom',
                'lacommuneStation1' => 'alias',
                'laposteStation1' => 'alias_laposte'
            ], Select::JOIN_LEFT)
            ->join([
            'cir2' => $this->db_manager->getCanonicName('circuits')
        ], $this->jointureAffectationsCircuits(2), [], Select::JOIN_LEFT)
            ->join([
            'sta2' => $this->db_manager->getCanonicName('stations')
        ], 'cir2.stationId = sta2.stationId', [
            'station2' => 'nom'
        ], Select::JOIN_LEFT)
            ->join(
            [
                'comsta2' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'comsta2.communeId = sta2.communeId',
            [
                'communeStation2' => 'nom',
                'lacommuneStation2' => 'alias',
                'laposteStation2' => 'alias_laposte'
            ], Select::JOIN_LEFT)
            ->where($where)
            ->group('ele.eleveId');
        die($this->getSqlString($select));
        return $select;
    }

    private function jointureAffectationsCircuits(int $n)
    {
        return implode(' And ',
            [
                'aff.millesime = cir' . $n . '.millesime',
                'aff.ligne1Id = cir' . $n . '.ligneId',
                'aff.sensligne1 = cir' . $n . '.sens',
                'aff.moment = cir' . $n . '.moment',
                'aff.ordreligne1 = cir' . $n . '.ordre',
                'aff.station' . $n . 'Id = cir' . $n . '.stationId'
            ]);
    }

    private function services()
    {
        $descripteur = sprintf('CONCAT_WS(\' \',%s, %s, %s, %s, %s, %s, %s, %s)',
            $this->getSqlSemaine('aff.jours'), 'aff.ligne1Id',
            'DATE_FORMAT(cir1.horaireD,\'%H:%i\')', 'sta1.nom', 'comsta1.alias',
            'DATE_FORMAT(cir2.horaireD,\'%H:%i\')', 'sta2.nom', 'comsta2.alias');
        $services = sprintf(
            'GROUP_CONCAT(%s ORDER BY cir1.horaireD ASC, cir1.semaine DESC SEPARATOR " | ")',
            $descripteur);
        return $services;
    }
}