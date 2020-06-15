/**
 * scripts de la page de sbm-gestion/transport/zonage-liste.phtml 
 * 
 * @project sbm
 * @filesource public/js/mgc/gestion-transport/zonage-liste.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 juin 2020
 * @version 2020-2.5.4
 */
var js_checkbox = (function(){
	$(document).ready(function() {
		$("#liste-inner input[type=checkbox]").change(function(){
			var id = $(this).attr('data-id');
			var action = ($(this).is(':checked'))?'check':'uncheck';
			action += $(this).attr('name');
		    $.ajax({
						url : '/sbmajaxtransport/'+action+'zonage/zonageId:'+id,
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