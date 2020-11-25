<?php
/**
 * Controlleur du portail des établissements
 *
 * @project sbm
 * @package SbmPortail/src/Controller/Service
 * @filesource EtablissementController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 nov. 2020
 * @version 2020-2.6.1
 */
namespace SbmPortail\Controller;

use SbmAuthentification\Model\CategoriesInterface;
use SbmPortail\Model\User\Etablissement as UserFeatures;
use SbmPortail\Model\Db\ObjectData\CriteresEtablissement as CriteresObject;
use SbmPortail\Form\CriteresEtablissementForm as CriteresForm;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmGestion\Model\Db\Filtre\Eleve\Filtre as FiltreEleve;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

class EtablissementController extends AbstractActionController
{
    use \SbmCommun\Model\Traits\DebugTrait;

    private $sansimpayes = true;

    /**
     * Page d'accueil du portail des établissements
     *
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function IndexAction()
    {
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        return new ViewModel(
            [
                'data' => $userFeatures->tableauStatistique(),
                'etablissements' => $userFeatures->listeDesNoms()
            ]);
    }

    public function carteEtablissementsAction()
    {
        ;
    }

    public function carteStationsAction()
    {
        ;
    }

    public function lignesAction()
    {
        ;
    }

    public function lignesPdfAction()
    {
        ;
    }

    public function lignesDownloadAction()
    {
        ;
    }

    public function servicesAction()
    {
        $millesime = Session::get('millesime');
        $as = $millesime . '-' . ($millesime + 1);
        return new ViewModel(
            [
                'paginator' => null,
                'count_per_page' => $this->getPaginatorCountPerPage('nb_services', 15),
                'page' => $this->params('page', 1),
                'as' => $as,
                'ligneId' => null,
                'effectifServices' => null,
                'criteres_form' => null
            ]);
    }

    public function servicesPdfAction()
    {
        ;
    }

    public function servicesDownloadAction()
    {
        ;
    }

    public function serviceGroupAction()
    {
        ;
    }

    public function serviceGroupPdfAction()
    {
        ;
    }

    public function circuitAction()
    {
        ;
    }

    public function circuitPdfAction()
    {
        ;
    }

    public function circuitDownloadAction()
    {
        ;
    }

    public function circuitGroupAction()
    {
        ;
    }

    public function circuitGroupPdfAction()
    {
        ;
    }

    public function elevesAction()
    {
        $result = $this->prepareListeEleves('eleves',
            function () {
                return $this->redirect()
                    ->toRoute('sbmportail/etablissement', [
                    'action' => 'index'
                ]);
            });
        if ($result instanceof Response) {
            return $result;
        }
        $paginator = $result['userFeatures']->getQuery()->paginatorEleves(
            $result['criteres_obj']->getWhere(), [
                'ele.nom',
                'ele.prenom'
            ]);
        return new ViewModel(
            [
                'paginator' => $paginator,
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
                'page' => $this->params('page', 1),
                'etablissement' => $result['userFeatures']->listeDesNoms(),
                'nbEtablissements' => $result['userFeatures']->getNbEtablissements(),
                'criteres_form' => $result['criteres_form']
            ]);
    }

    public function elevesPdfAction()
    {
        ;
    }

    public function elevesDownloadAction()
    {
        ;
    }

    private function prepareListeEleves(string $sessionNameSpace, callable $fncBack)
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false || StdLib::getParam('op', $prg, '') == 'retour') {
            $sbm_isPost = false;
            $args = Session::get('post', [],
                $this->getSessionNamespace($sessionNameSpace));
        } else {
            if (array_key_exists('cancel', $prg)) {
                return $fncBack();
            }
            $args = array_merge(
                Session::get('post', [], $this->getSessionNamespace($sessionNameSpace)),
                $prg);
            $sbm_isPost = true;
            Session::set('post', $args, $this->getSessionNamespace($sessionNameSpace));
        }
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        // formulaire des critères de recherche
        $criteres_form = new CriteresForm($userFeatures->getArrayEtablissements());
        $criteres_form->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->visibles())
            ->setValueOptions('classeId',
            $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout())
            ->setValueOptions('serviceId',
            $userFeatures->getQuery()
                ->listeServicesForSelect())
            ->setValueOptions('stationId',
            $userFeatures->getQuery()
                ->listeStationsForSelect());
        // CritereObject est un objectData qui contient la méthode getWhere() adhoc
        $criteres_obj = new CriteresObject($criteres_form->getElementNames(),
            $this->sansimpayes);
        if ($sbm_isPost) {
            $criteres_form->setData($args);
            if ($criteres_form->isValid()) {
                $criteres_obj->exchangeArray($criteres_form->getData());
            }
        }
        if (! $criteres_form->hasValidated() && ! empty($args)) {
            $criteres_obj->exchangeArray($args);
            $criteres_form->setData($criteres_obj->getArrayCopy());
        }
        return [
            'post' => $args,
            'userFeatures' => $userFeatures,
            'criteres_form' => $criteres_form,
            'criteres_obj' => $criteres_obj
        ];
    }
}