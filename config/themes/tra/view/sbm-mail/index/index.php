<?php
/**
 * Inclusion d'un message dans la page d'envoi d'un mail
 *
 * Ce fichier est associé à
 * - index.inc.html qui décrit le format de sprintf
 * - index.help.php qui décrit les paramètre disponibles
 *
 * @project sbm
 * @package config/themes/arlysere/view/sbm-mail/index
 * @filesource index.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 déc. 2019
 * @version 2019-2.5.4
 */
$format = file_get_contents(__DIR__ . '/index.inc.phtml');
return sprintf($format,
    /*1*/ $this->client['name'],
    /*2*/ implode(', ', $this->client['adresse']),
    /*3*/ $this->client['code_postal'],
    /*4*/ $this->client['commune'],
    /*5*/ $this->telephone($this->client['telephone']),
    /*6*/ $this->client['email']);