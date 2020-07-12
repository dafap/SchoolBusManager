/**
 * Ensemble des scripts des pages de sbm-gestion/elevegestion/deplacement.phtml
 * 
 * @project sbm
 * @filesource deplacement.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 juil. 2020
 * @version 2020-2.6.0
 */
$(function() {
	function setStationsValueOptions(valeur1, valeur2) {
		$.ajax({
			url : '/sbmajaxtransport/getstationsfordeplacement/initial:'
					+ valeur1 + '/final:' + valeur2,
			dataType : 'json',
			success : function(dataJson) {
				$("#stationId").empty();
				if (dataJson.success) {
					$.each(dataJson.data,
							function(d, k) {
								// key/value échangées pour le conserver le tri
								$('#stationId').append(
										'<option value="' + k + '">' + d
												+ '</option>');
							});
				} else {
					alert(dataJson.cr);
				}
			},
			error : function(xhr, ajaxOptions, thrownError) {
				alert(xhr.status + " " + thrownError);
			}
		});
	}

	$("input[name=carte]").on('change', function() {
		if ($("#carteoption1").is(':checked')) {
			$("div.row-inner.cartelot").show();
		} else {
			$("div.row-inner.cartelot").hide();
		}
	});
	$("#servicefinal").on('change', function() {
		var valeur1 = $("input[name=serviceinitial]").val();
		var valeur2 = $(this).val();
		setStationsValueOptions(valeur1, valeur2);
	});
	
	$("div.row-inner.cartelot").hide();
});