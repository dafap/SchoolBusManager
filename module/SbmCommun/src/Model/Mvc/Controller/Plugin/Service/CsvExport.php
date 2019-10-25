<?php
/**
 * Export par fichier csv téléchargeable
 * (Service déclaré dans module.config.php)
 *
 * Usage simple dans un controller :
 *   return $this->csvExport('foo.csv', $header, $records);
 *   - nom du fichier produit: foo.csv
 *   - ligne d'en-tête (ex: ['Nom', 'Prénom', 'Date de naissance'] )
 *   - données sous forme de tableau (ex: iterator_to_array($resultset))
 *   - pas de fonction callback
 *   - le délimiteur par défaut est ';'
 *   - le caractère d'encadrement par défaut est '"'
 *
 * Usage plus complexe :
 *   - définir une fonction de rappel qui préparera la ligne de donnée pour la mettre sous forme d'un tableau
 *   simple de colonnes
 *   - définir le délimiteur
 *   - définir le caractère d'encadrement
 *
 * Attention, le caractère d'encadrement n'est pas utilisé lorsque la valeur est numérique. Si nécessaire,
 * par exemple, pour des numéros de téléphone, utiliser la fonction callback.
 * @see https://stackoverflow.com/questions/2489553/forcing-fputcsv-to-use-enclosure-for-all-fields
 *
 * Classe inspirée du module publié sur https://ghithub.com/radnan/rdn-csv
 *
 * @project sbm
 * @package SbmCommun\Model\Mvc\Controller\Plugin\Service
 * @filesource CsvExport.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 oct. 2019
 * @version 2019-2.5.3
 */
namespace SbmCommun\Model\Mvc\Controller\Plugin\Service;

use SbmCommun\Model\Mvc\Controller\Plugin\Exception;
use Zend\Http\PhpEnvironment\Response as HttpResponse;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class CsvExport extends AbstractPlugin
{

    /**
     * Nom du fichier à renvoyer
     *
     * @var string
     */
    private $name;

    /**
     * Ligne d'en-tête du fichier
     *
     * @var array
     */
    private $header;

    /**
     * Les données à exporter
     *
     * @var array|\Traversable
     */
    private $content;

    /**
     * Fonction de rappel permettant de préparer la ligne de données afin qu'elle soit un
     * tableau de valeurs (le résultat est un tableau, second paramètre de fputcsv() )
     *
     * @var callable
     */
    private $callback;

    /**
     * Délimiteur des données sur une ligne : un seul caractère
     *
     * @var string
     */
    private $delimiter;

    /**
     * Caractère d'encadrement des données : un seul caractère
     *
     * @var string
     */
    private $enclosure;

    /**
     * Attention, le délimiteur par défaut est ';'
     *
     * @param string $filename
     * @param array $header
     * @param array $records
     * @param callable $callback
     * @param string $delimiter
     * @param string $enclosure
     *
     * @return \SbmCommun\Model\Mvc\Controller\Plugin\Service\CsvExport|\Zend\Http\PhpEnvironment\Response
     */
    public function __invoke(string $filename = null, array $header = null,
        array $records = null, callable $callback = null, string $delimiter = ';',
        string $enclosure = '"')
    {
        if (func_num_args() == 0) {
            return $this;
        } elseif (func_num_args() == 1) {
            return $this->setName($filename);
        }
        return $this->setName($filename)
            ->setHeader($header)
            ->setContent($records, $callback)
            ->setControls($delimiter, $enclosure)
            ->getResponse();
    }

    /**
     * Supprime l'extension . csv du fichier si elle est donnée dans le nom
     *
     * @param string $name
     *            le nom du fichier à produire
     * @return \SbmCommun\Model\Mvc\Controller\Plugin\Service\CsvExport
     */
    public function setName($name)
    {
        if (substr($name, - 4) == '.csv') {
            $name = substr($name, 0, - 4);
        }
        $this->name = $name;
        return $this;
    }

    /**
     * En général, la première ligne
     *
     * @param array $record
     *            le tableau décrivant la ligne d'en-tête
     * @return \SbmCommun\Model\Mvc\Controller\Plugin\Service\CsvExport
     */
    public function setHeader($record)
    {
        $this->header = $record;
        return $this;
    }

    /**
     * Les données à exportées
     *
     * @param array|\Traversable $records
     *            les données
     * @param callable $callback
     *
     * @return \SbmCommun\Model\Mvc\Controller\Plugin\Service\CsvExport
     */
    public function setContent($records, callable $callback = null)
    {
        $this->content = $records;
        $this->callback = $callback;
        return $this;
    }

    /**
     * Configuration demandée : délimiteur et enclosure
     *
     * @param string $delimiter
     *            délimiteur
     * @param string $enclosure
     *            enclosure
     * @return \SbmCommun\Model\Mvc\Controller\Plugin\Service\CsvExport
     */
    public function setControls($delimiter, $enclosure)
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        return $this;
    }

    /**
     * Préparation de la réponse Http contenant le fichier CSV en attaché
     *
     * @throws \SbmCommun\Model\Mvc\Controller\Plugin\Exception\UnexpectedValueException
     *         ou exception provoquée par la fonction de rappel
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function getResponse()
    {
        if (method_exists($this->controller, 'getResponse')) {
            /**
             *
             * @var HttpResponse $response
             */
            $response = $this->controller->getResponse();
        } else {
            $response = new HttpResponse();
        }
        $fp = fopen('php://output', 'w');
        ob_start();
        if (! empty($this->header)) {
            fputcsv($fp, $this->header, $this->delimiter, $this->enclosure);
        }
        foreach ($this->content as $i => $item) {
            try {
                $fields = $this->callback ? call_user_func($this->callback, $item) : $item;
                if (! is_array($fields)) {
                    throw new Exception\UnexpectedValueException(
                        'CsvExport can only accept arrays, ' . gettype($fields) .
                        ' provided at index ' . $i .
                        '. Either use arrays when setting the records or use a callback to convert each record into an array.');
                }
                fputcsv($fp, $fields, $this->delimiter, $this->enclosure);
            } catch (\Exception $ex) {
                ob_end_clean();
                throw $ex;
            }
        }
        fclose($fp);
        $response->setContent(ob_get_clean());

        $response->getHeaders()->addHeaders(
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment;filename="' .
                str_replace('"', '\\"', $this->name) . '.csv"'
            ]);

        return $response;
    }
}