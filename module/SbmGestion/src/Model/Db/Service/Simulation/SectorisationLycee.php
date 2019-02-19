<?php
/**
 * Règle de sectorisation pour les lycées
 * 
 * Ici, un seul lycée doit être marqué comme secteur scolaire.
 * A modifier lorsqu'il existe plusieurs lycées.
 * 
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Simulation
 * @filesource SectorisationLycee.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Simulation;

use SbmCommun\Model\Strategy\Niveau;
use Zend\Db\Sql\Where;

class SectorisationLycee
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    public function __construct($db_manager)
    {
        $this->db_manager = $db_manager;
    }

    /**
     * Renvoie l'identifiant de l'établissement du secteur scolaire de l'élève
     *
     * @return string
     *
     * @throws \SbmGestion\Model\Db\Service\Simulation\Exception
     */
    public function getEtablissementId()
    {
        $tEtablissements = $this->db_manager->get('Sbm\Db\Table\Etablissements');
        $where = new Where();
        $where->equalTo('niveau', Niveau::CODE_NIVEAU_SECOND_CYCLE)->notEqualTo(
            'rattacheA', '');
        $result = $tEtablissements->fetchAll($where);
        if ($result->count() == 1) {
            return $result->current()->etablissementId;
        }
        $msg = "Mauvaise configuration des secteurs scolaires de lycée.\n";
        if ($result->count()) {
            $msg .= "Plusieurs lycées sont indiqués en secteur scolaire. Il faut revoir la règle.";
        } else {
            $msg .= "Aucun lycée n'est indiqué comme secteur scolaire. Il en faut un.";
        }
        throw new Exception($msg);
    }
}
 