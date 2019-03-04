<?php
/**
 * Télécharge un fichier cacert.pem sur le site de CURL
 *
 * Ce fichier est placé dans le dossier config/ssl de School Bus Manager
 * 
 * @project sbm
 * @package tools
 * @filesource update_cacert.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 févr. 2019
 * @version 2019-2.5.0
 */
use Zend\Http\Client;
use SbmBase\Model\StdLib;

define('URL', "https://curl.haxx.se/ca/cacert.pem");

function getOfficialCacert()
{
    $client = new Client();
    $client->setOptions([
        'adapter' => 'Zend\Http\Client\Adapter\Curl'
    ]);
    $client->setUri(URL);
    $client->setMethod('GET');

    $response = $client->send();
    if (! $response->isSuccess()) {
        throw new \Exception(sprintf("Error: cannot get '%s'%s", IANA_URL, PHP_EOL));
    }
    return $response;
}

$cafile = StdLib::concatPath(StdLib::findParentPath(__DIR__, 'config/ssl'), 'cacert.pem');
$ctr = file_put_contents($cafile, $data = getOfficialCacert()->getBody());
?>
<?php if ($ctr) :?>
<p>La mise à jour de cacert.pem a réussi.</p>
<?php else :?>
<p>La mise à jour de cacert.pem a échoué.</p>
<?php endif;?>
<pre>
<?= $data;?>
</pre>