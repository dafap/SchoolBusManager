<?php
/**
 * Classe permettant de controler les classes enregistrées dans le service manager
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package ModulesTests
 * @filesource ControlListRegisteredClasses.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests;

use Zend\File\ClassFileLocator;
use Zend\File\PhpClassFile;
use ModulesTests\Bootstrap;

class ControlListRegisteredClasses
{

    private $service_manager;

    /**
     * Liste des classes à sauter dans le contrôle (Abtract, Interface, Trait, Exception etc)
     *
     * @var string[]
     */
    private $skip = [];

    public function __construct()
    {
        $this->service_manager = Bootstrap::getServiceManager();
    }

    public function setSkip(array $array)
    {
        $this->skip = $array;
    }

    public function getSectionArray($section = 'service_manager', $subsection = '')
    {
        $config = $this->service_manager->get('Config');
        if (empty($subsection)) {
            return isset($config[$section]) ? $config[$section] : [];
        } else {
            return isset($config[$section][$subsection]) ? $config[$section][$subsection] : [];
        }
    }

    public function unregistredNamespaceInSection($namespace, $section, $subsection = '')
    {
        $registred = $this->getSectionArray($section, $subsection);
        $base_path = dirname(dirname(__DIR__));
        $parts = explode('\\', $namespace);
        $path = $base_path . DIRECTORY_SEPARATOR . 'module' . DIRECTORY_SEPARATOR;
        $path .= $parts[0] . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        $path .= implode(DIRECTORY_SEPARATOR, $parts);
        $locator = new ClassFileLocator($path);
        $unregistred = [];
        foreach ($locator as $file) {
            $base_name = $file->getBaseName('.php');
            $ns = current($file->getNamespaces());
            if (in_array($base_name, $this->skip)) {
                continue;
            }
            $class_name = $ns . '\\' . $base_name;
            if (! in_array($class_name, $registred)) {
                $unregistred[] = $class_name;
            }
        }
        return $unregistred;
    }
}