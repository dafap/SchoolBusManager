/**
 * scripts de la page de sbm-ajax/eleve/formpaiement.phtml
 * 
 * @project sbm
 * @filesource formpaiement.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 avril 2018
 * @version 2018-2.4.1
 */
var is_xmlhttprequest;
var urlform;
var btnclick;
$(function() {
  $("#formpaiement input[name=cancel]").click(function(){btnclick='cancel';});
  $("#formpaiement input[name=submit]").click(function(){btnclick='submit';});
  $("form#formpaiement").submit(function() {
    if (is_xmlhttprequest == 0) return true;
    // ici c'est de l'ajax
    var gratuit = $('#formpaiement input[name=gratuit]:checked').val();
    var organismeId = $('#formpaiement select[name=organismeId] option:selected').val();
    var data = {
      'csrf' : $('#formpaiement input[name=csrf]').val(),
	  'eleveId' : $('#formpaiement input[name=eleveId]').val(),
	  'gratuit' : gratuit,
	  'organismeId' : organismeId,
	  'submit' : btnclick	
    };
    $.post(urlform, data, function(itemJson) {		
			$("#winpopup").dialog('close');
			js_edit.majPaiement(gratuit);						
        }, 'json');
    return false;
  });
  $("#gratuitradio0").on('change',function() {
    $("#formpaiement-organismeId").hide();
  });
  $("#gratuitradio1").on('change',function() {
    $("#formpaiement-organismeId").hide();    
  });
  $("#gratuitradio2").on('change',function() {
    $("#formpaiement-organismeId").show();
  });
});