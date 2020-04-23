/**
 * Chech uncheck selection dans la liste du plugin paybox
 * 
 * @project sbm
 * @package SbmPaiement - Plugin/PayBox
 * @filesource formulaire.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 avr.2020
 * @version 2020-2.6.0
 */
$(document).ready(function() {
  $("input[type=checkbox][name=selection]").change(function() {
    var action = ($(this).is(':checked'))?'check':'uncheck';
    var plugin = $(this).attr('data-plugin');
    var id = $(this).attr('data-id');
    $.ajax({
				url : '/sbmajaxfinance/'+action+'selectionplateforme/'+plugin+'Id:'+id,
				success : function(data) {},
				error : function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + " " + thrownError);
				}
			});
  });
});