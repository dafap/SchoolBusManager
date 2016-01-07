<?php
/**
 * Classe contenant les méthodes servant à préparer une simulation
 *
 * (doit être déclarée dans service_manager parmi les invokables)
 * 
 * @project sbm
 * @package SbmGestion/Model/Db/Factory/Simulation
 * @filesource Prepare.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 janv. 2016
 * @version 2016-1.7.1
 */
namespace SbmGestion\Model\Db\Factory\Simulation;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Select;

class Prepare implements ServiceLocatorAwareInterface
{

    /**
     * Service manager
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $sm;

    /**
     * Table des changements de classe
     *
     * @var int[]
     */
    private $classeIds = [];

    /**
     * Liste des eleveId à reprendre.
     * Cette liste est remplie par la méthode duplicateScolarites()
     *
     * @var int[]
     */
    private $eleveIds = [];

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->sm = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->sm;
    }

    private function setClasseIds()
    {
        if (empty($this->classeIds)) {
            $where = new Where();
            $where->isNotNull('suivantId');
            $resultset = $this->getServiceLocator()
                ->get('Sbm\Db\Table\Classes')
                ->fetchAll($where);
            foreach ($resultset as $classe) {
                $this->classeIds[$classe->classeId] = $classe->suivantId;
            }
        }
    }

    /**
     * Cette méthode duplique les circuits du millesime source dans le millésime cible 
     * à condition que le millésime cible soit vide.
     *
     *
     * @param int $source
     *            millésime source
     * @param int $cible
     *            millésime cible
     *            
     * @return \SbmGestion\Model\Db\Factory\Simulation\Prepare
     */
    public function duplicateCircuits($source, $cible)
    {
        $table = $this->getServiceLocator()->get('Sbm\Db\Table\Circuits');
        if ($table->isEmptyMillesime($cible)) {
            $resultset = $table->fetchAll(array(
                'millesime' => $source
            ));
            foreach ($resultset as $circuit) {
                $circuit->circuitId = null;
                $circuit->millesime = $cible;
                $table->saveRecord($circuit);
            }
        }
        return $this;
    }

    /**
     * Cette méthode est appelée par la méthode duplicateEleves().
     * Elle duplique la scolarité des élèves à reprendre à condition que les scolarites 
     * du millésime cible soit vide.
     *
     * @param int $source
     *            millésime source
     * @param int $cible
     *            millésime cible
     */
    private function duplicateScolarites($source, $cible)
    {
        $table = $this->getServiceLocator()->get('Sbm\Db\Table\Scolarites');
        if ($table->isEmptyMillesime($cible)) {
            $this->eleveIds = [];
            $this->setClasseIds();
            $where = new Where();
            $where->equalTo('millesime', $source)->in('classeId', array_keys($this->classeIds));
            $resultset = $table->fetchAll($where);
            foreach ($resultset as $scolarite) {
                $scolarite->millesime = $cible;
                $scolarite->classeId = $this->classeIds[$scolarite->classeId];
                $this->eleveIds[] = $scolarite->eleveId;
                $table->saveRecord($scolarite);
            }
        }
    }

    /**
     * Cette méthode cette méthode duplique les informations dans les tables scolarites et affectations
     * à condition que les affectations du millésime cible soit vide.
     *
     * @param int $source
     *            millésime source
     * @param int $cible
     *            millésime cible
     *            
     * @return \SbmGestion\Model\Db\Factory\Simulation\Prepare
     */
    public function duplicateEleves($source, $cible)
    {
        $table = $this->getServiceLocator()->get('Sbm\Db\Table\Affectations');
        if ($table->isEmptyMillesime($cible)) {
            $this->duplicateScolarites($source, $cible);
            $where = new Where();
            $where->equalTo('millesime', $source)->in('eleveId', $this->eleveIds);
            $resultset = $table->fetchAll($where);
            foreach ($resultset as $affectation) {
                $affectation->millesime = $cible;
                $table->saveRecord($affectation);
            }
        }
        return $this;
    }
}