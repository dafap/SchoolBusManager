<?php
/**
 * Reprend le filtre ZoneAdresse en conservant le % en premier caractère
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

class ZoneCritere extends ZoneAdresse
{

    /**
     *
     * {@inheritdoc}
     * @see \Zend\Filter\FilterInterface::filter()
     */
    public function filter($value)
    {
        if (is_string($value) && $value) {
            $premier = substr($value, 0, 1);
        } else {
            $premier = '';
        }
        $value = parent::filter($value);
        return $premier == '%' ? "%$value" : $value;
    }
}