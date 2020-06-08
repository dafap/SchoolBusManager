<?php
/**
 * Interface pour une Plateforme
 *
 * (voir AbstractPlateforme par exemple)
 *
 * @project sbm
 * @package package_name
 * @filesource PlateformeInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmPaiement\Plugin;

use Zend\Stdlib\Parameters;

interface PlateformeInterface
{

    /**
     * Renvoie le db manager permettant d'accéder à la base de données
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getDbManager();

    /**
     * Renvoie le formulaire du plugin après avoir enregistré les données dans la table
     * des appels et éventuellement procédé aux demandes et aux initialisations préalables
     *
     * @return \Zend\Form\Form
     */
    public function getForm();

    /**
     * Renvoie la configuration de la plateforme
     *
     * @return array
     */
    public function getPlateformeConfig();

    /**
     * Reçoit un tableau obtenu par la méthode prepareAppel et renvoie une clé unique
     *
     * @param array $params
     *
     * @return string
     */
    public function getUniqueId(array $params);

    /**
     * Renvoie l'URL d'appel de la plateforme qui se trouve en config
     *
     * @return string
     */
    public function getUrl();

    /**
     * Indique si l'adresse REMOTE_ADDR est autorisée
     *
     * @param string $remote_adress
     *
     * @return boolean
     */
    public function isAuthorizedRemoteAdress($remote_adress);

    /**
     * Vérification de REMOTE_ADDR puis vérification des DATA propre à la plateforme.
     *
     * @param string $method
     * @param Parameters $data
     *            données à vérifier
     * @param string $remote_addr
     *            adresse IP de l'appel
     * @return <b>string|false</b> renvoie false si l'adresse REMOTE_ADDR n'est pas
     *         autorisée
     */
    public function notification(string $method, Parameters $data, $remote_addr = '');

    /**
     *
     * @param \SbmFront\Model\Responsable\Responsable $responsable
     * @return self
     */
    public function setResponsable(\SbmFront\Model\Responsable\Responsable $responsable);

    /**
     *
     * @param int $nb
     * @return self
     */
    public function setPaiement3Fois(int $nb);

    /**
     * Prépare le plugin en initialisant les propriétés nécessaires au paiement
     *
     * @return self
     */
    public function prepare();

    /**
     * Inscription de la demande de paiement dans la table appels
     */
    public function initPaiement();

    /**
     * Vérifie si des appels du responsable enregistré sont non notifiés et si c'est le
     * cas prépare le plugin puis lance le rattrapage de ces notifications.
     *
     * @return void
     */
    public function checkPaiement();

    /**
     * Analyse les arguments reçus et lance une procédure de mise à jour des notifications
     *
     * @param array $args
     */
    public function majnotification(array $args);

    /**
     * Reçoit un fichier csv, vérifie que les lignes sont enregistrées dans la table
     * plugin et renvoie un tableau des lignes absentes.
     *
     * @param string $csvname
     * @param bool $firstline
     * @param string $separator
     * @param string $enclosure
     * @param string $escape
     * @return array
     */
    public function rapprochement(string $csvname, bool $firstline, string $separator,
        string $enclosure, string $escape): array;

    /**
     * Renvoie l'entête des lignes d'un compte-rendu d'un rapprochement (doit avoir autant
     * d'élément qu'une ligne de cr)
     *
     * @return array
     */
    public function rapprochementCrHeader(): array;

    /**
     * Vérifie que le paramètre post contient les clés nécessaires et lance une exception
     * sinon.
     *
     * @throws Exception
     *
     * @param \Zend\Stdlib\Parameters $post
     * @return void
     */
    public function validFormAbandonner(Parameters $post);
}