<?php
/**
 * Tous mes tests
 *
 * @project sbm
 * @package SbmFront/src/Controller
 * @filesource TestController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 juin 2020
 * @version 2020-2.5.4
 */
namespace SbmFront\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;

class TestController extends AbstractActionController
{
    use \SbmCommun\Model\Traits\DebugTrait;

    /**
     *
     * @var int
     */
    private $millesime;

    /**
     *
     * @var \SbmAuthentification\Authentication\AuthenticationService
     */
    private $auth;

    private function initDebug()
    {
        $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/tmp'), 'debug-test.log');
        $this->millesime = Session::get('millesime');
        $this->auth = $this->authenticate->by('email');
    }

    public function indexAction()
    {
        $this->initDebug();
        $error_msg[] = $this->auth->getIdentity();
        $error_msg[] = $this->auth->getCategorieId();
        $error_msg[] = $this->auth->getUserId();
        $error_msg[] = 'Terminé';
        // dump et print_r de l'objet 'obj'
        $viewmodel = new ViewModel([
            'obj' => $error_msg,
            'form' => null
        ]);
        $viewmodel->setTemplate('sbm-front/test/test.phtml');
        return $viewmodel;
    }

    public function zonageAction()
    {
        $cr = [];
        // lecture de la table zonage et enregistrement dans la table sbm_t_zonage
        $tzonage = $this->db_manager->get('Sbm\Db\Table\Zonage');
        $rowset = $tzonage->fetchAll();
        $za = new \SbmCommun\Filter\ZoneAdresse();
        foreach ($rowset as $row) {
            $row->nomSA = strtoupper($za->filter($row->nom));
            $tzonage->saveRecord($row);
        }
        $cr[] = 'Terminé';
        $viewmodel = new ViewModel([
            'obj' => $cr,
            'form' => null
        ]);
        $viewmodel->setTemplate('sbm-front/test/test.phtml');
        return $viewmodel;
    }

    public function insererDesUsersAction()
    {
        $adapter = $this->db_manager->getDbAdapter();
        $sql = new \Zend\Db\Sql\Sql($adapter);
        $select = $sql->select()
            ->from('communaux')
            ->columns([
            'nom' => 'correspondant',
            'email' => 'mail'
        ]);
        // die($sql->getSqlStringForSqlObject($select));
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $tUsers = $this->db_manager->get('Sbm\Db\Table\Users');
        $oUser = $tUsers->getObjData();
        // pour chaque enregistrement, préparer un objet zonage et l'enregistrer
        $error_msg = [];
        foreach ($rowset as $row) {
            $parts = explode(' ', $row['nom']);
            $oUser->exchangeArray(
                [
                    'nom' => $parts[1],
                    'email' => $row['email'],
                    'prenom' => $parts[0],
                    'titre' => 'Mme',
                    'categorieId' => 100
                ]);
            $oUser->completeToCreate();
            try {
                $tUsers->saveRecord($oUser);
            } catch (\Exception $e) {
                $error_msg[] = [
                    $row['nom'],
                    $e->getMessage()
                ];
            }
        }
        $error_msg[] = 'Terminé';
        $viewmodel = new ViewModel([
            'obj' => $error_msg,
            'form' => null
        ]);
        $viewmodel->setTemplate('sbm-front/test/test.phtml');
        return $viewmodel;
    }

    /**
     * Méthode prête pour donner un numéro aux élèves. La renommer testAction pour qu'elle
     * fonctionne. Si elle est trop longue, rajouter des ->limit(xxx) au select. Au
     * préalable, vider la table sbm_t_eleves et ré-initialiser AUTO_INCREMENT par : ALTER
     * TABLE `sbm_t_responsables` AUTO_INCREMENT = 1;
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function numeroterLesElevesAction()
    {
        $adapter = $this->db_manager->getDbAdapter();
        $sql = new \Zend\Db\Sql\Sql($adapter);
        $select = $sql->select()
            ->from('eleves')
            ->columns(
            [
                'nom',
                'nomSA',
                'prenom',
                'prenomSA',
                'dateN',
                'sexe',
                'responsable1Id',
                'responsable2Id',
                'id_tra'
            ])
            ->where((new Where())->equalTo('responsable1Id', 2070))
            ->order('responsable1Id');
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $tEleves = $this->db_manager->get('Sbm\Db\Table\Eleves');
        $oEleve = $tEleves->getObjData();
        // pour chaque enregistrement, préparer un objet zonage et l'enregistrer
        $error_msg = [];
        foreach ($rowset as $row) {
            $oEleve->exchangeArray(
                [
                    'nom' => $row['nom'],
                    'nomSA' => $row['nomSA'],
                    'prenom' => $row['prenom'],
                    'prenomSA' => $row['prenomSA'],
                    'dateN' => $row['dateN'],
                    'sexe' => $row['sexe'],
                    'responsable1Id' => $row['responsable1Id'],
                    'responsable2Id' => $row['responsable2Id'],
                    'id_tra' => $row['id_tra']
                ]);
            try {
                $tEleves->saveRecord($oEleve);
            } catch (\Exception $e) {
                $error_msg[] = [
                    $row['nomSA'],
                    $e->getMessage()
                ];
            }
        }
        $error_msg[] = 'Terminé';
        $viewmodel = new ViewModel([
            'obj' => $error_msg,
            'form' => null
        ]);
        $viewmodel->setTemplate('sbm-front/test/test.phtml');
        return $viewmodel;
    }
}