<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package SbmCommun/src/Model/Paiements
 * @filesource ResultatsInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Paiements;

interface ResultatsInterface
{
    public function equalTo(ResultatsInterface $r): bool;
    public function signature(): string;
}