/**
 * scripts de la page sbm-gestion/eleve/responsable-lite.phtml
 * 
 * @project sbm
 * @filesource gestion-eleve/responsable-liste.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 05 juin 2019
 * @version 2019-2.5.0
 */
var js_selection = (function(){
	var liste = "#liste-inner table.responsables tbody tr td.selection";
	var checkboxes_sel = liste+" input[type=checkbox]";
	var fiches_selectionnees = function(){
		var actions = "#liste-footer div.selection";
		var help = "#liste-footer div.sbm-description";
		var checked_boxes = $(liste).find("input[type=checkbox]:checked").length;
		if (checked_boxes > 0){
			$(help).hide();
			$(actions).show();
		} else {
			$(actions).hide();
			$(help).show();
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