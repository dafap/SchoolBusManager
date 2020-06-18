/**
 * scripts de la page sbm-gestion/finance/paiement-liste.phtml
 * 
 * @project sbm
 * @filesource gestion-finance/paiement-liste.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 juin 2020
 * @version 2020-2.6.0
 */
var js_selection = (function(){
	var liste = "#liste-inner table.paiements tbody tr td.selection";
	var checkboxes_sel = liste+"input[type=checkbox]";
	var fiches_selectionnees = function(){
		var actions = "#liste-footer div.selection";
		var help = "#liste-footer div.sbm-description";
		var checked_boxes = $(liste).find("input[type=checkbox]:checked").length;
		if (checked_boxes > 0){
			$(help).hide();
			$(actions).show();
		} else {
			$(actions).hide();
			$(help).show();
		}
	}
	$(document).ready(function() {
		$("#liste-inner input[type=checkbox][name=selection]").change(function(){
			var id = $(this).attr('data-id');
			var action = ($(this).is(':checked'))?'check':'uncheck';
		    $.ajax({
						url : '/sbmajaxfinance/'+action+'selectionpaiement/paiementId:'+id,
						success : function(data) {
							fiches_selectionnees();
						},
						error : function(xhr, ajaxOptions, thrownError) {
							alert(xhr.status + " " + thrownError);
						}
					});
		});
		$(document).on("change", checkboxes_sel, fiches_selectionnees);
		fiches_selectionnees();
	});
	return {
		"actions": fiches_selectionnees
	}
})();