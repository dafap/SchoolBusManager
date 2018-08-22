/**
 * Ensemble des scripts des formulaires winpopup de rpi-edit.phtml
 * 
 * @project sbm
 * @filesource ajax/formrpi.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 août 2018
 * @version 2018-2.4.2
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
			js_edit.flashMessenger(crJson);
			if (btnclick=='submit') {
				js_edit.majTableauCommunes(rpiId);
			}
		}, 'json');
		return false;
	});
	
	$('form#rpi-etablissement input[name=cancel]').click(function(){
		btnclick = 'cancel';
	});
	$('form#rpi-etablissement input[name=submit]').click(function(){
		btnclick = 'submit';
	});
	$('form#rpi-etablissement').submit(function(event){
		event.preventDefault;
		if (is_xmlhttprequest == 0)
			return true;
		var op = $('#rpi-etablissement input[name="op"]').val();
		var etablissementId;
		if (op=='add'){
			etablissementId = $('#rpi-etablissement select[name=etablissementId]').val();
		} else {
			etablissementId = $('#rpi-etablissement input[name=etablissementId]').val();
		}
		var rpiId = $('#rpi-etablissement input[name=rpiId]').val();
		var data = {
			'op' : op,
			'rpiId' : rpiId,
			'etablissementId' : etablissementId,
			'submit' : btnclick
		};
		$.post(this.action, data, function(crJson) {
			$("#winpopup").dialog('close');
			js_edit.flashMessenger(crJson);
			if (btnclick=='submit') {
				js_edit.majTableauEtablissements(rpiId);
			}
		}, 'json');
		return false;
	});
	
	$('form#rpi-classe input[name=cancel]').click(function(){
		btnclick = 'cancel';
	});
	$('form#rpi-classe input[name=submit]').click(function(){
		btnclick = 'submit';
	});
	$('form#rpi-classe').submit(function(event){
		event.preventDefault;
		if (is_xmlhttprequest == 0)
			return true;
		var op = $('#rpi-classe input[name="op"]').val();
		var niveau = $('#rpi-classe input[name="niveau"]').val();
		var classeId;
		if (op=='add'){
			classeId = $('#rpi-classe select[name=classeId]').val();
		} else {
			classeId = $('#rpi-classe input[name=classeId]').val();
		}
		var etablissementId = $('#rpi-classe input[name=etablissementId]').val();
		var data = {
			'op' : op,
			'niveau' : niveau,
			'etablissementId' : etablissementId,
			'classeId' : classeId,
			'submit' : btnclick
		};
		$.post(this.action, data, function(crJson) {
			$("#winpopup").dialog('close');
			js_edit.flashMessenger(crJson);
			if (btnclick=='submit') {
				js_edit.majTableauClasses(etablissementId);
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