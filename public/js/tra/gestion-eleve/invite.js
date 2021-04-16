/**
 * Ensemble des scripts des pages de sbm-gestion/elevegestion/correspondant-*.phtml 
 * 
 * @project sbm
 * @filesource deplacement.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 oct. 2020
 * @version 2020-2.6.1
 */
$(function() {
	function montreMotif() {
		if ($("#demanderadio0").is(':checked')) {
			$("#inner-motifRefus").show();
		} else {
			$("#inner-motifRefus").hide();
		}
	}
	$("input[name=demande]").on('change', function() {
		montreMotif();
	});
	return {
		"init": function() { montreMotif(); }
	}
});