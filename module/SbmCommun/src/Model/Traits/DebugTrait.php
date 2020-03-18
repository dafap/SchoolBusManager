<?php
/**
 * Outils de debuggage
 *
 * Utilisation:
 * - initialiser par : $this->debugInitLog('/debug', 'debug-ajax.txt');
 * - utiliser comme : $this->debugLog($value);
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Traits
 * @filesource ServiceTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 mars 2020
 * @version 2020-2.6.0
 */
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
     * @return self
     */
    public function debugInitLog(string $path, string $filename)
    {
        $this->filename = sprintf('%s/%s', rtrim($path), $filename);
        return $this;
    }

    /**
     * Inscrit dans le fichier et referme
     *
     * @param mixed $data
     *            chaine, valeur numérique, tableau ou objet
     * @return self
     */
    public function debugLog($data)
    {
        $fp = fopen($this->filename, 'a');
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
        fwrite($fp, "\n");
        fclose($fp);
        return $this;
    }

    /**
     *
     * @return self
     */
    public function debugClear()
    {
        $fp = fopen($this->filename, 'w');
        fclose($fp);
        return $this;
    }

    /**
     *
     * @return self
     */
    public function debugTrace()
    {
        $fp = fopen($this->filename, 'a');
        ob_start();
        var_dump(debug_backtrace());
        fwrite($fp, ob_get_clean());
        fclose($fp);
        return $this;
    }
}