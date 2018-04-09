<?php
/**
 * Formulaire d'un responsable avec l'identité verrouillée
 *
 * Compatible ZF3
 * 
 * @project sbm
 * @package SbmCommun/Form
 * @filesource ResponsableVerrouille.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Form;

class ResponsableVerrouille extends Responsable
{

    public function __construct()
    {
        parent::__construct(true);
    }
}