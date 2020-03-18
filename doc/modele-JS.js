/**
 * scripts des pages
 * 
 * Dans la page, on initialise le script par : js_mon_script.init(valeurA,
 * valeurB);
 * 
 * @project sbm
 * @filesource xxxx.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 avr. 2019
 * @version 2019-2.5.0
 */
 
var js_mon_nom =(function(){
	 // variables de la classe
	 var x;
	 // fonctions privées
	 function montre() {
		 
	 }
	 // mise en place des listeners
	 // selector pour filtrer les descendants du selector
	 // data pour passer à la fonction dans l'event e sous e.data
	 $(document).ready(function() {
			$("monSelecteur").on('events', 'selector', 'data', function(e){
			});
		});
	 // méthodes publiques
	 // (dans ces méthodes on passe tous les paramètres qu'on veut)
	 return {
		 "init": function(a, b) {},
		 "autre": function(x){}
	 }
})();