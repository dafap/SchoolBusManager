/**
 * Ensemble des scripts de la page sbm-admin/index/rpi-edit.phtml
 * 
 * @project sbm
 * @filesource rpi/edit.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 fév. 2019
 * @version 2019-2.4.7
 */
var js_edit = (function() {
	function showDialog(href, title) {
		$("#winpopup").empty();
		$("#winpopup").dialog({
			draggable : true,
			modal : true,
			autoOpen : false,
			height : 400,
			width : 600,
			resizable : false,
			title : title
		});
		$("#winpopup").load(href);
		$("#winpopup").dialog("open");
	}
	$(document).ready(function($) {
		$("#rpi-communes").on('click', "i[data-button=btncommune]", function() {
			var href = '/sbmajaxadmin/rpicommuneform'
				+ $(this).attr('data-href');
			showDialog(href, $(this).attr('title'));
		});
		$("#rpi-etablissements").on('click', "i[data-button=btnecole]", function() {
			var href = '/sbmajaxadmin/rpietablissementform'
				+ $(this).attr('data-href');
			showDialog(href, $(this).attr('title'));
		});
		$("#rpi-etablissements").on('click', "i[data-button=btnclasse]", function() {
			var niveau = $('input[name="niveau[]"][type=checkbox]:checked').map(function(_,el){
				return $(el).val();
			}).get();
			var href = '/sbmajaxadmin/rpiclasseform/niveau:'
				+ encodeURIComponent(JSON.stringify(niveau))
				+ $(this).attr('data-href');
			showDialog(href, $(this).attr('title'));
		});
	});
	return {
		"init" : function() {
		},
		"flashMessenger" : function(crRpivalidate) {
			var css;
			if (crRpivalidate.success == 1){
				css = 'success';
			} else {
				if (crRpivalidate.success == 2) {
					css = 'info';
				} else {
					css = 'warning';
				}				
			}
			var htmlString = '<ul class="' + css + '"><li>' + crRpivalidate.cr + '</li></ul>';
			$('div.flashMessenger').empty().html(htmlString);
		},
		"majTableauCommunes" : function(rpiId) {
			$.ajax({
				url:'/sbmajaxadmin/rpicommunetable/rpiId:' + rpiId,
				dataType:'html',
				success:function(data){
					$('table#rpi-communes').empty().html(data);
				},
				error:function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + ' ' + thrownError);
				}
			});
		},
		"majTableauClasses" : function(etablissementId) {
			$.ajax({
				url:'/sbmajaxadmin/rpiclassetable/etablissementId:' + etablissementId,
				dataType:'html',
				success:function(data){
					$('table#rpi-classes-'+etablissementId).empty().html(data);
				},
				error:function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + ' ' + thrownError);
				}
			});
		},
		"majTableauEtablissements" : function(rpiId) {
			$.ajax({
				url:'/sbmajaxadmin/rpietablissementtable/rpiId:' + rpiId,
				dataType:'html',
				success:function(data){
					$('table#rpi-etablissements').empty().html(data);
				},
				error:function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + ' ' + thrownError);
				}
			});
		}
	}
})();

/**
 * Script pour les formulaires en popup dialog
 */
function js_form() {
	var is_xmlhttprequest = 1;
	var btnclick;
	$('#rpicommune-cancel').click(function() {
		btnclick = 'cancel';
	});
	$('#rpicommune-submit').click(function() {
		btnclick = 'submit';
	});
	$('#rpicommune-form').submit(function(event) {
		event.preventDefault;
		if (is_xmlhttprequest == 0)
			return true;
		var op = $('#rpicommune-form input[name="op"]').val();
		var communeId;
		if (op=='add'){
			communeId = $('#rpicommune-form select[name=communeId]').val();
		} else {
			communeId = $('#rpicommune-form input[name=communeId]').val();
		}
		var rpiId = $('#rpicommune-form input[name=rpiId]').val();
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
	
	$('#rpietablissement-cancel').click(function(){
		btnclick = 'cancel';
	});
	$('#rpietablissement-submit').click(function(){
		btnclick = 'submit';
	});
	$('#rpietablissement-form').submit(function(event){
		event.preventDefault;
		if (is_xmlhttprequest == 0)
			return true;
		var op = $('#rpietablissement-form input[name="op"]').val();
		var etablissementId;
		if (op=='add'){
			etablissementId = $('#rpietablissement-form select[name=etablissementId]').val();
		} else {
			etablissementId = $('#rpietablissement-form input[name=etablissementId]').val();
		}
		var rpiId = $('#rpietablissement-form input[name=rpiId]').val();
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
	
	$('#rpiclasse-cancel').click(function(){
		btnclick = 'cancel';
	});
	$('#rpiclasse-submit').click(function(){
		btnclick = 'submit';
	});
	$('#rpiclasse-form').submit(function(event){
		event.preventDefault;
		if (is_xmlhttprequest == 0)
			return true;
		var op = $('#rpiclasse-form input[name="op"]').val();
		var niveau = $('#rpiclasse-form input[name="niveau"]').val();
		var classeId;
		if (op=='add'){
			classeId = $('#rpiclasse-form select[name=classeId]').val();
		} else {
			classeId = $('#rpiclasse-form input[name=classeId]').val();
		}
		var etablissementId = $('#rpiclasse-form input[name=etablissementId]').val();
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
			btnclick = '';
		}
	}
};