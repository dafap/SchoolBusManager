/**
 * Ensemble des scripts des formulaires winpopup de rpi-edit.phtml
 * 
 * @project sbm
 * @filesource ajax/formrpi.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 juin. 2018
 * @version 2018-2.4.1
 */
var formrpi = (function() {
	var is_xmlhttprequest = 1;
	var btnclick;
	$('form#rpi-commune input[name=cancel]').click(function() {
		btnclick = 'cancel';
	});
	$('form#rpi-commune input[name=submit]').click(function() {
		btnclick = 'submit';
	});
	$('form#rpi-commune').submit(function(event) {
		event.preventDefault;
		if (is_xmlhttprequest == 0)
			return true;
		var op = $('#rpi-commune input[name="op"]').val();
		var communeId;
		if (op=='add'){
			communeId = $('#rpi-commune select[name=communeId]').val();
		} else {
			communeId = $('#rpi-commune input[name=communeId]').val();
		}
		var rpiId = $('#rpi-commune input[name=rpiId]').val();
		var data = {
			'op' : op,
			'rpiId' : rpiId,
			'communeId' : communeId,
			'submit' : btnclick
		};
		$.post(this.action, data, function(crJson) {
			$("#winpopup").dialog('close');
			js_edit.flashMessenger(crJson)
			if (btnclick=='submit') {
				js_edit.majTableauCommunes(rpiId);
			}
		}, 'json');
		return false;
	});
	return {
		"init" : function(x) {
			is_xmlhttprequest = x;
		}
	}
})();