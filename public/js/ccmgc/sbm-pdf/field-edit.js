/**
 * scripts de la page de sbm-pdf/pdf/field-edit.phtml
 * 
 * @project sbm
 * @filesource field-edit.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 jan. 2019
 * @version 2019-2.4.6
 */

var js_nature = (function() {
	function adapte() {
		if ($("#field-nature").is(":checked")) {
			$("#label").hide();
			$("#photo").show();
		} else {
			$("#label").show();
			$("#photo").hide();
		}
	}
	$(function() {
		$("input[type=radio][name=nature]").change(function() {
			adapte();
		});
	});
	return {
		"init" : function() {
			adapte();
		}
	}
})();