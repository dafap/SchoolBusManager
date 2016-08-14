/**
 * scripts dela page de sbm-gestion/eleve/eleve-ajout.phtml
 * 
 * @project sbm
 * @filesource ajout.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 août 2016
 * @version 2016-2.1.10
 */

var js_ajout = (function() {
	$(document).ready(function($) {
		$("#eleve-ga").click(function() {
			js_ajout.montreResponsable2($(this).is(":checked"));
		});
	});
	return {
		"montreResponsable2" : function(voir) {
			if (voir) {
				$("#wrapper-responsable2Id").show();
			} else {
				$("#wrapper-responsable2Id").hide();
			}
		}
	};
})();
