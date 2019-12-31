/**
 * scripts des pages sbm-parent/index/index.phtml
 * 
 * @project sbm
 * @filesource parent/index.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 fév. 2019
 * @version 2019-2.4.7
 */
$(document).ready(function($) {
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