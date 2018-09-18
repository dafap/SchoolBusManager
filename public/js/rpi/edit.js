/**
 * Ensemble des scripts des pages de sbm-admin/index/rpi-edit.phtml
 * 
 * @project sbm
 * @filesource rpi/edit.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 août 2018
 * @version 2018-2.4.2
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