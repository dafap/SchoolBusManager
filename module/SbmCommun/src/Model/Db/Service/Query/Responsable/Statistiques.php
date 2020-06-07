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
 * @date 5 juin 2020
 * @version 2020-2.6.0
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
        return iterator_to_array($this->renderResult($this->selectNbEnregistres()));
    }

    protected function selectNbEnregistres()
    {
        return $this->sql->select()
            ->from($this->db_manager->getCanonicName('responsables', 'table'))
            ->columns([
            'effectif' => new Expression('count(responsableId)')
        ]);
    }

    /**
     * Renvoie le nombre de responsables ayant inscrit des enfants cette année
     *
     * @return array
     */
    public function getNbAvecEnfant()
    {
        return iterator_to_array($this->renderResult($this->selectNbAvecEnfant()));
    }

    protected function selectNbAvecEnfant()
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
        return $this->sql->select()
            ->from(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns([
            'effectif' => new Expression('count(responsableId)')
        ])
            ->where($where);
    }

    /**
     * Renvoie le nombre de responsables enregistrés, sans enfant cette année
     *
     * @return array
     */
    public function getNbSansEnfant()
    {
        return iterator_to_array($this->renderResult($this->selectNbSansEnfant()));
    }

    protected function selectNbSansEnfant()
    {
        $where1 = new Where();
        $where1->equalTo('millesime', $this->millesime);
        $select = $this->sql->select();
        $select->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'sco.eleveId = ele.eleveId', [])
            ->where($where1);

        $where2 = new Where();
        $where2->isNull('eleveId');
        return $this->sql->select()
            ->from(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns([
            'effectif' => new Expression('count(responsableId)')
        ])
            ->join([
            'ele' => $select
        ],
            'ele.responsable1Id = res.responsableId Or ele.responsable2Id = res.responsableId',
            [], $select::JOIN_LEFT)
            ->where($where2);
    }

    /**
     * Renvoie le nombre de responsables ayant des enfants inscrits et résidant dans une
     * commune non membre
     *
     * @return array
     */
    public function getNbCommuneNonMembre()
    {
        return iterator_to_array($this->renderResult($this->selectNbCommuneNonMembre()));
    }

    protected function selectNbCommuneNonMembre()
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
        return $this->sql->select()
            ->from(
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
    }

    /**
     * Renvoie le nombre de responsables ayant des enfants inscrits et ayant déménagé
     *
     * @return array
     */
    public function getNbDemenagement()
    {
        return iterator_to_array($this->renderResult($this->selectNbDemenagement()));
    }

    protected function selectNbDemenagement()
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
        return $this->sql->select()
            ->from(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns([
            'effectif' => new Expression('count(responsableId)')
        ])
            ->where($where);
    }
}