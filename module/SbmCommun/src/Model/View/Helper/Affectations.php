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
 * @date 29 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;
use SbmCommun\Model\Strategy\Semaine;

class Affectations extends AbstractHelper
{
    use \SbmCommun\Model\Traits\ServiceTrait;

    const TR = '<tr>';

    const END_TR = '</tr>';

    /**
     * Modèle de ligne du tableau des affectations On trouve dans l'ordre : - les jours de
     * passage - le moment de passage - la ligne 1 - la station 1 - l'horaire à la station
     * 1 - la station 2 (descente) - le ligne 2 (correspondance) Les éléments <i> passent
     * - data-trajet qui est un entier (le trajet) - data-href qui est une url composée à
     * l'aide de $args
     *
     * @var string
     */
    const TD = '
	<td class="first">%s</td>
	<td class="next second">%s</td>
	<td class="next third">%s</td>
	<td class="next fourth">%s</td>
	<td class="next fifth">%s</td>
	<td class="next sixth">%s</td>
	<td class="next seventh">%s</td>
	<td class="next eighth">%s</td>
';

    const I = '
    <td class="next last">
    <i class="fam-car" title="Modifier une affectation" data-button="btnaffectation" data-trajet="%s" data-href="/op:edit%s"></i>
    <i class="fam-car-delete" title="Supprimer une affectation" data-button="btnaffectation" data-trajet="%s" data-href="/op:delete%s"></i></td>
';

    /**
     * Renvoie un tableau d'affectations
     *
     * @param int $trajet
     *            le numéro de trajet (1 ou 2) correspondant au responsable 1 (ou adresse
     *            perso) ou au responsable 2
     * @param array $structure
     *
     * @return string (le code html à inclure)
     */
    public function __invoke($trajet, $structure)
    {
        $aMoments = $this->getMoment();
        $oSemaine = new Semaine();
        $render = '';
        if (isset($structure['annee_courante'])) {
            $render = '<table class="eleve-affectations annee-courante">';
            foreach ($structure['annee_courante'] as $m => $affectationsMoment) {
                foreach ($affectationsMoment as $j => $affectationsJours) {
                    foreach ($affectationsJours as $rang => $affectation) {
                        // il faut décoder $j
                        $jours = $oSemaine->renderSemaine($j);
                        $args = '/jours:' . $j;
                        $args .= '/moment:' . $m;
                        $args .= '/correspondance:' . $rang;
                        $args .= '/station1Id:' . $affectation['station1Id'];
                        $args .= '/station2Id:' . ($affectation['station2Id'] ?: 'null');
                        $args .= '/ligne1Id:' . $affectation['ligne1Id'];
                        $args .= '/sensligne1:' . $affectation['sensligne1'];
                        $args .= '/ordreligne1:' . $affectation['ordreligne1'];
                        $args .= '/ligne2Id:' . ($affectation['ligne2Id'] ?: 'null');
                        $args .= '/sensligne2:' . $affectation['sensligne2'];
                        $args .= '/ordreligne2:' . $affectation['ordreligne2'];
                        $render .= sprintf(self::TR . self::TD . self::I . self::END_TR,
                            $jours, $aMoments[$m], $affectation['ligne1Id'],
                            $affectation['station1'], $affectation['horaireD'],
                            $affectation['station2'], $affectation['horaireA'],
                            $affectation['ligne2Id'], $trajet, $args, $trajet, $args);
                    }
                    unset($affectation);
                }
                unset($affectationsJours);
            }
            unset($affectationsMoment);
            $render .= "\n</table>";
        }
        if (isset($structure['annee_precedente'])) {
            $render .= '<span class="annee-precedente">Année précédente</span>';
            $render .= '<table class="eleve-affectations annee-precedente">';
            foreach ($structure['annee_precedente'] as $m => $affectationsMoment) {
                foreach ($affectationsMoment as $j => $affectationsJours) {
                    foreach ($affectationsJours as $rang => $affectation) {
                        // il faut décoder $j
                        $jours = $oSemaine->renderSemaine($j);
                        $render .= sprintf(self::TR . self::TD . self::END_TR, $jours,
                            $aMoments[$m], $affectation['ligne1Id'],
                            $affectation['station1'], $affectation['horaireD'],
                            $affectation['station2'], $affectation['horaireA'],
                            $affectation['ligne2Id']);
                    }
                    unset($affectation);
                }
                unset($affectationsJours);
            }
            unset($affectationsMoment);
            $render .= "\n</table>";
        }
        return $render;
    }
}