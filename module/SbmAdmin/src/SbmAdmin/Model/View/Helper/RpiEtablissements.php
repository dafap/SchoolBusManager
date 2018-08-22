<?php
/**
 * Ecriture d'un tableau des établissements dans une fiche rpi
 *
 * L'aide de vue utilise la variable de vue $rpiId. Si elle n'existe pas (appel depuis
 * le controller) ou si elle est nulle, $rpiId est remplacée par '?'. A charge pour
 * l'utilisateur de remplacer ce ? par la valeur convenable.
 * 
 * @project sbm
 * @package SbmAdmin/Model/View/Helper
 * @filesource RpiEtablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 août 2018
 * @version 2018-2.4.2
 */
namespace SbmAdmin\Model\View\Helper;

class RpiEtablissements extends AbstractHelper
{

    /**
     * Aide de vue renvoyant le tableau des établissements et de leurs classes
     *
     * @param array $aetablissements
     *            Chaque ligne de ce tableau est un tableau décrivant les établissements et leurs classes. 
     *            Les index de chaque ligne sont 'etablissementId', 'nom', 'commune' et 'classes'
     *            L'index 'classes' est un tableau qui contient la liste des classes ('classeId', 'nom')
     * @param function $fncTableClasses
     *            aide de vue renvoyant le tableau des classes pour un établissement donné
     *            
     * @return string
     */
    public function __invoke($aetablissements, $fncTableClasses)
    {
        $this->button_name = 'btnecole';
        $rpiId = $this->getView()->rpiId;
        $html = sprintf('<tr><td>%s</td></tr>', 
            $this->btnAjout([
                'rpiId' => $rpiId ?  : '?'
            ], 'école'));
        foreach ($aetablissements as $etablissement) {
            $html .= '<tr style="border-bottom: 1px solid #fdddcc;">';
            $html .= '<td style="vertical-align: top; width: 25em;">';
            $html .= '<div>' . $etablissement['nom'] . '</div>';
            $html .= '<div>' . $etablissement['commune'] . '</div>';
            $html .= '</td>';
            $html .= '<td style="vertical-align: top; border-right: 1px solid #fdddcc;">';
            $html .= $this->btnSuppr(
                [
                    'rpiId' => $rpiId ?  : '?',
                    'etablissementId' => $etablissement['etablissementId'],
                    'etablissement' => urlencode($etablissement['nom']),
                    'commune' => urlencode($etablissement['commune'])
                ], $etablissement['nom']);
            $html .= '</td>';
            $html .= '<td style="vertical-align: top; width: 10em;">';
            // table des classes
            $html .= $fncTableClasses($etablissement);
            $html .= '</td></tr>';
        }
        return $html;
    }
}
