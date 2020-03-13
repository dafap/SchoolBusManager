<?php
namespace SbmCommun\Model\Traits;

/**
 *
 * @author admin
 */
trait DebugTrait
{

    /**
     *
     * @var string
     */
    private $filename;

    /**
     * Initialise le nom du fichier de log
     *
     * @param string $path
     * @param string $filename
     */
    public function debugInitLog(string $path, string $filename)
    {
        $this->filename = sprintf('%s/%s', rtrim($path), $filename);
    }

    /**
     * Inscrit dans le fichier et referme
     *
     * @param mixed $data
     *            chaine, valeur numérique, tableau ou objet
     */
    public function debugLog($data)
    {
        $fp = fopen(sprintf('%s/%s', $this->filename), 'a');
        fwrite($fp, (new \DateTime())->format('d-m-Y H:i:s') . ' : ');
        if (is_string($data)) {
            fwrite($fp, $data);
        } elseif (is_integer($data)) {
            fwrite($fp, sprintf('%d', $data));
        } elseif (is_numeric($data)) {
            fwrite($fp, sprintf('%f', $data));
        } else {
            fwrite($fp, print_r($data, true));
        }
        fclose($fp);
    }
}