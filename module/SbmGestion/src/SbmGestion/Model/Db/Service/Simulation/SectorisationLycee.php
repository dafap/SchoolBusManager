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
 * @date 24 août 2018
 * @version 2018-2.4.3
 */
namespace SbmGestion\Model\Db\Service\Simulation;

use Zend\Db\Sql\Where;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Strategy\Niveau;

class SectorisationLycee
{

    /**
     *
     * @var DbManager
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
     * @throws Exception
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
 