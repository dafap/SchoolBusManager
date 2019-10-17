<?php
/**
 * Modification des horaires
 *
 * @project sbm
 * @package SbmGestion/Form
 * @filesource ModifHoraires.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 mars 2019
 * @version 2019-2.5.0
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
        $this->add($this->text('z1-min'));
        $this->add($this->text('z1-sec'));
        $this->add($this->text('z2-min'));
        $this->add($this->text('z2-sec'));
        $this->add($this->text('z3-min'));
        $this->add($this->text('z3-sec'));
        $this->add($this->radio('m1-op'));
        $this->add($this->radio('m2-op'));
        $this->add($this->radio('m3-op'));
        $this->add($this->radio('s1-op'));
        $this->add($this->radio('s2-op'));
        $this->add($this->radio('s3-op'));
        $this->add($this->radio('z1-op'));
        $this->add($this->radio('z2-op'));
        $this->add($this->radio('z3-op'));
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'decision-cancel',
                    'autofocus' => 'autofocus',
                    'class' => 'button default cancel left-10px'
                ]
            ]);
        $this->add(
            [
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
                'class' => "decalage $name"
            ],
            'options' => []
        ];
    }

    public function initData()
    {
        $this->setData(
            [
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
                's3-sec' => 0,
                'z1-op' => 0,
                'z1-min' => 0,
                'z1-sec' => 0,
                'z2-op' => 0,
                'z2-min' => 0,
                'z2-sec' => 0,
                'z3-op' => 0,
                'z3-min' => 0,
                'z3-sec' => 0
            ]);
    }
}