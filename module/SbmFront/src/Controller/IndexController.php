<?php
/**
 * Controleur principal de l'application
 *
 * Compatible ZF3
 *
 * @project sbm
 * @package module/SbmFront
 * @filesource src/SbmFront/Controller/IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 juin 2019
 * @version 2019-2.5.0
 */
namespace SbmFront\Controller;

use SbmBase\Model\Session;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;

/**
 * Dispose des propriétés provenant de IndexControllerFactory : - theme (objet
 * \SbmInstallation\Model\Theme) - db_manager (objet
 * \SbmCommun\Model\Db\Service\DbManager) - login_form (objet \SbmFront\Form\Login) -
 * client - accueil (url de l'organisateur - voir config/autoload/sbm.local.php) -
 * url_ts_region (url du site d'inscription de la région - voir
 * config/autoload/sbm.local.php)
 *
 * @author admin
 */
class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $form = $this->login_form;
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('login', [
                'action' => 'login'
            ]));
        $tCalendar = $this->db_manager->get('Sbm\Db\System\Calendar');
        $view = new ViewModel(
            [
                'form' => $form->prepare(),
                'communes' => $this->db_manager->get('Sbm\Db\Table\Communes'),
                'calendar' => $tCalendar,
                'theme' => $this->theme,
                'client' => $this->client,
                'accueil' => $this->accueil,
                'millesime' => Session::get('millesime'),
                'as' => Session::get('as')['libelle'],
                'dateDebutAs' => Session::get('as')['dateDebut'],
                'url_ts_organisateur' => $this->url_ts_organisateur,
                'url_ts_region' => $this->url_ts_region
            ]);
        switch ($tCalendar->getEtatDuSite()['etat']) {
            case $tCalendar::ETAT_AVANT:
                $view->setTemplate('sbm-front/index/index-avant.phtml');
                break;
            case $tCalendar::ETAT_PENDANT:
                $view->setTemplate('sbm-front/index/index-pendant.phtml');
                break;
            case $tCalendar::ETAT_APRES:
                $view->setTemplate('sbm-front/index/index-apres.phtml');
                break;
            default:
                $view->setTemplate('sbm-front/index/index-ferme.phtml');
                break;
        }
        return $view;
    }

    public function horsZoneAction()
    {
        return new ViewModel(
            [
                'accueil' => $this->accueil,
                'client' => $this->client,
                'commune' => $this->params('id')
            ]);
    }

    public function testAffectationAction()
    {
        $cr = [];
        $chercheTrajets = $this->db_manager->get('Sbm\ChercheTrajet');
        $televes = $this->db_manager->get('Sbm\Db\Table\Eleves');
        $tscolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $taffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
        $rowset = $tscolarites->fetchAll((new Where())->notEqualTo('stationIdR1', 0));
        foreach ($rowset as $row) {
            $responsableId = $televes->getRecord($row->eleveId)->responsable1Id;
            for ($moment = 1; $moment <= 3; $moment ++) {

                $chercheTrajets->setEtablissementId($row->etablissementId);
                $chercheTrajets->setStationId($row->stationIdR1);
                for ($i = 1, $trajetsPossibles = []; ! count($trajetsPossibles) && $i <= 4; $i ++) {
                    $trajetsPossibles = $chercheTrajets->getTrajets($moment, $i);
                }
                $i --;
                // DEBUG
                $cr[$row->eleveId][$moment]['nb_circuits'] = $i;
                $cr[$row->eleveId][$moment]['nb_trajets'] = count($trajetsPossibles);

                if (count($trajetsPossibles)) {
                    // @TODO: contrôle des places disponibles
                    $trajet = current($trajetsPossibles);
                    // DEBUG
                    $cr[$row->eleveId][$moment]['trajet'] = $trajet;
                    $oAffectation = $taffectations->getObjData();
                    $oAffectation->millesime = $row->millesime;
                    $oAffectation->eleveId = $row->eleveId;
                    $oAffectation->trajet = 1;
                    $oAffectation->moment = $moment;
                    $oAffectation->responsableId = $responsableId;
                    for ($j = 1; $j <= $i; $j ++) {
                        $oAffectation->jours = $trajet["semaine_$j"];
                        $oAffectation->correspondance = $j;
                        $oAffectation->station1Id = $trajet["station1Id_$j"];
                        $oAffectation->ligne1Id = $trajet["ligne1Id_$j"];
                        $oAffectation->sensligne1 = $trajet["sensligne1_$j"];
                        $oAffectation->ordreligne1 = $trajet["ordreligne1_$j"];
                        $oAffectation->station2Id = $trajet["station2Id_$j"];
                        // DEBUG
                        $cr[$row->eleveId][$moment]['affectation'][$j] = $oAffectation->getArrayCopy();
                        // @TODO: enregistre l'affectation
                        $taffectations->saveRecord($oAffectation);
                    }
                }
            }
        }

        $cr[] = 'Terminé';
        // dump de l'objet 'obj'
        return new ViewModel([
            'obj' => $cr
        ]);
    }

    /**
     * Méthode prête pour donner un numéro aux élèves. La renommer testAction pour qu'elle
     * fonctionne. Si elle est trop longue, rajouter des ->limit(xxx) au select. Au
     * préalable, vider la table sbm_t_eleves et ré-initialiser AUTO_INCREMENT par : ALTER
     * TABLE `sbm_t_responsables` AUTO_INCREMENT = 1;
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function testAction()
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
            ->where((new Where())->equalTo('responsable1Id', 2070) )
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
        // dump de l'objet 'obj'
        return new ViewModel([
            'obj' => $error_msg
        ]);
    }
}