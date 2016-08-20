<?php
/**
 * Méthode de transformation de dates
 *
 * @project sbm
 * @package module/SbmBase/src/SbmCommun/Model
 * @filesource DateLib.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 août 2016
 * @version 2016-2.2.0
 */
namespace SbmBase\Model;

use DateTime;

class DateLib
{

    /**
     * Reçoit une date au format Mysql (an-mois-jour) et renvoie une date au format FR (jour/mois/an)
     *
     * @param string $d            
     *
     * @return string
     */
    public static function formatDateFromMysql($d)
    {
        if (empty($d)) {
            return '';
        } else {
            if ($date = DateTime::createFromFormat('Y-m-d', $d)) {
                return $date->format('d/m/Y');
            } elseif ($date = DateTime::createFromFormat('Y-m-d H:i:s', $d)) {
                return $date->format('d/m/Y');
            } else {
                throw new Exception("La donnée $d a un format incorrect (an-mois-jour attendu).");
            }
        }
    }

    /**
     * Reçoit une dateTime au format Mysql (an-mois-jour heure:min:s) et renvoie une dateTime au format FR (jour/mois/an heure:min:s)
     *
     * @param string $d            
     *
     * @return string
     */
    public static function formatDateTimeFromMysql($d)
    {
        if (empty($d)) {
            return '';
        } else {
            if ($date = DateTime::createFromFormat('Y-m-d H:i:s', $d)) {
                return $date->format('d/m/Y H:i:s');
            } elseif ($date = DateTime::createFromFormat('Y-m-d|', $d)) {
                 return $date->format('d/m/Y H:i:s');
            } else {
                throw new Exception("La donnée $d a un format incorrect (an-mois-jour heure:minute:seconde attendu).");
            }
        }
    }

    /**
     * Reçoit une date au format FR (jour/mois/an) et renvoie une date au format Mysql (an-mois-jour)
     *
     * @param string $d            
     *
     * @return string
     */
    public static function formatDateToMysql($d)
    {
        if (empty($d)) {
            return null;
        } else {
            if ($date = DateTime::createFromFormat('d/m/Y', $d)) {
                return $date->format('Y-m-d');
            } elseif ($date = DateTime::createFromFormat('d/m/Y H:i:s', $d)) {
                return $date->format('Y-m-d');
            } else {
                throw new Exception("La donnée $d a un format incorrect (jour/mois/an attendu).");
            }
        }
    }

    /**
     * Reçoit une dateTime au format FR (jour/mois/an heure:min:s) et renvoie une dateTime au format Mysql (an-mois-jour heure:min:s)
     *
     * @param string $d            
     *
     * @return string
     */
    public static function formatDateTimeToMysql($d)
    {
        if (empty($d)) {
            return null;
        } else {
            if ($date = DateTime::createFromFormat('d/m/Y H:i:s', $d)) {
                return $date->format('Y-m-d H:i:s');
            } elseif ($date = DateTime::createFromFormat('d/m/Y|', $d)) {
                return $date->format('Y-m-d H:i:s');
            } else {
                throw new Exception("La donnée $d a un format incorrect (jour/mois/an heure:minute:seconde attendu).");
            }
        }
    }
    
    /**
     * Renvoie la date-heure actuelle au format (an-mois-jour heure:min:s)
     */
    public static function nowToMysql()
    {
        $date = new DateTime();
        return $date->format('Y-m-d H:i:s');
    }
    
    /**
     * Renvoie la date actuelle au format (an-mois-jour)
     */
    public static function todayToMysql()
    {
        $date = new DateTime();
        return $date->format('Y-m-d');
    }
    
    /**
     * Renvoie la date-heure actuele au format (jour/mois/an heure:min:s)
     */
    public static function now()
    {
        $date = new DateTime();
        return $date->format('d/m/Y H:i:s');
    }
    
    /**
     * Renvoie la date actuelle au format (jour/mois/an)
     */
    public static function today()
    {
        $date = new DateTime();
        return $date->format('d/m/Y');
    }
}
 