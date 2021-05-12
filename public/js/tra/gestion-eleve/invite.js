/**
 * Ensemble des scripts des pages de sbm-gestion/elevegestion/invite-*.phtml 
 * 
 * @project sbm
 * @filesource invite.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2021
 * @version 2021-2.6.1
 */
$(function() {
	$("#tabs").tabs();
});
$(function() {
	function moveContent(destId, srcId) {
		$('#'+destId).append($('#'+srcId).contents().detach());
	}
	function montreMotif() {
		if ($("#demanderadio0").is(':checked')) {
			$("#inner-motifRefus").show();
		} else {
			$("#inner-motifRefus").hide();
		}
	}
	$("input[name=demande]").on('change', function() {
		montreMotif();
	});
	$("#onglet2").click(function() {
		$.ajax({
			url: '/sbmajaxeleve/getelevesvalueoptions',
			dataType: 'json',
			success: function(dataJson) {
				$('#invite-eleveId').empty();
				$('#invite-eleveId').append('<option value>Choisir dans la liste</option>');
				if (dataJson.success == 1) {
					$.each(dataJson.data, function(k, d) {
						$('#invite-eleveId').append(
							'<option value="' + d + '">' + k + '</option>');
					});
				} else {
					alert(dataJson.msg);
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(xhr.status + " " +   thrownError);
				$('html', 'body').css('cursor', 'auto');
			}
		})
	});
	$("#onglet3").click(function() {
		$.ajax({
			url: '/sbmajaxeleve/getresponsablesvalueoptions',
			dataType: 'json',
			success: function(dataJson) {
				$('#invite-responsableId').empty();
				$('#invite-responsableId').append('<option value>Choisir dans la liste</option>');
				if (dataJson.success == 1) {
					if (dataJson.success == 1) {
						$.each(dataJson.data, function(k, d) {
							$('#invite-responsableId').append(
								'<option value="' + d + '">' + k + '</option>');
						});
					}
				} else {
					alert(dataJson.msg);
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(xhr.status + " " + thrownError);
				$('html', 'body').css('cursor', 'auto');
			}
		});
	});
	$("#onglet4").click(function() {
		$.ajax({
			url: '/sbmajaxeleve/getorganismesvalueoptions',
			dataType: 'json',
			success: function(dataJson) {
				$('#invite-organismeId').empty();
				$('#invite-organismeId').append('<option value>Choisir dans la liste</option>');
				if (dataJson.success == 1) {
					if (dataJson.success == 1) {
						$.each(dataJson.data, function(k, d) {
							$('#invite-organismeId').append(
								'<option value="' + d + '">' + k + '</option>');
						});
					}
				} else {
					alert(dataJson.msg);
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(xhr.status + " " + thrownError);
				$('html', 'body').css('cursor', 'auto');
			}
		});
	});
	$("#onglet5").click(function() { 
		
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
	return {
		"init": function() { montreMotif(); }
	}
});