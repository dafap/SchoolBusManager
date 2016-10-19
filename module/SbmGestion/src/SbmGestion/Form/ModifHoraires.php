<?php
/**
 * Modification des horaires
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmGestion/Form
 * @filesource ModifHoraires.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 oct. 2016
 * @version 2016-2.2.1
 */
namespace SbmGestion\Form;

use Zend\Form\Form;

class ModifHoraires extends Form
{

    public function __construct()
    {
        parent::__construct('modif-horaires');
        $this->add($this->text('m1-min'));
        $this->add($this->text('m1-sec'));
        $this->add($this->text('m2-min'));
        $this->add($this->text('m2-sec'));
        $this->add($this->text('m3-min'));
        $this->add($this->text('m3-sec'));
        $this->add($this->text('s1-min'));
        $this->add($this->text('s1-sec'));
        $this->add($this->text('s2-min'));
        $this->add($this->text('s2-sec'));
        $this->add($this->text('s3-min'));
        $this->add($this->text('s3-sec'));
        $this->add($this->radio('m1-op'));
        $this->add($this->radio('m2-op'));
        $this->add($this->radio('m3-op'));
        $this->add($this->radio('s1-op'));
        $this->add($this->radio('s2-op'));
        $this->add($this->radio('s3-op'));
        $this->add([
            'name' => 'cancel',
            'attributes' => [
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'decision-cancel',
                'autofocus' => 'autofocus',
                'class' => 'button default cancel left-10px'
            ]
        ]);
        $this->add([
            'name' => 'submit',
            'attributes' => [
                'type' => 'submit',
                'value' => 'Valider',
                'id' => 'decision-submit',
                'class' => 'button default submit left-10px'
            ]
        ]);
    }

    private function radio($name)
    {
        return [
            'type' => 'Zend\Form\Element\Radio',
            'name' => $name,
            'attributes' => [],
            'options' => [
                'value_options' => [
                    '0' => 'Inchangé',
                    '-1' => 'Avancé de',
                    '1' => 'Retardé de'
                ]
            ]
        ];
    }

    private function text($name)
    {
        return [
            'type' => 'text',
            'name' => $name,
            'attributes' => [
                'class' => 'sbm-width-5c'
            ],
            'options' => []
        ];
    }

    public function initData()
    {
        $this->setData([
            'm1-op' => 0,
            'm1-min' => 0,
            'm1-sec' => 0,
            'm2-op' => 0,
            'm2-min' => 0,
            'm2-sec' => 0,
            'm3-op' => 0,
            'm3-min' => 0,
            'm3-sec' => 0,
            's1-op' => 0,
            's1-min' => 0,
            's1-sec' => 0,
            's2-op' => 0,
            's2-min' => 0,
            's2-sec' => 0,
            's3-op' => 0,
            's3-min' => 0,
            's3-sec' => 0
        ]);
    }
}