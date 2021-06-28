<?php
/**
 * Description du fichier
 *
 * @project sbm
 * @package
 * @filesource CriteresForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 juin 2021
 * @version 2021-2.6.2
 */
namespace SbmGestion\Form\Finances;

use SbmCommun\Form\CriteresForm as SbmCommunCriteresForm;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Db\Sql\Where;

class CriteresForm extends SbmCommunCriteresForm implements InputFilterProviderInterface
{

    public function getWhere():Where
    {
        $where = new Where();

        return $where;
    }

    public function getTitle(): string
    {
        $title = 'à écrire';
        return $title;
    }
}