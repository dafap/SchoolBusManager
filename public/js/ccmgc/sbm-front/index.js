/**
 * scripts des pages sbm-front/index/index-pendant.phtml et
 * sbm-front/index/index-après.phtml
 * 
 * @project sbm
 * @filesource front/index.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 juin 2019
 * @version 2019-2.4.7
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
});