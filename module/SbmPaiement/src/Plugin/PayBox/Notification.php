<?php
/**
 * Cette classe a été écrite pour gérer les notifications dans Paybox car elles arrivent en GET.
 * La structure normale de SBM traite les notifications reçues en POST.
 *
 * Elle dérive de AbtractNotification qui regroupe les méthodes communes au traitement des notifications
 * et du rapprochement (pour effectuer les rattrapages nécessaires).
 *
 * @project sbm
 * @package SbmPaiement/Model/Plugin/PayBox
 * @filesource Notification.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 août 2020
 * @version 2020-2.6.0
 */
namespace SbmPaiement\Plugin\PayBox;

class Notification extends AbstractNotification
{

}