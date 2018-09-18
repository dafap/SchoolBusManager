<?php
/**
 * Classe permettant de modifier les horaires d'un ensemble de points d'arrêts
 *
 * Les points d'arrêts marqués 'selection' auront leurs horaires modifiés comme 
 * décrit dans le tableau actions.
 * 
 * @project sbm
 * @package SbmGestion/Model
 * @filesource ModifHoraires.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmGestion\Model;

use SbmBase\Model\Session;

class ModifHoraires
{

    private $actions;

    private $tcircuits;

    /**
     *
     * @param array $actions
     * @param \SbmCommun\Model\Db\Service\Table\Circuits $tcircuits
     */
    public function __construct($actions, $tcircuits)
    {
        $this->actions = $actions;
        $this->tcircuits = $tcircuits;
    }

    /**
     * Modifie les horaires et renvoie un compte-rendu.
     *
     * @return boolean
     */
    public function run()
    {
        $horaires = [
            'm1',
            'm2',
            'm3',
            's1',
            's2',
            's3'
        ];
        $millesime = Session::get('millesime');
        $circuits = $this->tcircuits->fetchAll(
            [
                'millesime' => $millesime,
                'selection' => 1
            ]);
        foreach ($circuits as $circuit) {
            $cr = true;
            $change = false;
            foreach ($horaires as $horaire) {
                $str_laps = sprintf('PT%dM%dS', $this->actions[$horaire . '-min'],
                    $this->actions[$horaire . '-sec']);
                if ($this->actions[$horaire . '-op'] == - 1) {
                    $change = true;
                    $date = new \DateTime($circuit->{$horaire});
                    if ($date->sub(new \DateInterval($str_laps)) === false) {
                        $cr = false;
                    }
                    $circuit->{$horaire} = $date->format('H:i:s');
                } elseif ($this->actions[$horaire . '-op'] == 1) {
                    $change = true;
                    $date = new \DateTime($circuit->{$horaire});
                    if ($date->add(new \DateInterval($str_laps)) === false) {
                        $cr = false;
                    }
                    $circuit->{$horaire} = $date->format('H:i:s');
                } else {
                    continue; // inchangé
                }
            }
            if ($change) {
                $this->tcircuits->updateRecord($circuit);
            }
        }

        return $cr;
    }
}
 