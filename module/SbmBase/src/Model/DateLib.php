<?php
/**
 * Méthode de transformation de dates
 *
 * @project sbm
 * @package module/SbmBase/src/SbmCommun/Model
 * @filesource DateLib.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 juin 2019
 * @version 2019-2.5.0
 */
namespace SbmBase\Model;

use DateTime;

class DateLib
{

    private static function getDateTimeFromFr(string $d): DateTime
    {
        if ($date = DateTime::createFromFormat('d/m/Y H:i:s', $d)) {
            return $date;
        } elseif ($date = DateTime::createFromFormat('d/m/Y|', $d)) {
            return $date;
        } else {
            throw new Exception\RuntimeException(
                "La donnée $d a un format incorrect (jour/mois/an [heure:minute:seconde] attendu).");
        }
    }

    /**
     * Essai de créer un DateTime à partir de la chaine $d au format Mysql
     *
     * @param string $d
     * @throws Exception\RuntimeException
     * @return \DateTime
     */
    private static function getDateTimeFromMysql(string $d): DateTime
    {
        if ($date = DateTime::createFromFormat('Y-m-d H:i:s', $d)) {
            return $date;
        } elseif ($date = DateTime::createFromFormat('Y-m-d|', $d)) {
            return $date;
        } else {
            throw new Exception\RuntimeException(
                "La donnée $d a un format incorrect (an-mois-jour [heure:minute:seconde] attendu).");
        }
    }

    /**
     * Reçoit une date au format Mysql (an-mois-jour) et renvoie une date au format FR
     * (jour/mois/an)
     *
     * @param string $d
     *
     * @throws \SbmBase\Model\Exception\RuntimeException
     *
     * @return string
     */
    public static function formatDateFromMysql($d)
    {
        if (empty($d)) {
            return '';
        } else {
            return self::getDateTimeFromMysql($d)->format('d/m/Y');
        }
    }

    /**
     * Reçoit une dateTime au format Mysql (an-mois-jour heure:min:s) et renvoie une
     * dateTime au format FR (jour/mois/an heure:min:s)
     *
     * @param string $d
     *
     * @throws \SbmBase\Model\Exception\RuntimeException
     *
     * @return string
     */
    public static function formatDateTimeFromMysql($d)
    {
        if (empty($d)) {
            return '';
        } else {
            return self::getDateTimeFromMysql($d)->format('d/m/Y H:i:s');
        }
    }

    /**
     * Reçoit une date au format FR (jour/mois/an) et renvoie une date au format Mysql
     * (an-mois-jour)
     *
     * @param string $d
     *
     * @throws \SbmBase\Model\Exception\RuntimeException
     *
     * @return string
     */
    public static function formatDateToMysql($d)
    {
        if (empty($d)) {
            return null;
        } else {
            return self::getDateTimeFromFr($d)->format('Y-m-d');
        }
    }

    /**
     * Reçoit une dateTime au format FR (jour/mois/an heure:min:s) et renvoie une dateTime
     * au format Mysql (an-mois-jour heure:min:s)
     *
     * @param string $d
     *
     * @throws \SbmBase\Model\Exception\RuntimeException
     *
     * @return string
     */
    public static function formatDateTimeToMysql($d)
    {
        if (empty($d)) {
            return null;
        } else {
            return self::getDateTimeFromFr($d)->format('Y-m-d H:i:s');
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

    /**
     * Reçoit une date sous l'un des types indiqués et renvoie la date complète en
     * français. Si la date est donnée sous forme de chaine de caractères ni au format
     * d/m/Y, ni au format ni au fommat Y-m-d, une exception est lancée.
     *
     * @param string|DateTime|null $d
     * @return string
     */
    public static function formatDateComplete($d = null)
    {
        if (empty($d)) {
            $d = new DateTime();
        } elseif (! ($d instanceof DateTime)) {
            try {
                $d = self::getDateTimeFromFr($d);
            } catch (Exception\RuntimeException $e) {
                $d = self::getDateTimeFromMysql($d);
            }
        }
        $nom_jour_fr = array(
            "dimanche",
            "lundi",
            "mardi",
            "mercredi",
            "jeudi",
            "vendredi",
            "samedi"
        );
        $mois_fr = Array(
            "",
            "janvier",
            "février",
            "mars",
            "avril",
            "mai",
            "juin",
            "juillet",
            "août",
            "septembre",
            "octobre",
            "novembre",
            "décembre"
        );
        list ($num_jour, $jour, $mois, $annee) = explode('/', $d->format('w/j/n/Y'));
        return $nom_jour_fr[$num_jour] . ' ' . $jour . ' ' . $mois_fr[$mois] . ' ' . $annee;
    }
}
