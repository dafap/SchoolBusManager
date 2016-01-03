<?php
/**
 * Ecriture d'un tableau d'affectations dans une fiche élève
 *
 * 
 * @project sbm
 * @package SbmCommun/Form/View/Helper
 * @filesource Affectations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 déc. 2015
 * @version 2015-1
 */
namespace SbmCommun\Form\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Affectations extends AbstractHelper
{

    /**
     * Modèle de ligne du tableau des affectations
     * On trouve dans l'ordre :
     * - les jours de passage
     * - le sens de passage
     * - le service 1
     * - la station 1
     * - la station 2
     * - le service 2 (correspondance)
     * Les éléments <i> passent
     * - data-trajet qui est un entier (le trajet)
     * - data-href qui est une url composée à l'aide de $args
     *
     * @var string
     */
    private $template = '
<tr>
	<td class="first">%s</td>
	<td class="next second">%s</td>
	<td class="next third">%s</td>
	<td class="next">%s</td>
	<td class="next">%s</td>
	<td class="next">%s</td>
	<td class="next last">
        <i class="fam-car" title="Modifier une affectation" data-button="btnaffectation" data-trajet="%s" data-href="/op:edit%s"></i> 
        <i class="fam-car-delete" title="Supprimer une affectation" data-button="btnaffectation" data-trajet="%s" data-href="/op:delete%s"></i></td>
</tr>
';

    /**
     * Renvoie un tableau d'affectations
     *
     * @param int $trajet
     *            le numéro de trajet (1 ou 2) correspondant au responsable 1 (ou adresse perso) ou au responsable 2
     * @param array $structure            
     *
     * @return string (le code html à inclure)
     */
    public function __invoke($trajet, $structure)
    {
        $render = '';
        if (isset($structure)) {
            $render = '<table class="eleve-affectations">';
            foreach ($structure as $j => $affectationsParJours) {
                // il faut décoder $j
                $jours = 'Lu Ma Me Je Ve'; // pour le moment je ne traite pas les jours différents
                foreach ($affectationsParJours as $s => $affectationsSens) {
                    $sens = array(
                        1 => 'Aller',
                        2 => 'Retour',
                        3 => 'Aller-retour'
                    )[$s];
                    foreach ($affectationsSens as $rang => $affectation) {
                        $args = '/jours:' . $j;
                        $args .= '/sens:' . $s;
                        $args .= '/correspondance:' . $rang;
                        $args .= '/station1Id:' . $affectation['station1Id'];
                        $args .= '/station2Id:' . ($affectation['station2Id'] ?  : 'null');
                        $args .= '/service1Id:' . $affectation['service1Id'];
                        $args .= '/service2Id:' . ($affectation['service2Id'] ?  : 'null');
                        $render .= sprintf($this->template, $jours, $sens, $affectation['service1Id'], $affectation['station1'], $affectation['station2'], $affectation['service2Id'], $trajet, $args, $trajet, $args);
                    }
                    unset($affectation);
                }
                unset($affectationsSens);
            }
            $render .= "\n</table>";
        }
        return $render;
    }
}