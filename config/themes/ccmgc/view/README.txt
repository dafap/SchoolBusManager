***************************************************************************************************
* Ce dossier doit contenir les pages modifiables par l'administrateur du site.                    *
* Ces pages décrivent une partie de la vue dans laquelle elles sont insérées.                     *
* Elles sont placées dans la même arborescence que la vue dans laquelle elles seront insérées.    *
***************************************************************************************************
Chaque page est composée de 3 fichiers :
- un fichier `nom_page.php` qui contient le sprintf().
- un fichier `nom_page.inc.html` qui contient le modèle pour sprintf()
- un fichier `nom_page.help.php` qui contient la liste des variables disponibles pour le sprintf()
Seul le fichier nom_page.inc est modifiable.

Les variables sont générées dans nom_page.php à partir des variables et des objets reçus dans la vue.
Pour leur utilisation, on priviligiera l'usage des paramètres fictifs de la forme %1$s, %2$s ...
A noter dans l'utilisation des spécificateurs de position (de la forme n$ où n est le rang de la variable),
tout autre spécificateur doit être placé entre $ et s (ou d).

Format général : %[parameter][flags][width][.precision][length]type
avec :
	parameter de la forme n$
	flag de la forme: - pour complément à droite par des espaces (chaine); 
					  + pour affichage du signe + devant les nombres positifs (nombre);
					  0 pour complément à gauche par des 0 (nombre)

Exemples : 
%2$06d donnera un entier complété par des 0 à gauche pour obtenir un nombre à 6 chiffres.
%1$-20s donnera une chaine complétée à droite par des espaces pour obtenir une chaine de 20 caractères.