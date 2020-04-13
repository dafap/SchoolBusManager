<?php
/**
 * Modification des horaires
 *
 * @project sbm
 * @package SbmGestion/Form
 * @filesource ModifHoraires.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Form;

use Zend\Form\Form;

class ModifHoraires extends Form
{

    public function __construct()
    {
        parent::__construct('modif-horaires');
        $this->add([
            'name' => 'ligneId',
            'type' => 'hidden'
        ])
            ->add([
            'name' => 'sens',
            'type' => 'hidden'
        ])
            ->add([
            'name' => 'moment',
            'type' => 'hidden'
        ])
            ->add([
            'name' => 'ordre',
            'type' => 'hidden'
        ])
            ->add($this->text('horaireA-min'))
            ->add($this->text('horaireA-sec'))
            ->add($this->text('horaireD-min'))
            ->add($this->text('horaireD-sec'))
            ->add($this->radio('horaireA-op'))
            ->add($this->radio('horaireD-op'))
            ->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'decision-cancel',
                    'autofocus' => 'autofocus',
                    'class' => 'button default cancel left-10px'
                ]
            ])
            ->add(
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

    public function initData($ligneId, $sens, $moment, $ordre)
    {
        $this->setData(
            [
                'ligneId' => $ligneId,
                'sens' => $sens,
                'moment' => $moment,
                'ordre' => $ordre,
                'horaireA-op' => 0,
                'horaireA-min' => 0,
                'horaireA-sec' => 0,
                'horaireD-op' => 0,
                'horaireD-min' => 0,
                'horaireD-sec' => 0
            ]);
    }
}