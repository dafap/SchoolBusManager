<?php
/**
 * Construit le code d'un formulaire permettant d'éditer le contenu d'un tableau
 * de profondeur maxi = 2 et dont le niveau 2 est associatif
 *
 * @project sbm
 * @package SbmInstallation/src/Model/View/Helper
 * @filesource SbmArrayN2Asso.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmInstallation\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;

class SbmArrayN2Asso extends AbstractHelper
{

    /**
     *
     * @var \Zend\Escaper\Escaper
     */
    private $escaper;

    public function __construct()
    {
        $this->escaper = new \Zend\Escaper\Escaper('utf-8');
    }

    /**
     * Rend des lignes de formulaire pour modifier le contenu d'un tableau dont la
     * profondeur maximale du tableau est 2 et dont le niveau 2 est associatif.
     *
     * @param array $array
     *            Tableau associatif à 2 dimensions à éditer
     */
    public function __invoke($array)
    {
        $buffer = [];
        foreach ((array) $array as $key => $value) {
            if (is_array($value)) {
                $name = $key . '[]';
                $id = 1;
                if (! empty($value)) {
                    foreach ($value as $index => $item) {
                        $buffer[] = $this->renderLigneNiveau2($name, $index, $item,
                            $key . $id ++);
                    }
                } else {
                    $buffer[] = $this->renderLigneNiveau2($name, '', '', $key . $id);
                }
            } else {
                $buffer[] = $this->renderLigneNiveau1($key, $value, $key);
            }
        }
        return implode(PHP_EOL, $buffer);
    }

    private function renderLigneNiveau1($name, $value, $id)
    {
        $ligne = <<<'EOT'
<div class="wrapper-element" id="wrapper-%3$s">
    <label for="%3$s">%1$s</label>
    <input type="text" id="%3$s" class="noncol" name="%1$s" value="%2$s">
</div>
EOT;
        return sprintf($ligne, $name, $this->escaper->escapeHtml($value), $id);
    }

    private function renderLigneNiveau2($name, $index, $value, $id)
    {
        $ligne = <<<'EOT'
<div class="wrapper-element" id="wrapper-%4$s">
    <label for="index-%4$s">%1$s</label>
    <input type="text" id="index-%4$s" class="col1" name="index-%1$s" value="%2$s">
    <code>=></code>
    <input type="text" id="value-%4$s" class="col2" name="value-%1$s" value="%3$s">
</div>
EOT;
        return sprintf($ligne, $name, $index, $this->escaper->escapeHtml($value), $id);
    }
}