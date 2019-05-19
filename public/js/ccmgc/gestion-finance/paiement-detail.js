/**
 * Ensemble des scripts de la page sbm-gestion/gestion-finance paiement-detail.phtml
 * 
 * @project sbm
 * @filesource paiement-detail.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 avr. 2019
 * @version 2019-2.5.0
 */

var js_paiement_detail = (function(){
	var responsableId;
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
	function majAbonnements(montantPourInscrits, montantPourPreinscrits){
		$("#abo-inscrits").empty();
		$("#abo-inscrits").html(montantPourInscrits);
		$("#abo-preinscrits").empty();
		$("#abo-preinscrits").html(montantPourPreinscrits);
		formatEuro();
	}
	// listeners
	$(document).ready(function() {
		$("input[type=checkbox").change(function(){
			var action;
			var chk = $(this);
			var eleveId = chk.attr('id').substring(3);
		    if (chk.is(':checked')){
		       action = 'check';
		    } else {
		       action = 'uncheck';
		    }
		    var url = '/responsableId:'+responsableId+'/eleveId:'+eleveId;
		    $.ajax({
				url : '/sbmajaxfinance/'+action+'paiementscolarite'+url,
				dataType: 'json',
				success : function(dataJson) {
					if (dataJson.success == 0) {
						chk.removeAttr('checked');
						alert(dataJson.cr);
						return false;
					} else {
						majAbonnements(dataJson.inscrits, dataJson.preinscrits);
					}
				},
				error : function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + " " + thrownError);
				}
			});
		});
	});
	// méthodes publiques
	return {
		"init": function(resId, tous, inscrits){
			responsableId = resId;
			formatEuro();
		}
	}
})();