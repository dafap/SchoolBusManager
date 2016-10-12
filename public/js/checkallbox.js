/**
 * Script qui gère une case à cocher "Tout cocher / Tout décocher" pour un
 * multicheckbox.
 * 
 * On lance la mise en place de la case par : multicheckbox_actions.init(id_form, id_div)
 * où
 *   id_form est l'id du formulaire contenant le multicheckbox</li>
 *   id_div est l'id de la div contenant le multicheckbox. Elle est structurée comme suit : 
 *    <div id="...">
 *        <fieldset>
 *            <legend>...</legend>
 *            <label>...</label> 
 *            ... 
 *        </fieldset>
 *    </div>
 * 
 * @project sbm
 * @filesource checkallbox.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 oct. 2016
 * @version 2016-2.2.1
 */
var multicheckbox_actions = (function() {
	var checkboxes_sel = "input.sbm-multicheckbox:enabled";
	var id_container;
	var id_wrapper;
	var checkboxes_changed = function() {
		var $form = $("#" + id_container);
		var total_boxes = $form.find(checkboxes_sel).length;
		var checked_boxes = $form.find(checkboxes_sel + ":checked").length;
		var $checkall = $form.find("input.checkall_box");
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
			$(this.form).find(checkboxes_sel).prop("checked", is_checked);
		});

		var legend = $("#" + id_wrapper + " fieldset legend");
		$(legend).html('<span>'
							+ $(legend).text()
							+ '<input type="checkbox" class="checkall_box"></span>');

		checkboxes_changed();
	});
	return {
		"init" : function(id_form, id_div) {
			id_container = id_form;
			id_wrapper = id_div;
		}
	}
})();