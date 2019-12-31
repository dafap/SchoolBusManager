/**
 * Scripts dela page de sbm-gestion/eleve/eleve-ajout31.phtml
 * 
 * @project sbm
 * @filesource ajout31.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 03 juin 2019
 * @version 2019-2.5.0
 */

$(function(){
	var r = $('input[type="radio"].input-error').parent().parent().parent();
	r.addClass('input-error');
	var c = $('input[type="checkbox"].input-error').parent().parent().parent();
	c.addClass('input-error');
});

var js_ajout31 = (function() {
	function estTropPres() {
		return ($("#eleve-distanceR1").val() < 1) && ($("#eleve-distanceR2").val() < 1);
	}
	function montreDebutFin(ancomplet) {
		if (ancomplet) {
			$("#wrapper-dateDebut").hide();
			$("#wrapper-dateFin").hide();
		} else {
			$("#wrapper-dateDebut").show();
			$("#wrapper-dateFin").show();
		}
	}
	function montreMotifDerogation(derogation) {
		if (derogation) {
			$("#wrapper-motifDerogation").show();
		} else {
			$("#wrapper-motifDerogation").hide();
		}
	}
	// script exécuté au chargement
	$(document).ready(function($) {
		// adapte le select de la classe à l'établissement
		$("#eleve-etablissementId").change(function(){
			var classeId = $("#eleve-classeId").val();
			var etablissementId = $(this).val();
			$.ajax({
				url : '/sbmajaxeleve/getclassesforselect/etablissementId:' + etablissementId,
				dataType : 'json',
				success : function(dataJson) {
					if (dataJson.success == 1) {
						var select = $("#eleve-classeId");
						select.empty();
						$.each(dataJson.data, function(niveau, descripteur){
							var optgroup = $("<optgroup></optgroup>");
							optgroup.attr('label',descripteur.label);
							$.each(descripteur.options, function(id, libelle){
								var option = $("<option></option>");
								option.val(id);
								option.text(libelle);
								optgroup.append(option);
							});
							select.append(optgroup);
						});
					} else {
						alert(dataJson.cr);
					}
				},
				error : function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + " " + thrownError);
				}
			});
		});
		$("#eleve-anneeComplete").click(function(){
			montreDebutFin($(this).is(":checked"));
		});
		$("#eleve-derogation").change(function() {
			montreMotifDerogation($(this).find("option:selected").val() > 0);
		});
		$("input[type='text'][name^='distanceR']").dblclick(function(){
		    $(this).css("cursor", "wait");
		    var myid = '#'+$(this).attr('id');
		    var name = $(this).attr('name');
		    var n = 1;
		    if (name.indexOf('1') == -1) {
		        n = 2;
		    }
		    var id = '#eleve-responsable'+n+'Id';
		    var responsableid = $(id).val();
		    var etablissementid = $("#eleve-etablissementId").val();
		    var args = 'etablissementId:'+etablissementid+'/responsableId:'+responsableid;
		    $.ajax({
					url : '/sbmajaxeleve/donnedistance/'+args,
					type: 'GET',
					dataType: 'json',
					success : function(data){
						$(myid).val(data.distance);
						$(myid).css('cursor','auto');
						montreDerogation();
		            },
					error : function(xhr, ajaxOptions, thrownError) {
							alert(xhr.status + " " + thrownError);
							$(myid).css('cursor','auto');
					}
			});
		});
	});
	return {
		"init" : function() {
			montreDebutFin($("#eleve-anneeComplete").is(":checked"));
			montreMotifDerogation($("#eleve-derogation").find("option:selected").val() > 0);
		}
	};
})();