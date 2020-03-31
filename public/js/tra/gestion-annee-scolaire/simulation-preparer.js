/**
 * Ensemble des scripts des pages de sbm-gestion/annee-scolaire/vider-reseau.phtml
 * 
 * @project sbm
 * @filesource simulation-preparer.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 mars 2020
 * @version 2020-2.6.0
 */
var js_objet =(function(){
	 // fonctions privées
	 function attendre() {
		 $("#simulation-preparer").addClass("attendre");
	 }
	 // mise en place des listeners
	 // selector pour filtrer les descendants du selector
	 // data pour passer à la fonction dans l'event e sous e.data
	 $(document).ready(function() {
			$("#simulation").submit(function(e){
				attendre();
			});
		});
	 // méthodes publiques
	 // (dans ces méthodes on passe tous les paramètres qu'on veut)
	 return {
		 "init": function() {
			 $("#simulation-preparer").removeClass('attendre');
		 }
	 }
})();