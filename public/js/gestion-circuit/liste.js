/**
 * scripts de la page sbm-gestion/transport/circuit-lite.phtml
 * 
 * @project sbm
 * @filesource gestion-circuit/edit.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 oct. 2016
 * @version 2016-2.2.1
 */
var js_selection = (function(){
	var liste = "#liste-inner table.circuits tbody tr td.selection";
	var checkboxes_sel = liste+"input[type=checkbox]";
	var fiches_selectionnees = function(){
		var actions = "#liste-footer div.selection";
		var checked_boxes = $(liste).find("input[type=checkbox]:checked").length;
		if (checked_boxes > 0){
			$(actions).show();
		} else {
			$(actions).hide();
		}
	}
	$(document).ready(function() {
		$(document).on("change", checkboxes_sel, fiches_selectionnees);
		fiches_selectionnees();
	});
	return {
		"actions": fiches_selectionnees
	}
})();