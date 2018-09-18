<?php
/**
 * Partie du formulaire d'inscription d'un enfant concernant le second responsable 
 * en cas de garde alternée - formulaire restreint
 *
 * Cette classe utilisée en tant que Collection est dérivée du formulaire AbstractResponsable2.
 * 
 * @project sbm
 * @package SbmParent/Form
 * @filesource Responsable2Restreint.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2016
 * @version 2016-2
 */
namespace SbmParent\Form;

class Responsable2Restreint extends AbstractResponsable2
{
    public function __construct()
    {
        $this->complet = false;
        parent::__construct();
    }
}