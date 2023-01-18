<?php
/**
 * Requêtes sur la table `invites`
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Services/Query/Invite
 * @filesource Invites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 jan. 2023
 * @version 2021-2.6.7
 */
namespace SbmCommun\Model\Db\Service\Query\Invite;

use SbmBase\Model\DateLib;
use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Invites extends AbstractQuery
{

    protected function init()
    {
    }

    public function paginatorInvites(Where $where, $order)
    {
        return $this->paginator($this->selectPaginatorInvites($where, $order));
    }

    private function selectPaginatorInvites(Where $where, $order = null)
    {
        $select = $this->sql->select(
            [
                'inv' => $this->db_manager->getCanonicName('invites', 'table')
            ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(
            [
                'inviteId',
                'demande',
                'dateDebut',
                'dateFin',
                'beneficiaire' => new Literal($this->xSqlBeneficiaire()),
                'responsable' => new Literal($this->xSqlResponsable()),
                'adresseL1' => new Literal($this->xSqlAdresseL(1)),
                'adresseL2' => new Literal($this->xSqlAdresseL(2)),
                'adresseL3' => new Literal($this->xSqlAdresseL(3)),
                'codePostal' => new Literal($this->xSqlCodePostal()),
                'commune' => new Literal($this->xSqlCommune()),
                'responsable2' => new Literal($this->xSqlResponsable2()),
                'adresseL1' => new Literal($this->xSqlAdresseR2L(1)),
                'adresseL2' => new Literal($this->xSqlAdresseR2L(2)),
                'adresseL3' => new Literal($this->xSqlAdresseR2L(3)),
                'codePostal' => new Literal($this->xSqlCodePostalR2()),
                'commune' => new Literal($this->xSqlCommuneR2()),
                'station' => new Literal($this->xSqlStation()),
                'stationR2' => new Literal($this->xSqlStationR2()),
                'etablissement' => new Literal($this->xSqlEtablissement()),
                'servicesMatin' => new Literal($this->xSqlServicesMatin()),
                'servicesMidi' => new Literal($this->xSqlServicesMidi()),
                'servicesSoir' => new Literal($this->xSqlServicesSoir()),
                'servicesMerSoir' => new Literal($this->xSqlServicesMerSoir())
            ])
            ->join([
            'ele' => $this->subselectEleves()
        ], 'inv.eleveId=ele.eleveId', [], Select::JOIN_LEFT)
            ->join([
            'res' => $this->subselectResponsable()
        ], 'inv.responsableId=res.responsableId', [], Select::JOIN_LEFT)
            ->join([
            'org' => $this->subselectOrganismes()
        ], 'org.organismeId = inv.organismeId', [], Select::JOIN_LEFT)
            ->join([
            'cominv' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'inv.communeId = cominv.communeId',
            [
                // 'commune' => 'nom',
                'lacommune' => 'alias'
                // 'laposte' => 'alias_laposte'
            ], Select::JOIN_LEFT)
            ->join([
            'eta' => $this->subselectEtablissements()
        ], 'inv.etablissementId = eta.etablissementId', [], Select::JOIN_LEFT)
            ->join([
            'sta' => $this->subselectStations()
        ], 'sta.stationId = inv.stationId', [], Select::JOIN_LEFT)
            ->where($where);
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select;
    }

    private function subselectEleves(): Select
    {
        return $this->sql->select(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns([
            'stationIdR1',
            'stationIdR2',
            'etablissementId'
        ])
            ->join([
            'eta' => $this->subselectEtablissements()
        ], 'eta.etablissementId = sco.etablissementId', [
            'etablissement'
        ])
            ->join([
            'st1' => $this->subselectStations()
        ], 'st1.stationId = sco.stationIdR1', [
            'stationR1' => 'station'
        ])
            ->join([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ], 'sco.eleveId = ele.eleveId', [
            'eleveId',
            'responsable2Id',
            'nom',
            'prenom'
        ])
            ->join([
            'r1' => $this->subselectResponsable()
        ], 'r1.responsableId = ele.responsable1Id',
            [
                'responsable1' => 'responsable',
                'adresseL1R1' => 'adresseL1',
                'adresseL2R1' => 'adresseL2',
                'adresseL3R1' => 'adresseL3',
                'codePostalR1' => 'codePostal',
                // 'communeR1' => 'commune',
                'lacommuneR1' => 'lacommune'
                // 'laposteR1' => 'laposte'
            ])
            ->join([
            'r2' => $this->subselectResponsable()
        ], 'r2.responsableId = ele.responsable2Id',
            [
                'responsable2' => 'responsable',
                'adresseL1R2' => 'adresseL1',
                'adresseL2R2' => 'adresseL2',
                'adresseL3R2' => 'adresseL3',
                'codePostalR2' => 'codePostal',
                // 'communeR2' => 'commune',
                'lacommuneR2' => 'lacommune'
                // 'laposteR2' => 'laposte'
            ], Select::JOIN_LEFT)
            ->join([
            'st2' => $this->subselectStations()
        ], 'st2.stationId = sco.stationIdR2', [
            'stationR2' => 'station'
        ], Select::JOIN_LEFT)
            ->join([
            'saff' => $this->subselectAffectations()
        ], 'saff.millesime = sco.millesime AND saff.eleveId = sco.eleveId',
            [
                'matin',
                'midi',
                'soir',
                'msoir'
            ], Select::JOIN_LEFT)
            ->where((new Where())->equalTo('sco.millesime', $this->millesime));
    }

    private function subselectResponsable(): Select
    {
        return $this->sql->select(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns(
            [
                'responsableId',
                'responsable' => new Literal(
                    'CONCAT_WS(" ",res.titre,res.nom,res.prenom)'),
                'adresseL1',
                'adresseL2',
                'adresseL3',
                'codePostal'
            ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'res.communeId = com.communeId',
            [
                // 'commune' => 'nom',
                'lacommune' => 'alias'
                // 'laposte' => 'alias_laposte'
            ]);
    }

    private function subselectEtablissements(): Select
    {
        return $this->sql->select(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ])
            ->columns(
            [
                'etablissementId',
                'etablissement' => new Literal('CONCAT_WS(" - ", com.alias, eta.nom)')
            ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'com.communeId = eta.communeId',
            [ // 'commune' => 'nom',
               // 'lacommune' => 'alias',
               // 'laposte' => 'alias_laposte'
            ]);
    }

    private function subselectStations(): Select
    {
        return $this->sql->select(
            [
                'sta' => $this->db_manager->getCanonicName('stations', 'table')
            ])
            ->columns(
            [
                'stationId',
                'station' => new Literal('CONCAT_WS(" - ", com.alias, sta.nom)')
            ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'com.communeId = sta.communeId',
            [ // 'commune' => 'nom',
               // 'lacommune' => 'alias',
               // 'laposte' => 'alias_laposte'
            ]);
    }

    private function subselectOrganismes(): Select
    {
        return $this->sql->select(
            [
                'org' => $this->db_manager->getCanonicName('organismes', 'table')
            ])
            ->columns(
            [
                'organismeId',
                'organisme' => 'nom',
                'adresseL1' => 'adresse1',
                'adresseL2' => 'adresse2',
                'adresseL3' => null,
                'codePostal'
            ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'com.communeId = org.communeId',
            [
                // 'commune' => 'nom',
                'lacommune' => 'alias'
                // 'laposte' => 'alias_laposte'
            ]);
    }

    // ===============================================================
    private function subselectAffectations(): Select
    {
        $where = new Where();
        $where->equalTo('aff.millesime', $this->millesime);
        return $this->sql->select()
            ->from(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ])
            ->columns([
            'millesime',
            'eleveId',
            'trajet'
        ])
            ->join([
            'tmp' => $this->db_manager->getCanonicName('invites', 'table')
        ], 'tmp.millesime = aff.millesime AND tmp.eleveId = aff.eleveId', [])
            ->join([
            'smat' => $this->subselectServicesMatin()
        ], 'smat.millesime = aff.millesime AND smat.eleveid = aff.eleveId', [
            'matin'
        ], Select::JOIN_LEFT)
            ->join([
            'smid' => $this->subselectServicesMidi()
        ], 'smid.millesime = aff.millesime AND smid.eleveId = aff.eleveId', [
            'midi'
        ], Select::JOIN_LEFT)
            ->join([
            'ssoi' => $this->subselectServicesSoir()
        ], 'ssoi.millesime = aff.millesime AND ssoi.eleveId = aff.eleveId', [
            'soir'
        ], Select::JOIN_LEFT)
            ->join([
            'smso' => $this->subselectServicesMercrediSoir()
        ], 'smso.millesime = aff.millesime AND smso.eleveId = aff.eleveId', [
            'msoir'
        ], Select::JOIN_LEFT)
            ->where((new Where())->equalTo('aff.millesime', $this->millesime))
            ->group([
            'aff.millesime',
            'aff.eleveId',
            'aff.trajet'
        ]);
    }

    private function subselectServicesMatin(): Select
    {
        $where = new Where();
        $where->literal('moment = 1')->equalTo('millesime', $this->millesime);
        return $this->sql->select(
            $this->db_manager->getCanonicName('affectations', 'table'))
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'matin' => new Literal("GROUP_CONCAT(DISTINCT ligne1Id SEPARATOR '-')")
            ])
            ->where($where)
            ->group([
            'millesime',
            'eleveId',
            'trajet'
        ]);
    }

    private function subselectServicesMidi(): Select
    {
        $where = new Where();
        $where->literal('moment = 2')->equalTo('millesime', $this->millesime);
        return $this->sql->select(
            $this->db_manager->getCanonicName('affectations', 'table'))
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'midi' => new Literal("GROUP_CONCAT(DISTINCT ligne1Id SEPARATOR '-')")
            ])
            ->where($where)
            ->group([
            'millesime',
            'eleveId',
            'trajet'
        ]);
    }

    private function subselectServicesSoir(): Select
    {
        $where = new Where();
        $where->literal('moment = 3')
            ->equalTo('millesime', $this->millesime)
            ->literal('jours & 11 = 11');
        return $this->sql->select(
            $this->db_manager->getCanonicName('affectations', 'table'))
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'soir' => new Literal("GROUP_CONCAT(DISTINCT ligne1Id SEPARATOR '-')")
            ])
            ->where($where)
            ->group([
            'millesime',
            'eleveId',
            'trajet'
        ]);
    }

    private function subselectServicesMercrediSoir(): Select
    {
        $where = new Where();
        $where->literal('moment = 3')
            ->equalTo('millesime', $this->millesime)
            ->literal('jours & 4 = 4');
        return $this->sql->select(
            $this->db_manager->getCanonicName('affectations', 'table'))
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'msoir' => new Literal("GROUP_CONCAT(DISTINCT ligne1Id SEPARATOR '-')")
            ])
            ->where($where)
            ->group([
            'millesime',
            'eleveId',
            'trajet'
        ]);
    }

    // ===============================================================
    private function xSqlBeneficiaire(): string
    {
        $sql1 = 'CONCAT_WS(" ",ele.nom,ele.prenom)';
        $sql2 = 'CONCAT_WS(" ",inv.nom,inv.prenom)';
        return "IF(ISNULL(inv.nom),$sql1,$sql2)";
    }

    private function xSqlResponsable(): string
    {
        $sql2 = 'IF(inv.organismeId > 0,org.organisme,inv.chez)';
        $sql1 = "IF(inv.responsableId > 0,res.responsable,$sql2)";
        return "IF(inv.eleveId > 0,ele.responsable1,$sql1)";
    }

    private function xSqlResponsable2(): string
    {
        $sql = 'IF(ele.responsable2Id IS NOT NULL,ele.responsable2,NULL)';
        return "IF(inv.eleveId > 0,$sql,NULL)";
    }

    private function xSqlAdresseL(int $n): string
    {
        $sql2 = "IF(inv.organismeId > 0,org.adresseL$n,inv.adresseL$n)";
        $sql1 = "IF(inv.responsableId > 0,res.adresseL$n,$sql2)";
        return "IF(inv.eleveId > 0,ele.adresseL$n" . "R1,$sql1)";
    }

    private function xSqlAdresseR2L(int $n): string
    {
        $sql = "IF(ele.responsable2Id IS NOT NULL, ele.adresseL$n" . "R2,NULL)";
        return "IF(inv.eleveId > 0,$sql,NULL)";
    }

    private function xSqlCodePostal(): string
    {
        $sql2 = 'IF(inv.organismeId > 0,org.codePostal,inv.codePostal)';
        $sql1 = "IF(inv.responsableId > 0,res.codePostal,$sql2)";
        return "IF(inv.eleveId > 0,ele.codePostalR1,$sql1)";
    }

    private function xSqlCodePostalR2(): string
    {
        $sql = 'IF(ele.responsable2Id IS NOT NULL,ele.codePostalR2,NULL)';
        return "IF(inv.eleveId > 0,$sql,NULL)";
    }

    private function xSqlCommune(): string
    {
        $sql2 = 'IF(inv.organismeId > 0,org.lacommune,cominv.lacommune)';
        $sql1 = "IF(inv.responsableId > 0,res.lacommune,$sql2)";
        return "IF(inv.eleveId > 0,ele.lacommuneR1,$sql1)";
    }

    private function xSqlCommuneR2(): string
    {
        $sql = 'IF(ele.responsable2Id IS NOT NULL,ele.lacommuneR2,NULL)';
        return "IF(inv.eleveId > 0,$sql,NULL)";
    }

    private function xSqlStation(): string
    {
        return 'IF(inv.eleveId > 0 AND ISNULL(inv.stationId),ele.stationR1,sta.station)';
    }

    private function xSqlStationR2(): string
    {
        $sql = 'IF(ele.responsable2Id IS NOT NULL,ele.stationR2,NULL)';
        return "IF(inv.eleveId > 0,$sql,NULL)";
    }

    private function xSqlEtablissement(): string
    {
        return 'IF(inv.eleveId > 0,ele.etablissement,eta.etablissement)';
    }

    private function xSqlServicesMatin(): string
    {
        return 'IF(inv.eleveId > 0 AND ISNULL(inv.stationId),ele.matin,inv.servicesMatin)';
    }

    private function xSqlServicesMidi(): string
    {
        return 'IF(inv.eleveId > 0 AND ISNULL(inv.stationId),ele.midi,inv.servicesMidi)';
    }

    private function xSqlServicesSoir(): string
    {
        return 'IF(inv.eleveId > 0 AND ISNULL(inv.stationId),ele.soir,inv.servicesSoir)';
    }

    private function xSqlServicesMerSoir(): string
    {
        return 'IF(inv.eleveId > 0 AND ISNULL(inv.stationId),ele.msoir,inv.servicesMerSoir)';
    }

    public function getInvitePourPass(int $inviteId): array
    {
        $error_msg = 'Ce bénéficiaire n\'a pas été trouvé !';
        try {
            $oInvite = $this->db_manager->get('Sbm\Db\Table\Invites')->getRecord(
                $inviteId);
        } catch (\Exception $e) {
            throw new \SbmCommun\Model\Db\Exception\RuntimeException($error_msg);
        }
        $eleveId = $oInvite->eleveId;
        $responsableId = $oInvite->responsableId;
        $organismeId = $oInvite->organismeId;
        $stationId = $oInvite->stationId;
        if (! empty($eleveId)) {
            if (! empty($stationId)) {
                $select = $this->selectCas1($inviteId);
            } else {
                $select = $this->selectCas2($inviteId, $eleveId);
            }
        } elseif (! empty($responsableId)) {
            $select = $this->selectCas3($inviteId);
        } elseif (! empty($organismeId)) {
            $select = $this->selectCas4($inviteId);
        } else {
            $select = $this->selectCas5($inviteId);
        }
        $resultset = $this->renderResult($select);
        if ($resultset->count() > 0) {
            $aoInvite = $resultset->current();
            $aoInvite->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            if (isset($aoInvite->mersoir)) {
                $mersoir = $aoInvite->mersoir;
            } else {
                $mersoir = null;
            }
            if (empty($mersoir)) {
                $soir = $aoInvite->soir;
            } else {
                $soir = sprintf('%s - Me soir : %s', $aoInvite->soir, $mersoir);
            }
            if (isset($aoInvite->chez)) {
                $chez = $aoInvite->chez;
            } else {
                $chez = null;
            }
            return [
                'du' => DateLib::formatDateFromMysql($aoInvite->du),
                'au' => DateLib::formatDateFromMysql($aoInvite->au),
                'chez' => $chez,
                'beneficiaire' => $aoInvite->beneficiaire,
                'responsable' => $aoInvite->responsable,
                'adresseL1' => $aoInvite->adresseL1,
                'adresseL2' => $aoInvite->adresseL2,
                'adresseCommune' => $aoInvite->adresseCommune,
                'ecole' => $aoInvite->ecole,
                'station' => $aoInvite->station,
                'matin' => $aoInvite->matin,
                'midi' => $aoInvite->midi,
                'soir' => $soir
            ];
        } else {
            throw new \SbmCommun\Model\Db\Exception\RuntimeException($error_msg);
        }
    }

    private function selectCas1(int $inviteId): Select
    {
        $columns = [
            'du' => 'dateDebut',
            'au' => 'dateFin',
            'chez' => 'chez',
            'beneficiaire' => new Literal('CONCAT_WS(" ",ele.nom, ele.prenom)'),
            'responsable' => new Literal('CONCAT_WS(" ", res.titre, res.nom, res.prenom)'),
            'adresseL1' => 'adresseL1',
            'adresseL2' => 'adresseL2',
            'adresseCommune' => new Literal('CONCAT_WS(" ",inv.codePostal,cominv.alias)'),
            'ecole' => new Literal('CONCAT_WS(" - ",eta.nom, cometa.alias)'),
            'station' => new Literal('CONCAT(sta.nom, " (", comsta.alias, ")")'),
            'matin' => 'servicesMatin',
            'midi' => 'servicesMidi',
            'soir' => 'servicesSoir',
            'mersoir' => 'servicesMerSoir'
        ];
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->equalTo('inviteId', $inviteId);
        return $this->sql->select()
            ->columns($columns)
            ->from([
            'inv' => $this->db_manager->getCanonicName('invites', 'table')
        ])
            ->join([
            'cominv' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cominv.communeId = inv.communeId', [])
            ->join([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ], 'ele.eleveId = inv.eleveId', [])
            ->join(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'res.responsableId = ele.responsable1Id', [])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'eta.etablissementId = inv.etablissementId', [])
            ->join([
            'cometa' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cometa.communeId = eta.communeId', [])
            ->join([
            'sta' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'sta.stationId = inv.stationId', [])
            ->join([
            'comsta' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'comsta.communeId = sta.communeId', [])
            ->where($where);
    }

    private function selectCas2(int $inviteId, int $eleveId): Select
    {
        $columns = [
            'du' => 'dateDebut',
            'au' => 'dateFin',
            'beneficiaire' => new Literal('CONCAT_WS(" ",inv.nom, inv.prenom)')
        ];
        $where = new Where();
        $where->equalTo('inviteId', $inviteId);
        return $this->sql->select()
            ->columns($columns)
            ->from([
            'inv' => $this->db_manager->getCanonicName('invites', 'table')
        ])
            ->join(
            [
                'ele' => $this->db_manager->get('Sbm\Db\Query\ElevesDivers')
                    ->selectDataForDuplicata($this->millesime, $eleveId)
            ], 'ele.millesime = inv.millesime AND ele.eleveId = inv.eleveId',
            [
                'chez' => 'eleve',
                'responsable',
                'adresseL1',
                'adresseL2',
                'adresseCommune',
                'ecole',
                'station',
                'matin',
                'midi',
                'soir'
            ])
            ->where($where);
    }

    private function selectCas3(int $inviteId): Select
    {
        $columns = [
            'du' => 'dateDebut',
            'au' => 'dateFin',
            'chez' => new Literal('CONCAT_WS(" ", res.titre, res.nom, res.prenom)'),
            'beneficiaire' => new Literal('CONCAT_WS(" ",inv.nom, inv.prenom)'),
            'responsable' => new Literal('CONCAT_WS(" ", res.titre, res.nom, res.prenom)'),
            'adresseCommune' => new Literal('CONCAT_WS(" ",res.codePostal,comres.alias)'),
            'ecole' => new Literal('CONCAT_WS(" - ",eta.nom, cometa.alias)'),
            'station' => new Literal('CONCAT(sta.nom, " (", comsta.alias, ")")'),
            'matin' => 'servicesMatin',
            'midi' => 'servicesMidi',
            'soir' => 'servicesSoir',
            'mersoir' => 'servicesMerSoir'
        ];
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->equalTo('inviteId', $inviteId);
        return $this->sql->select()
            ->columns($columns)
            ->from([
            'inv' => $this->db_manager->getCanonicName('invites', 'table')
        ])
            ->join(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'res.responsableId = inv.responsableId',
            [
                'adresseL1' => 'adresseL1',
                'adresseL2' => 'adresseL2'
            ])
            ->join([
            'comres' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'res.communeId = comres.communeId', [])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'eta.etablissementId = inv.etablissementId', [])
            ->join([
            'cometa' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cometa.communeId = eta.communeId', [])
            ->join([
            'sta' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'sta.stationId = inv.stationId', [])
            ->join([
            'comsta' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'comsta.communeId = sta.communeId', [])
            ->where($where);
    }

    private function selectCas4(int $inviteId): Select
    {
        $columns = [
            'du' => 'dateDebut',
            'au' => 'dateFin',
            // 'chez' => new Literal('CONCAT_WS(" ", res.titre, res.nom, res.prenom)'),
            'beneficiaire' => new Literal('CONCAT_WS(" ",inv.nom, inv.prenom)'),
            'ecole' => new Literal('CONCAT_WS(" - ",eta.nom, cometa.alias)'),
            'station' => new Literal('CONCAT(sta.nom, " (", comsta.alias, ")")'),
            'matin' => 'servicesMatin',
            'midi' => 'servicesMidi',
            'soir' => 'servicesSoir',
            'mersoir' => 'servicesMerSoir'
        ];
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->equalTo('inviteId', $inviteId);
        return $this->sql->select()
            ->columns($columns)
            ->from([
            'inv' => $this->db_manager->getCanonicName('invites', 'table')
        ])
            ->join([
            'org' => $this->db_manager->getCanonicName('organismes', 'table')
        ], 'org.organismeId = inv.organismeId',
            [
                'responsable' => 'nom',
                'adresseL1' => 'adresse1',
                'adresseL2' => 'adresse2',
                'adresse2'
            ])
            ->join([
            'comorg' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'comorg.communeId = org.communeId',
            [
                'adresseCommune' => new literal(
                    'CONCAT_WS(" ",org.codePostal,comorg.alias)')
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'eta.etablissementId = inv.etablissementId', [])
            ->join([
            'cometa' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cometa.communeId = eta.communeId', [])
            ->join([
            'sta' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'sta.stationId = inv.stationId', [])
            ->join([
            'comsta' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'comsta.communeId = sta.communeId', [])
            ->where($where);
    }

    private function selectCas5(int $inviteId): Select
    {
        $columns = [
            'du' => 'dateDebut',
            'au' => 'dateFin',
            'chez' => 'chez',
            'beneficiaire' => new Literal('CONCAT_WS(" ",inv.nom, inv.prenom)'),
            'responsable' => 'chez',
            'adresseL1' => 'adresseL1',
            'adresseL2' => 'adresseL2',
            'adresseCommune' => new Literal('CONCAT_WS(" ",inv.codePostal,cominv.alias)'),
            'ecole' => new Literal('CONCAT_WS(" - ",eta.nom, cometa.alias)'),
            'station' => new Literal('CONCAT(sta.nom, " (", comsta.alias, ")")'),
            'matin' => 'servicesMatin',
            'midi' => 'servicesMidi',
            'soir' => 'servicesSoir',
            'mersoir' => 'servicesMerSoir'
        ];
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->equalTo('inviteId', $inviteId);
        return $this->sql->select()
            ->columns($columns)
            ->from([
            'inv' => $this->db_manager->getCanonicName('invites', 'table')
        ])
            ->join([
            'cominv' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cominv.communeId = inv.communeId', [])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'eta.etablissementId = inv.etablissementId', [])
            ->join([
            'cometa' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cometa.communeId = eta.communeId', [])
            ->join([
            'sta' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'sta.stationId = inv.stationId', [])
            ->join([
            'comsta' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'comsta.communeId = sta.communeId', [])
            ->where($where);
    }
}