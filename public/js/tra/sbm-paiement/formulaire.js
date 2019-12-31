/**
 * Ensemble des scripts de la page sbm-paiement/formulaire située dans le plugin payfip/view/formulaire.phtml
 * 
 * @project sbm
 * @filesource formulaire.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 juin 2019
 * @version 2019-2.5.0
 */
var js_formulaire = (function(){
	$.formatCurrency.regions['fr-FR'] = {
		symbol: '€',
		positiveFormat: '%n %s',
		negativeFormat: '-%n %s',
		decimalSymbol: ',',
		digitGroupSymbol: ' ',
		groupDigits: true
	};
	// fonctions
	function formatEuro() {
		$("span[class=formatEuro").formatCurrency({ colorize:true, region: 'fr-FR' });
	}
	// méthodes publiques
	return {
		"init": function(){
			formatEuro();
		}
	}
})();