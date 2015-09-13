<?php
/**
 * Formulaire permettant de positionner la première étiquette sur une planche d'étiquettes
 *
 * Ce formulaire est composé de boutons radios et de boutons submit
 * Il reçoit en paramètres un tableau array('nbcols' => nbcols, 'nbrows' => nbrows)
 * où nbcols est le nombre de colonnes dans la planche d'étiquettes et nbrows est le nombre de lignes
 * 
 * @project sbm
 * @package SbmGestion/Form
 * @filesource PlancheEtiquettesForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 sept. 2015
 * @version 2015-1
 */
namespace SbmGestion\Form;

use Zend\Form\Form;

class PlancheEtiquettesForm extends Form
{

    protected $nbcols;

    protected $nbrows;

    /**
     * Pour renderplanche
     *
     * @var string
     */
    protected $messageCloseString = '</li></ul>';

    protected $messageOpenFormat = '<ul%s><li>';

    protected $messageSeparatorString = '</li><li>';

    public function __construct($name = null, $options = array())
    {
        if (! array_key_exists('nbcols', $options)) {
            throw new Exception('Appel incorrect: le nombre de colonnes de la planche d\'étiquettes n\'a pas été indiqué.');
        }
        $this->nbcols = $options['nbcols'];
        if (! array_key_exists('nbrows', $options)) {
            throw new Exception('Appel incorrect: le nombre de rangées de la planche d\'étiquettes n\'a pas été indiqué.');
        }
        $this->nbrows = $options['nbrows'];
        $value_options = array();
        for ($row = 1; $row <= $this->nbrows; $row ++) {
            for ($col = 1; $col <= $this->nbcols; $col ++) {
                $id = "$row-$col";
                $value_options[$id] = array(
                    'value' => $id,
                    'attributes' => array(
                        'id' => $id
                    )
                );
            }
        }
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        $this->setAttribute('target', '_blank');
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'planche',
            'attributes' => array(
                'id' => 'planche'
            ),
            'options' => array(
                'label' => 'Indiquez la position de l\'étiquette dans la planche',
                'value_options' => $value_options,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Lancer l\'édition',
                'class' => 'button default submit left-95px'
            )
        ));
        $this->add(array(
            'type' => 'submit',
            'name' => 'cancel',
            'attributes' => array(
                'id' => 'btcancel',
                'autofocus' => 'autofocus',
                'value' => 'Abandonner',
                'class' => 'button default cancel'
            )
        ));
    }

    public function isValid()
    {
        $isValid = parent::isValid();
        if (! $isValid) {
            // Value is required : ce message n'est pas traduit dans zend-i18n-resources
            $planche = $this->get('planche');
            $message = $planche->getMessages();
            if (! empty($message)) {
                $planche->setMessages(array(
                    'Choisissez une position dans le cadre ci-dessus.'
                ));
            }
        }
        return $isValid;
    }

    public function renderPlanche()
    {
        $planche = $this->get('planche');
        $viewHelperRadio = new \Zend\Form\View\Helper\FormRadio();
        $value_options = $planche->getValueOptions();
        $render = "<table>\n";
        $current_row = 0;
        $end_row = '';
        foreach ($value_options as $key => $inputAttributes) {
            $inputAttributes['name'] = 'planche';
            $inputAttributes['type'] = 'radio';
            list ($row, $col) = explode('-', $key);
            if ($current_row != $row) {
                $render .= "$end_row<tr>\n";
                $current_row = $row;
                $end_row = "</tr>\n";
            }
            $render .= '    <td>';
            $render .= sprintf('<input %s%s', $viewHelperRadio->createAttributesString($inputAttributes), $viewHelperRadio->getInlineClosingBracket());
            $render .= "</td>\n";
        }
        $render .= "</table>\n";
        $messages = $planche->getMessages();
        if (! empty($messages)) {
            $elementErrorsHelper = new \Zend\Form\View\Helper\FormElementErrors();
            $render .= $elementErrorsHelper->render($planche);
        }
        return $render;
    }
}