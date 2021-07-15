/**
 * Chech uncheck selection dans la liste du plugin payfip
 * 
 * @project sbm
 * @package SbmPaiement - Plugin/PayBox
 * @filesource formulaire.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 juil. 2021
 * @version 2021-2.5.13
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