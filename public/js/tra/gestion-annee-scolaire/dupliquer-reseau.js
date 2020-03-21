/**
 * Ensemble des scripts des pages de sbm-gestion/annee-scolaire/dupliquer-reseau.phtml
 * 
 * @project sbm
 * @filesource dupliquer-reseau.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 mars 2020
 * @version 2020-2.6.0
 */
var js_objet =(function(){
	 // fonctions privées
	 function attendre() {
		 $("#dupliquer_reseau").addClass("attendre");
	 }
	 // mise en place des listeners
	 // selector pour filtrer les descendants du selector
	 // data pour passer à la fonction dans l'event e sous e.data
	 $(document).ready(function() {
			$("#dupliquer_reseau").submit(function(e){
				attendre();
			});
		});
	 // méthodes publiques
	 // (dans ces méthodes on passe tous les paramètres qu'on veut)
	 return {
		 "init": function() {
			 $("#dupliquer_reseau").removeClass('attendre');
		 }
	 }
})();