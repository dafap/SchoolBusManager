<?php
/**
 * Ecriture d'un tableau des établissements dans une fiche rpi
 *
 * 
 * @project sbm
 * @package SbmAdmin/Model/View/Helper
 * @filesource RpiEtablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 juin 2018
 * @version 2018-2.4.1
 */
namespace SbmAdmin\Model\View\Helper;

class RpiEtablissements extends AbstractHelper
{

    public function __invoke($aetatlissements, $fncTableClasses)
    {
        $this->button_name = 'btnecole';
        $rpiId = $this->getView()->rpiId;
        $html = sprintf('<tr><td>%s</td></tr>', 
            $this->btnAjout([
                'rpiId' => $rpiId
            ], 'école'));
        foreach ($aetatlissements as $etablissement) {
            $html .= '<tr style="border-bottom: 1px solid #fdddcc;">';
            $html .= '<td style="vertical-align: top; width: 25em;">';
            $html .= '<div>' . $etablissement['nom'] . '</div>';
            $html .= '<div>' . $etablissement['commune'] . '</div>';
            $html .= '</td>';
            $html .= '<td style="vertical-align: top; border-right: 1px solid #fdddcc;">';
            $html .= $this->btnSuppr(
                [
                    'rpiId' => $rpiId,
                    'etablissementId' => $etablissement['etablissementId'],
                    'etablissement' => urlencode($etablissement['nom']),
                    'commune' => urlencode($etablissement['commune'])
                ], $etablissement['nom']);
            $html .= '</td>';
            $html .= '<td style="vertical-align: top; width: 10em;">';
            // table des classes
            $html .= $fncTableClasses($this->getView(), $etablissement);
            $html .= '</td></tr>';
        }
        return $html;
    }
}
