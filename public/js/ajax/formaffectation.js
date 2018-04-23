/**
 * scripts de la page de sbm-ajax/eleve/formaffectation.phtml
 * 
 * @project sbm
 * @filesource formaffectation.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 avril 2018
 * @version 2018-2.4.1
 */
var is_xmlhttprequest;
var formaffectation;
var urlform;
var station1Id;
var station2Id;
var btnclick;
var affectation = (function() {
  function setStationsValueOptions(valeur, station1Id, station2Id) 
  {
      $.ajax({
	        url : '/sbmajaxeleve/getstationsforselect/serviceId:'+valeur,
	        dataType: 'json',
	        success : function(dataJson)
	        {
	            $("#affectation-station1Id").empty();
	            $("#affectation-station2Id").empty();
	            if (dataJson.success) {
	                $.each(dataJson.data, function(k, d) 
	                {
	                    if (station1Id == k) {
	                        $('#affectation-station1Id').append('<option value="' + k + '" selected>' + d + '</option>'); 
	                    } else { 
	                        $('#affectation-station1Id').append('<option value="' + k + '">' + d + '</option>'); 
	                    } 
	                    if (station2Id == k) { 
	                        $('#affectation-station2Id').append('<option value="' + k + '" selected>' + d + '</option>');
	                    } else {
	                        $('#affectation-station2Id').append('<option value="' + k + '">' + d + '</option>');
	                    }
	                });
	            }
	        },
	        error : function(xhr, ajaxOptions, thrownError) 
	        {
		        alert(xhr.status + " " + thrownError);
		    }
	  });
  }  
  $(formaffectation + ' input[name=cancel]').click(function(){btnclick='cancel';});
  $(formaffectation + ' input[name=submit]').click(function(){btnclick='submit';});
  $("form#decision").submit(
    function() 
    {
	    // if not call by ajax submit to showformAction
	    if (is_xmlhttprequest == 0)
            return true;
		// if by ajax check by ajax : formaffectationvalidateAction
		var trajet = $(formaffectation + ' input[name=trajet]').val();
		var demande = 'demandeR'+trajet;
		var data = {
		    'csrf' : $(formaffectation + ' input[name=csrf]').val(),
			'eleveId' : $(formaffectation + ' input[name=eleveId]').val(),
			'millesime' : $(formaffectation + ' input[name=millesime]').val(),
			'trajet' : trajet,
			'jours' : $(formaffectation + ' input[name=jours]').val(),
			'sens' : $(formaffectation + ' input[name=sens]').val(),
			'correspondance' : $(formaffectation + ' input[name=correspondance]').val(),
			'responsableId' : $(formaffectation + ' input[name=responsableId]').val(),
			'demandeR1' : $(formaffectation + ' input[name=demandeR1]').val(),
			'demandeR2' : $(formaffectation + ' input[name=demandeR2]').val(),
			'service1Id' : $(formaffectation + ' select[name=service1Id]').val(),
			'service2Id' : $(formaffectation + ' select[name=service2Id]').val(),
			'station1Id' : $(formaffectation + ' select[name=station1Id]').val(),
			'station2Id' : $(formaffectation + ' select[name=station2Id]').val(),
			'op' : $(formaffectation + ' input[name=op]').val(),
			'submit' : btnclick	
		}
		$.post(urlform, data, function(itemJson) {		
			$("#winpopup").dialog('close');
			js_edit.majBlockAffectations(trajet);
			//alert(itemJson.cr);						
        }, 'json');
        return false;
	});
	$("#affectation-service1Id").on('change',function() 
	{
	    var valeur = $(this).val();
	    setStationsValueOptions(valeur, null, null);
	});
	return {
	    "init" : function(station1Id, station2Id) 
	    {
	        var valeur = $("#affectation-service1Id option:selected").val();
	        if (valeur) {
	            setStationsValueOptions(valeur, station1Id, station2Id);
	        }
	    }
	}
})();