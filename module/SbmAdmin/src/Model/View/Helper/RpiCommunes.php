<?php
/**
 * Ecriture d'un tableau des communes dans une fiche rpi
 * 
 * L'aide de vue utilise la variable de vue $rpiId. Si elle n'existe pas (appel depuis
 * le controller) ou si elle est nulle, $rpiId est remplacée par '?'. A charge pour
 * l'utilisateur de remplacer ce ? par la valeur convenable.
 *
 * 
 * @project sbm
 * @package SbmAdmin/Model/View/Helper
 * @filesource RpiCommunes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 août 2018
 * @version 2018-2.4.2
 */
namespace SbmAdmin\Model\View\Helper;

class RpiCommunes extends AbstractHelper
{

    /**
     * Aide de vue renvoyant le tableau des communes
     *
     * @param array $acommunes
     *            chaque ligne de ce tableau décrit une commune ('communeId', 'nom')
     *            
     * @return string
     */
    public function __invoke($acommunes)
    {
        $this->button_name = 'btncommune';
        $rpiId = $this->getView()->rpiId;
        $html = sprintf('<tr><td>%s</td></tr>',
            $this->btnAjout([
                'rpiId' => $rpiId ?: '?'
            ], 'commune'));
        foreach ($acommunes as $commune) {
            $html .= '<tr><td>' . $commune['nom'] . '</td>';
            $html .= '<td class="bouton">' . $this->btnSuppr(
                [
                    'rpiId' => $rpiId ?: '?',
                    'communeId' => $commune['communeId'],
                    'commune' => urlencode($commune['nom'])
                ], $commune['nom']) . '</td></tr>';
        }
        return $html;
    }
}
 