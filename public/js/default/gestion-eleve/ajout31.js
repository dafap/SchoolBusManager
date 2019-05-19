/**
 * Scripts dela page de sbm-gestion/eleve/eleve-ajout31.phtml
 * 
 * @project sbm
 * @filesource ajout31.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 août 2016
 * @version 2016-2.1.10
 */

var js_ajout31 = (function() {
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
		$("#eleve-anneeComplete").click(function(){
			montreDebutFin($(this).is(":checked"));
		});
		$("#eleve-derogation").click(function(){
			montreMotifDerogation($(this).is(":checked"));
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
					    if (data.success) {
					      $(myid).val(data.distance);
					      $(myid).css('cursor', 'auto');
					    } else {
					        alert(data.cr);
					    }
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
			montreMotifDerogation($("#eleve-derogation").is(":checked"));
		}
	};
})();