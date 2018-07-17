<?php
/**
 * Ecriture d'un tableau des classes d'un établissement dans une fiche rpi
 *
 * 
 * @project sbm
 * @package SbmAdmin/Model/View/Helper
 * @filesource RpiClasses.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 juin 2018
 * @version 2018-2.4.1
 */
namespace SbmAdmin\Model\View\Helper;

class RpiClasses extends AbstractHelper
{

    public function __invoke($etablissement)
    {
        $etablissementId = $etablissement['etablissementId'];
        $nom = $etablissement['nom'];
        $commune = $etablissement['commune'];
        $aclasses = $etablissement['classes'];
        $this->button_name = 'btnclasse';
        $rpiId = $this->getView()->rpiId;
        $html = sprintf('<tr><td>%s</td></tr>', 
            $this->btnAjout(
                [
                    'etablissementId' => $etablissementId,
                    'etablissement' => urlencode($nom),
                    'commune' => urlencode($commune)
                ], 'classe'));
        foreach ($aclasses as $classe) {
            $html .= '<tr><td>' . $classe['nom'] . '</td>';
            $html .= '<td class="bouton">' . $this->btnSuppr(
                [
                    'classeId' => $classe['classeId'],
                    'classe' => $classe['nom'],
                    'etablissementId' => $etablissementId,
                    'etablissement' => urlencode($nom),
                    'commune' => urlencode($commune)
                ], $classe['nom']) . '</td></tr>';
        }
        return $html;
    }
}
 