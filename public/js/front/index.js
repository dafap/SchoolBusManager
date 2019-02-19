/**
 * scripts des pages sbm-front/index/index-pendant.phtml et
 * sbm-front/index/index-après.phtml
 * 
 * @project sbm
 * @filesource front/index.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 fév. 2019
 * @version 2019-2.4.7
 */
$(document).ready(function($) {
	$("#help-delai").on("click", function() {
		$("#help-delai").hide();
		$("#help-delai-content").show();
	});
	$("#help-delai-content").on("click", function() {
		$("#help-delai-content").hide();
		$("#help-delai").show();
	});
	$("#help-autre").on("click", function() {
		$("#inscription").hide();
		$("#help-autre-content").show();
	});
	$("#help-retour").on("click", function() {
		$("#help-autre-content").hide();
		$("#inscription").show();
	});
});