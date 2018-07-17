<?php
/**
 * Ecriture d'un tableau des communes dans une fiche rpi
 *
 * 
 * @project sbm
 * @package SbmAdmin/Model/View/Helper
 * @filesource RpiCommunes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 juin 2018
 * @version 2018-2.4.1
 */
namespace SbmAdmin\Model\View\Helper;

class RpiCommunes extends AbstractHelper
{

    public function __invoke($acommunes)
    {
        $this->button_name = 'btncommune';
        $rpiId = $this->getView()->rpiId;
        $html = sprintf('<tr><td>%s</td></tr>', 
            $this->btnAjout([
                'rpiId' => $rpiId
            ], 'commune'));
        foreach ($acommunes as $commune) {
            $html .= '<tr><td>' . $commune['nom'] . '</td>';
            $html .= '<td class="bouton">' . $this->btnSuppr(
                [
                    'rpiId' => $rpiId,
                    'communeId' => $commune['communeId'],
                    'commune' => urlencode($commune['nom'])
                ], $commune['nom']) . '</td></tr>';
        }
        return $html;
    }
}
 