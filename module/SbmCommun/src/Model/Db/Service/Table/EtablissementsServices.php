<?php
/**
 * Gestion de la table `etablissements-services`
 * (à déclarer dans module.config.php)
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource EtablissementsServices.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Table;

class EtablissementsServices extends AbstractSbmTable implements EffectifInterface
{
    use OutilsMillesimeTrait;

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'etablissements-services';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\EtablissementsServices';
        $this->id_name = [
            'etablissementId',
            'millesime',
            'ligneId',
            'sens',
            'moment',
            'ordre'
        ];
    }
}

