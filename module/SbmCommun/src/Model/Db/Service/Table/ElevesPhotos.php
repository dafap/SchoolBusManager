<?php
/**
 * Gestion de la table `elevesphotos`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Db/Table
 * @filesource ElevesPhotos.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 mai 2022
 * @version 2022-2.6.5
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use SbmCommun\Model\Photo\PhotoValiditeInterface;
use Zend\Db\Sql\Literal;

class ElevesPhotos extends AbstractSbmTable implements PhotoValiditeInterface
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'elevesphotos';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\ElevesPhotos';
        $this->id_name = 'eleveId';
    }

    public function saveRecord(ObjectDataInterface $obj_data)
    {
        try {
            $old_data = $this->getRecord($obj_data->getId());
            $is_new = false;
        } catch (Exception\RuntimeException $e) {
            $this->supprAncienne($obj_data->getId());
            $is_new = true;
        }
        if ($is_new) {
            $obj_data->setCalculateFields([
                'dateCreation'
            ]);
        } else {
            // on vérifie si des données ont changé
            if ($obj_data->isUnchanged($old_data)) {
                return;
            }
            $obj_data->setCalculateFields([
                'dateModification'
            ]);
        }
        parent::saveRecord($obj_data);
    }

    /**
     * Renvoie la date du dernier lot d'extraction de photos
     */
    public function getLastDateExtraction()
    {
        $select = $this->table_gateway->getSql()
            ->select()
            ->columns([
            'lastDateExtraction' => new Literal('MAX(dateExtraction)')
        ]);
        $rowset = $this->table_gateway->selectWith($select);
        return $rowset->current()->lastDateExtraction;
    }

    /**
     * Supprime la photo si la date de validité est dépassée
     *
     * @param int $eleveId
     * @return number
     */
    public function supprAncienne(int $eleveId)
    {
        try {
            $photo = null;
            if ($this->estTropAncien($eleveId, $photo)) {
                return $this->deleteRecord($eleveId);
            }
        } catch (Exception\RuntimeException $e) {
            // pas de photo : rien à supprimer
        }
    }

    /**
     * Renvoie la photo si la date de validité n'est pas dépassée
     * Sinon, lance une exception.
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::getRecord()
     */
    public function getRecord($eleveId)
    {
        $photo = null;
        if ($this->estTropAncien($eleveId, $photo)) {
            throw new Exception\RuntimeException('Photo trop ancienne');
        }
        return $photo;
    }

    /**
     *
     * @param int $eleveId
     * @param mixed $photo
     *            paramètre permettant à getRecord de récupérer l'enregistrement s'il est
     *            trouvé
     * @return bool
     */
    private function estTropAncien(int $eleveId, &$photo): bool
    {
        $photo = parent::getRecord($eleveId);
        $d1 = \DateTime::createFromFormat('Y-m-d H:i:s', $photo->dateCreation);
        $d2 = \DateTime::createFromFormat('Y-m-d H:i:s', $photo->dateModification);
        $dateRef = \DateTime::createFromFormat('Y-m-d|',
            \SbmBase\Model\Session::get('as')['dateDebut']);
        $dateRef->sub(new \DateInterval(sprintf('P%dY', self::VALIDITE)));
        return ($d1 < $dateRef) && ($d2 < $dateRef);
    }
}