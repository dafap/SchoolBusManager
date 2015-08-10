<?php
/**
 * Formulaire de choix des tables à sauvegarder
 *
 * @project sbm
 * @package SbmInstallation/Form
 * @filesource DumpTables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 oct. 2014
 * @version 2014-1
 */

namespace SbmInstallation\Form;

use Zend\Form\Form;
use Zend\Form\Element\MultiCheckbox;
use Zend\Form\Element\Submit;
class DumpTables extends Form
{
    public function __construct($name = 'dump-tables', $options = array())
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        
        $element = new MultiCheckbox('tables');
        $element->setLabel('Tables')->setValueOptions(array());
        $this->add($element);
        
        $element = new MultiCheckbox('systems');
        $element->setLabel('Tables système')->setValueOptions(array());
        $this->add($element);        
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'onscreen',
            'attributes' => array(
                'id' => 'dumptables-onscreen',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Voulez-vous une copie du résultat à l\'écran ?',
                'label_attributes' => array(),
                'value_options' => array(
                    '0' => 'Non',
                    '1' => 'Oui'
                )
            )
        ));
        $this->add(array(
            'name' => 'copy',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Lancer la copie',
                'id' => 'dump-tables-submit',
                'autofocus' => 'autofocus',
                'class' => 'button default submit top-6px'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'dump-tables-cancel',
                'class' => 'button default cancel top-6px'
            )
        ));
    }
    
    /**
     * Initialise les cases à cocher pour les tables et pour les tables system
     * 
     * @param string $name
     * @param array $values
     */
    public function setValueOptions($name, $values = array())
    {
        $element = $this->get($name);
        $element->setValueOptions($this->arrayConstruct($values));
    }
    
    private function arrayConstruct($array)
    {
        $result = array();
        foreach ($array as $item) {
            $parts = explode('\\', $item);
            $result[$item] = array_pop($parts);
        }
        return $result;
    }
}