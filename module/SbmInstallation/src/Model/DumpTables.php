<?php
/**
 * Méthode de copie des tables sélectionnées
 * 
 * Compatible ZF3
 *
 * @project sbm
 * @package SbmInstallation/Model
 * @filesource DumpTables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmInstallation\Model;

use SbmBase\Model\StdLib;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Strategy;

class DumpTables
{

    /**
     *
     * @var DbManager
     */
    private $db_manager;

    /**
     *
     * @var string
     */
    private $template_head;

    private $tables = [];

    private $onScreen;

    private $screen = '';

    private $version;

    public function __construct(DbManager $db_manager)
    {
        $this->db_manager = $db_manager;
        $this->version = include StdLib::concatPath(
            StdLib::findParentPath(__DIR__, 'config'), 'version.inc.php');
        $this->template_head = <<<EOT
<?php
/**
 * Données de la %s `%s`
 *
 * Fichier permettant de recharger la table à partir du module SbmInstallation, action create
 * 
 * @project sbm
 * @package SbmInstallation
 * @filesource %s
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date %s
 * @version %s
 */
return [
EOT;
    }

    /**
     * Initialisation :
     * On lui passe un tableau d'alias de tables dans le ServiceManager
     *
     * @param array $tables
     * @param bool $onScreen
     *            si true, la méthode copy renverra une chaine au format html pour affichage du
     *            résultat sur l'écran
     */
    public function init($tables, $onScreen = false)
    {
        $this->tables = $tables;
        $this->onScreen = $onScreen;
    }

    /**
     * Effectue la copy dans les fichiers et renvoie le buffer d'écran si la propriété onScreen est
     * vraie (sinon, une chaine vide)
     *
     * @return string
     */
    public function copy()
    {
        // buffer pour afficher le résultat à l'écran
        $this->screen = $this->onScreen ? "<pre>\n" : '';

        // initialisation des invariants de boucle
        // $path = SBM_BASE_PATH . '/module/SbmInstallation/db_design/data/';
        $path_db_design = StdLib::findParentPath(__DIR__, 'db_design');
        $format_date = '%e %b %Y';
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $format_date = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format_date);
        }
        $aujourdhui = strftime($format_date);
        foreach ($this->tables as $table_alias) {
            // initialisation des variables de boucle
            $table = $this->db_manager->get($table_alias);
            $table_type = $table->getTableType();
            $table_name = $table->getTableName();
            // lecture du fichier descripteur
            if ($table_type == 'system') {
                $template_head_1 = 'table système';
                $db_design = 'system.' . $table_name . '.php';
            } else {
                $template_head_1 = 'table';
                $db_design = 'table.' . $table_name . '.php';
            }
            $descripteur = include (StdLib::concatPath($path_db_design, $db_design));
            $file_name = StdLib::getParam('data', $descripteur);
            if (is_null($file_name)) {
                continue;
            }
            $columns = $this->db_manager->getColumnTypes($table_name, $table_type);

            // ouverture du fichier
            $fp = fopen($file_name, 'w');
            $this->fputs($fp,
                sprintf($this->template_head, $template_head_1, $table_name, $file_name,
                    $aujourdhui, $this->version));
            // lecture de la table
            foreach ($table->fetchAll() as $row) {
                $ligne = "\n    [";
                foreach ($row->getArrayCopy() as $key => $column) {
                    if ($columns[$key] == 'blob') {
                        $val = "addslashes(base64_decode('" .
                            base64_encode(stripslashes($column)) . "'))";
                    } elseif (is_numeric($column)) {
                        if (substr($column, 0, 1) == 0 && strlen(trim($column)) > 1) {
                            $val = "'$column'"; // 0 à gauche
                        } else {
                            $val = $column;
                        }
                    } elseif (is_null($column)) {
                        $val = 'null';
                    } elseif (is_bool($column)) {
                        $val = $column ? 'true' : 'false';
                    } elseif (is_array($column)) {
                        try {
                            $val = $table->getStrategie($key)->extract($column);
                        } catch (\Exception $e) {
                            ob_start();
                            var_dump($column);
                            $dump = html_entity_decode(strip_tags(ob_get_clean()));
                            throw new Exception(
                                __METHOD__ .
                                " - Table: $table_name - Codage impossible pour $key\n$dump",
                                999, $e);
                            break;
                        }
                    } elseif ($table->getTableName() == 'tarifs' &&
                        $table->getTableType() == 'table') {
                        switch ($key) {
                            case 'mode':
                                $strategie = new Strategy\TarifAttributs(
                                    $table->getModes(), "$column est un mode invalide.");
                                $val = $strategie->extract($column);
                                break;
                            case 'rythme':
                                $strategie = new Strategy\TarifAttributs(
                                    $table->getRythmes(), "$column est un rythme invalide.");
                                $val = $strategie->extract($column);
                                break;
                            case 'grille':
                                $strategie = new Strategy\TarifAttributs(
                                    $table->getGrilles(),
                                    "$column est une grille invalide.");
                                $val = $strategie->extract($column);
                                break;
                            default:
                                $val = "'" . addslashes($column) . "'";
                                break;
                        }
                    } else {
                        if (substr($key, - 5) == 'color' && substr($column, 0, 1) == '#') {
                            $column = substr($column, 1);
                        }
                        $val = "'" . addslashes($column) . "'";
                    }

                    $ligne .= "\n        '$key' => $val, ";
                }
                $ligne .= "\n    ],";
                $this->fputs($fp, $ligne);
            }
            $this->fputs($fp, "\n];");
            // fermeture du fichier
            fclose($fp);
        }

        if ($this->onScreen)
            $this->screen .= '</pre>';
        return $this->screen;
    }

    private function fputs($fp, $txt)
    {
        fputs($fp, $txt);
        if ($this->onScreen)
            $this->screen .= $txt;
    }
}