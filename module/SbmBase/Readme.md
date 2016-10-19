Module SbmBase
==

Ce module doit être chargé en premier. Ses classes sont :

+ AbstractModule
+ Session
+ StdLib
+ DateLib

AbstractModule
--
Toutes les classes Module du projet doivent être dérivées de cette classe, à l'exception de SbmBase\Module.
Elle définit la façon de charger les classes et la configuration du module.

Session
--
Dérivée de Zend\Session\Container, cette classe présente des méthodes statiques publiques qui facilitent l'usage des sessions.

StdLib
--
Ensemble de méthodes statiques publiques permettant de rechercher dans un tableau multi dimensions, de transformer, de traduire ...

DateLib
--
Ensemble de méthodes statiques publiques permettant de changer le format des dates.