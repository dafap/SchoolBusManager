/**
 * Script qui gère une case à cocher "Tout cocher / Tout décocher" pour un
 * multicheckbox.
 * 
 * On lance la mise en place de la case par : multicheckbox_actions.init(id_div)
 * où
 *   id_div est l'id de la div contenant le multicheckbox. Elle est structurée comme suit : 
 *    <div id="...">
 *        <fieldset>
 *            <legend>...</legend>
 *            <label>...<input type="checkbox" class="sbm-multicheckbox"...></label> 
 *            ... 
 *        </fieldset>
 *    </div>
 * Le script rajoute un checkbox à droite de <legend> comme suit :
 *   <legend><span>...<input type="checkbox" class="checkall_box" name="checkall_box_..."></span></legend>
 *   où 
 *   ... suivant <span> est l'étiquette du multicheckbox
 *   ... dans name="checkall_box_..." est le `id_div` passé en paramètre
 *   
 * @project sbm
 * @filesource checkallbox.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 juin 2017
 * @version 2017-2.3.3
 */
var multicheckbox_actions = (function() {
	var checkboxes_sel = "input.sbm-multicheckbox:enabled";
	var id_wrapper;
	var checkboxes_changed = function() {
		var $container = $("#" + id_wrapper);
		var total_boxes = $container.find(checkboxes_sel).length;
		var checked_boxes = $container.find(checkboxes_sel + ":checked").length;
		var $checkall = $container.find("input.checkall_box");
		if (total_boxes == checked_boxes) {
			$checkall.prop({
				checked : true,
				indeterminate : false
			});
		} else if (checked_boxes > 0) {
			$checkall.prop({
				checked : true,
				indeterminate : true
			});
		} else {
			$checkall.prop({
				checked : false,
				indeterminate : false
			});
		}
	}

	$(document).ready(function() {
        $(document).on("change", checkboxes_sel, checkboxes_changed);
		$(document).on("change", "input.checkall_box", function() {
			var is_checked = $(this).is(":checked");
			$("#"+id_wrapper).find(checkboxes_sel).prop("checked", is_checked);
		});

		var legend = $("#" + id_wrapper + " fieldset legend");
		$(legend).html('<span>'
							+ $(legend).text()
							+ '<input type="checkbox" class="checkall_box" name="checkall_box_' + id_wrapper + '"></span>');

		checkboxes_changed();
	});
	return {
		"init" : function(id_div) {
			id_wrapper = id_div;
		}
	}
})();