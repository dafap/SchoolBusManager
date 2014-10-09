<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource DumpTables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 oct. 2014
 * @version 2014-1
 */
namespace SbmInstallation\Model;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCommun\Model\Strategy\ClasseNiveau;
use SbmCommun\Model\Strategy\Semaine;
use SbmCommun\Model\Strategy\TarifAttributs;

class DumpTables implements ServiceLocatorAwareInterface
{
    private $sm;
    
    private $template_head = <<<EOT
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
 * @version 2014-1
 */
return array(
EOT;
    
    private $tables = array();
    
    private $onScreen;
    
    private $screen = '';
    
    /**
     * Initialisation :
     * On lui passe un tableau d'alias de tables dans le ServiceManager
     * 
     * @param array $tables
     * @param bool $onScreen
     *      si true, la méthode copy renverra une chaine au format html pour affichage du résultat sur l'écran
     */
    public function init($tables, $onScreen = false)
    {
        $this->tables = $tables;
        $this->onScreen = $onScreen;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::setServiceLocator()
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->sm = $serviceLocator;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::getServiceLocator()
     */
    public function getServiceLocator()
    {
        return $this->sm;
    }
    
    /**
     * Effectue la copy dans les fichiers et renvoie le buffer d'écran si la propriété onScreen est vraie (sinon, une chaine vide)
     * 
     * @return string
     */
    public function copy()
    {
        // buffer pour afficher le résultat à l'écran
        $this->screen = $this->onScreen ? "<pre>\n" : '';
        
        // initialisation des invariants de boucle
        $path = SBM_BASE_PATH . '/module/SbmInstallation/db_design/data/';
        $format_date = '%e %b %Y';
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $format_date = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format_date);
        }
        $aujourdhui = strftime($format_date);
        
        foreach ($this->tables as $table_alias) {
            // initialisation des variables de boucle
            $table = $this->getServiceLocator()->get($table_alias);
            $table_type = $table->getTableType();
            $table_name = $table->getTableName();
            if ($table_type == 'system') {
                $template_head_1 = 'table système';
                $file_name = 'data.system.' . $table_name . '.php';
            } else {
                $template_head_1 = 'table';
                $file_name = 'data.' . $table_name . '.php';
            }
            
            // ouverture du fichier
            $fp = fopen($path . $file_name, 'w');
            $this->fputs($fp, sprintf($this->template_head, $template_head_1, $table_name, $file_name, $aujourdhui));
            // lecture de la table
            foreach ($table->fetchAll() as $row) {
                $ligne = "\n    array(";
                foreach ($row->getArrayCopy() as $key => $column) {
                    if (is_numeric($column)) {
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
                        switch ($key) {
                            case 'niveau':
                                $strategie = new ClasseNiveau();
                                $val = $strategie->extract($column);
                                break;
                            case 'jOuverture':
                                $strategie = new Semaine();
                                $val = $strategie->extract($column);
                                break;
                            default:
                                ob_start();
                                var_dump($column);
                                $dump = ob_get_clean();
                                throw new Exception(__METHOD__ . "Codage inconnu pour $key\n$debug");
                            break;
                        }
                    } elseif ($table->getTableName() == 'tarifs' && $table->getTableType() == 'table') {
                        switch ($key) {
                            case 'mode':
                                $strategie = new TarifAttributs($table->getModes(), "$column est un mode invalide.");
                                $val = $strategie->extract($column);
                                break;
                            case 'rythme':
                                $strategie = new TarifAttributs($table->getRythmes(), "$column est un rythme invalide.");
                                $val = $strategie->extract($column);
                                break;
                            case 'grille':
                                $strategie = new TarifAttributs($table->getGrilles(), "$column est une grille invalide.");
                                $val = $strategie->extract($column);
                                break;
                            default:
                                $val = "'" . addslashes($column) . "'";
                                break;
                        }
                    } else {
                        $val = "'" . addslashes($column) . "'";
                    }
                
                    $ligne .= "\n        '$key' => $val, ";
                }
                $ligne .= "\n    ),";
                $this->fputs($fp, $ligne);
            }
            $this->fputs($fp, "\n);");
            // fermeture du fichier
            fclose($fp);
        }
        
        if ($this->onScreen) $this->screen .= '</pre>';
        return $this->screen;
    }
    
    private function fputs($fp, $txt)
    {
        fputs($fp, $txt);
        if ($this->onScreen) $this->screen .= $txt;
    }
}