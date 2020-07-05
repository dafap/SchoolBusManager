/**
 * scripts de la page sbm-gestion/eleve/eleve-liste.phtml
 * 
 * @project sbm
 * @filesource gestion-eleve/eleve-liste.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 05 juil. 2020
 * @version 2020-2.6.0
 */
var js_selection = (function(){
	var fiches_selectionnees = function(){}
	$(document).ready(function() {
		$("#liste-inner input[type=checkbox][name=selection]").change(function(){
			var id = $(this).attr('data-id');
			var action = ($(this).is(':checked'))?'check':'uncheck';
		    $.ajax({
						url : '/sbmajaxeleve/'+action+'selectioneleve/eleveId:'+id,
						success : function(data) {
							if (data['success']==0) {
								alert(data['cr']);
							}
						},
						error : function(xhr, ajaxOptions, thrownError) {
							alert(xhr.status + " " + thrownError);
						}
					});
		});
		$("#liste-inner input[type=checkbox][name=demande]").change(function(){
			var id = $(this).attr('data-id');
			var trajet =$(this).attr('data-trajet');
			var td = $(this).parent();
			if ($(this).is(':checked')) {
				$.ajax({
					url: '/sbmajaxeleve/demandetraitee/eleveId:'+id+'/trajet:'+trajet,
					dataType : 'json',
					success: function(data) {
						if (data['success']==0) {
							alert(data['cr']);
						} else {
							var tr = $('table.eleves > tbody > tr').filter(':has([name=demande]:checked)');
							tr.removeClass('alert');
							td.empty();
						}
					},
					error: function(xhr, ajaxOptions, thrownError){
						alert(xhr.status + " " + thrownError);
					}
				});
			}
		});
	});
	return {
		"actions": fiches_selectionnees
	}
})();