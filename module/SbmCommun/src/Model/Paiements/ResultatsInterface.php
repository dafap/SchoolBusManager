<?php
/**
 * Description d'un objet Resultats
 *
 * @project sbm
 * @package SbmCommun/src/Model/Paiements
 * @filesource ResultatsInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 juin 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Model\Paiements;

interface ResultatsInterface
{
    public function equalTo(ResultatsInterface $r): bool;
    public function signature(): string;
}