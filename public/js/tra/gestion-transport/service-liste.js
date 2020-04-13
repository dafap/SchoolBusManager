/**
 * scripts de la page sbm-gestion/transport/service-lite.phtml
 * 
 * @project sbm
 * @filesource gestion-transport/service-liste.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 avr. 2020
 * @version 2020-2.6.0
 */
var js_selection = (function(){
	$(document).ready(function() {
		$("#liste-inner input[type=checkbox][name=selection]").change(function(){
			var id = $(this).attr('data-id');
			var action = ($(this).is(':checked'))?'check':'uncheck';
		    $.ajax({
						url : '/sbmajaxtransport/'+action+'selectionservice/serviceId:'+id,
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
	});
})();