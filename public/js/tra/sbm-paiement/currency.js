/**
 * Ensemble des scripts de la page sbm-paiement/formulaire située dans le plugin payfip/view/formulaire.phtml
 * 
 * @project sbm
 * @filesource currency.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 avr. 2020
 * @version 2020-2.6.0
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
