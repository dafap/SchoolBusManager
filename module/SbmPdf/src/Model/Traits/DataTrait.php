<?php
/**
 * Renvoie le contenu de la propriété 'data' initialisée par la procédure appelante
 *
 *
 * @project sbm
 * @package SbmPdf/src/Model/Traits
 * @filesource DataTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 oct. 2020
 * @version 2020-2.6.1
 */
namespace SbmPdf\Model\Traits;

trait DataTrait {
    protected function getData() {
        return $this->data;
    }
}