/**
 * Montre la div si le checkbox est coché
 */

$(function() {
	$("#responsable-demenagement").change(
			function() {
				var demenagement = $(this).is(":checked");
				$("#responsable-edit-blocAncien").toggle();
				if (demenagement) {
					$("#responsable-ancienAdresseL1").val(
							$("#responsable-adresseL1").val());
					$("#responsable-ancienAdresseL2").val(
							$("#responsable-adresseL2").val());
					$("#responsable-ancienCodePostal").val(
							$("#responsable-codePostal").val());
					$("#responsable-ancienCommuneId").val(
							$("#responsable-communeId").val());
				} else {
					$("#responsable-ancienAdresseL1").val('');
					$("#responsable-ancienAdresseL2").val('');
					$("#responsable-ancienCodePostal").val('');
					$("#responsable-ancienCommuneId").removeAttr('selected');
				}
			});
	// adapte le select de la commune au code postal
	  $("#responsable-codePostal").on('keyup', function() {
	    var valeur = $(this).val();
	    if (valeur.length==5) {
	    $.ajax({
					url : '/sbmajaxparent/getcommunesforselect/codePostal:' + valeur,
					dataType: 'json',
					success : function(dataJson) {
						$('#responsable-communeId').empty();
						if (dataJson.success==1) {
						    $.each(dataJson.data, function(k, d) {
		                        $('#responsable-communeId').append('<option value="' + d + '">' + k + '</option>');
		                    });
		                }
					},
					error : function(xhr, ajaxOptions, thrownError) {
						alert(xhr.status + " " + thrownError);
					}
				});
		}
	  });
});
