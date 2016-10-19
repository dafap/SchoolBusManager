/**
 * scripts dela page de sbm-gestion/eleve-gestion/affecter.phtml
 * 
 * @project sbm
 * @filesource affecter.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 août 2016
 * @version 2016-2.1.10
 */

var js_affecter =(function(){
	
    $(document).ready(function($) {
        $("#affectation-service1Id").on('change',function() {
	        var valeur = $(this).val();
	        $.ajax({
	            url: '/sbmajaxeleve/getstationsforselect/serviceId:'+valeur,
	            dataType: 'json',
	            success: function(dataJson){
	                $("#affectation-station1Id").empty();
	                $("#affectation-station2Id").empty();
	                if (dataJson.success) {
	                    $.each(dataJson.data, function(k, d) {
	                        $('#affectation-station1Id').append('<option value="' + k + '">' + d + '</option>');
	                        $('#affectation-station2Id').append('<option value="' + k + '">' + d + '</option>');
	                    });
	                }
	            },
	            error: function(xhr, ajaxOptions, thrownError) {
			        alert(xhr.status + " " + thrownError);
			    }
	        });
	    });
        $("#decision_derogation").click(function(){
        	js_affecter.adaptedecision();
        });
        $("#decision_accordR").click(function(){
        	js_affecter.adaptedecision();
        });
    });
    return {
    	"adaptedecision": function ()
    	{
    		if ($("#decision_district").is(":checked")){
    			$("#row-decision_derogation").hide();
    			$("#row-decision_motifDerogation").hide();
    		} else {
    			$("#decision_derogation").show();
    			if ($("#decision_derogation").is(":checked")){
    				$("#row-decision_motifDerogation").show();
    				$("#row-decision_accordR").show();
    				$("#decision_accordR").prop("checked", true); 
    			} else {
    				$("#row-decision_motifDerogation").hide();
    				$("#decision_accordR").removeAttr("checked"); 
    				$("#row-decision_accordR").hide();
    			}
    		}
    		if ($("#decision_accordR").is(":checked")) {
    			$("#row-decision_motifRefusR").hide();
    		} else {
    			$("#row-decision_motifRefusR").show();
    		}
    	}
    };
})();