<?php
/**
 * Stratégie pour hydrater les champs représentant la nature de la carte de transport
 *
 * 
 * @project sbm
 * @package SbmCommun/Model/Strategy
 * @filesource NatureCarte.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 fév. 2019
 * @version 2019-2.4.8
 */
namespace SbmCommun\Model\Strategy;

class NatureCarte extends AbstractPower2
{

    /**
     * Table de libellés des cartes
     *
     * @var array
     */
    private $naturecartes = [];

    public function hydrate($value)
    {
        $this->nombre_de_codes = count($this->naturecartes);
        return parent::hydrate($value);
    }

    public function valid($value)
    {
        return array_key_exists($value, $this->naturecartes);
    }

    /**
     * Initialise le tableau naturecartes en ajoutant la valeur suivante
     * 
     * @param string $value            
     */
    public function addNatureCarte($value)
    {
        $i = count($this->naturecartes);
        $this->naturecartes[1 << $i] = $value;
    }

    public function getNatureCartes()
    {
        return $this->naturecartes;
    }
} 