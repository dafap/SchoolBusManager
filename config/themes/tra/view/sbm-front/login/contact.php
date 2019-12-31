<?php
/**
 * Mise en place de la page de contact
 *
 * Ce fichier est associé à
 * - contact.inc.html qui décrit le format de sprintf
 * - contact.help.php qui décrit les paramètre disponibles
 *
 * @project sbm
 * @package config/themes/arlysere/view/sbm-front/login
 * @filesource contact.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 déc. 2019
 * @version 2019-2.5.4
 */
$format = file_get_contents(__DIR__ . '/contact.inc.phtml');
return sprintf($format,
    /*1*/ $this->accueil,
    /*2*/ $this->client['name'],
    /*3*/ $this->client['adresse'][0],
    /*4*/ $this->client['adresse'][1],
    /*5*/ $this->client['code_postal'],
    /*6*/ $this->client['commune'],
    /*7*/ $this->telephone($this->client['telephone']),
    /*8*/ $this->client['email']);