/**
 * scripts de la page sbm-gestion/transport/etabissement-service-ajout.phtml
 * 
 * @project sbm
 * @filesource gestion-transport/etabissement-service-ajout.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 fév. 2019
 * @version 2019-2.4.7
 */
$(document).ready(function($) {
  // adapte le select aux stations de ce circuit
  $("#serviceIdElement").change(function() {
    var valeur = $(this).val();
    if (valeur != '') {
        $.ajax({
				url : '/sbmajaxtransport/getcircuitstations/serviceId:' + valeur,
				dataType: 'json',
				success : function(dataJson) {
					$('#stationIdElement').empty();
					if (dataJson.success==1) {
					    $.each(dataJson.data, function(k, d) {
	                        $('#stationIdElement').append('<option value="' + d + '">' + k + '</option>');
	                    });
	                }
				},
				error : function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + " " + thrownError);
				}
			});
	}
  });
});