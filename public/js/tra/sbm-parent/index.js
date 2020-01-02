/**
 * scripts des pages sbm-parent/index/index.phtml
 * 
 * @project sbm
 * @filesource parent/index.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 02 jan. 2020
 * @version 2020-2.5.4
 */
$(document).ready(function($) {
	$("*[id^='help-'").on("click", function() {
		var id = $(this).attr('id');
		id = id.replace('help', 'content');
		$(this).hide();
		$("#"+id).show();
	});
	$("div[id^='content-'].retour").on("click", function() {
		var id = $(this).attr('id');
		id = id.replace('content', 'help');
		$(this).hide();
		$("#"+id).show();
	});
	$("div[id^='content-'] .retour").on("click", function() {
		var id = $(this).parents("div").attr('id');
		$("#"+id).hide();
		id = id.replace('content', 'help');
		$("#"+id).show();
	});
	$("#help-preinscrits").trigger("click");
	$("#help-preinscrits").on("click", function() {
		var content = $("#help-preinscrits-content").html();
		$("#winpopup").dialog({
			draggable : true,
			modal : true,
			autoOpen : false,
			height : 400,
			width : 610,
			resizable : false,
			title : $(this).attr('title')
		});
		$("#winpopup-content").html(content);
		$("#winpopup").dialog("open");
	});
});