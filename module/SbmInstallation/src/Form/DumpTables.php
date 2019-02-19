<?php
/**
 * Formulaire de choix des tables à sauvegarder
 *
 * @project sbm
 * @package SbmInstallation/Form
 * @filesource DumpTables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmInstallation\Form;

use Zend\Form\Form;
use Zend\Form\Element\MultiCheckbox;

class DumpTables extends Form
{

    public function __construct($name = 'dump-tables', $options = [])
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $element = new MultiCheckbox('tables');
        $element->setLabel('Tables')->setValueOptions([]);
        $this->add($element);

        $element = new MultiCheckbox('systems');
        $element->setLabel('Tables système')->setValueOptions([]);
        $this->add($element);

        $element = new MultiCheckbox('plugin');
        $element->setLabel('Table du plugin de paiement en ligne')->setValueOptions([]);
        $this->add($element);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'onscreen',
                'attributes' => [
                    'id' => 'dumptables-onscreen',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Voulez-vous une copie du résultat à l\'écran ?',
                    'label_attributes' => [],
                    'value_options' => [
                        '0' => 'Non',
                        '1' => 'Oui'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'copy',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Lancer la copie',
                    'id' => 'dump-tables-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button default submit top-6px'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'dump-tables-cancel',
                    'class' => 'button default cancel top-6px'
                ]
            ]);
    }

    /**
     * Initialise les cases à cocher pour les tables et pour les tables system
     *
     * @param string $name
     * @param array $values
     */
    public function setValueOptions($name, $values = [])
    {
        $element = $this->get($name);
        $element->setValueOptions($this->arrayConstruct($values));
    }

    private function arrayConstruct($array)
    {
        $result = [];
        foreach ($array as $item) {
            $parts = explode('\\', $item);
            $result[$item] = array_pop($parts);
        }
        return $result;
    }
}