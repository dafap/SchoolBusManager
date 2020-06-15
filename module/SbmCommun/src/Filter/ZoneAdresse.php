<?php
/**
 * A partir d'un tableau ou d'une chaine de caractères succeptible de représenter une adresse
 * ce filtre va renvoyer une zone adresse ou un tableau de zones adresses. Si le tableau initial
 * contient des enregistrements vides (ou null) ces lignes ne seront pas conservées. Le résultat
 * peut être un tableau vide.
 *
 * 1/ remplace le numéro éventuel en tête de la chaine par un espace
 * 2/ remplace les caractères isolés entre 2 espaces ou entre un espace et une ponctuation
 *    (par exemple B après le numéro de la rue) par un espace
 * 3/ remplace les non alphanumériques par un espace
 * 4/ remplace les mots bis, ter par un espace
 * 5/ supprime les espaces multiples en les remplaçant par un espace simple
 * 7/ supprime les accents
 * 6/ applique trim sur le ou les résultats
 * 7/ sans accent
 *
 * @project sbm
 * @package SbmCommun/Filter
 * @filesource ZoneAdresse.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Filter;

class ZoneAdresse extends SansAccent
{

    /**
     *
     * {@inheritdoc}
     * @see \Zend\Filter\FilterInterface::filter()
     */
    public function filter($value)
    {
        $value = parent::filter($value);
        $search = [
            '/^\d*/', // début numérique
            '/ [a-z][\W]? /i', // lettre isolée entre espaces (ponctuation éventuelle avant espace)
            '/\W/u', // caractères non mot
            '/ bis | ter /i',
            '/\s+/' // espaces multiples
        ];
        $replace = ' ';
        $value = preg_replace($search, $replace, $value);
        if (is_array($value)) {
            return parent::filter(array_filter(array_map('ltrim', $value)));
        } else {
            return parent::filter(ltrim($value));
        }
    }
}