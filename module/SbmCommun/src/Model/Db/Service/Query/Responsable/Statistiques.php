<?php
/**
 * Requêtes pour les statistiques concernant les responsables
 * (classe déclarée dans mocule.config.php sous l'alias 'Sbm\Statistiques\Eleve')
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Responsable
 * @filesource Statistiques.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Responsable;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class Statistiques extends AbstractQuery
{

    protected function init()
    {
    }

    /**
     * Renvoie le nombre de responsables enregistrés
     *
     * @return array
     */
    public function getNbEnregistres()
    {
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('responsables', 'table'))
            ->columns([
            'effectif' => new Expression('count(responsableId)')
        ]);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie le nombre de responsables ayant inscrit des enfants cette année
     *
     * @return array
     */
    public function getNbAvecEnfant()
    {
        $where1 = new Where();
        $where1->equalTo('millesime', $this->millesime);
        $select1 = $this->sql->select();
        $select1->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->columns([
            'responsableId' => 'responsable1Id'
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'sco.eleveId = ele.eleveId', [])
            ->where($where1);
        $where2 = new Where();
        $where2->equalTo('millesime', $this->millesime)->isNotNull('responsable2Id');
        $select2 = $this->sql->select();
        $select2->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->columns([
            'responsableId' => 'responsable2Id'
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'sco.eleveId = ele.eleveId', [])
            ->where($where2);
        $select1->combine($select2);
        $select3 = $this->sql->select();
        $select3->from([
            'id' => $select1
        ]);

        $where = new Where();
        $where->in('responsableId', $select3);
        $select = $this->sql->select();
        $select->from(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns([
            'effectif' => new Expression('count(responsableId)')
        ])
            ->where($where);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie le nombre de responsables enregistrés, sans enfant cette année
     *
     * @return array
     */
    public function getNbSansEnfant()
    {
        $where1 = new Where();
        $where1->equalTo('millesime', $this->millesime);
        $select1 = $this->sql->select();
        $select1->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'sco.eleveId = ele.eleveId', [])
            ->where($where1);

        $where2 = new Where();
        $where2->isNull('eleveId');
        $select2 = $this->sql->select();
        $select2->from(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns([
            'effectif' => new Expression('count(responsableId)')
        ])
            ->join([
            'ele' => $select1
        ],
            'ele.responsable1Id = res.responsableId Or ele.responsable2Id = res.responsableId',
            [], $select2::JOIN_LEFT)
            ->where($where2);
        return iterator_to_array($this->renderResult($select2));
    }

    /**
     * Renvoie le nombre de responsables ayant des enfants inscrits et résidant dans une
     * commune non membre
     *
     * @return array
     */
    public function getNbCommuneNonMembre()
    {
        $where1 = new Where();
        $where1->equalTo('millesime', $this->millesime);
        $select1 = $this->sql->select();
        $select1->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->columns([
            'responsableId' => 'responsable1Id'
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'sco.eleveId = ele.eleveId', [])
            ->where($where1);
        $where2 = new Where();
        $where2->equalTo('millesime', $this->millesime)->isNotNull('responsable2Id');
        $select2 = $this->sql->select();
        $select2->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->columns([
            'responsableId' => 'responsable2Id'
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'sco.eleveId = ele.eleveId', [])
            ->where($where2);
        $select1->combine($select2);
        $select3 = $this->sql->select();
        $select3->from([
            'id' => $select1
        ]);

        $where = new Where();
        $where->in('responsableId', $select3)->literal('com.membre = 0');
        $select = $this->sql->select();
        $select->from(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns([
            'effectif' => new Expression('count(responsableId)')
        ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'com.communeId = res.communeId', [])
            ->where($where);
        return iterator_to_array($this->renderResult($select));
    }

    /**
     * Renvoie le nombre de responsables ayant des enfants inscrits et ayant déménagé
     *
     * @return array
     */
    public function getNbDemenagement()
    {
        $where1 = new Where();
        $where1->equalTo('millesime', $this->millesime);
        $select1 = $this->sql->select();
        $select1->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->columns([
            'responsableId' => 'responsable1Id'
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'sco.eleveId = ele.eleveId', [])
            ->where($where1);
        $where2 = new Where();
        $where2->equalTo('millesime', $this->millesime)->isNotNull('responsable2Id');
        $select2 = $this->sql->select();
        $select2->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->columns([
            'responsableId' => 'responsable2Id'
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'sco.eleveId = ele.eleveId', [])
            ->where($where2);
        $select1->combine($select2);
        $select3 = $this->sql->select();
        $select3->from([
            'id' => $select1
        ]);

        $where = new Where();
        $where->in('responsableId', $select3)->literal('demenagement = 1');
        $select = $this->sql->select();
        $select->from(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns([
            'effectif' => new Expression('count(responsableId)')
        ])
            ->where($where);
        return iterator_to_array($this->renderResult($select));
    }
}