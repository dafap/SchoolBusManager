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
 * @date 4 oct. 2018
 * @version 2019-2.5.0
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