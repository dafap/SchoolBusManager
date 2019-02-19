<?php
/**
 * Aide de vue permettant de préparer le tableau 'menu des rapports'
 *
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmGestion/Model/View/Helper
 * @filesource MenuRapports.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\View\Helper;

use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;

class MenuRapports extends AbstractHelper implements FactoryInterface
{

    protected $db_manager;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db_manager = $serviceLocator->getServiceLocator()->get('Sbm\DbManager');
        return $this;
    }

    /**
     * Renvoie un tableau dans lequel on trouve le ou les liens à afficher dans le menu pour
     * l'option 'rapports' et le tableau $hiddens du formulaire de la barre de menu.
     *
     * Le tableau renvoyé a 2 clés 'hiddens' et 'content'.<ul>
     * <li> 'hiddens' => tableau des hiddens reçu en paramètre auquel vient s'ajouter
     * 'documentId' => libelle lorsqu'il n'y a qu'une option dans le menu</li>
     * <li> 'content' => tableau qui peut prendre 2 formes, selon qu'il y a une ou plusieurs
     * options dans le menu.</li></ul>
     *
     * @param string $route
     *            url de la page où l'on doit afficher le menu
     * @param string $formaction
     *            action à appeler pour éditer le document
     * @param string $class
     *            classe css du bouton à afficher dans la barre de menu
     * @param string $value
     *            libellé du bouton de la barre de menu (peut être vide si on utilise une classe
     *            fam-fam)
     * @param array $hiddens
     *            hiddens du formulaire qui sera complété par le libellé du menu s'il n'y a qu'un
     *            document proposé
     *            
     * @return array Tableau de la forme ['hiddens' => [...], 'content' => [...]]
     */
    public function __invoke($route, $formaction, $class, $value = '', $hiddens = [])
    {
        $where = new Where();
        $where->equalTo('route', $route);
        $resultset = $this->db_manager->get('Sbm\Db\System\DocAffectations')->fetchAll(
            $where, 'ordinal_position');
        $content = [];
        foreach ($resultset as $affectation) {
            $documentId = sprintf('documentId[%d]', $affectation->ordinal_position);
            $content[$documentId] = [
                'value' => $affectation->libelle,
                'formaction' => $formaction . '/id/' . $affectation->docaffectationId,
                'formtarget' => '_blank'
            ];
        }
        // die(var_dump($content));
        if (count($content) == 1) {
            $hiddens['documentId'] = $affectation->libelle;
            return [
                'hiddens' => $hiddens,
                'content' => [
                    'class' => $class,
                    'formaction' => $formaction . '/id/' . $affectation->docaffectationId,
                    'value' => $value,
                    'title' => $affectation->libelle,
                    'formtarget' => '_blank'
                ]
            ];
        } else {
            return [
                'hiddens' => $hiddens,
                'content' => [
                    'label' => true,
                    'class' => $class,
                    'menu' => $content,
                    'title' => empty($content) ? 'Pas de document disponible' : ''
                ]
            ];
        }
    }
}