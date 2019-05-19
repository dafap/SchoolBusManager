/**
 * Ensemble des scripts de la page de sbm-gestion/finance pour les paiements :
 * paiement-suppr.phtml
 * 
 * @project sbm
 * @filesource paiement-suppr.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 avr. 2019
 * @version 2019-2.5.0
 */

/**
 * Cet objet met en place les listeners 
 * et propose la méthode publique init().
 */
var js_paiement_suppr = (function(){
	function cacheListe() {
		$("#paiement-suppr-liste").hide();
	}
	function montreListe() {
		$("#paiement-suppr-liste").show();
	}
	// mise en place des listeners
	$(document).ready(function() {
		$("#nature-abonnement").change(function(){
			if ($(this).is(":checked")) {
				montreListe();
			} else {
				cacheListe();
			}
		});
	});
	// méthodes publiques
	return {
		"init": function(){
			cacheListe();
		}
	}
})();