/**
 * scripts dela page de sbm-gestion/eleve/eleve-ajout.phtml
 * 
 * @project sbm
 * @filesource ajout.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 août 2016
 * @version 2016-2.2.0
 */

var js_ajout = (function() {
	function montreResponsable2(voir) {
	    if (voir) {
	    	$("#wrapper-responsable2Id").show();
		} else {
			$("#wrapper-responsable2Id").hide();
		}
	}
	$(document).ready(function($) {
		$("#eleve-ga").click(function() {
			montreResponsable2($(this).is(":checked"));
		});
	});
	return {
		"montreGa" : function(){
			montreResponsable2($("#eleve-ga").is(":checked"));
		}
		
	};
})();
