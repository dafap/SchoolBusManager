<?php
/**
 * Construit le code d'un formulaire permettant d'éditer le contenu d'un tableau
 * de profondeur maxi = 2 et dont le niveau 2 est indexé, pas associatif.
 *
 * @project sbm
 * @package SbmInstallation/src/Model/View/Helper
 * @filesource SbmArrayN2Idx.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmInstallation\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;

class SbmArrayN2Idx extends AbstractHelper
{

    /**
     * Rend des lignes de formulaire pour modifier le contenu d'un tableau dont la
     * profondeur maximale du tableau est 2 et dont le niveau 2 n'est pas associatif.
     *
     * @param array $array
     *            Tableau associatif à 2 dimensions, de niveau 2 indexé à éditer
     */
    public function __invoke($array)
    {
        $buffer = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $name = $key . '[]';
                $id = 1;
                foreach ($value as $item) {
                    $buffer[] = $this->renderLigne($name, $item, $key . $id ++);
                }
            } else {
                $buffer[] = $this->renderLigne($key, $value, $key);
            }
        }
        return implode(PHP_EOL, $buffer);
    }

    private function renderLigne($name, $value, $id)
    {
        $ligne = <<<'EOT'
<div class="wrapper-element" id="wrapper-%3$s">
    <label for="%3$s">%1$s</label>
    <input type="text" id="%3$s" name="%1$s" value="%2$s">
</div>
EOT;
        return sprintf($ligne, $name, $value, $id);
    }
}