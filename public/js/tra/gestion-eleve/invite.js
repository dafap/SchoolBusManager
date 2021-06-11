/**
 * Ensemble des scripts des pages de sbm-gestion/elevegestion/invite-*.phtml 
 * 
 * @project sbm
 * @filesource invite.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 juin 2021
 * @version 2021-2.6.2
 */

var js_invite = (function() {
	var inviteId = -1;
	function moveContent(destId, srcId) {
		$('#' + destId).append($('#' + srcId).contents().detach());
	}
	function montreMotif() {
		if ($("#demanderadio0").is(':checked')) {
			$("#inner-motifRefus").show();
		} else {
			$("#inner-motifRefus").hide();
		}
	}
	function montreOnglet(etat) {
		if (etat <= 0) {
			etat = 1;
		}
		var url = '/sbmajaxeleve/inviteformonglet'+etat+'/inviteId:'+inviteId;
		var onglet;
		$.ajax({
				url: url,
				type: 'GET',
				dataType: 'text',
				success: function(dataHtml) {
					for (var i = 1; i <=5; i++) {
						onglet = "#onglet"+i;
						$(onglet).empty();
						if (i == etat) {
							$(onglet).append(dataHtml);
						}
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + " " + thrownError);
					$('html', 'body').css('cursor', 'auto');
				}
			});
	}
	$(document).ready(function($) {
		$("input[name=demande]").on('change', function() {
			montreMotif();
		});
		$("a[href='#onglet1']").click(function() {
			montreOnglet(1);
		});
		$("a[href='#onglet2']").click(function() {
			montreOnglet(2);
		});
		$("a[href='#onglet3']").click(function() {
			montreOnglet(3);
		});
		$("a[href='#onglet4']").click(function() {
			montreOnglet(4);
		});
		$("a[href='#onglet5']").click(function() {
			montreOnglet(5);
		});
		$("#invite-codePostal").on('keyup', function() {
			var valeur = $('#invite-codePostal').val();
			if (valeur.length == 5) {
				$.ajax({
					url: '/sbmajaxparent/getcommunesforselect/codePostal:' + valeur,
					dataType: 'json',
					success: function(dataJson) {
						$('#invite-communeId').empty();
						$('#invite-communeId').append('<option value>Choisir dans la liste</option>');
						if (dataJson.success == 1) {
							$.each(dataJson.data, function(k, d) {
								$('#invite-communeId').append(
									'<option value="' + d + '">' + k
									+ '</option>');
							});
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(xhr.status + " " + thrownError);
						$('html', 'body').css('cursor', 'auto');
					}
				});
			}
		});
	});
	return {
		"init": function(id) {
			inviteId = id;
			montreMotif();
		},
		"montreOnglet": function(etat) {
			montreOnglet(etat);
		}
	}
})();