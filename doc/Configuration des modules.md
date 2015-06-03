#Configuration des modules

## Le module DafapTcpdf
C'est un module particulier qui sera détaillé ultérieurement.

## Les autres modules
Ils sont tous dérivés de ZfCommons\ZfcBase.

La structure d'une classe Module est la suivante :

```php

namespace nomDuModule;

use ZfcBase\Module\AbstractModule;

class Module extends AbstractModule
{

    public function getDir()
    {
        return __DIR__;
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }
}

```

Cela permet de mettre facilement en oeuvre les autoload_classmap.

## Construction des ClassMapAutoLoaders

Pour construire les autoload_classmap.php de chaque module, suivre la procédure suivante :

* ouvrir une boite de commande
* se placer dans le dossier du module
* taper le commande suivante : ..\\..\vendor\bin\classmap_generator.php.bat

