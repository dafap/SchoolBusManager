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
 * @date 18 août 2015
 * @version 2015-1
 */
namespace SbmGestion\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Where;
use DafapSession\Model\Session;

class MenuRapports extends AbstractHelper implements FactoryInterface
{

    protected $db_manager;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db_manager = $serviceLocator->getServiceLocator()->get('Sbm\DbManager');
        return $this;
    }

    /**
     * Renvoie le texte à afficher dans le menu pour l'option 'rapports' et le tableau $hiddens du formulaire de la barre de menu
     *
     * @param string $route
     *            url de la page où l'on doit afficher le menu
     * @param string $formaction
     *            action à appeler pour éditer le document
     * @param string $class
     *            classe css du bouton à afficher dans la barre de menu
     * @param string $value
     *            libellé du bouton de la barre de menu (peut être vide si on utilise une classe fam-fam)
     * @param array $hiddens
     *            hiddens du formulaire qui sera complété par le libellé du menu s'il n'y a qu'un document proposé
     *            
     * @return string
     */
    public function __invoke($route, $formaction, $class, $value = '', $hiddens = array())
    {
        $where = new Where();
        $where->equalTo('route', $route);
        $resultset = $this->db_manager->get('Sbm\Db\System\DocAffectations')->fetchAll($where, 'ordinal_position');
        $content = array();
        foreach ($resultset as $affectation) {
            $documentId = sprintf('documentId[%d]', $affectation->ordinal_position);
            $content[$documentId] = array(
                'value' => $affectation->libelle,
                'formaction' => $formaction . '/id/' . $affectation->docaffectationId
            );
        }
        // die(var_dump($content));
        if (count($content) == 1) {
            $hiddens['documentId'] = $affectation->libelle;
            return array(
                'hiddens' => $hiddens,
                'content' => array(
                    'class' => $class,
                    'formaction' => $formaction . '/id/' . $affectation->docaffectationId,
                    'value' => $value,
                    'title' => $affectation->libelle
                )
            );
        } else {
            return array(
                'hiddens' => $hiddens,
                'content' => array(
                    'label' => true,
                    'class' => $class,
                    'menu' => $content,
                    'title' => empty($content) ? 'Pas de document disponible' : ''
                )
            );
        }
    }
}