/**
 * scripts de la page sbm-gestion/transport/ligne-liste.phtml
 * 
 * @project sbm
 * @filesource gestion-transport/ligne-liste.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 sept. 2020
 * @version 2020-2.6.1
 */
var js_ligne = (function() {
	$(document).ready(function() {
		$("#liste-inner input[type=checkbox][name=selection]").change(function() {
			var id = $(this).attr('data-id');
			var action = ($(this).is(':checked')) ? 'check' : 'uncheck';
			$.ajax({
				url: '/sbmajaxtransport/' + action + 'selectionligne/ligneId:' + id,
				success: function(data) {
					if (data['success'] == 0) {
						alert(data['cr']);
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + " " + thrownError);
				}
			});
		});
	});
	return {
		"via": function(visible) {
			if (visible) {
				$(".via").show();
			} else {
				$(".via").hide();
			}
		}
	}
})();