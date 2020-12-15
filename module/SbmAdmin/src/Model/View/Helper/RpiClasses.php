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
 * @date 22 août 2018
 * @version 2018-2.4.2
 */
namespace SbmAdmin\Model\View\Helper;

class RpiClasses extends AbstractHelper
{

    /**
     * Aide de vue renvoyant le tableau des classes d'un établissement
     *
     * @param array $aetablissement
     *            structure décrivant un établissement.
     *            Les index sont 'etablissementId', 'nom', 'commune, 'classes'.
     *            L'index 'classes' est un tableau qui contient la liste des classes
     *            ('classeId',
     *            'nom')
     *            
     * @return string
     */
    public function __invoke($aetablissement)
    {
        $etablissementId = $aetablissement['etablissementId'];
        $nom = $aetablissement['nom'];
        $commune = $aetablissement['commune'];
        $aclasses = $aetablissement['classes'];
        $this->button_name = 'btnclasse';
        // $rpiId = $this->getView()->rpiId;
        $html = sprintf('<tr><td>%s</td></tr>',
            $this->btnAjout(
                [
                    'etablissementId' => $etablissementId,
                    'etablissement' => urlencode($nom),
                    'commune' => urlencode($commune)
                ], 'classe'));
        foreach ($aclasses as $classe) {
            $html .= '<tr><td>' . $classe['nom'] . '</td>';
            $html .= '<td class="bouton">' .
                $this->btnSuppr(
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
 