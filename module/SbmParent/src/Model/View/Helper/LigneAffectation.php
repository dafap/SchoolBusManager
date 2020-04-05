<?php
/**
 * Renvoie l'identification d'un service avec le transporteur
 *
 *
 * @project sbm
 * @package SbmParent/src/Model/View/Helper
 * @filesource LigneAffectation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmParent\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;

class LigneAffectation extends AbstractHelper
{
    use \SbmCommun\Model\Traits\ServiceTrait;

    /**
     *
     * @param array|\ArrayObject $affectation
     * @return string
     */
    public function __invoke($affectation)
    {
        $data = [
            'ligneId' => $affectation['ligne1Id'],
            'sens' => $affectation['sensligne1'],
            'moment' => $affectation['moment'],
            'ordre' => $affectation['ordreligne1']
        ];
        return sprintf('%s (%s)', $this->identifiantService($data),
            $affectation['transporteur1']);
    }
}